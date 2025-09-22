<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
     * Envoi du lien de réinitialisation
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => "Cet email n'existe pas"]);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

        // Envoi email (ici simplifié, tu peux remplacer par Mailable)
        Mail::raw("Cliquez sur ce lien pour réinitialiser votre mot de passe : $resetUrl", function ($message) use ($request) {
            $message->to($request->email)
                ->subject("Réinitialisation du mot de passe");
        });

        return back()->with('status', "Un lien de réinitialisation a été envoyé à votre adresse email.");
    }

    /**
     * Formulaire pour saisir le nouveau mot de passe
     */
    public function showResetForm(Request $request, $token)
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->email
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

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return back()->withErrors(['email' => "Lien invalide ou expiré"]);
        }

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password),
        ]);

        // Supprimer le token utilisé
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', "Mot de passe réinitialisé avec succès !");
    }
}