<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Diagnostic de délivrabilité : envoie un e-mail de test vers n'importe quelle
 * adresse (Gmail, Yahoo, e-mail professionnel...) et affiche le résultat SMTP.
 *
 * Usage : php artisan mail:test destinataire@exemple.com
 */
class TestMail extends Command
{
    protected $signature = 'mail:test {email : Adresse du destinataire}';

    protected $description = "Envoie un e-mail de test pour vérifier la configuration SMTP et la délivrabilité";

    public function handle(): int
    {
        $email = $this->argument('email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Adresse e-mail invalide : {$email}");
            return self::FAILURE;
        }

        $this->info('Configuration mail active :');
        $this->table(['Paramètre', 'Valeur'], [
            ['MAILER', config('mail.default')],
            ['HOST', config('mail.mailers.smtp.host')],
            ['PORT', config('mail.mailers.smtp.port')],
            ['SCHEME', config('mail.mailers.smtp.scheme') ?? '(auto)'],
            ['FROM', config('mail.from.address')],
        ]);

        $this->info("Envoi d'un e-mail de test à {$email} ...");

        try {
            Mail::raw(
                "Ceci est un e-mail de test envoyé depuis la plateforme PSSP IMPACT+ le " . now()->format('d/m/Y à H:i:s') . ".\n\n"
                . "Si vous recevez ce message, la configuration SMTP fonctionne pour cette adresse.\n"
                . "Vérifiez aussi le dossier spam / courrier indésirable.",
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Test de délivrabilité - PSSP IMPACT+ (' . now()->format('H:i:s') . ')');
                }
            );

            $this->info('✔ E-mail accepté par le serveur SMTP.');
            $this->line('Si le destinataire ne le reçoit pas (y compris en spam), le blocage est côté fournisseur destinataire — vérifiez SPF/DKIM/DMARC du domaine expéditeur et la réputation IP.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('✘ Échec de l\'envoi : ' . $e->getMessage());
            $this->line('Vérifiez les identifiants SMTP (MAIL_USERNAME / MAIL_PASSWORD), le port et le scheme (smtps pour 465, smtp pour 587).');

            return self::FAILURE;
        }
    }
}
