<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envoi d'e-mails robuste : un échec SMTP ne doit jamais interrompre
 * l'action métier (création de compte, reset, etc.). Chaque envoi est
 * journalisé pour permettre le diagnostic de délivrabilité en production.
 */
class MailService
{
    /**
     * Envoie un mailable et retourne true/false au lieu de lever une exception.
     */
    public static function send(string $to, Mailable $mailable, string $context = ''): bool
    {
        try {
            Mail::to($to)->send($mailable);

            Log::info('E-mail envoyé', [
                'to' => $to,
                'mailable' => get_class($mailable),
                'context' => $context,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Échec envoi e-mail', [
                'to' => $to,
                'mailable' => get_class($mailable),
                'context' => $context,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
