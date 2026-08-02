<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StoreSetting extends Model
{
    protected $fillable = [
        'name', 'description', 'logo_path',
        'facebook_url', 'instagram_url', 'tiktok_url', 'snapchat_url',
    ];

    public static function current(): self
    {
        return static::firstOrCreate([], ['name' => config('app.name')]);
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
        ]);
    }
}
