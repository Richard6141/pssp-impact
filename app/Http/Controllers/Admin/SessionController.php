<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SessionManagementService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    protected $sessionService;

    public function __construct(SessionManagementService $sessionService)
    {
        $this->sessionService = $sessionService;
        $this->middleware('auth');
    }

    /**
     * Afficher les sessions actives
     */
    public function index()
    {
        $user = auth()->user();
        $sessions = $this->sessionService->getUserSessions($user);
        $stats = $this->sessionService->getSessionStats($user);

        return view('admin.sessions.index', compact('sessions', 'stats'));
    }

    /**
     * Terminer une session spécifique
     */
    public function destroy(string $sessionId)
    {
        if ($this->sessionService->terminateSession($sessionId)) {
            return redirect()
                ->route('admin.sessions.index')
                ->with('success', 'Session terminée avec succès.');
        }

        return back()->withErrors(['error' => 'Impossible de terminer cette session.']);
    }

    /**
     * Terminer toutes les autres sessions
     */
    public function destroyOthers()
    {
        $count = $this->sessionService->terminateOtherSessions(auth()->user());

        return redirect()
            ->route('admin.sessions.index')
            ->with('success', "$count session(s) terminée(s).");
    }
}
