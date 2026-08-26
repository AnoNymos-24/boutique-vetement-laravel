<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'promo_text', 'button_text', 'button_link',
        'image_path', 'starts_at', 'expires_at', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'expires_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', now()));
    }

    public function imageUrl(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }

    public function isScheduled(): bool
    {
        return $this->starts_at !== null && $this->starts_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Inactive';
        }

        if ($this->isExpired()) {
            return 'Expirée';
        }

        if ($this->isScheduled()) {
            return 'Programmée';
        }

        return 'Active';
    }
}
