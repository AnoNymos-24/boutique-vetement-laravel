<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Label;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['category', 'images', 'variants'])
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhereHas('variants', fn ($q) => $q->where('sku', 'like', "%{$search}%"))
            ))
            ->when($request->category, fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->stock, function ($q, $stock) {
                return match ($stock) {
                    'rupture' => $q->whereDoesntHave('variants', fn ($q) => $q->where('stock_quantity', '>', 0)),
                    'bas' => $q->whereHas('variants', fn ($q) => $q
                        ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                        ->where('stock_quantity', '>', 0)),
                    default => $q,
                };
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'categories' => Category::orderBy('name')->get(),
            'labels' => Label::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,active,archived'],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['exists:labels,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'images' => ['required', 'array', 'min:2'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.size' => ['nullable', 'string', 'max:20'],
            'variants.*.color' => ['nullable', 'string', 'max:30'],
            'variants.*.sku' => ['required', 'string', 'max:50', 'distinct', 'unique:product_variants,sku'],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0'],
            'variants.*.low_stock_threshold' => ['required', 'integer', 'min:0'],
        ], [
            'images.required' => "Ajoutez au moins 2 photos du produit.",
            'images.min' => "Ajoutez au moins 2 photos du produit.",
            'variants.required' => "Ajoutez au moins une variante (taille/couleur).",
            'variants.*.sku.distinct' => "Deux variantes ne peuvent pas partager le même SKU.",
        ]);

        $data['price'] = (int) round($data['price'] * 100);
        $data['cost_price'] = isset($data['cost_price']) ? (int) round($data['cost_price'] * 100) : null;
        $data['compare_at_price'] = isset($data['compare_at_price']) ? (int) round($data['compare_at_price'] * 100) : null;

        $product = Product::create(collect($data)->except(['labels', 'tags', 'images', 'variants'])->all());

        $product->labels()->sync($data['labels'] ?? []);
        $product->tags()->sync($data['tags'] ?? []);

        foreach ($data['variants'] as $variant) {
            $product->variants()->create($variant);
        }

        foreach ($request->file('images') as $index => $file) {
            $path = $file->store('products', 'public');
            $product->images()->create(['path' => $path, 'sort_order' => $index]);
        }

        return redirect()->route('products.index')->with('status', 'Produit ajouté avec succès.');
    }

    public function destroy(Product $product)
    {
        $name = $product->name;

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        ActivityLog::record('product.deleted', "Produit « {$name} » supprimé.");

        return back()->with('status', 'Produit supprimé.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate(['ids' => ['required', 'array']])['ids'];

        $products = Product::with('images')->whereIn('id', $ids)->get();

        foreach ($products as $product) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }
            $product->delete();
        }

        return back()->with('status', count($ids) . ' produit(s) supprimé(s).');
    }
}
