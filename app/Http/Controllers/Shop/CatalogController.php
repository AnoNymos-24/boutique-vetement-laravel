<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request, ?Category $category = null): View
    {
        $products = Product::query()
            ->with(['category', 'images', 'variants'])
            ->where('status', 'active')
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->size, fn ($q, $size) => $q->whereHas('variants', fn ($q) => $q
                ->where('size', $size)->where('stock_quantity', '>', 0)))
            ->when($request->color, fn ($q, $color) => $q->whereHas('variants', fn ($q) => $q
                ->where('color', $color)->where('stock_quantity', '>', 0)))
            ->when($request->price_max, fn ($q, $max) => $q->where('price', '<=', (int) $max * 100))
            ->when($request->boolean('in_stock'), fn ($q) => $q->whereHas('variants', fn ($q) => $q
                ->where('stock_quantity', '>', 0)))
            ->when($request->sort, function ($q, $sort) {
                return match ($sort) {
                    'prix_asc' => $q->orderBy('price'),
                    'prix_desc' => $q->orderByDesc('price'),
                    default => $q->latest(),
                };
            }, fn ($q) => $q->latest())
            ->paginate(12)
            ->withQueryString();

        return view('shop.catalog', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'activeCategory' => $category,
            'sizes' => ProductVariant::whereNotNull('size')->distinct()->orderBy('size')->pluck('size'),
            'colors' => ProductVariant::whereNotNull('color')->distinct()->orderBy('color')->pluck('color'),
        ]);
    }
}
