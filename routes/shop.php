<?php

use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\ContactController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\OrderTrackingController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\TermsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('shop.home');

Route::get('/contact', [ContactController::class, 'index'])->name('shop.contact');
Route::post('/contact', [ContactController::class, 'store'])->name('shop.contact.store');

Route::get('/conditions-generales', [TermsController::class, 'show'])->name('shop.terms');

Route::get('/boutique', [CatalogController::class, 'index'])->name('shop.catalog');
Route::get('/boutique/{category}', [CatalogController::class, 'index'])->name('shop.catalog.category');

Route::get('/produit/{product}', [ProductController::class, 'show'])->name('shop.product');

Route::get('/panier', [CartController::class, 'index'])->name('shop.cart');
Route::post('/panier', [CartController::class, 'store'])->name('shop.cart.add');
Route::patch('/panier/{variant}', [CartController::class, 'update'])->name('shop.cart.update');
Route::delete('/panier/{variant}', [CartController::class, 'destroy'])->name('shop.cart.remove');

Route::middleware('auth:customer')->group(function () {
    Route::get('/commande', [CheckoutController::class, 'show'])->name('shop.checkout');
    Route::post('/commande', [CheckoutController::class, 'store'])->name('shop.checkout.store');

    Route::get('/commande/{orderNumber}/confirmation', [OrderTrackingController::class, 'confirmation'])->name('shop.orders.confirmation');
    Route::get('/suivi/{orderNumber}', [OrderTrackingController::class, 'show'])->name('shop.orders.tracking');
});
