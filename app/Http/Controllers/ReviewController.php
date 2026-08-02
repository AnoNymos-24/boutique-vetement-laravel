<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::query()
            ->with(['product.images', 'customer'])
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('author_name', 'like', "%{$search}%")
                ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->rating, fn ($q, $rating) => $q->where('rating', $rating))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $published = Review::where('status', 'publie');

        $kpis = [
            'note_moyenne' => round((clone $published)->avg('rating') ?? 0, 1),
            'total' => Review::count(),
            'en_attente' => Review::where('status', 'en_attente')->count(),
            'taux_reponse' => (clone $published)->count() > 0
                ? round((clone $published)->whereNotNull('admin_reply')->count() / (clone $published)->count() * 100)
                : 0,
        ];

        return view('reviews.index', [
            'reviews' => $reviews,
            'kpis' => $kpis,
        ]);
    }

    public function create()
    {
        return view('reviews.create', [
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'author_name' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:2000'],
            'status' => ['required', 'in:en_attente,publie'],
        ]);

        Review::create($data);

        return redirect()->route('reviews')->with('status', 'Avis ajouté.');
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'publie']);

        return back()->with('status', 'Avis publié.');
    }

    public function reject(Review $review)
    {
        $review->update(['status' => 'rejete']);

        return back()->with('status', 'Avis rejeté.');
    }

    public function reply(Request $request, Review $review)
    {
        $data = $request->validate([
            'admin_reply' => ['required', 'string', 'max:1000'],
        ]);

        $review->update([
            'admin_reply' => $data['admin_reply'],
            'replied_at' => now(),
        ]);

        return back()->with('status', 'Réponse publiée.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('status', 'Avis supprimé.');
    }
}
