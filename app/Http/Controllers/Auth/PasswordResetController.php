<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use App\Services\MailService;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    /**
     * Formulaire "Mot de passe oublié"
     */
    public function requestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Envoi du lien de réinitialisation.
     *
     * La réponse est volontairement identique que l'adresse existe ou non
     * (protection contre l'énumération de comptes).
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $genericStatus = "Si un compte est associé à cette adresse, un lien de réinitialisation vient de lui être envoyé. Pensez à vérifier votre dossier spam / courrier indésirable.";

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $plainToken = PasswordResetService::createToken($user->email);

            $resetUrl = route('password.reset', [
                'token' => $plainToken,
                'email' => $user->email,
            ]);

            $sent = MailService::send(
                $user->email,
                new PasswordResetMail($resetUrl, $user->email),
                'password-reset'
            );

            if (!$sent) {
                Log::warning('Lien de réinitialisation non délivré', ['email' => $user->email]);
            }
        }

        return back()->with('status', $genericStatus);
    }

    /**
     * Formulaire pour saisir le nouveau mot de passe
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Mise à jour du mot de passe
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!PasswordResetService::validateToken($request->email, $request->token)) {
            return back()->withErrors([
                'email' => 'Ce lien est invalide ou a expiré. Veuillez refaire une demande depuis « Mot de passe oublié ».',
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Ce lien est invalide ou a expiré.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        PasswordResetService::deleteToken($request->email);

        Log::info('Mot de passe réinitialisé', ['user_id' => $user->user_id]);

        return redirect()->route('login')->with('status', 'Mot de passe réinitialisé avec succès ! Vous pouvez maintenant vous connecter.');
    }
}
