<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public const STATUS_FLOW = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'sku',
        'unit_price',
        'quantity',
        'status',
        'tracking_note',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'line_total',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'options' => 'array',
            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function allowedNextStatuses(): array
    {
        return match ($this->status) {
            'pending' => ['confirmed'],
            'confirmed' => ['processing'],
            'processing' => ['shipped'],
            'shipped' => ['delivered', 'failed'],
            'failed' => ['returned'],
            default => [],
        };
    }

    public function canTransitionTo(string $status): bool
    {
        return $status === $this->status || in_array($status, $this->allowedNextStatuses(), true);
    }
}
