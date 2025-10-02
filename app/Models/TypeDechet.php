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
        'code', // on ajoute le champ code
    ];

    // Événement pour générer automatiquement UUID et code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // UUID auto
            if (empty($model->type_dechet_id)) {
                $model->type_dechet_id = (string) Str::uuid();
            }

            // Code auto
            if (empty($model->code)) {
                $lastCode = self::orderBy('created_at', 'desc')->value('code');

                if ($lastCode) {
                    $lastNumber = intval(substr($lastCode, 3)); // extrait le numéro après "DBM"
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }

                $model->code = 'DBM' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relations
    public function collectes()
    {
        return $this->hasMany(Collecte::class, 'type_dechet_id', 'type_dechet_id');
    }

    // Scope avec statistiques
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

    // Calcul du poids total
    public function getPoidsTotal($periode = null)
    {
        $query = $this->collectes();
        if ($periode) {
            $query->where('date_collecte', '>=', $periode);
        }
        return $query->sum('poids');
    }
}
