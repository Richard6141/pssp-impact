<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f7f9fc; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .logo { font-size: 24px; font-weight: bold; color: #1e293b; margin-bottom: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .content { color: #475569; line-height: 1.6; font-size: 16px; margin-bottom: 30px; }
        .button-container { text-align: center; margin-top: 30px; margin-bottom: 30px; }
        .button { display: inline-block; padding: 12px 24px; font-size: 16px; font-weight: bold; color: #ffffff; background-color: #2563eb; text-decoration: none; border-radius: 6px; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2); }
        .warning { background-color: #fef9c3; border-left: 4px solid #eab308; padding: 12px 16px; border-radius: 4px; font-size: 14px; color: #713f12; margin-top: 20px; }
        .footer { text-align: center; font-size: 14px; color: #94a3b8; margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .small-text { font-size: 12px; color: #64748b; margin-top: 10px; word-break: break-all; }
    </style>
</head>
<body>
    <div style="padding: 40px 20px;">
        <div class="email-container">
            <div class="header">
                <div class="logo">PSSP IMPACT+</div>
            </div>

            <div class="content">
                <p>Bonjour,</p>

                <p>Nous avons reçu une demande de réinitialisation du mot de passe pour le compte associé à <strong>{{ $userEmail }}</strong>.</p>

                <p>Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>

                <div class="button-container">
                    <a href="{{ $resetUrl }}" class="button">Réinitialiser mon mot de passe</a>
                </div>

                <div class="warning">
                    Ce lien est valide pendant <strong>60 minutes</strong>. Après ce délai, vous devrez faire une nouvelle demande.
                </div>

                <p style="margin-top: 20px;">Si vous n'avez pas demandé la réinitialisation de votre mot de passe, ignorez cet email. Votre compte reste sécurisé.</p>
            </div>

            <div class="footer">
                <p>&copy; {{ date('Y') }} PSSP IMPACT+. Tous droits réservés.</p>
                <div class="small-text">
                    Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :<br>
                    <a href="{{ $resetUrl }}" style="color: #2563eb;">{{ $resetUrl }}</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
