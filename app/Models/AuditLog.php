<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    protected $primaryKey = 'audit_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'audit_id',
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'performed_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'performed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->audit_id)) {
                $model->audit_id = Str::uuid()->toString();
            }
            if (empty($model->performed_at)) {
                $model->performed_at = now();
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
     * Scope pour filtrer par action
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope pour filtrer par entité
     */
    public function scopeEntity($query, $type, $id = null)
    {
        $query->where('entity_type', $type);
        
        if ($id) {
            $query->where('entity_id', $id);
        }
        
        return $query;
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopePeriod($query, $start, $end = null)
    {
        $query->where('performed_at', '>=', $start);
        
        if ($end) {
            $query->where('performed_at', '<=', $end);
        }
        
        return $query;
    }
}
