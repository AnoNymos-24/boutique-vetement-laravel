<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class ShopLayout extends Component
{
    public function render(): View
    {
        return view('layouts.shop');
    }
}
