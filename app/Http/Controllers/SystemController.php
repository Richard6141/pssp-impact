<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SystemController extends Controller
{

    /**
     * Afficher les logs système
     */
    public function logs(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logFile)) {
            $content = File::get($logFile);
            $lines = array_reverse(explode("\n", $content));

            $currentLog = null;
            foreach ($lines as $line) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.+)/', $line, $matches)) {
                    if ($currentLog) {
                        $logs[] = $currentLog;
                    }
                    $currentLog = [
                        'timestamp' => $matches[1],
                        'environment' => $matches[2],
                        'level' => $matches[3],
                        'message' => $matches[4],
                        'context' => ''
                    ];
                } else if ($currentLog && !empty(trim($line))) {
                    $currentLog['context'] .= $line . "\n";
                }
            }
            if ($currentLog) {
                $logs[] = $currentLog;
            }
        }

        // Filtrage par niveau si demandé
        $level = $request->input('level');
        if ($level) {
            $logs = array_filter($logs, function ($log) use ($level) {
                return strtolower($log['level']) === strtolower($level);
            });
        }

        // Pagination manuelle
        $perPage = 50;
        $page = $request->input('page', 1);
        $total = count($logs);
        $logs = array_slice($logs, ($page - 1) * $perPage, $perPage);

        return view('system.logs', compact('logs', 'level', 'total', 'page', 'perPage'));
    }

    /**
     * Gestion des sauvegardes
     */
    public function backup(Request $request)
    {
        $backupPath = storage_path('app/backups');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $backups = collect(File::files($backupPath))
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'date' => Carbon::createFromTimestamp($file->getMTime()),
                    'path' => $file->getPathname()
                ];
            })
            ->sortByDesc('date')
            ->values();

        return view('system.backup', compact('backups'));
    }

    /**
     * Créer une sauvegarde
     */
    public function createBackup(Request $request)
    {
        try {
            $backupName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('app/backups/' . $backupName);

            $databaseName = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $host = env('DB_HOST');

            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s',
                $host,
                $username,
                $password,
                $databaseName,
                $backupPath
            );

            exec($command, $output, $return_var);

            if ($return_var === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sauvegarde créée avec succès',
                    'backup' => $backupName
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création de la sauvegarde'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mode maintenance
     */
    public function maintenance(Request $request)
    {
        $isDown = File::exists(storage_path('framework/maintenance.php'));

        return view('system.maintenance', compact('isDown'));
    }

    /**
     * Activer/Désactiver le mode maintenance
     */
    public function toggleMaintenance(Request $request)
    {
        try {
            if (File::exists(storage_path('framework/maintenance.php'))) {
                // Désactiver la maintenance
                Artisan::call('up');
                $message = 'Mode maintenance désactivé';
                $status = 'up';
            } else {
                // Activer la maintenance
                Artisan::call('down', [
                    '--render' => 'maintenance',
                    '--retry' => 60,
                ]);
                $message = 'Mode maintenance activé';
                $status = 'down';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Informations système
     */
    public function info(Request $request)
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'os' => PHP_OS,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'disk_free_space' => $this->formatBytes(disk_free_space('/')),
            'disk_total_space' => $this->formatBytes(disk_total_space('/')),
        ];

        // Informations sur la base de données
        $dbInfo = [
            'connection' => config('database.default'),
            'host' => config('database.connections.' . config('database.default') . '.host'),
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'version' => DB::select('SELECT VERSION() as version')[0]->version ?? 'N/A',
        ];

        // Cache d'application
        $cacheInfo = [
            'driver' => config('cache.default'),
            'stores' => array_keys(config('cache.stores')),
        ];

        // Extensions PHP installées
        $extensions = get_loaded_extensions();
        sort($extensions);

        return view('system.info', compact('systemInfo', 'dbInfo', 'cacheInfo', 'extensions'));
    }

    /**
     * Informations base de données
     */
    public function database(Request $request)
    {
        // Tables et leurs tailles
        $tables = DB::select("
            SELECT 
                table_name as name,
                table_rows as rows,
                round(((data_length + index_length) / 1024 / 1024), 2) as size_mb
            FROM information_schema.TABLES 
            WHERE table_schema = '" . config('database.connections.' . config('database.default') . '.database') . "'
            ORDER BY (data_length + index_length) DESC
        ");

        // Statistiques générales
        $stats = [
            'total_tables' => count($tables),
            'total_rows' => array_sum(array_column($tables, 'rows')),
            'total_size_mb' => array_sum(array_column($tables, 'size_mb')),
        ];

        // Dernières migrations
        $migrations = DB::table('migrations')
            ->orderBy('batch', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return view('system.database', compact('tables', 'stats', 'migrations'));
    }

    /**
     * Optimiser la base de données
     */
    public function optimizeDatabase(Request $request)
    {
        try {
            // Optimiser toutes les tables
            $tables = DB::select("SHOW TABLES");
            $tableKey = 'Tables_in_' . config('database.connections.' . config('database.default') . '.database');

            foreach ($tables as $table) {
                DB::statement("OPTIMIZE TABLE " . $table->$tableKey);
            }

            return response()->json([
                'success' => true,
                'message' => 'Base de données optimisée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'optimisation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vider les caches
     */
    public function clearCache(Request $request)
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            return response()->json([
                'success' => true,
                'message' => 'Tous les caches ont été vidés'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du nettoyage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistiques d'utilisation
     */
    public function usage(Request $request)
    {
        $period = $request->input('period', '30'); // 7, 30, 90 jours

        $stats = [
            'users' => [
                'total' => \App\Models\User::count(),
                'active' => \App\Models\User::where('isActive', true)->count(),
                'recent' => \App\Models\User::where('created_at', '>=', now()->subDays($period))->count(),
            ],
            'sites' => [
                'total' => \App\Models\Site::count(),
                'active' => \App\Models\Site::whereHas('collectes', function ($q) use ($period) {
                    $q->where('created_at', '>=', now()->subDays($period));
                })->count(),
            ],
            'collectes' => [
                'total' => \App\Models\Collecte::count(),
                'recent' => \App\Models\Collecte::where('created_at', '>=', now()->subDays($period))->count(),
                'validated' => \App\Models\Collecte::where('isValid', true)->count(),
            ],
            'factures' => [
                'total' => \App\Models\Facture::count(),
                'recent' => \App\Models\Facture::where('created_at', '>=', now()->subDays($period))->count(),
                'amount' => \App\Models\Facture::sum('montant_facture'),
            ],
        ];

        // Évolution quotidienne sur la période
        $dailyStats = [];
        for ($i = $period - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyStats[] = [
                'date' => $date,
                'collectes' => \App\Models\Collecte::whereDate('created_at', $date)->count(),
                'factures' => \App\Models\Facture::whereDate('created_at', $date)->count(),
                'users' => \App\Models\User::whereDate('created_at', $date)->count(),
            ];
        }

        return view('system.usage', compact('stats', 'dailyStats', 'period'));
    }

    /**
     * Télécharger un fichier de sauvegarde
     */
    public function downloadBackup(Request $request, $filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (!File::exists($path)) {
            abort(404, 'Fichier de sauvegarde non trouvé');
        }

        return response()->download($path);
    }

    /**
     * Supprimer un fichier de sauvegarde
     */
    public function deleteBackup(Request $request, $filename)
    {
        try {
            $path = storage_path('app/backups/' . $filename);

            if (File::exists($path)) {
                File::delete($path);
                return response()->json([
                    'success' => true,
                    'message' => 'Sauvegarde supprimée avec succès'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Fichier non trouvé'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exécuter une commande Artisan
     */
    public function runCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string'
        ]);

        $command = $request->input('command');

        // Liste des commandes autorisées pour sécurité
        $allowedCommands = [
            'cache:clear',
            'config:clear',
            'view:clear',
            'route:clear',
            'optimize',
            'queue:restart',
            'migrate:status',
            'storage:link'
        ];

        if (!in_array($command, $allowedCommands)) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non autorisée'
            ], 403);
        }

        try {
            Artisan::call($command);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Commande exécutée avec succès',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatage des tailles de fichiers
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Vérification de la santé du système
     */
    public function healthCheck(Request $request)
    {
        $checks = [];

        // Vérification base de données
        try {
            DB::connection()->getPdo();
            $checks['database'] = ['status' => 'OK', 'message' => 'Connexion réussie'];
        } catch (\Exception $e) {
            $checks['database'] = ['status' => 'ERROR', 'message' => $e->getMessage()];
        }

        // Vérification cache
        try {
            Cache::put('health_check', 'test', 60);
            $value = Cache::get('health_check');
            $checks['cache'] = $value === 'test'
                ? ['status' => 'OK', 'message' => 'Cache fonctionnel']
                : ['status' => 'ERROR', 'message' => 'Cache non fonctionnel'];
        } catch (\Exception $e) {
            $checks['cache'] = ['status' => 'ERROR', 'message' => $e->getMessage()];
        }

        // Vérification stockage
        try {
            Storage::put('health_check.txt', 'test');
            $exists = Storage::exists('health_check.txt');
            Storage::delete('health_check.txt');
            $checks['storage'] = $exists
                ? ['status' => 'OK', 'message' => 'Stockage fonctionnel']
                : ['status' => 'ERROR', 'message' => 'Problème de stockage'];
        } catch (\Exception $e) {
            $checks['storage'] = ['status' => 'ERROR', 'message' => $e->getMessage()];
        }

        // Vérification permissions
        $writablePaths = [
            storage_path(),
            storage_path('logs'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views')
        ];

        $permissionIssues = [];
        foreach ($writablePaths as $path) {
            if (!is_writable($path)) {
                $permissionIssues[] = $path;
            }
        }

        $checks['permissions'] = empty($permissionIssues)
            ? ['status' => 'OK', 'message' => 'Toutes les permissions sont correctes']
            : ['status' => 'ERROR', 'message' => 'Permissions incorrectes: ' . implode(', ', $permissionIssues)];

        // Status global
        $globalStatus = collect($checks)->every(function ($check) {
            return $check['status'] === 'OK';
        });

        return view('system.health', compact('checks', 'globalStatus'));
    }
}
