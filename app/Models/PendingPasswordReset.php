<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingPasswordReset extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'otp_code',
        'attempts',
        'verified_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
