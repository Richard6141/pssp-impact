<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Facture extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $primaryKey = 'facture_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'numero_facture',
        'date_facture',
        'montant_facture',
        'statut',
        'photo_facture',
        'site_id',
        'comptable_id',
    ];

    protected $dates = [
        'date_facture',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'date_facture' => 'date',
        'montant_facture' => 'decimal:2'
    ];

    /**
     * Relation avec le site
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }

    /**
     * Relation avec le comptable (utilisateur)
     */
    public function comptable()
    {
        return $this->belongsTo(User::class, 'comptable_id', 'user_id');
    }

    public function factureCollectes()
    {
        return $this->hasMany(FactureCollecte::class, 'facture_id', 'facture_id');
    }

    /**
     * Relation many-to-many avec les collectes
     * ESSAI SANS le modèle pivot personnalisé
     */
    public function collectes()
    {
        return $this->belongsToMany(
            Collecte::class,
            'facture_collectes',
            'facture_id',
            'collecte_id'
        )->withTimestamps();
    }

    /**
     * Relation avec les paiements
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'facture_id', 'facture_id');
    }

    public function getMontantFormatteAttribute()
    {
        return number_format($this->montant_facture, 2, ',', ' ') . ' FCFA';
    }

    // Scope pour filtrer par statut
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en attente');
    }

    public function scopePayee($query)
    {
        return $query->where('statut', 'payée');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('statut', $status);
    }

    public function scopeByPeriod($query, $start, $end)
    {
        return $query->whereBetween('date_facture', [$start, $end]);
    }

    // Accessors
    public function getMontantFormateAttribute()
    {
        return number_format($this->montant_facture, 0, ',', ' ') . ' FCFA';
    }

    public function getStatutBadgeAttribute()
    {
        $badges = [
            'en attente' => 'warning',
            'envoyee' => 'info',
            'payee' => 'success',
            'impayee' => 'danger'
        ];
        return $badges[$this->statut] ?? 'secondary';
    }

    public function getTotalPayeAttribute()
    {
        return $this->paiements()->where('statut', 'valide')->sum('montant');
    }

    public function getResteAPayerAttribute()
    {
        return $this->montant_facture - $this->total_paye;
    }
}
