<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StoreSetting extends Model
{
    protected $fillable = [
        'name', 'description', 'logo_path', 'currency_code', 'address',
        'facebook_url', 'instagram_url', 'tiktok_url', 'snapchat_url',
        'youtube_url', 'linkedin_url', 'google_maps_url',
        'mail_username', 'mail_password', 'mail_from_name',
        'orange_money_number', 'orange_money_enabled',
        'wave_number', 'wave_enabled',
        'cash_on_delivery_enabled',
        'terms_content', 'terms_updated_at',
    ];

    protected $hidden = ['mail_password'];

    // Un seul enregistrement par requête : évite une requête DB à chaque appel de
    // current() (formatage de prix appelé potentiellement des dizaines de fois
    // par page, ex. une grille de produits).
    protected static ?self $cached = null;

    protected function casts(): array
    {
        return [
            // Chiffré avec APP_KEY, jamais stocké en clair en base — même si la
            // base n'est pas censée être exposée, c'est un mot de passe réel (Gmail).
            'mail_password' => 'encrypted',
            'orange_money_enabled' => 'boolean',
            'wave_enabled' => 'boolean',
            'cash_on_delivery_enabled' => 'boolean',
            'terms_updated_at' => 'datetime',
        ];
    }

    public static function current(): self
    {
        // Les valeurs par défaut des colonnes (orange_money_enabled, etc.) sont
        // fixées à true au niveau de la migration, mais firstOrCreate() ne
        // relit jamais la ligne après l'INSERT : sur une base toute neuve, la
        // toute première requête de tout le site récupérait un objet en
        // mémoire avec ces booléens à null tant qu'aucun rechargement complet
        // n'avait eu lieu — ce qui pouvait afficher "aucun moyen de paiement
        // disponible" au tout premier chargement après un déploiement. On les
        // fixe donc explicitement ici pour que l'objet créé soit toujours
        // fidèle à ce que la base contient réellement.
        return static::$cached ??= static::firstOrCreate([], [
            'name' => config('app.name'),
            'orange_money_enabled' => true,
            'wave_enabled' => true,
            'cash_on_delivery_enabled' => true,
        ]);
    }

    public function hasMailConfigured(): bool
    {
        return ! empty($this->mail_username) && ! empty($this->mail_password);
    }

    /**
     * Devises proposées à l'admin dans Paramètres → Identité. Le nombre de décimales
     * n'affecte que l'affichage : les montants restent stockés en centimes/millimes
     * (÷100) partout dans l'appli, quelle que soit la devise choisie.
     */
    public static function currencies(): array
    {
        return [
            'XOF' => ['label' => 'Franc CFA — UEMOA (XOF)', 'symbol' => 'FCFA', 'decimals' => 0],
            'TND' => ['label' => 'Dinar tunisien (TND)', 'symbol' => 'DT', 'decimals' => 3],
            'DZD' => ['label' => 'Dinar algérien (DZD)', 'symbol' => 'DA', 'decimals' => 3],
            'MAD' => ['label' => 'Dirham marocain (MAD)', 'symbol' => 'DH', 'decimals' => 2],
            'EUR' => ['label' => 'Euro (EUR)', 'symbol' => '€', 'decimals' => 2],
            'USD' => ['label' => 'Dollar américain (USD)', 'symbol' => '$', 'decimals' => 2],
            'GBP' => ['label' => 'Livre sterling (GBP)', 'symbol' => '£', 'decimals' => 2],
        ];
    }

    public function currencyMeta(): array
    {
        return static::currencies()[$this->currency_code] ?? static::currencies()['TND'];
    }

    public function formatMoney(int $cents): string
    {
        $meta = $this->currencyMeta();

        return number_format($cents / 100, $meta['decimals'], ',', ' ') . ' ' . $meta['symbol'];
    }

    /**
     * Moyens de paiement proposés au client sur le tunnel d'achat en ligne. Chaque
     * entrée décrit ce dont l'admin peut disposer (numéro à afficher, étapes à suivre)
     * — la disponibilité réelle (activé/désactivé) et les numéros sont propres à
     * l'instance via paymentMethodEnabled()/paymentMethodNumber(), jamais codés en dur
     * ici, pour rester cohérent avec le pattern currencies()/currencyMeta().
     */
    public static function paymentMethods(): array
    {
        return [
            'orange_money' => ['label' => 'Orange Money', 'requires_number' => true, 'requires_reference' => true],
            'wave' => ['label' => 'Wave', 'requires_number' => true, 'requires_reference' => true],
            'especes' => ['label' => 'Paiement à la livraison', 'requires_number' => false, 'requires_reference' => false],
        ];
    }

    public function paymentMethodEnabled(string $method): bool
    {
        return match ($method) {
            'orange_money' => (bool) $this->orange_money_enabled,
            'wave' => (bool) $this->wave_enabled,
            'especes' => (bool) $this->cash_on_delivery_enabled,
            default => false,
        };
    }

    public function paymentMethodNumber(string $method): ?string
    {
        return match ($method) {
            'orange_money' => $this->orange_money_number,
            'wave' => $this->wave_number,
            default => null,
        };
    }

    /**
     * Liste filtrée pour le tunnel d'achat : seuls les moyens activés par l'admin,
     * et pour ceux qui ont besoin d'un numéro, seulement si un numéro est renseigné
     * (un moyen "activé" sans numéro configuré ne doit jamais s'afficher au client —
     * il n'aurait aucun moyen de payer).
     */
    public function enabledPaymentMethods(): array
    {
        return collect(static::paymentMethods())
            ->filter(function ($meta, $key) {
                if (! $this->paymentMethodEnabled($key)) {
                    return false;
                }

                return ! $meta['requires_number'] || filled($this->paymentMethodNumber($key));
            })
            ->all();
    }

    /**
     * Résumé "X, Y et Z" des moyens de paiement activés, pour les mentions génériques
     * hors du tunnel d'achat (bandeau d'annonce, pied de page, accroche d'accueil) —
     * évite de citer en dur un moyen que l'admin a désactivé dans Paramètres → Paiement.
     */
    public function enabledPaymentMethodsLabel(): string
    {
        $labels = collect($this->enabledPaymentMethods())->pluck('label')->all();

        return match (count($labels)) {
            0 => 'nous contacter',
            1 => $labels[0],
            default => implode(', ', array_slice($labels, 0, -1)) . ' ou ' . end($labels),
        };
    }

    public function termsHtml(): string
    {
        return \App\Support\SimpleMarkdown::toHtml($this->terms_content);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function socialLinks(): array
    {
        return array_filter([
            'facebook' => $this->facebook_url,
            'instagram' => $this->instagram_url,
            'tiktok' => $this->tiktok_url,
            'snapchat' => $this->snapchat_url,
            'youtube' => $this->youtube_url,
            'linkedin' => $this->linkedin_url,
            'map-pin' => $this->google_maps_url,
        ]);
    }

    /**
     * ucfirst($network) suffit pour la plupart des clés ("facebook" → "Facebook")
     * mais pas pour "map-pin" (Google Maps, nom de clé = nom d'icône, pas un
     * nom de réseau) ni pour la casse interne de "YouTube"/"LinkedIn" — utilisé
     * par le pied de page de la boutique pour l'attribut title de chaque icône.
     */
    public function socialLinkLabel(string $network): string
    {
        return match ($network) {
            'youtube' => 'YouTube',
            'linkedin' => 'LinkedIn',
            'map-pin' => 'Google Maps',
            default => ucfirst($network),
        };
    }
}
