<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class CategoryController extends Controller
{
    public function index()
    {
        return view('settings.categories', [
            'categories' => Category::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ]);

        Category::create($data);

        return back()->with('status', 'Catégorie ajoutée.');
    }

    public function destroy(Category $category)
    {
        try {
            $category->delete();
        } catch (QueryException) {
            return back()->with('error', "Impossible de supprimer « {$category->name} » : des produits utilisent encore cette catégorie.");
        }

        return back()->with('status', 'Catégorie supprimée.');
    }
}
