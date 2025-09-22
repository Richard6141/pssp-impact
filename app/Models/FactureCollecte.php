<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FactureCollecte extends Pivot
{
    use HasFactory, SoftDeletes;

    protected $table = 'facture_collectes';
    protected $primaryKey = 'factureCollecte_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'factureCollecte_id',
        'facture_id',
        'collecte_id',
    ];

    /**
     * Relation avec la facture
     */
    public function facture()
    {
        return $this->belongsTo(Facture::class, 'facture_id', 'facture_id');
    }

    /**
     * Relation avec la collecte
     */
    public function collecte()
    {
        return $this->belongsTo(Collecte::class, 'collecte_id', 'collecte_id');
    }

    /**
     * Boot method pour générer automatiquement l'UUID
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->factureCollecte_id)) {
                $model->factureCollecte_id = (string) Str::uuid();
            }
        });
    }
}
