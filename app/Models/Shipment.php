<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'delivery_manager_id',
        'tracking_number',
        'carrier',
        'status',
        'tracking_notes',
        'shipped_at',
        'estimated_delivery_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'estimated_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'delivery_manager_id');
    }

    public function events()
    {
        return $this->hasMany(ShipmentEvent::class);
    }
}
