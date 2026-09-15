<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Notifications\AdminAlertNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        return view('shop.contact', ['customer' => $customer]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $data['customer_id'] = Auth::guard('customer')->id();

        $message = ContactMessage::create($data);

        AdminAlertNotification::broadcastToTeam(
            'Message',
            "#{$message->id} — {$message->subject}",
            "{$message->name} vous a envoyé un message : « {$message->subject} ».",
            route('contact-messages')
        );

        return back()->with('status', 'Votre message a été envoyé, nous vous répondons rapidement.');
    }
}
