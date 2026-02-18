<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Site;

class UserInvitation extends Model
{
    protected $fillable = [
        'email',
        'token',
        'role_id',
        'site_id',
        'assign_as_site_responsable',
        'inviter_id',
        'registered_at',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'registered_at' => 'datetime',
        'assign_as_site_responsable' => 'boolean',
    ];

    /**
     * Obtenir l'utilisateur qui a envoyé l'invitation
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id', 'user_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
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
