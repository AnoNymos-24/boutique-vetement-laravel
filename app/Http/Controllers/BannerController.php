<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        return view('banners.index', [
            'banners' => Banner::orderBy('sort_order')->orderByDesc('created_at')->get(),
        ]);
    }

    public function create()
    {
        return view('banners.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $data['image_path'] = $request->file('image')->store('banners', 'public');

        Banner::create($data);

        return redirect()->route('banners.index')->with('status', 'Bannière créée.');
    }

    public function edit(Banner $banner)
    {
        return view('banners.edit', ['banner' => $banner]);
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validateData($request, $banner);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image_path);
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return redirect()->route('banners.index')->with('status', 'Bannière mise à jour.');
    }

    public function toggleActive(Banner $banner)
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('status', $banner->is_active ? 'Bannière activée.' : 'Bannière désactivée.');
    }

    public function destroy(Banner $banner)
    {
        Storage::disk('public')->delete($banner->image_path);
        $banner->delete();

        return back()->with('status', 'Bannière supprimée.');
    }

    private function validateData(Request $request, ?Banner $banner = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'promo_text' => ['nullable', 'string', 'max:100'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_link' => ['nullable', 'string', 'max:255'],
            'image' => [$banner ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $request->integer('sort_order');

        return collect($data)->except('image')->all();
    }
}
