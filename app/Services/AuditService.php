<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuditService
{
    /**
     * Récupérer les logs d'audit avec filtres
     */
    public function getLogs(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = AuditLog::with('user')->orderBy('performed_at', 'desc');

        // Filtre par utilisateur
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filtre par action
        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        // Filtre par entité
        if (!empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }

        if (!empty($filters['entity_id'])) {
            $query->where('entity_id', $filters['entity_id']);
        }

        // Filtre par période
        if (!empty($filters['start_date'])) {
            $query->where('performed_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('performed_at', '<=', $filters['end_date']);
        }

        // Recherche libre (toutes les pages) : action, entité, description
        // ou nom / email de l'utilisateur a l'origine de l'action.
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('entity_type', 'like', "%{$search}%")
                    ->orWhere('entity_id', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Récupérer l'historique d'une entité spécifique
     */
    public function getEntityHistory(string $entityType, string $entityId): Collection
    {
        return AuditLog::with('user')
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('performed_at', 'desc')
            ->get();
    }

    /**
     * Récupérer les statistiques d'audit
     */
    public function getStats(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $query = AuditLog::query();

        if ($startDate) {
            $query->where('performed_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('performed_at', '<=', $endDate);
        }

        return [
            'total_actions' => $query->count(),
            'by_action' => $query->select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray(),
            'by_entity' => $query->select('entity_type', DB::raw('count(*) as count'))
                ->groupBy('entity_type')
                ->pluck('count', 'entity_type')
                ->toArray(),
            'top_users' => $query->select('user_id', DB::raw('count(*) as count'))
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->orderByDesc('count')
                ->limit(10)
                ->with('user:user_id,firstname,lastname')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Enregistrer une action manuelle
     */
    public function log(array $data): AuditLog
    {
        return AuditLog::create(array_merge([
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'performed_at' => now(),
        ], $data));
    }

    /**
     * Logger une connexion utilisateur
     */
    public function logLogin(User $user, bool $success = true): void
    {
        $this->log([
            'user_id' => $user->user_id,
            'action' => $success ? 'login' : 'login_failed',
            'entity_type' => 'User',
            'entity_id' => $user->user_id,
            'description' => $success 
                ? "Connexion réussie pour {$user->firstname} {$user->lastname}"
                : "Tentative de connexion échouée pour {$user->email}",
        ]);
    }

    /**
     * Logger une déconnexion
     */
    public function logLogout(User $user): void
    {
        $this->log([
            'user_id' => $user->user_id,
            'action' => 'logout',
            'entity_type' => 'User',
            'entity_id' => $user->user_id,
            'description' => "Déconnexion de {$user->firstname} {$user->lastname}",
        ]);
    }

    /**
     * Nettoyer les vieux logs (plus de X jours)
     */
    public function cleanup(int $daysToKeep = 365): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return AuditLog::where('performed_at', '<', $cutoffDate)->delete();
    }

    /**
     * Exporter les logs en CSV
     */
    public function exportCsv(array $filters = []): string
    {
        $logs = $this->getLogs($filters, 10000);

        $filename = storage_path("app/exports/audit_logs_" . now()->format('Y-m-d_His') . ".csv");
        
        // Créer le répertoire si nécessaire
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }

        $file = fopen($filename, 'w');
        
        // En-têtes
        fputcsv($file, [
            'Date',
            'Utilisateur',
            'Action',
            'Entité',
            'ID Entité',
            'Description',
            'IP',
        ]);

        // Données
        foreach ($logs as $log) {
            fputcsv($file, [
                $log->performed_at->format('Y-m-d H:i:s'),
                $log->user ? $log->user->firstname . ' ' . $log->user->lastname : 'Système',
                $log->action,
                $log->entity_type,
                $log->entity_id,
                $log->description,
                $log->ip_address,
            ]);
        }

        fclose($file);

        return $filename;
    }
}
