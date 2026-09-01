<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserInvitation;
use App\Models\Site;
use App\Services\UserInvitationService;
use Illuminate\Support\Facades\Auth;

class UserInvitationController extends Controller
{
    protected $invitationService;

    public function __construct(UserInvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
        $this->middleware('auth');
        // $this->middleware('permission:user.invite'); // Si vous utilisez les permissions
    }

    /**
     * Liste des invitations
     */
    public function index(Request $request)
    {
        $query = UserInvitation::with(['inviter', 'site'])->latest();

        // Recherche cote serveur (porte sur toutes les pages)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhereIn('role_id', \Spatie\Permission\Models\Role::where('name', 'like', "%{$search}%")->pluck('id'))
                    ->orWhereHas('site', fn ($s) => $s->where('site_name', 'like', "%{$search}%"))
                    ->orWhereHas('inviter', function ($u) use ($search) {
                        $u->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $invitations = $query->paginate(10)->withQueryString();

        return view('admin.users.invitations.index', compact('invitations'));
    }

    /**
     * Formulaire d'invitation
     */
    public function create()
    {
        $roles = \Spatie\Permission\Models\Role::all();
        $sites = Site::orderBy('site_name')->get(['site_id', 'site_name']);

        return view('admin.users.invitations.create', compact('roles', 'sites'));
    }

    /**
     * Envoyer l'invitation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'site_id' => 'nullable|exists:sites,site_id',
            'assign_as_site_responsable' => 'nullable|boolean',
        ]);

        if ($request->boolean('assign_as_site_responsable') && empty($validated['site_id'])) {
            return back()
                ->withInput()
                ->withErrors(['site_id' => 'Selectionnez un site avant de le definir au responsable invite.']);
        }

        try {
            $this->invitationService->createInvitation(
                $request->email,
                $request->role_id,
                Auth::user(),
                $request->site_id,
                $request->boolean('assign_as_site_responsable')
            );

            return redirect()->route('admin.users.invitations.index')
                ->with('success', 'Invitation envoyée avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['email' => $e->getMessage()]);
        }
    }

    /**
     * Annuler une invitation
     */
    public function destroy($id)
    {
        try {
            $this->invitationService->cancelInvitation($id);
            return redirect()->route('admin.users.invitations.index')
                ->with('success', 'Invitation annulée.');
        } catch (\Exception $e) {
            return back()->with('error', 'Impossible d\'annuler l\'invitation.');
        }
    }

    /**
     * Renvoyer une invitation
     */
    public function resend($id)
    {
        try {
            $this->invitationService->resendInvitation($id);
            return back()->with('success', 'Invitation renvoyée.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
