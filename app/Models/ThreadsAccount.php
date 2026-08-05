<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThreadsAccount extends Model
{
    protected $fillable = [
        'club_id',
        'threads_username',
        'threads_user_id',
        'access_token',
        'is_active',
        'token_expires_at',
        'last_post_at',
        'refresh_token',
        'oauth_state',
        'connection_method',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'token_expires_at' => 'datetime',
        'last_post_at' => 'datetime',
    ];

    /**
     * Relationship: Threads account belongs to a club
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Check if token is still valid
     */
    public function isTokenValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->token_expires_at && $this->token_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Get decrypted access token
     */
    public function getDecryptedToken(): string
    {
        return decrypt($this->access_token);
    }

    /**
     * Set encrypted access token
     */
    public function setAccessTokenAttribute(string $token): void
    {
        $this->attributes['access_token'] = encrypt($token);
    }
}
