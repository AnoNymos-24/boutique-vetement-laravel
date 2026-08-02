<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Models\Tag;
use Illuminate\Http\Request;

class TaxonomyController extends Controller
{
    protected function modelFor(string $type): string
    {
        return match ($type) {
            'etiquettes' => Label::class,
            'tags' => Tag::class,
            default => abort(404),
        };
    }

    public function index()
    {
        return view('settings.taxonomies', [
            'labels' => Label::withCount('products')->orderBy('name')->get(),
            'tags' => Tag::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $type)
    {
        $model = $this->modelFor($type);
        $table = (new $model)->getTable();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', "unique:{$table},name"],
        ]);

        $model::create($data);

        return back()->with('status', 'Ajouté avec succès.');
    }

    public function destroy(string $type, int $id)
    {
        $model = $this->modelFor($type);
        $model::findOrFail($id)->delete();

        return back()->with('status', 'Supprimé.');
    }
}
