<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\View\View;

class TermsController extends Controller
{
    public function show(): View
    {
        return view('shop.terms', [
            'setting' => StoreSetting::current(),
        ]);
    }
}
