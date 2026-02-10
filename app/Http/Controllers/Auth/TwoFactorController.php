<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use App\Services\SessionManagementService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    protected $tfaService;
    protected $sessionService;
    protected $auditService;

    public function __construct(
        TwoFactorAuthService $tfaService,
        SessionManagementService $sessionService,
        AuditService $auditService
    ) {
        $this->tfaService = $tfaService;
        $this->sessionService = $sessionService;
        $this->auditService = $auditService;
        $this->middleware('auth')->except(['verify', 'validateCode']);
    }

    /**
     * Afficher la page de configuration 2FA
     */
    public function enable()
    {
        $user = auth()->user();
        
        // Générer le secret et le QR code
        $data = $this->tfaService->generateSecret($user);
        
        return view('auth.2fa.enable', $data);
    }

    /**
     * Activer le 2FA après vérification
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        
        if ($this->tfaService->enable($user, $request->code)) {
            $this->auditService->log([
                'user_id' => $user->user_id,
                'action' => '2fa_enabled',
                'entity_type' => 'User',
                'entity_id' => $user->user_id,
                'description' => 'Authentification à deux facteurs activée'
            ]);

            return redirect()
                ->route('account.security')
                ->with('success', 'Authentification à deux facteurs activée avec succès !');
        }

        return back()->withErrors(['code' => 'Code invalide. Veuillez réessayer.']);
    }

    /**
     * Désactiver le 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();
        $this->tfaService->disable($user);

        $this->auditService->log([
            'user_id' => $user->user_id,
            'action' => '2fa_disabled',
            'entity_type' => 'User',
            'entity_id' => $user->user_id,
            'description' => 'Authentification à deux facteurs désactivée'
        ]);

        return redirect()
            ->route('account.security')
            ->with('success', 'Authentification à deux facteurs désactivée.');
    }

    /**
     * Afficher la page de vérification 2FA au login
     */
    public function verify()
    {
        if (!session('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.2fa.verify');
    }

    /**
     * Vérifier le code 2FA au login
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = session('2fa:user:id');
        $remember = session('2fa:remember', false);
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'Session expirée']);
        }

        // Essayer avec le code 2FA
        if ($this->tfaService->verify($user, $request->code)) {
            $this->completeLogin($request, $user, $remember);
            
            return redirect()->intended(route('dashboard'));
        }

        // Essayer avec un code de récupération
        if ($this->tfaService->verifyRecoveryCode($user, $request->code)) {
            $this->completeLogin($request, $user, $remember);
            
            $this->auditService->log([
                'user_id' => $user->user_id,
                'action' => '2fa_recovery_used',
                'entity_type' => 'User',
                'entity_id' => $user->user_id,
                'description' => 'Connexion avec un code de récupération'
            ]);

            return redirect()
                ->route('dashboard')
                ->with('warning', 'Vous avez utilisé un code de récupération. Pensez à en régénérer.');
        }

        return back()->withErrors(['code' => 'Code invalide']);
    }

    private function completeLogin(Request $request, $user, $remember)
    {
        session()->forget(['2fa:user:id', '2fa:remember']);
        auth()->login($user, $remember);
        $request->session()->regenerate();
        
        // Créer la session trackée
        $this->sessionService->createSession($user);

        // Mettre à jour les infos de connexion
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Audit log
        $this->auditService->logLogin($user, true);
    }

    /**
     * Régénérer les codes de récupération
     */
    public function regenerateRecoveryCodes()
    {
        $codes = $this->tfaService->regenerateRecoveryCodes(auth()->user());
        
        return view('auth.2fa.recovery-codes', compact('codes'));
    }

    /**
     * Afficher la page de paramètres de sécurité
     */
    public function securitySettings()
    {
        $user = auth()->user();
        $tfaEnabled = $this->tfaService->isEnabled($user);
        
        return view('auth.security', compact('tfaEnabled'));
    }
}
