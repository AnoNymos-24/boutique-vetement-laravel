<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ContactMessage;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use App\Services\Cart;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.navigation-items', function ($view) {
            $view->with('outOfStockCount', ProductVariant::where('stock_quantity', 0)->count());
            $view->with('newComplaintsCount', Complaint::where('status', 'nouveau')->count());
            $view->with('newMessagesCount', ContactMessage::where('status', 'nouveau')->count());
        });

        View::composer(['layouts.app', 'layouts.guest', 'layouts.shop', 'pos.index'], function ($view) {
            $view->with('storeName', StoreSetting::current()->name);
        });

        View::composer('layouts.shop', function ($view) {
            $view->with('shopCartCount', app(Cart::class)->count());
            $view->with('shopNavCategories', Category::orderBy('name')->limit(5)->get());
            $view->with('storeSetting', StoreSetting::current());
        });
    }
}
