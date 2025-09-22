<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Incident extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'incident_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'collecte_id',
        'reported_by',
        'description',
        'date_incident',
        'statut',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // Relation avec Collecte
    public function collecte()
    {
        return $this->belongsTo(Collecte::class, 'collecte_id', 'collecte_id');
    }

    // Relation avec User (reporter)
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by', 'user_id');
    }
}
