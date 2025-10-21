<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AliExpressToken extends Model
{
    protected $fillable = [
        'account',
        'access_token',
        'refresh_token',
        'expires_at',
        'refresh_expires_at',
        'account_platform',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'refresh_expires_at' => 'datetime',
    ];

    /**
     * Check if access token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if refresh token is expired
     */
    public function canRefresh(): bool
    {
        return $this->refresh_expires_at->isFuture();
    }
}
