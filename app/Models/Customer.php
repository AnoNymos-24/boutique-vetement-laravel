<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'phone', 'email', 'address', 'note', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function ordersCount(): int
    {
        return $this->orders_count ?? $this->orders()->count();
    }

    public function totalSpent(): int
    {
        return $this->total_spent ?? (int) $this->orders()->where('payment_status', 'verifie')->sum('total');
    }

    public function totalSpentFormatted(): string
    {
        return number_format($this->totalSpent() / 100, 2, ',', ' ') . ' €';
    }

    public function segment(): string
    {
        $count = $this->ordersCount();

        if ($count >= 5) {
            return 'vip';
        }

        if ($count >= 2) {
            return 'regulier';
        }

        return 'nouveau';
    }

    public function segmentLabel(): string
    {
        return match ($this->segment()) {
            'vip' => 'VIP',
            'regulier' => 'Régulier',
            default => 'Nouveau',
        };
    }

    public static function findOrCreateFromOrder(string $name, string $phone, ?string $address = null): self
    {
        $customer = self::firstOrNew(['phone' => $phone]);
        $customer->name = $name;
        $customer->address = $address ?: $customer->address;
        $customer->save();

        return $customer;
    }
}
