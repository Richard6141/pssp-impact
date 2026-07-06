<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Gestion des tokens de réinitialisation : le token envoyé par e-mail
 * n'est jamais stocké en clair (SHA-256 en base) et expire après 60 minutes.
 */
class PasswordResetService
{
    public const EXPIRATION_MINUTES = 60;

    /**
     * Crée (ou remplace) un token pour cet e-mail et retourne le token en clair
     * à insérer dans le lien envoyé.
     */
    public static function createToken(string $email): string
    {
        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => hash('sha256', $plainToken), 'created_at' => Carbon::now()]
        );

        return $plainToken;
    }

    /**
     * Vérifie qu'un token en clair correspond à l'enregistrement et n'est pas expiré.
     */
    public static function validateToken(string $email, string $plainToken): bool
    {
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return false;
        }

        if (Carbon::parse($record->created_at)->addMinutes(self::EXPIRATION_MINUTES)->isPast()) {
            return false;
        }

        return hash_equals($record->token, hash('sha256', $plainToken));
    }

    public static function deleteToken(string $email): void
    {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }
}
