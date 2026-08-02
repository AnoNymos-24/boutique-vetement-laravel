<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::orderByDesc('created_at')->get();

        $kpis = [
            'actifs' => $promoCodes->filter(fn ($p) => $p->statusLabel() === 'Actif')->count(),
            'utilisations' => $promoCodes->sum('used_count'),
            'reduction_totale' => (int) Order::whereNotNull('promo_code')
                ->where('payment_status', 'verifie')
                ->sum('discount_amount'),
            'meilleur_code' => $promoCodes->sortByDesc('used_count')->first()?->code,
        ];

        return view('marketing.index', [
            'promoCodes' => $promoCodes,
            'kpis' => $kpis,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:promo_codes,code'],
            'type' => ['required', 'in:pourcentage,montant_fixe'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['value'] = $data['type'] === 'pourcentage'
            ? min((int) round($data['value']), 100)
            : (int) round($data['value'] * 100);
        $data['min_order_amount'] = isset($data['min_order_amount']) ? (int) round($data['min_order_amount'] * 100) : null;

        PromoCode::create($data);

        return back()->with('status', "Code « {$data['code']} » créé.");
    }

    public function toggleActive(PromoCode $promoCode)
    {
        $promoCode->update(['is_active' => ! $promoCode->is_active]);

        return back()->with('status', $promoCode->is_active ? 'Code activé.' : 'Code désactivé.');
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();

        return back()->with('status', 'Code promo supprimé.');
    }
}
