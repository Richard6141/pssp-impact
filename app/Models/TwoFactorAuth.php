<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TwoFactorAuth extends Model
{
    protected $table = 'two_factor_auth';
    protected $primaryKey = 'tfa_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tfa_id',
        'user_id',
        'secret',
        'recovery_codes',
        'is_enabled',
        'enabled_at',
        'last_used_at',
    ];

    protected $casts = [
        'recovery_codes' => 'array',
        'is_enabled' => 'boolean',
        'enabled_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->tfa_id)) {
                $model->tfa_id = Str::uuid()->toString();
            }
        });
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Vérifier si un code de récupération est valide
     */
    public function isValidRecoveryCode(string $code): bool
    {
        return in_array($code, $this->recovery_codes ?? []);
    }

    /**
     * Utiliser un code de récupération
     */
    public function useRecoveryCode(string $code): bool
    {
        $codes = $this->recovery_codes ?? [];
        $key = array_search($code, $codes);
        
        if ($key === false) {
            return false;
        }
        
        unset($codes[$key]);
        $this->recovery_codes = array_values($codes);
        $this->save();
        
        return true;
    }
}
