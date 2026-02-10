<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvitation extends Model
{
    protected $fillable = [
        'email',
        'token',
        'role_id',
        'inviter_id',
        'registered_at',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    /**
     * Obtenir l'utilisateur qui a envoyé l'invitation
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id', 'user_id');
    }

    /**
     * Scope pour les invitations actives (non expirées et non enregistrées)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('registered_at')
                     ->where('expires_at', '>', now());
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
}
