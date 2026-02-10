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
        .footer { text-align: center; font-size: 14px; color: #94a3b8; margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .small-text { font-size: 12px; color: #64748b; margin-top: 10px; }
    </style>
</head>
<body>
    <div style="padding: 40px 20px;">
        <div class="email-container">
            <div class="header">
                <div class="logo">PSSP IMPACT+</div>
            </div>
            
            <div class="content">
                <p>Hello,</p>
                
                <p>Vous avez été invité(e) à rejoindre la plateforme **PSSP IMPACT+**.</p>
                
                <p>
                    <strong>Invité par :</strong> {{ $inviterName }}<br>
                    <strong>Rôle assigné :</strong> {{ $role }}
                </p>
                
                <p>Pour activer votre compte et définir votre mot de passe, veuillez cliquer sur le bouton ci-dessous :</p>
                
                <div class="button-container">
                    <a href="{{ route('invitation.accept', $token) }}" class="button">Accepter l'invitation</a>
                </div>
                
                <p>Ce lien est valide pour 48 heures. Si vous n'avez pas créé de compte d'ici là, l'invitation expirera.</p>
                
                <p>Si vous ne vous attendiez pas à recevoir cette invitation, vous pouvez ignorer cet email.</p>
            </div>
            
            <div class="footer">
                <p>&copy; {{ date('Y') }} PSSP IMPACT+. Tous droits réservés.</p>
                <div class="small-text">
                    Si le bouton ne fonctionne pas, copiez et collez le lien suivant dans votre navigateur :<br>
                    <a href="{{ route('invitation.accept', $token) }}" style="color: #2563eb;">{{ route('invitation.accept', $token) }}</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
