<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = Complaint::query()
            ->with(['customer', 'order'])
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

        return back()->with('status', 'Réponse enregistrée.');
    }
}
