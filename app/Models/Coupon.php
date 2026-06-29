<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'minimum_order_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isValidFor(float $subtotal): bool
    {
        if (! $this->is_active || $subtotal < (float) $this->minimum_order_amount) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        $now = now();

        return (! $this->starts_at || $this->starts_at <= $now)
            && (! $this->expires_at || $this->expires_at >= $now);
    }

    public function discountFor(float $subtotal): float
    {
        if (! $this->isValidFor($subtotal)) {
            return 0;
        }

        return $this->type === 'percent'
            ? round($subtotal * ((float) $this->value / 100), 2)
            : min((float) $this->value, $subtotal);
    }
}
