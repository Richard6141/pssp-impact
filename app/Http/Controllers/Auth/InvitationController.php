<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserInvitation;
use App\Services\UserInvitationService;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    protected $invitationService;

    public function __construct(UserInvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
        $this->middleware('guest');
    }

    /**
     * Afficher le formulaire de définition du mot de passe
     */
    public function show($token)
    {
        $invitation = UserInvitation::with('site')->where('token', $token)
            ->active()
            ->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Cette invitation est invalide ou a expiré.']);
        }

        return view('auth.invitation.accept', compact('invitation'));
    }

    /**
     * Traiter l'acceptation et créer le compte
     */
    public function accept(Request $request, $token)
    {
        $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        try {
            $user = $this->invitationService->acceptInvitation(
                $token,
                $request->only('firstname', 'lastname', 'username'),
                $request->password
            );

            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Bienvenue ! Votre compte a été créé avec succès.');
        
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['email' => $e->getMessage()]);
        }
    }
}
