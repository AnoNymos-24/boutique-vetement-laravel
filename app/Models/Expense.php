<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = ['category', 'label', 'amount', 'expense_date', 'note', 'user_id'];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'expense_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'livraison' => 'Livraison',
            'hebergement' => 'Hébergement',
            'marketing' => 'Marketing',
            'commissions' => 'Commissions de paiement',
            'salaires' => 'Salaires',
            'autre' => 'Autre',
        };
    }

    public function amountFormatted(): string
    {
        return StoreSetting::current()->formatMoney($this->amount);
    }
}
