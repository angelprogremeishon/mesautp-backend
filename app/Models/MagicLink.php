<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MagicLink extends Model
{
    protected $fillable = ['email', 'token', 'expires_at', 'used'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }
}
