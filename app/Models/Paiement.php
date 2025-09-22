<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paiement extends Model
{
    use HasFactory, SoftDeletes, HasUuids;
    protected $table = 'paiements';
    protected $primaryKey = 'paiement_id';
    public $incrementing = false; // UUID
    protected $keyType = 'string';

    protected $fillable = [
        'facture_id',
        'numero_paiement',
        'montant',
        'mode_paiement',
        'date_paiement',
        'reference',
        'paiement_photo',
        'statut',
    ];

    protected $dates = [
        'date_paiement',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Relation vers la facture associée
     */
    public function facture()
    {
        return $this->belongsTo(Facture::class, 'facture_id', 'facture_id');
    }

    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopeByPeriod($query, $start, $end)
    {
        return $query->whereBetween('date_paiement', [$start, $end]);
    }

    // Accessors
    public function getMontantFormateAttribute()
    {
        return number_format($this->montant, 0, ',', ' ') . ' FCFA';
    }

    public function getStatutBadgeAttribute()
    {
        $badges = [
            'en attente' => 'warning',
            'valide' => 'success',
            'rejete' => 'danger'
        ];
        return $badges[$this->statut] ?? 'secondary';
    }
}
