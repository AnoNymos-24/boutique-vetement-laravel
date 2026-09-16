<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Complaint;
use App\Models\Refund;
use App\Notifications\ComplaintRefundNotification;
use App\Notifications\ComplaintReplyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = Complaint::query()
            ->with(['customer', 'order', 'refund'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('subject', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $kpis = [
            'nouvelles' => Complaint::where('status', 'nouveau')->count(),
            'en_cours' => Complaint::where('status', 'en_cours')->count(),
            'resolues' => Complaint::where('status', 'resolu')->count(),
            'total' => Complaint::count(),
        ];

        return view('complaints.index', ['complaints' => $complaints, 'kpis' => $kpis]);
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        $data = $request->validate(['status' => ['required', 'in:nouveau,en_cours,resolu,ferme']]);

        $complaint->update($data);

        ActivityLog::record('complaint.status_changed', "Réclamation #{$complaint->id} → {$complaint->statusLabel()}.", $complaint);

        return back()->with('status', 'Statut mis à jour.');
    }

    public function reply(Request $request, Complaint $complaint)
    {
        $data = $request->validate(['admin_reply' => ['required', 'string', 'max:2000']]);

        $complaint->update([
            'admin_reply' => $data['admin_reply'],
            'replied_at' => now(),
            'status' => $complaint->status === 'nouveau' ? 'en_cours' : $complaint->status,
        ]);

        ActivityLog::record('complaint.replied', "Réponse envoyée sur la réclamation #{$complaint->id}.", $complaint);

        try {
            $complaint->customer?->notify(new ComplaintReplyNotification($complaint));
        } catch (\Throwable $e) {
            Log::warning("Échec d'envoi de la réponse à la réclamation #{$complaint->id} : {$e->getMessage()}");
        }

        return back()->with('status', 'Réponse envoyée par e-mail.');
    }

    /**
     * Enregistre un remboursement déjà effectué manuellement (Orange Money/Wave/
     * espèces — cette app ne débite/recrédite rien elle-même) pour l'un des motifs
     * de l'article 9.1 des CGU. Un seul remboursement par réclamation ; nécessite
     * une commande liée (impossible de rembourser un montant sans commande de
     * référence pour son prix/moyen de paiement).
     */
    public function refund(Request $request, Complaint $complaint)
    {
        if (! $complaint->order_id) {
            return back()->with('error', 'Cette réclamation n\'est liée à aucune commande : impossible d\'enregistrer un remboursement.');
        }

        if ($complaint->hasRefund()) {
            return back()->with('error', 'Un remboursement a déjà été enregistré pour cette réclamation.');
        }

        // Motifs proposés ici volontairement limités à ceux qui naissent d'une
        // réclamation — "annulation_avant_expedition" et "indisponibilite" (CGU
        // 9.1) passent par d'autres parcours (annulation de commande), pas
        // celui-ci.
        $data = $request->validate([
            'reason' => ['required', 'in:produit_defectueux,non_conforme,erreur_commande,retour_reclamation'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        // Plafonné au total de la commande moins ce qui a déjà été remboursé
        // dessus (via d'autres réclamations) — sans ça, une erreur de saisie ou
        // un montant malveillant pouvait gonfler arbitrairement la ligne
        // "Remboursements" du module Finance, sans rapport avec l'argent
        // réellement rendu.
        $amountCents = (int) round($data['amount'] * 100);
        $alreadyRefunded = (int) Refund::where('order_id', $complaint->order_id)->sum('amount');
        $refundable = $complaint->order->total - $alreadyRefunded;

        if ($amountCents > $refundable) {
            return back()->withInput()->with('error',
                'Montant trop élevé : il reste au maximum ' . \App\Models\StoreSetting::current()->formatMoney($refundable) . ' remboursable sur cette commande.'
            );
        }

        $refund = Refund::create([
            'order_id' => $complaint->order_id,
            'complaint_id' => $complaint->id,
            'processed_by' => Auth::id(),
            'reason' => $data['reason'],
            'amount' => $amountCents,
            'method' => $data['method'] ?? null,
            'note' => $data['note'] ?? null,
            'processed_at' => now(),
        ]);

        $complaint->update(['status' => 'resolu']);

        ActivityLog::record('complaint.refunded', "Remboursement de {$refund->amountFormatted()} enregistré sur la réclamation #{$complaint->id}.", $complaint);

        try {
            $complaint->customer?->notify(new ComplaintRefundNotification($refund));
        } catch (\Throwable $e) {
            Log::warning("Échec d'envoi de la confirmation de remboursement (réclamation #{$complaint->id}) : {$e->getMessage()}");
        }

        return back()->with('status', 'Remboursement enregistré et confirmé au client par e-mail.');
    }
}
