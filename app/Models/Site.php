<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Site extends Model
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'sites';
    protected $primaryKey = 'site_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'site_id',
        'site_name',
        'site_departement',
        'site_commune',
        'localisation',
        'longitude',
        'latitude',
        'responsable',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->site_id)) {
                $model->site_id = (string) Str::uuid();
            }
        });
    }

    // 🔹 Relation avec l'utilisateur responsable
    public function responsableUser()
    {
        return $this->belongsTo(User::class, 'responsable', 'user_id');
    }

    public function collectes()
    {
        return $this->hasMany(Collecte::class, 'site_id', 'site_id');
    }

    public function factures()
    {
        return $this->hasMany(Facture::class, 'site_id', 'site_id');
    }

    public function observations()
    {
        return $this->hasMany(Observation::class, 'site_id', 'site_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'site_id', 'site_id');
    }

    // Scopes
    public function scopeByDepartement($query, $departement)
    {
        return $query->where('site_departement', $departement);
    }

    public function scopeByCommune($query, $commune)
    {
        return $query->where('site_commune', $commune);
    }

    public function scopeActive($query)
    {
        return $query->whereHas('collectes', function ($q) {
            $q->where('created_at', '>=', now()->subMonths(3));
        });
    }

    // Accessors
    public function getAdresseCompleteAttribute()
    {
        return $this->localisation . ', ' . $this->site_commune . ', ' . $this->site_departement;
    }

    public function getTotalCollectesAttribute()
    {
        return $this->collectes()->count();
    }

    public function getPoidsTotal($periode = null)
    {
        $query = $this->collectes();
        if ($periode) {
            $query->where('date_collecte', '>=', $periode);
        }
        return $query->sum('poids');
    }

    public function getCaTotal($periode = null)
    {
        $query = $this->factures();
        if ($periode) {
            $query->where('date_facture', '>=', $periode);
        }
        return $query->sum('montant_facture');
    }
}
