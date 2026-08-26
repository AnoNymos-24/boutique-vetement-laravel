<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $banners = Banner::active()->orderBy('sort_order')->get();

        $newArrivals = Product::with(['category', 'images', 'variants'])
            ->where('status', 'active')
            ->whereHas('variants', fn ($q) => $q->where('stock_quantity', '>', 0))
            ->latest()
            ->limit(8)
            ->get();

        $categories = Category::withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->having('products_count', '>', 0)
            ->get()
            ->map(function ($category) {
                $category->cover = Product::where('category_id', $category->id)
                    ->where('status', 'active')
                    ->with('images')
                    ->first()?->images->first()?->url();

                return $category;
            });

        $reviews = Review::with('product')
            ->where('status', 'publie')
            ->where('rating', '>=', 4)
            ->latest()
            ->limit(6)
            ->get();

        return view('shop.home', [
            'banners' => $banners,
            'newArrivals' => $newArrivals,
            'categories' => $categories,
            'reviews' => $reviews,
        ]);
    }
}
