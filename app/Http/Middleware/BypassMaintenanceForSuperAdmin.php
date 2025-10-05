<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BypassMaintenanceForSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'app est en mode maintenance
        if (app()->isDownForMaintenance()) {
            // Vérifier si l'utilisateur est connecté et a le rôle Super Admin
            if (auth()->check() && auth()->user()->hasRole('Super Admin')) {
                // Logger l'accès pendant la maintenance
                \Log::info('Super Admin accessed during maintenance', [
                    'user' => auth()->user()->email,
                    'url' => $request->fullUrl(),
                ]);

                // Désactiver temporairement le mode maintenance pour cette requête
                app()->instance(
                    'Illuminate\Contracts\Foundation\MaintenanceMode',
                    new class {
                        public function active()
                        {
                            return false;
                        }
                        public function data()
                        {
                            return [];
                        }
                        public function activate(array $payload) {}
                        public function deactivate() {}
                    }
                );
            }
        }

        return $next($request);
    }
}
