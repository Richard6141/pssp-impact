<?php

namespace App\Models;

use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeDechet extends Model
{
    use HasFactory, Notifiable, HasUuids;

    // Table associée
    protected $table = 'type_dechets';

    // Clé primaire
    protected $primaryKey = 'type_dechet_id';
    public $incrementing = false; // car on utilise un UUID
    protected $keyType = 'string';

    // Champs remplissables
    protected $fillable = [
        'type_dechet_id',
        'libelle',
        'description',
    ];

    // Événement pour générer un UUID automatiquement lors de la création
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->type_dechet_id)) {
                $model->type_dechet_id = (string) Str::uuid();
            }
        });
    }

    public function collectes()
    {
        return $this->hasMany(Collecte::class, 'type_dechet_id', 'type_dechet_id');
    }
    public function scopeWithStats($query, $periode = null)
    {
        return $query->withCount(['collectes' => function ($q) use ($periode) {
            if ($periode) {
                $q->where('date_collecte', '>=', $periode);
            }
        }])->with(['collectes' => function ($q) use ($periode) {
            if ($periode) {
                $q->where('date_collecte', '>=', $periode);
            }
        }]);
    }

    public function getPoidsTotal($periode = null)
    {
        $query = $this->collectes();
        if ($periode) {
            $query->where('date_collecte', '>=', $periode);
        }
        return $query->sum('poids');
    }
}
