<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserImportController extends Controller
{
    protected $importService;

    public function __construct(UserImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Afficher le formulaire d'import
     */
    public function index()
    {
        return view('admin.users.import');
    }

    /**
     * Traiter le fichier CSV importé
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        
        // Appel du service d'import
        $result = $this->importService->import($file->getRealPath());

        if ($result['success_count'] > 0) {
            return redirect()->route('admin.users.import.index')
                ->with('success', "Import terminé : {$result['success_count']} utilisateur(s) créé(s). " . ($result['error_count'] > 0 ? "Mais {$result['error_count']} erreur(s) survenue(s)." : ""));
        } else {
            return back()->with('error', "Aucun utilisateur n'a pu être importé. Erreurs : " . implode(', ', $result['errors']));
        }
    }

    /**
     * Télécharger un modèle de fichier CSV
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_import_template.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['firstname', 'lastname', 'username', 'email', 'role', 'site_name', 'phone']);
            fputcsv($handle, ['Jean', 'Dupont', 'jdupont', 'jean.dupont@example.com', 'Agent collecte', 'Site Alpha', '0102030405']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
