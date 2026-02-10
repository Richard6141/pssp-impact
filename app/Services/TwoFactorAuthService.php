<?php

namespace App\Services;

use App\Models\User;
use App\Models\TwoFactorAuth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorAuthService
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Générer un secret 2FA pour un utilisateur
     */
    public function generateSecret(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();
        
        // Créer ou mettre à jour l'enregistrement 2FA
        $tfa = TwoFactorAuth::updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'secret' => $secret,
                'is_enabled' => false,
            ]
        );

        // Générer le QR code
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // Générer l'image SVG du QR code
        $qrCode = $this->generateQrCode($qrCodeUrl);

        return [
            'secret' => $secret,
            'qr_code' => $qrCode,
            'recovery_codes' => $this->generateRecoveryCodes($tfa),
        ];
    }

    /**
     * Générer le QR code SVG
     */
    protected function generateQrCode(string $url): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        
        return $writer->writeString($url);
    }

    /**
     * Générer des codes de récupération
     */
    protected function generateRecoveryCodes(TwoFactorAuth $tfa): array
    {
        $codes = [];
        
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
        }
        
        $tfa->update(['recovery_codes' => $codes]);
        
        return $codes;
    }

    /**
     * Activer le 2FA après validation
     */
    public function enable(User $user, string $code): bool
    {
        $tfa = TwoFactorAuth::where('user_id', $user->user_id)->first();
        
        if (!$tfa || !$tfa->secret) {
            return false;
        }

        if (!$this->verify($user, $code)) {
            return false;
        }

        $tfa->update([
            'is_enabled' => true,
            'enabled_at' => now(),
        ]);

        return true;
    }

    /**
     * Vérifier un code 2FA
     */
    public function verify(User $user, string $code): bool
    {
        $tfa = TwoFactorAuth::where('user_id', $user->user_id)->first();
        
        if (!$tfa || !$tfa->secret) {
            return false;
        }

        $valid = $this->google2fa->verifyKey($tfa->secret, $code);
        
        if ($valid) {
            $tfa->update(['last_used_at' => now()]);
        }

        return $valid;
    }

    /**
     * Vérifier avec un code de récupération
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $tfa = TwoFactorAuth::where('user_id', $user->user_id)->first();
        
        if (!$tfa) {
            return false;
        }

        return $tfa->useRecoveryCode($code);
    }

    /**
     * Désactiver le 2FA
     */
    public function disable(User $user): void
    {
        TwoFactorAuth::where('user_id', $user->user_id)->delete();
    }

    /**
     * Vérifier si le 2FA est activé
     */
    public function isEnabled(User $user): bool
    {
        $tfa = TwoFactorAuth::where('user_id', $user->user_id)->first();
        
        return $tfa && $tfa->is_enabled;
    }

    /**
     * Régénérer les codes de récupération
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $tfa = TwoFactorAuth::where('user_id', $user->user_id)->first();
        
        if (!$tfa) {
            throw new \Exception('2FA non configuré');
        }

        return $this->generateRecoveryCodes($tfa);
    }
}
