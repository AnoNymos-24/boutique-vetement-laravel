<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ContactMessage;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use App\Services\Cart;
use Illuminate\Support\Facades\Schema;
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

        $this->configureMailFromSettings();
        $this->shareCurrency();
    }

    /**
     * Devise partagée globalement (boutique ET back-office) plutôt que par vue précise :
     * les montants formatés à la main (paniers, KPI, exports) sont éparpillés dans des
     * dizaines de fichiers qui n'étendent pas tous le même layout composant.
     */
    private function shareCurrency(): void
    {
        try {
            if (! Schema::hasTable('store_settings') || ! Schema::hasColumn('store_settings', 'currency_code')) {
                View::share('currency', StoreSetting::currencies()['TND']);

                return;
            }

            View::share('currency', StoreSetting::current()->currencyMeta());
        } catch (\Throwable) {
            View::share('currency', StoreSetting::currencies()['TND']);
        }
    }

    /**
     * L'adresse d'envoi (Gmail) est modifiable par l'admin depuis Paramètres → E-mail
     * plutôt que codée en dur dans .env — on surcharge la config mail au runtime si
     * un compte a été renseigné en base. Sans configuration, on garde le comportement
     * .env par défaut (mailer "log" tant que rien n'est branché).
     */
    private function configureMailFromSettings(): void
    {
        // Gardes nécessaires : cette méthode tourne à CHAQUE boot, y compris avant que
        // la table existe (première installation, `migrate` lui-même) ou sans DB dispo.
        // Ne jamais laisser une config mail cassée empêcher le reste de l'appli de démarrer.
        try {
            if (! Schema::hasTable('store_settings')) {
                return;
            }

            $setting = StoreSetting::current();

            if (! $setting->hasMailConfigured()) {
                return;
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => 'smtp.gmail.com',
                'mail.mailers.smtp.port' => 587,
                'mail.mailers.smtp.encryption' => 'tls',
                'mail.mailers.smtp.username' => $setting->mail_username,
                'mail.mailers.smtp.password' => $setting->mail_password,
                'mail.from.address' => $setting->mail_username,
                'mail.from.name' => $setting->mail_from_name ?: $setting->name,
            ]);
        } catch (\Throwable) {
            // DB pas encore prête (ex. avant la première migration) — on laisse .env décider.
        }
    }
}
