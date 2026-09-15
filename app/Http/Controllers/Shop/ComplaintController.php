<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Notifications\AdminAlertNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        return view('shop.account.complaints', [
            'complaints' => $customer->complaints()->latest()->get(),
            'orders' => $customer->orders()->latest()->limit(20)->get(['id', 'order_number']),
        ]);
    }

    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'order_id' => ['nullable', 'exists:orders,id'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        if (! empty($data['order_id']) && ! $customer->orders()->where('id', $data['order_id'])->exists()) {
            abort(403);
        }

        $complaint = $customer->complaints()->create($data);

        AdminAlertNotification::broadcastToTeam(
            'Réclamation',
            "#{$complaint->id} — {$complaint->subject}",
            "{$customer->name} a déposé une réclamation : « {$complaint->subject} ».",
            route('complaints')
        );

        return back()->with('status', 'Votre réclamation a été envoyée, nous revenons vers vous rapidement.');
    }
}
