<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuditController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
        $this->middleware('auth');
        $this->middleware('permission:audit.view');
    }

    /**
     * Afficher la liste des logs d'audit
     */
    public function index(Request $request)
    {
        // Préparer les filtres
        $filters = $request->only(['user_id', 'action', 'entity_type', 'entity_id', 'start_date', 'end_date', 'search']);
        
        // Récupérer les logs
        $logs = $this->auditService->getLogs($filters, 25);
        
        // Récupérer les stats
        $stats = $this->auditService->getStats(
            $request->start_date ? Carbon::parse($request->start_date) : null,
            $request->end_date ? Carbon::parse($request->end_date) : null
        );

        return view('admin.audit.index', compact('logs', 'stats', 'filters'));
    }

    /**
     * Afficher le détail d'un log
     */
    public function show(string $id)
    {
        $log = AuditLog::with('user')->findOrFail($id);
        
        return view('admin.audit.show', compact('log'));
    }

    /**
     * Exporter les logs en CSV
     */
    public function export(Request $request)
    {
        $filters = $request->only(['user_id', 'action', 'entity_type', 'start_date', 'end_date']);
        
        $filename = $this->auditService->exportCsv($filters);
        
        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /**
     * Nettoyer les vieux logs
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:30',
        ]);

        $deleted = $this->auditService->cleanup($request->days);

        return redirect()
            ->route('admin.audit.index')
            ->with('success', "$deleted logs supprimés avec succès.");
    }
}
