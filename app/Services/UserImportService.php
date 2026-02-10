<?php

namespace App\Services;

use App\Models\User;
use App\Models\Site;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UserImportService
{
    /**
     * Importer des utilisateurs depuis un fichier CSV
     *
     * @param string $filePath Chemin du fichier
     * @return array Résultat de l'import (succès, erreurs)
     */
    public function import(string $filePath)
    {
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'Fichier introuvable.'];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Impossible d\'ouvrir le fichier.'];
        }

        // Lire l'en-tête
        $header = fgetcsv($handle, 1000, ',');
        
        // Mapping des colonnes (si nécessaire, ou on suppose un ordre fixe)
        // On suppose : firstname, lastname, username, email, role, site_name, phone
        
        $results = [
            'success_count' => 0,
            'error_count' => 0,
            'errors' => [],
        ];

        $rowNumber = 1; // 1 pour l'en-tête

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNumber++;
            
            // Vérifier le nombre de colonnes minimum
            if (count($data) < 4) {
                $results['error_count']++;
                $results['errors'][] = "Ligne $rowNumber : Format invalide (colonnes manquantes).";
                continue;
            }

            $firstname = trim($data[0] ?? '');
            $lastname = trim($data[1] ?? '');
            $username = trim($data[2] ?? '');
            $email = trim($data[3] ?? '');
            $roleName = trim($data[4] ?? '');
            $siteName = trim($data[5] ?? '');
            $phone = trim($data[6] ?? '');

            // Validation
            $validator = Validator::make([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'username' => $username,
                'email' => $email,
                'role' => $roleName,
            ], [
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'role' => 'nullable|exists:roles,name',
            ]);

            if ($validator->fails()) {
                $results['error_count']++;
                $results['errors'][] = "Ligne $rowNumber ($email) : " . implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                // Création de l'utilisateur
                $password = Str::random(10); // Mot de passe aléatoire temporaire
                
                $user = User::create([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'phone' => $phone,
                    'isActive' => true,
                ]);

                // Assigner le rôle
                if ($roleName) {
                    $user->assignRole($roleName);
                } else {
                    $user->assignRole('Visiteur'); // Rôle par défaut
                }

                // Assigner le site si fourni
                if ($siteName) {
                    $site = Site::where('site_name', $siteName)->first();
                    if ($site) {
                        $user->site_id = $site->site_id;
                        $user->save();
                    }
                }

                $results['success_count']++;
                
                // Ici on pourrait envoyer un email avec les identifiants
                // Mail::to($user)->send(new NewAccountMail($user, $password));

            } catch (\Exception $e) {
                $results['error_count']++;
                $results['errors'][] = "Ligne $rowNumber : Erreur système (" . $e->getMessage() . ")";
                Log::error("Import User Error: " . $e->getMessage());
            }
        }

        fclose($handle);
        return $results;
    }
}
