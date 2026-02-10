<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Site;
use App\Models\Facture;
use App\Models\Incident;
use App\Models\Observation;
use App\Models\Validation;
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
    // Dans $fillable
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
        'site_id', // Site principal (legacy ou par défaut)
        'social_links',
        'settings',
        'isActive',
        'availability_status',
        'service_communes',
        'specialties',
        'last_active_at',
    ];

    // Dans $casts
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'isActive' => 'boolean',
        'social_links' => 'array',
        'settings' => 'array',
        'service_communes' => 'array',
        'specialties' => 'array',
        'last_active_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Relation Many-to-Many avec les sites
     * Un utilisateur peut être affecté à plusieurs sites
     */
    public function sites()
    {
        return $this->belongsToMany(Site::class, 'site_user', 'user_id', 'site_id')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

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

    /**
     * Vérifier si l'utilisateur a des rôles
     */
    public function hasAnyRole(...$roles): bool
    {
        if (empty($roles)) {
            return $this->roles()->exists();
        }

        return parent::hasAnyRole($roles);
    }

    /**
     * Vérifier si l'utilisateur a des permissions
     */
    public function hasAnyPermission(...$permissions): bool
    {
        if (empty($permissions)) {
            return $this->getAllPermissions()->isNotEmpty();
        }

        return parent::hasAnyPermission($permissions);
    }

    public function collectes()
    {
        return $this->hasMany(Collecte::class, 'agent_id', 'user_id');
    }

    public function factures()
    {
        return $this->hasMany(Facture::class, 'comptable_id', 'user_id');
    }

    public function observations()
    {
        return $this->hasMany(Observation::class, 'user_id', 'user_id');
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'reported_by', 'user_id');
    }

    public function validations()
    {
        return $this->hasMany(Validation::class, 'validated_by', 'user_id');
    }

    public function responsableSites()
    {
        return $this->hasMany(Site::class, 'responsable', 'user_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }
}
