<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingRegistration extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'otp_code',
        'attempts',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function getDestinationAttribute(): string
    {
        return $this->email;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
