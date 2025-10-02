<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
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
        $attempt = [
            $loginType => $credentials['login'],
            'password' => $credentials['password'],
        ];

        if (Auth::attempt($attempt, $request->filled('remember'))) {
            $user = Auth::user();

            // Méthode 1: Vérification directe via les relations
            $hasRoles = $user->roles()->exists();
            $hasPermissions = $user->permissions()->exists();

            // Méthode 2: Vérification via les collections (plus fiable)
            $rolesCount = $user->roles->count();
            $permissionsCount = $user->getAllPermissions()->count();

            // Si l'utilisateur n'a ni rôle ni permission
            if ($rolesCount === 0 && $permissionsCount === 0) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'login' => 'Votre compte n\'est pas encore activé. Veuillez contacter l\'administrateur pour l\'activation de votre compte.',
                ])->onlyInput('login');
            }

            $request->session()->regenerate();

            // Message de bienvenue avec informations sur les rôles (optionnel pour debug)
            $roleNames = $user->getRoleNames()->implode(', ');

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'login' => 'Identifiants incorrects. Veuillez vérifier votre email/nom d\'utilisateur et mot de passe.',
        ])->onlyInput('login');
    }



    /**
     * Déconnexion.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
