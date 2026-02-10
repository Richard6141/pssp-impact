<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;

class SessionManagementService
{
    /**
     * Créer une nouvelle session pour un utilisateur
     */
    public function createSession(User $user): UserSession
    {
        $agent = new Agent();
        
        // Marquer toutes les autres sessions comme non courantes
        UserSession::where('user_id', $user->user_id)->update(['is_current' => false]);

        return UserSession::create([
            'user_id' => $user->user_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'device_type' => $this->getDeviceType($agent),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'last_activity' => now(),
            'is_current' => true,
            'expires_at' => now()->addMinutes(config('session.lifetime', 120)),
        ]);
    }

    /**
     * Déterminer le type d'appareil
     */
    protected function getDeviceType(Agent $agent): string
    {
        if ($agent->isDesktop()) {
            return 'desktop';
        } elseif ($agent->isTablet()) {
            return 'tablet';
        } elseif ($agent->isMobile()) {
            return 'mobile';
        }
        
        return 'unknown';
    }

    /**
     * Mettre à jour l'activité de la session courante
     */
    public function updateActivity(User $user): void
    {
        UserSession::where('user_id', $user->user_id)
            ->where('is_current', true)
            ->update([
                'last_activity' => now(),
                'expires_at' => now()->addMinutes(config('session.lifetime', 120)),
            ]);
    }

    /**
     * Récupérer toutes les sessions d'un utilisateur
     */
    public function getUserSessions(User $user)
    {
        return UserSession::where('user_id', $user->user_id)
            ->active()
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    /**
     * Terminer une session spécifique
     */
    public function terminateSession(string $sessionId): bool
    {
        $session = UserSession::find($sessionId);
        
        if (!$session) {
            return false;
        }

        // Si c'est la session courante, déconnecter l'utilisateur
        if ($session->is_current) {
            Session::flush();
            auth()->logout();
        }

        return $session->delete();
    }

    /**
     * Terminer toutes les autres sessions
     */
    public function terminateOtherSessions(User $user): int
    {
        return UserSession::where('user_id', $user->user_id)
            ->where('is_current', false)
            ->delete();
    }

    /**
     * Terminer toutes les sessions (déconnexion complète)
     */
    public function terminateAllSessions(User $user): void
    {
        UserSession::where('user_id', $user->user_id)->delete();
        Session::flush();
        auth()->logout();
    }

    /**
     * Nettoyer les sessions expirées
     */
    public function cleanupExpiredSessions(): int
    {
        return UserSession::where('expires_at', '<', now())->delete();
    }

    /**
     * Obtenir les statistiques de sessions
     */
    public function getSessionStats(User $user): array
    {
        $sessions = $this->getUserSessions($user);

        return [
            'total' => $sessions->count(),
            'by_device' => $sessions->groupBy('device_type')->map->count(),
            'by_browser' => $sessions->groupBy('browser')->map->count(),
            'by_platform' => $sessions->groupBy('platform')->map->count(),
        ];
    }
}
