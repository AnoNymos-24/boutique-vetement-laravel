<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::with(['images', 'variants' => fn ($q) => $q->where('stock_quantity', '>', 0)])
            ->where('status', 'active')
            ->whereHas('variants', fn ($q) => $q->where('stock_quantity', '>', 0))
            ->orderBy('name')
            ->get();

        return view('pos.index', [
            'products' => $products,
        ]);
    }

    public function checkPromoCode(Request $request)
    {
        $promoCode = PromoCode::where('code', strtoupper((string) $request->query('code')))->first();

        // Le sous-total du panier n'est pas connu ici (c'est un simple lookup par code) :
        // on ne vérifie que le statut du code, le minimum de panier est comparé côté client.
        if (! $promoCode || ! $promoCode->isActiveAndAvailable()) {
            return response()->json(['valid' => false]);
        }

        return response()->json([
            'valid' => true,
            'type' => $promoCode->type,
            'value' => $promoCode->value,
            'min_order_amount' => $promoCode->min_order_amount,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['required', 'in:especes,orange_money,wave'],
            'promo_code' => ['nullable', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Le panier est vide.',
        ]);

        $variants = ProductVariant::with('product')
            ->whereIn('id', collect($data['items'])->pluck('product_variant_id'))
            ->get()
            ->keyBy('id');

        foreach ($data['items'] as $line) {
            $variant = $variants->get($line['product_variant_id']);

            if (! $variant || $variant->stock_quantity < $line['quantity']) {
                return back()->withInput()->with('error',
                    "Stock insuffisant pour « {$variant?->product->name} » (reste {$variant?->stock_quantity})."
                );
            }
        }

        $subtotal = 0;
        foreach ($data['items'] as $line) {
            $subtotal += $variants->get($line['product_variant_id'])->product->price * $line['quantity'];
        }

        $promoCode = null;
        if (! empty($data['promo_code'])) {
            $promoCode = PromoCode::where('code', strtoupper($data['promo_code']))->first();

            if (! $promoCode || ! $promoCode->isUsable($subtotal)) {
                return back()->withInput()->with('error', 'Ce code promo n\'est plus valide.');
            }
        }

        $order = DB::transaction(function () use ($data, $variants, $promoCode, $subtotal) {
            $now = now();

            // ?? null avant ?: : ces deux champs sont "nullable" côté validation,
            // donc absents de $data (pas juste vides) quand la vendeuse ne les
            // renseigne pas — un accès direct ($data['customer_phone']) plantait
            // alors avec "Undefined array key" au lieu de retomber sur la valeur
            // par défaut.
            $customer = null;
            if (! empty($data['customer_phone'] ?? null)) {
                $customer = Customer::findOrCreateFromOrder(
                    ($data['customer_name'] ?? null) ?: 'Client comptoir',
                    $data['customer_phone']
                );
            }

            $discount = $promoCode ? $promoCode->discountFor($subtotal) : 0;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'channel' => 'vente_manuelle',
                'customer_id' => $customer?->id,
                'customer_name' => ($data['customer_name'] ?? null) ?: 'Client comptoir',
                'customer_phone' => ($data['customer_phone'] ?? null) ?: '—',
                'status' => 'livree',
                'payment_status' => 'verifie',
                'payment_method' => $data['payment_method'],
                'promo_code' => $promoCode?->code,
                'discount_amount' => $discount,
                'total' => 0,
                'confirmed_at' => $now,
                'prepared_at' => $now,
                'shipped_at' => $now,
                'delivered_at' => $now,
            ]);

            foreach ($data['items'] as $line) {
                $variant = $variants->get($line['product_variant_id']);
                $product = $variant->product;
                $quantity = $line['quantity'];
                $lineSubtotal = $product->price * $quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'variant_label' => $variant->label(),
                    'unit_price' => $product->price,
                    'unit_cost' => $product->cost_price ?? 0,
                    'quantity' => $quantity,
                    'subtotal' => $lineSubtotal,
                ]);

                $newStock = $variant->stock_quantity - $quantity;

                $variant->stockMovements()->create([
                    'user_id' => Auth::id(),
                    'type' => 'sortie',
                    'quantity_change' => -$quantity,
                    'stock_after' => $newStock,
                    'note' => "Vente manuelle {$order->order_number}",
                ]);

                $variant->update(['stock_quantity' => $newStock]);
            }

            $order->update(['total' => $subtotal - $discount]);

            $promoCode?->increment('used_count');

            return $order;
        });

        return redirect()->route('pos')->with('status', "Vente {$order->order_number} enregistrée : {$order->totalFormatted()}.");
    }
}
