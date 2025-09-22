<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * La clé primaire n’est pas un entier auto-incrémenté mais un UUID.
     */
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Attributs remplissables (mass assignable).
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'email',
        'password',
        'about',
        'profile_image',
        'company',
        'job_title',
        'phone',
        'address',
        'country',
        'localisation',
        'longitude',
        'latitude',
        'social_links',
        'settings',
        'isActive',
    ];

    /**
     * Attributs cachés (exclus de la sérialisation).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attributs castés automatiquement.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'isActive' => 'boolean',
    ];

    /**
     * Boot method pour générer automatiquement un UUID.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function collectes()
    {
        return $this->hasMany(Collecte::class, 'agent_id', 'user_id');
    }
}
