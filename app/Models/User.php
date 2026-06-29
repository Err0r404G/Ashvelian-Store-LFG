<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'profile_photo_path',
        'is_restricted',
        'last_login_at',
        'email_verified_at',
        'email_changed_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_restricted' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isDeliveryManager(): bool
    {
        return $this->role === 'delivery_manager';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Get the total amount spent on non-cancelled orders.
     */
    public function getLifetimeSpentAttribute(): float
    {
        return (float) $this->orders()
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');
    }

    /**
     * Get the user's membership tier.
     */
    public function getMembershipTierAttribute(): string
    {
        $spent = $this->lifetime_spent;

        if ($spent >= 50000) {
            return 'Elite Black';
        }
        if ($spent >= 15000) {
            return 'Gold';
        }
        if ($spent >= 5000) {
            return 'Silver';
        }

        return 'Bronze';
    }

    /**
     * Get membership tier progress details.
     */
    public function getMembershipProgressAttribute(): array
    {
        $spent = $this->lifetime_spent;

        if ($spent >= 50000) {
            return [
                'current_spent' => $spent,
                'next_tier' => null,
                'target_spent' => null,
                'remaining' => 0,
                'percent' => 100,
            ];
        }

        if ($spent >= 15000) {
            $nextTier = 'Elite Black';
            $target = 50000;
            $prev = 15000;
        } elseif ($spent >= 5000) {
            $nextTier = 'Gold';
            $target = 15000;
            $prev = 5000;
        } else {
            $nextTier = 'Silver';
            $target = 5000;
            $prev = 0;
        }

        $remaining = max(0, $target - $spent);
        $range = $target - $prev;
        $progressInTier = $spent - $prev;
        $percent = min(100, max(0, ($progressInTier / $range) * 100));

        return [
            'current_spent' => $spent,
            'next_tier' => $nextTier,
            'target_spent' => $target,
            'remaining' => $remaining,
            'percent' => round($percent),
        ];
    }
}
