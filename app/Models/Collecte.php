<?php

namespace App\Models;

use App\Models\Site;
use App\Models\User;
use App\Models\TypeDechet;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Collecte extends Model
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;
    protected $table = 'collectes';
    protected $primaryKey = 'collecte_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'collecte_id',
        'numero_collecte',
        'date_collecte',
        'poids',
        'type_dechet_id',
        'agent_id',
        'site_id',
        'signature_responsable_site',
        'statut',
        'isValid'
    ];

    protected $casts = [
        'date_collecte' => 'datetime',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->collecte_id)) {
                $model->collecte_id = (string) Str::uuid();
            }
        });
    }

    // 🔹 Relations
    public function typeDechet()
    {
        return $this->belongsTo(TypeDechet::class, 'type_dechet_id', 'type_dechet_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id', 'user_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }

    public function incident()
    {
        return $this->hasOne(Incident::class, 'collecte_id', 'collecte_id');
    }



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // 🔹 Relation avec validation
    public function validation()
    {
        return $this->hasOne(Validation::class, 'collecte_id', 'collecte_id');
    }

    public function factures()
    {
        return $this->belongsToMany(
            Facture::class,
            'facture_collectes',
            'collecte_id',
            'facture_id'
        )->withTimestamps();
    }

    public function factureCollectes()
    {
        return $this->hasMany(FactureCollecte::class, 'collecte_id', 'collecte_id');
    }

    public function scopeValid($query)
    {
        return $query->where('isValid', true);
    }

    public function scopeSigned($query)
    {
        return $query->where('signature_responsable_site', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('statut', $status);
    }

    public function scopeByPeriod($query, $start, $end)
    {
        return $query->whereBetween('date_collecte', [$start, $end]);
    }

    // Accessors
    public function getPoidsFormateAttribute()
    {
        return number_format($this->poids, 2) . ' kg';
    }

    public function getStatutBadgeAttribute()
    {
        $badges = [
            'en_attente' => 'warning',
            'validee' => 'success',
            'terminee' => 'primary'
        ];
        return $badges[$this->statut] ?? 'secondary';
    }
}
