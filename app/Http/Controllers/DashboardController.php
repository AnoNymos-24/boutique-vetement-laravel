<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;

class DashboardController extends Controller
{
    public function index()
    {
        $lowStock = ProductVariant::with('product.category')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')
            ->limit(5)
            ->get();

        $outOfStock = ProductVariant::where('stock_quantity', 0)->count();

        $caJour = Order::where('payment_status', 'verifie')->whereDate('created_at', today())->sum('total');
        $caHier = Order::where('payment_status', 'verifie')->whereDate('created_at', today()->subDay())->sum('total');

        $publishedReviews = Review::where('status', 'publie');

        $kpis = [
            'ca_jour' => $caJour,
            'ca_variation' => $caHier > 0 ? round((($caJour - $caHier) / $caHier) * 100) : null,
            'commandes_a_traiter' => Order::where('status', 'en_attente')->count(),
            'alertes_stock' => $lowStock->count() + $outOfStock,
            'note_moyenne' => round((clone $publishedReviews)->avg('rating') ?? 0, 1),
            'avis_count' => (clone $publishedReviews)->count(),
        ];

        $recentProducts = Product::with(['category', 'variants'])->latest()->limit(6)->get();

        return view('dashboard', compact('kpis', 'lowStock', 'outOfStock', 'recentProducts'));
    }
}
