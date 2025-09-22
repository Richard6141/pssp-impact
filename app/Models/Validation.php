<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Validation extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'validations';
    protected $primaryKey = 'validation_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'validation_id',
        'collecte_id',
        'validated_by',
        'type_validation',
        'date_validation',
        'commentaire',
        'signature',
    ];

    protected $dates = ['date_validation', 'deleted_at'];

    /**
     * Générer automatiquement un UUID lors de la création.
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->validation_id)) {
                $model->validation_id = Str::uuid()->toString();
            }
        });
    }

    /**
     * Relation : une validation appartient à une collecte.
     */
    public function collecte()
    {
        return $this->belongsTo(Collecte::class, 'collecte_id', 'collecte_id');
    }

    /**
     * Relation : une validation est faite par un utilisateur (responsable).
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by', 'user_id');
    }
}
