<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

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

        return back()->with('status', 'Réponse enregistrée.');
    }
}
