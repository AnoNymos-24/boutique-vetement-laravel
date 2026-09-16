<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IdentityController extends Controller
{
    public function edit()
    {
        return view('settings.identity', [
            'setting' => StoreSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'currency_code' => ['required', 'in:' . implode(',', array_keys(StoreSetting::currencies()))],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'snapchat_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'google_maps_url' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $setting = StoreSetting::current();
        $currencyChanged = $data['currency_code'] !== $setting->currency_code;

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('branding', 'public');
        } elseif ($request->boolean('remove_logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $data['logo_path'] = null;
        }

        $setting->update(collect($data)->except(['logo', 'remove_logo'])->all());

        if ($currencyChanged) {
            ActivityLog::record('settings.currency_changed', "Devise changée en {$data['currency_code']}.");
        }

        return back()->with('status', 'Identité de la boutique mise à jour.');
    }
}
