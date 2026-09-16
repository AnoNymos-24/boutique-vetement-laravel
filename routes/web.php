<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Settings\CategoryController;
use App\Http\Controllers\Settings\IdentityController;
use App\Http\Controllers\Settings\MailController;
use App\Http\Controllers\Settings\PaymentController;
use App\Http\Controllers\Settings\TermsController;
use App\Http\Controllers\Settings\TaxonomyController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('no-cache')->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('produits', ProductController::class)->parameters(['produits' => 'product'])->names('products');
        Route::delete('/produits-bulk', [ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');

        Route::get('/stock', [StockController::class, 'index'])->name('stock');
        Route::get('/stock/mouvements', [StockController::class, 'history'])->name('stock.history');
        Route::post('/stock/mouvements', [StockController::class, 'store'])->name('stock.movements.store');

        Route::get('/commandes', [OrderController::class, 'index'])->name('orders');
        Route::get('/commandes/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/commandes/{order}/avancer', [OrderController::class, 'advanceStatus'])->name('orders.advance');
        Route::patch('/commandes/{order}/annuler', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::patch('/commandes/{order}/paiement', [OrderController::class, 'updatePayment'])->name('orders.payment');

        Route::get('/vente-manuelle', [PosController::class, 'index'])->name('pos');
        Route::post('/vente-manuelle', [PosController::class, 'store'])->name('pos.store');
        Route::get('/vente-manuelle/code-promo', [PosController::class, 'checkPromoCode'])->name('pos.check-promo');
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance');
        Route::get('/finance/export', [FinanceController::class, 'export'])->name('finance.export');
        Route::get('/finance/rapport', [FinanceController::class, 'report'])->name('finance.report');
        Route::post('/finance/depenses', [FinanceController::class, 'storeExpense'])->name('finance.expenses.store');
        Route::delete('/finance/depenses/{expense}', [FinanceController::class, 'destroyExpense'])->name('finance.expenses.destroy');

        Route::get('/clients', [ClientController::class, 'index'])->name('clients');
        Route::get('/clients/{customer}', [ClientController::class, 'show'])->name('clients.show');
        Route::patch('/clients/{customer}/note', [ClientController::class, 'updateNote'])->name('clients.note');
        Route::get('/avis', [ReviewController::class, 'index'])->name('reviews');
        Route::get('/avis/creer', [ReviewController::class, 'create'])->name('reviews.create');
        Route::post('/avis', [ReviewController::class, 'store'])->name('reviews.store');
        Route::patch('/avis/{review}/approuver', [ReviewController::class, 'approve'])->name('reviews.approve');
        Route::patch('/avis/{review}/rejeter', [ReviewController::class, 'reject'])->name('reviews.reject');
        Route::post('/avis/{review}/reponse', [ReviewController::class, 'reply'])->name('reviews.reply');
        Route::delete('/avis/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::get('/marketing', [MarketingController::class, 'index'])->name('marketing');
        Route::post('/marketing', [MarketingController::class, 'store'])->name('marketing.store');
        Route::patch('/marketing/{promoCode}/activer', [MarketingController::class, 'toggleActive'])->name('marketing.toggle');
        Route::delete('/marketing/{promoCode}', [MarketingController::class, 'destroy'])->name('marketing.destroy');

        Route::resource('bannieres', BannerController::class)->except(['show'])->parameters(['bannieres' => 'banner'])->names('banners');
        Route::patch('/bannieres/{banner}/activer', [BannerController::class, 'toggleActive'])->name('banners.toggle');

        Route::get('/reclamations', [ComplaintController::class, 'index'])->name('complaints');
        Route::patch('/reclamations/{complaint}/statut', [ComplaintController::class, 'updateStatus'])->name('complaints.status');
        Route::post('/reclamations/{complaint}/reponse', [ComplaintController::class, 'reply'])->name('complaints.reply');
        Route::post('/reclamations/{complaint}/remboursement', [ComplaintController::class, 'refund'])->name('complaints.refund');

        Route::get('/messages', [ContactMessageController::class, 'index'])->name('contact-messages');
        Route::patch('/messages/{contactMessage}/statut', [ContactMessageController::class, 'updateStatus'])->name('contact-messages.status');
        Route::post('/messages/{contactMessage}/reponse', [ContactMessageController::class, 'reply'])->name('contact-messages.reply');

        Route::middleware('admin')->group(function () {
            Route::resource('utilisateurs', UserController::class)->except(['show'])->parameters(['utilisateurs' => 'user'])->names('users');
            Route::patch('/utilisateurs/{user}/activer', [UserController::class, 'toggleActive'])->name('users.toggle');

            Route::get('/journal', [ActivityLogController::class, 'index'])->name('activity-log.index');
        });

        Route::prefix('parametres')->name('settings.')->group(function () {
            Route::get('/identite', [IdentityController::class, 'edit'])->name('identity.edit');
            Route::put('/identite', [IdentityController::class, 'update'])->name('identity.update');

            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

            Route::get('/etiquettes', [TaxonomyController::class, 'index'])->name('taxonomies.index');
            Route::post('/etiquettes/{type}', [TaxonomyController::class, 'store'])->name('taxonomies.store');
            Route::delete('/etiquettes/{type}/{id}', [TaxonomyController::class, 'destroy'])->name('taxonomies.destroy');

            // Identifiants Gmail sensibles — réservé à l'administrateur, contrairement au
            // reste de Paramètres (identité/catégories/étiquettes) ouvert aux vendeurs.
            Route::middleware('admin')->group(function () {
                Route::get('/email', [MailController::class, 'edit'])->name('mail.edit');
                Route::put('/email', [MailController::class, 'update'])->name('mail.update');
                Route::post('/email/test', [MailController::class, 'sendTest'])->name('mail.test');

                // Numéros Wave/Orange Money vers lesquels les clientes envoient de
                // l'argent — un vendeur malveillant qui les changerait détournerait
                // directement les paiements, donc réservé à l'admin comme l'e-mail.
                Route::get('/paiement', [PaymentController::class, 'edit'])->name('payment.edit');
                Route::put('/paiement', [PaymentController::class, 'update'])->name('payment.update');

                // Conditions générales : document juridique engageant la boutique —
                // seul l'admin doit pouvoir le modifier, pas n'importe quel vendeur.
                Route::get('/conditions', [TermsController::class, 'edit'])->name('terms.edit');
                Route::put('/conditions', [TermsController::class, 'update'])->name('terms.update');
            });
        });
    });

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';
});

require __DIR__.'/customer-auth.php';
require __DIR__.'/shop.php';
