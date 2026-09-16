<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Notifications\ContactMessageReplyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $messages = ContactMessage::query()
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('subject', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $kpis = [
            'nouveaux' => ContactMessage::where('status', 'nouveau')->count(),
            'traites' => ContactMessage::where('status', 'traite')->count(),
            'total' => ContactMessage::count(),
        ];

        return view('contact-messages.index', ['messages' => $messages, 'kpis' => $kpis]);
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage)
    {
        $data = $request->validate(['status' => ['required', 'in:nouveau,lu,traite']]);

        $contactMessage->update($data);

        return back()->with('status', 'Statut mis à jour.');
    }

    public function reply(Request $request, ContactMessage $contactMessage)
    {
        $data = $request->validate(['admin_reply' => ['required', 'string', 'max:2000']]);

        $contactMessage->update([
            'admin_reply' => $data['admin_reply'],
            'replied_at' => now(),
            'status' => 'traite',
        ]);

        ActivityLog::record('message.replied', "Réponse envoyée au message #{$contactMessage->id}.", $contactMessage);

        // Un visiteur du formulaire de contact n'a pas de compte donc pas d'espace où
        // consulter la réponse : l'e-mail est le seul canal de retour possible pour lui.
        $mailFailed = false;
        try {
            Notification::route('mail', $contactMessage->email)
                ->notify(new ContactMessageReplyNotification($contactMessage));
        } catch (\Throwable $e) {
            $mailFailed = true;
            Log::warning("Échec d'envoi de la réponse au message #{$contactMessage->id} : {$e->getMessage()}");
        }

        return back()->with(
            $mailFailed ? 'error' : 'status',
            $mailFailed
                ? "Réponse enregistrée, mais l'e-mail n'a pas pu être envoyé au client (vérifiez la configuration dans Paramètres → E-mail)."
                : 'Réponse enregistrée et envoyée par e-mail au client.'
        );
    }
}
