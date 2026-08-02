<?php

use App\Http\Controllers\Shop\AccountController;
use App\Http\Controllers\Shop\AddressController;
use App\Http\Controllers\Shop\Auth\AuthenticatedCustomerSessionController;
use App\Http\Controllers\Shop\Auth\CustomerPasswordResetLinkController;
use App\Http\Controllers\Shop\Auth\NewCustomerPasswordController;
use App\Http\Controllers\Shop\Auth\RegisteredCustomerController;
use App\Http\Controllers\Shop\ReviewController;
use App\Http\Controllers\Shop\WishlistController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:customer')->group(function () {
    Route::get('inscription', [RegisteredCustomerController::class, 'create'])->name('customer.register');
    Route::post('inscription', [RegisteredCustomerController::class, 'store']);

    Route::get('connexion', [AuthenticatedCustomerSessionController::class, 'create'])->name('customer.login');
    Route::post('connexion', [AuthenticatedCustomerSessionController::class, 'store']);

    Route::get('mot-de-passe-oublie', [CustomerPasswordResetLinkController::class, 'create'])->name('customer.password.request');
    Route::post('mot-de-passe-oublie', [CustomerPasswordResetLinkController::class, 'store'])->name('customer.password.email');

    Route::get('reinitialiser-mot-de-passe/{token}', [NewCustomerPasswordController::class, 'create'])->name('customer.password.reset');
    Route::post('reinitialiser-mot-de-passe', [NewCustomerPasswordController::class, 'store'])->name('customer.password.store');
});

Route::middleware('auth:customer')->group(function () {
    Route::post('deconnexion', [AuthenticatedCustomerSessionController::class, 'destroy'])->name('customer.logout');

    Route::get('mon-compte', [AccountController::class, 'show'])->name('customer.account');
    Route::patch('mon-compte/profil', [AccountController::class, 'updateProfile'])->name('customer.account.profile');

    Route::get('mon-compte/commandes', [AccountController::class, 'orders'])->name('customer.account.orders');
    Route::get('mon-compte/commandes/{order}', [AccountController::class, 'orderShow'])->name('customer.account.orders.show');

    Route::get('mon-compte/adresses', [AddressController::class, 'index'])->name('customer.addresses');
    Route::post('mon-compte/adresses', [AddressController::class, 'store'])->name('customer.addresses.store');
    Route::patch('mon-compte/adresses/{address}', [AddressController::class, 'update'])->name('customer.addresses.update');
    Route::delete('mon-compte/adresses/{address}', [AddressController::class, 'destroy'])->name('customer.addresses.destroy');

    Route::get('mon-compte/favoris', [WishlistController::class, 'index'])->name('customer.wishlist');
    Route::post('favoris/{product}', [WishlistController::class, 'toggle'])->name('customer.wishlist.toggle');

    Route::get('mon-compte/avis', [ReviewController::class, 'index'])->name('customer.reviews');
    Route::post('mon-compte/avis', [ReviewController::class, 'store'])->name('customer.reviews.store');
});
