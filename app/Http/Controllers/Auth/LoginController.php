<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\TwoFactorAuthService;
use App\Services\SessionManagementService;
use App\Services\AuditService;
use App\Models\PasswordPolicy;

class LoginController extends Controller
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
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        // Trouver l'utilisateur AVANT d'utiliser Auth::attempt
        $user = \App\Models\User::where($loginType, $credentials['login'])->first();
        
        // Si l'utilisateur n'existe pas
        if (!$user) {
            return back()->withErrors([
                'login' => 'Identifiants incorrects. Veuillez vérifier votre email/nom d\'utilisateur et mot de passe.',
            ])->onlyInput('login');
        }

        // Vérifier si l'utilisateur est verrouillé
        if ($user->locked_until && $user->locked_until->isFuture()) {
            $minutesLeft = now()->diffInMinutes($user->locked_until);
            
            return back()->withErrors([
                'login' => "Compte verrouillé. Réessayez dans {$minutesLeft} minutes.",
            ])->onlyInput('login');
        }

        // Vérifier le mot de passe
        if (!Auth::attempt([
            $loginType => $credentials['login'],
            'password' => $credentials['password']
        ], false)) {
            // Mot de passe incorrect
            $this->handleFailedLogin($credentials['login']);
            
            return back()->withErrors([
                'login' => 'Identifiants incorrects. Veuillez vérifier votre email/nom d\'utilisateur et mot de passe.',
            ])->onlyInput('login');
        }

        // À ce stade, l'utilisateur est authentifié temporairement
        $user = Auth::user();

        // Réinitialiser les tentatives échouées
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        // Vérifier les rôles et permissions
        $rolesCount = $user->roles->count();
        $permissionsCount = $user->getAllPermissions()->count();

        if ($rolesCount === 0 && $permissionsCount === 0) {
            Auth::logout();
            $this->auditService->log([
                'user_id' => $user->user_id,
                'action' => 'login_failed',
                'entity_type' => 'User',
                'entity_id' => $user->user_id,
                'description' => 'Tentative de connexion avec compte non activé',
            ]);

            return back()->withErrors([
                'login' => 'Votre compte n\'est pas encore activé. Veuillez contacter l\'administrateur.',
            ])->onlyInput('login');
        }

        // Vérifier si le 2FA est activé
        if ($this->tfaService->isEnabled($user)) {
            // IMPORTANT: Stocker AVANT de déconnecter
            $request->session()->put('2fa:user:id', $user->user_id);
            $request->session()->put('2fa:remember', $request->filled('remember'));
            
            // Déconnecter sans invalider la session
            Auth::logout();
            
            // Régénérer le token CSRF mais garder les données de session
            $request->session()->regenerateToken();
            
            return redirect()->route('2fa.verify');
        }

        // Finaliser la connexion (avec remember si demandé)
        if ($request->filled('remember')) {
            Auth::login($user, true);
        }
        
        return $this->finalizeLogin($request, $user);
    }

    /**
     * Finaliser la connexion après validation (avec ou sans 2FA)
     */
    protected function finalizeLogin(Request $request, $user)
    {
        $request->session()->regenerate();

        // Créer une session trackée
        $this->sessionService->createSession($user);

        // Mettre à jour les informations de connexion
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Logger la connexion
        $this->auditService->logLogin($user, true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Gérer les tentatives de connexion échouées
     */
    protected function handleFailedLogin(string $login)
    {
        $loginType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = \App\Models\User::where($loginType, $login)->first();

        if ($user) {
            $policy = PasswordPolicy::getActive();
            $attempts = $user->failed_login_attempts + 1;

            $user->increment('failed_login_attempts');

            // Verrouiller le compte si trop de tentatives
            if ($policy && $attempts >= $policy->max_login_attempts) {
                $user->update([
                    'locked_until' => now()->addMinutes($policy->lockout_duration_minutes),
                ]);
            }

            // Logger la tentative échouée
            $this->auditService->logLogin($user, false);
        }
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // Logger la déconnexion
            $this->auditService->logLogout($user);
            
            // Terminer la session courante
            $this->sessionService->terminateAllSessions($user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
