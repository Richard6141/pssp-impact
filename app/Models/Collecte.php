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

    /**
     * Nombre de decimales conservees pour le poids d'une collecte.
     * Doit rester aligne avec la migration decimal(12, self::POIDS_DECIMALES)
     * et avec l'attribut `step` des formulaires de collecte.
     */
    public const POIDS_DECIMALES = 3;

    protected $casts = [
        'date_collecte' => 'datetime',
        'poids' => 'decimal:' . self::POIDS_DECIMALES,
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

    /**
     * Détermine si l'utilisateur peut signer (valider les quantités de)
     * cette collecte : le responsable du site, ou tout utilisateur disposant
     * de la permission `collectes.validate_site` rattaché au site de la
     * collecte (ex. Agent santé — demande client du 29/06/2026).
     */
    public function canBeSignedBy(?User $user): bool
    {
        if (!$user || !$this->site) {
            return false;
        }

        if ((string) $this->site->responsable === (string) $user->user_id) {
            return true;
        }

        if (!$user->can('collectes.validate_site')) {
            return false;
        }

        if ((string) $user->site_id === (string) $this->site_id) {
            return true;
        }

        return $user->sites()->where('sites.site_id', $this->site_id)->exists();
    }

    /**
     * Formate un poids exactement tel qu'il a ete enregistre : jusqu'a
     * POIDS_DECIMALES decimales, sans arrondi et sans zeros inutiles.
     * 12.500 -> "12,5"   12.000 -> "12"   1234.125 -> "1 234,125"
     *
     * A utiliser partout ou un poids de collecte est affiche, afin de ne
     * jamais retomber sur un number_format(..., 1) qui arrondit.
     */
    public static function formatPoids($poids, bool $avecUnite = false): string
    {
        $valeur = number_format((float) ($poids ?? 0), self::POIDS_DECIMALES, ',', ' ');

        // Retire les zeros de fin (et la virgule si plus rien derriere)
        if (str_contains($valeur, ',')) {
            $valeur = rtrim(rtrim($valeur, '0'), ',');
        }

        return $avecUnite ? $valeur . ' kg' : $valeur;
    }

    /**
     * Valeur destinee a un <input type="number"> : point decimal, sans les
     * zeros de fin ajoutes par le cast decimal (12.500 -> 12.5).
     */
    public static function poidsInputValue($poids): string
    {
        $valeur = (string) ($poids ?? '');

        if ($valeur === '' || !str_contains($valeur, '.')) {
            return $valeur;
        }

        return rtrim(rtrim($valeur, '0'), '.');
    }

    // Accessors
    public function getPoidsFormateAttribute()
    {
        return self::formatPoids($this->poids, true);
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
