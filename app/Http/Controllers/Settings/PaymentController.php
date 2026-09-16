<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function edit()
    {
        return view('settings.payment', [
            'setting' => StoreSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'orange_money_enabled' => ['nullable', 'boolean'],
            'orange_money_number' => ['nullable', 'required_if:orange_money_enabled,1', 'string', 'max:30'],
            'wave_enabled' => ['nullable', 'boolean'],
            'wave_number' => ['nullable', 'required_if:wave_enabled,1', 'string', 'max:30'],
            'cash_on_delivery_enabled' => ['nullable', 'boolean'],
        ]);

        $setting = StoreSetting::current();

        $setting->update([
            'orange_money_enabled' => $request->boolean('orange_money_enabled'),
            'orange_money_number' => $data['orange_money_number'] ?? null,
            'wave_enabled' => $request->boolean('wave_enabled'),
            'wave_number' => $data['wave_number'] ?? null,
            'cash_on_delivery_enabled' => $request->boolean('cash_on_delivery_enabled'),
        ]);

        ActivityLog::record('settings.payment_updated', 'Moyens de paiement de la boutique mis à jour.');

        return back()->with('status', 'Moyens de paiement mis à jour.');
    }
}
