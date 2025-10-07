<?php

namespace App\Http\Controllers;

use Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

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
            // Augmenter le temps d'exécution et la mémoire
            set_time_limit(600); // 10 minutes
            ini_set('memory_limit', '1024M');

            $backupName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('app/backups/' . $backupName);

            // Vérifier que le dossier existe
            if (!File::exists(storage_path('app/backups'))) {
                File::makeDirectory(storage_path('app/backups'), 0755, true);
            }

            // Utiliser uniquement la méthode PHP native (compatible avec tous les hébergeurs)
            return $this->createBackupNative($backupPath, $backupName);
        } catch (\Exception $e) {
            // Nettoyer en cas d'erreur
            if (isset($backupPath) && File::exists($backupPath)) {
                File::delete($backupPath);
            }

            \Log::error('Backup creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la sauvegarde: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une sauvegarde en utilisant PHP natif
     */
    private function createBackupNative($backupPath, $backupName)
    {
        try {
            $handle = fopen($backupPath, 'w');

            if (!$handle) {
                throw new \Exception('Impossible de créer le fichier de sauvegarde');
            }

            // En-tête SQL
            fwrite($handle, "-- MySQL Dump (PHP Native)\n");
            fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "-- Database: " . config('database.connections.' . config('database.default') . '.database') . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
            fwrite($handle, "SET AUTOCOMMIT=0;\n");
            fwrite($handle, "START TRANSACTION;\n\n");

            // Récupérer toutes les tables
            $tables = DB::select('SHOW TABLES');
            $dbName = config('database.connections.' . config('database.default') . '.database');
            $tableKey = 'Tables_in_' . $dbName;

            $totalTables = count($tables);
            $processedTables = 0;

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                $processedTables++;

                // Log progression
                if ($processedTables % 5 == 0) {
                    \Log::info("Backup progress: {$processedTables}/{$totalTables} tables");
                }

                // Structure de la table
                fwrite($handle, "\n--\n");
                fwrite($handle, "-- Table structure for table `{$tableName}`\n");
                fwrite($handle, "--\n\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
                fwrite($handle, $createTable->{'Create Table'} . ";\n\n");

                // Compter le nombre de lignes
                $count = DB::table($tableName)->count();

                if ($count > 0) {
                    fwrite($handle, "--\n");
                    fwrite($handle, "-- Dumping data for table `{$tableName}` ({$count} rows)\n");
                    fwrite($handle, "--\n\n");

                    // Récupérer les noms de colonnes
                    $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
                    $columnList = '`' . implode('`, `', $columns) . '`';

                    // Traiter les données par lots pour économiser la mémoire
                    $chunkSize = 500;

                    DB::table($tableName)->orderBy($columns[0])->chunk($chunkSize, function ($rows) use ($handle, $tableName, $columnList, $columns) {
                        $insertStatement = "INSERT INTO `{$tableName}` ({$columnList}) VALUES\n";
                        $values = [];

                        foreach ($rows as $row) {
                            $rowValues = [];
                            foreach ($columns as $column) {
                                $value = $row->$column ?? null;

                                if (is_null($value)) {
                                    $rowValues[] = 'NULL';
                                } elseif (is_numeric($value)) {
                                    $rowValues[] = $value;
                                } else {
                                    // Échapper correctement les caractères spéciaux
                                    $escaped = str_replace(
                                        ["\\", "\0", "\n", "\r", "'", '"', "\x1a"],
                                        ["\\\\", "\\0", "\\n", "\\r", "\\'", '\\"', "\\Z"],
                                        $value
                                    );
                                    $rowValues[] = "'" . $escaped . "'";
                                }
                            }
                            $values[] = '(' . implode(', ', $rowValues) . ')';
                        }

                        if (!empty($values)) {
                            fwrite($handle, $insertStatement);
                            fwrite($handle, implode(",\n", $values) . ";\n\n");
                        }
                    });
                }

                // Libérer la mémoire
                unset($rows, $values);
                if ($processedTables % 10 == 0) {
                    gc_collect_cycles();
                }
            }

            fwrite($handle, "COMMIT;\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);

            if (File::exists($backupPath) && File::size($backupPath) > 0) {
                \Log::info('Backup created successfully', ['file' => $backupName, 'size' => File::size($backupPath)]);

                return response()->json([
                    'success' => true,
                    'message' => 'Sauvegarde créée avec succès',
                    'backup' => $backupName,
                    'size' => $this->formatBytes(File::size($backupPath))
                ]);
            } else {
                throw new \Exception('Le fichier de sauvegarde est vide ou invalide');
            }
        } catch (\Exception $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            if (File::exists($backupPath)) {
                File::delete($backupPath);
            }

            \Log::error('Native backup failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Vérifier si une commande existe
     */
    private function commandExists($command)
    {
        try {
            $whereIsCommand = (PHP_OS_FAMILY === 'Windows') ? 'where' : 'which';
            $process = proc_open(
                "$whereIsCommand $command",
                [
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes
            );

            if ($process !== false) {
                $stdout = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                return !empty(trim($stdout));
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Mode maintenance
     */
    public function maintenance(Request $request)
    {
        // Vérifier l'état du mode maintenance
        $isDown = app()->isDownForMaintenance();

        // Log pour debug
        \Log::info('Maintenance status check', [
            'isDown' => $isDown,
            'file_exists' => File::exists(storage_path('framework/down')),
            'storage_path' => storage_path('framework/down')
        ]);

        return view('system.maintenance', compact('isDown'));
    }

    /**
     * Activer/Désactiver le mode maintenance
     */
    public function toggleMaintenance(Request $request)
    {
        try {
            $isCurrentlyDown = app()->isDownForMaintenance();

            if ($isCurrentlyDown) {
                Artisan::call('up');
                $message = 'Mode maintenance désactivé';
                $status = 'up';
                $secret = null;
            } else {
                // Générer un secret unique
                $secret = \Illuminate\Support\Str::random(32);

                Artisan::call('down', [
                    '--retry' => 60,
                    '--secret' => $secret,
                    '--render' => 'errors::503',
                ]);

                $message = 'Mode maintenance activé';
                $status = 'down';

                // Sauvegarder le secret
                Cache::put('maintenance_secret', $secret, now()->addDays(1));
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $status,
                'secret' => $secret, // Retourner le secret pour accès admin
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
    /**
     * Informations base de données
     */
    public function database(Request $request)
    {
        // Tables et leurs tailles
        $tables = DB::select("
        SELECT 
            table_name as name,
            table_rows as `rows`,
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

    /**
     * Vider les logs système
     */
    public function clearLogs(Request $request)
    {
        try {
            $logFile = storage_path('logs/laravel.log');

            if (!File::exists($logFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier de logs non trouvé'
                ], 404);
            }

            // Vérifier que le fichier n'est pas vide
            $fileSize = File::size($logFile);
            if ($fileSize === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le fichier de logs est déjà vide'
                ], 400);
            }

            // Créer le dossier d'archives s'il n'existe pas
            $archivePath = storage_path('logs/archives');
            if (!File::exists($archivePath)) {
                File::makeDirectory($archivePath, 0755, true);
            }

            // Créer le nom du fichier d'archive avec timestamp
            $timestamp = date('Y-m-d_H-i-s');
            $archiveFile = $archivePath . '/laravel_' . $timestamp . '.log';

            // Copier le fichier de log vers les archives
            if (!File::copy($logFile, $archiveFile)) {
                throw new \Exception('Impossible de créer la copie d\'archive des logs');
            }

            // Compresser l'archive pour économiser de l'espace (optionnel)
            if (function_exists('gzencode')) {
                $logContent = File::get($archiveFile);
                $compressedFile = $archiveFile . '.gz';

                if (File::put($compressedFile, gzencode($logContent, 9))) {
                    // Supprimer la version non compressée
                    File::delete($archiveFile);
                    $archiveFile = $compressedFile;
                }
            }

            // Vider le fichier de log actuel
            File::put($logFile, '');

            // Logger l'action avec les détails
            \Log::info('Logs système vidés', [
                'user' => auth()->user()->firstname,
                'user_id' => auth()->id(),
                'archive_file' => basename($archiveFile),
                'original_size' => $this->formatBytes($fileSize),
                'archive_size' => $this->formatBytes(File::size($archiveFile)),
                'timestamp' => $timestamp
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Les logs ont été vidés avec succès',
                'archive' => [
                    'filename' => basename($archiveFile),
                    'size' => $this->formatBytes(File::size($archiveFile)),
                    'original_size' => $this->formatBytes($fileSize),
                    'path' => 'storage/logs/archives/' . basename($archiveFile)
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors du vidage des logs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du vidage des logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Télécharger les logs système
     */
    public function downloadLogs(Request $request)
    {
        try {
            $logFile = storage_path('logs/laravel.log');

            if (!File::exists($logFile)) {
                abort(404, 'Fichier de logs non trouvé');
            }

            // Nom du fichier avec timestamp
            $filename = 'laravel-logs-' . date('Y-m-d_H-i-s') . '.log';

            return response()->download($logFile, $filename, [
                'Content-Type' => 'text/plain',
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors du téléchargement des logs', ['error' => $e->getMessage()]);
            abort(500, 'Erreur lors du téléchargement des logs');
        }
    }
}
