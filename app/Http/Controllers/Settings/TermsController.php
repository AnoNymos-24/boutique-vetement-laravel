<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function edit()
    {
        return view('settings.terms', [
            'setting' => StoreSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'terms_content' => ['required', 'string', 'max:50000'],
        ]);

        StoreSetting::current()->update([
            'terms_content' => $data['terms_content'],
            'terms_updated_at' => now(),
        ]);

        ActivityLog::record('settings.terms_updated', 'Conditions générales mises à jour.');

        return back()->with('status', 'Conditions générales mises à jour.');
    }
}
