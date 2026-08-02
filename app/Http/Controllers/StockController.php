<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $variants = ProductVariant::query()
            ->with(['product.category', 'product.images'])
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('sku', 'like', "%{$search}%")
                ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ))
            ->when($request->category, fn ($q, $categoryId) => $q->whereHas('product', fn ($q) => $q->where('category_id', $categoryId)))
            ->when($request->stock, function ($q, $stock) {
                return match ($stock) {
                    'rupture' => $q->where('stock_quantity', 0),
                    'bas' => $q->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->where('stock_quantity', '>', 0),
                    default => $q,
                };
            })
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->orderBy('products.name')
            ->select('product_variants.*')
            ->paginate(12)
            ->withQueryString();

        $kpis = [
            'references' => ProductVariant::count(),
            'ruptures' => ProductVariant::where('stock_quantity', 0)->count(),
            'stock_bas' => ProductVariant::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->where('stock_quantity', '>', 0)->count(),
            'valeur_stock' => (int) ProductVariant::join('products', 'products.id', '=', 'product_variants.product_id')
                ->sum(DB::raw('products.price * product_variants.stock_quantity')),
        ];

        return view('stock.index', [
            'variants' => $variants,
            'categories' => Category::orderBy('name')->get(),
            'kpis' => $kpis,
        ]);
    }

    public function history(Request $request)
    {
        $movements = StockMovement::query()
            ->with(['productVariant.product', 'user'])
            ->when($request->product_variant_id, fn ($q, $variantId) => $q->where('product_variant_id', $variantId))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('stock.history', [
            'movements' => $movements,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'type' => ['required', 'in:entree,sortie,ajustement'],
            'quantity' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $variant = ProductVariant::with('product')->findOrFail($data['product_variant_id']);

        if ($data['type'] === 'sortie' && $data['quantity'] > $variant->stock_quantity) {
            return back()->with('error', "Impossible de retirer {$data['quantity']} unités : il n'en reste que {$variant->stock_quantity}.");
        }

        $newStock = match ($data['type']) {
            'entree' => $variant->stock_quantity + $data['quantity'],
            'sortie' => $variant->stock_quantity - $data['quantity'],
            'ajustement' => $data['quantity'],
        };

        DB::transaction(function () use ($variant, $data, $newStock) {
            $variant->stockMovements()->create([
                'user_id' => Auth::id(),
                'type' => $data['type'],
                'quantity_change' => $newStock - $variant->stock_quantity,
                'stock_after' => $newStock,
                'note' => $data['note'] ?? null,
            ]);

            $variant->update(['stock_quantity' => $newStock]);
        });

        $label = $variant->product->name . ' (' . $variant->label() . ')';

        return back()->with('status', "Stock de « {$label} » mis à jour : {$newStock} unité(s).");
    }
}
