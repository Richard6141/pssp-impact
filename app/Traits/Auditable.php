<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait Auditable
{
    /**
     * Boot du trait
     */
    protected static function bootAuditable()
    {
        // Audit sur création
        static::created(function ($model) {
            static::auditAction('create', $model, null, $model->getAttributes());
        });

        // Audit sur mise à jour
        static::updated(function ($model) {
            static::auditAction('update', $model, $model->getOriginal(), $model->getChanges());
        });

        // Audit sur suppression
        static::deleted(function ($model) {
            static::auditAction('delete', $model, $model->getAttributes(), null);
        });
    }

    /**
     * Créer un log d'audit
     */
    protected static function auditAction(string $action, $model, $oldValues, $newValues)
    {
        // Filtrer les champs sensibles
        $oldValues = static::filterSensitiveFields($oldValues);
        $newValues = static::filterSensitiveFields($newValues);

        AuditLog::create([
            'audit_id' => Str::uuid()->toString(),
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => class_basename($model),
            'entity_id' => $model->getKey(),
            'description' => static::getActionDescription($action, $model),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'performed_at' => now(),
        ]);
    }

    /**
     * Filtrer les champs sensibles (mots de passe, etc.)
     */
    protected static function filterSensitiveFields($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $sensitiveFields = ['password', 'remember_token', 'two_factor_secret'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***FILTERED***';
            }
        }

        return $data;
    }

    /**
     * Générer une description lisible de l'action
     */
    protected static function getActionDescription(string $action, $model): string
    {
        $entityName = class_basename($model);
        $id = $model->getKey();

        return match($action) {
            'create' => "Création de $entityName #$id",
            'update' => "Modification de $entityName #$id",
            'delete' => "Suppression de $entityName #$id",
            default => "Action $action sur $entityName #$id",
        };
    }

    /**
     * Méthode pour auditer une action personnalisée
     */
    public function auditCustomAction(string $action, string $description, array $data = [])
    {
        AuditLog::create([
            'audit_id' => Str::uuid()->toString(),
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => class_basename($this),
            'entity_id' => $this->getKey(),
            'description' => $description,
            'new_values' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'performed_at' => now(),
        ]);
    }
}
