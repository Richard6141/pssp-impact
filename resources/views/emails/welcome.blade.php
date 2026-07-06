<!DOCTYPE html>
<html lang="fr">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7f5; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .logo { font-size: 24px; font-weight: bold; color: #14532d; margin-bottom: 8px; }
        .tagline { font-size: 13px; color: #64748b; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #16a34a; padding-bottom: 16px; }
        .content { color: #475569; line-height: 1.6; font-size: 16px; margin-bottom: 30px; }
        .infos { background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 16px 20px; border-radius: 6px; margin: 20px 0; }
        .infos p { margin: 6px 0; }
        .button-container { text-align: center; margin: 30px 0; }
        .button { display: inline-block; padding: 13px 28px; font-size: 16px; font-weight: bold; color: #ffffff !important; background-color: #16a34a; text-decoration: none; border-radius: 6px; }
        .warning { background-color: #fef9c3; border-left: 4px solid #eab308; padding: 12px 16px; border-radius: 4px; font-size: 14px; color: #713f12; margin-top: 20px; }
        .footer { text-align: center; font-size: 13px; color: #94a3b8; margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .small-text { font-size: 12px; color: #64748b; margin-top: 10px; word-break: break-all; }
    </style>
</head>
<body>
    <div style="padding: 40px 20px;">
        <div class="email-container">
            <div class="header">
                <div class="logo">PSSP IMPACT+</div>
                <div class="tagline">Plateforme de gestion des déchets biomédicaux</div>
            </div>

            <div class="content">
                <p>Bonjour <strong>{{ $user->firstname }} {{ $user->lastname }}</strong>,</p>

                <p>Votre compte vient d'être créé sur la plateforme <strong>PSSP IMPACT+</strong>. Bienvenue&nbsp;!</p>

                <div class="infos">
                    <p><strong>Nom d'utilisateur :</strong> {{ $user->username }}</p>
                    <p><strong>Adresse e-mail :</strong> {{ $user->email }}</p>
                    @if($roleName)
                        <p><strong>Profil :</strong> {{ $roleName }}</p>
                    @endif
                </div>

                @if($setPasswordUrl)
                    <p>Pour activer votre accès, définissez votre mot de passe personnel en cliquant sur le bouton ci-dessous&nbsp;:</p>

                    <div class="button-container">
                        <a href="{{ $setPasswordUrl }}" class="button">Définir mon mot de passe</a>
                    </div>

                    <div class="warning">
                        Ce lien est valide pendant <strong>60 minutes</strong>. Passé ce délai, utilisez la fonction
                        «&nbsp;Mot de passe oublié&nbsp;» sur la page de connexion pour recevoir un nouveau lien.
                    </div>
                @else
                    <p>Vous pouvez dès maintenant vous connecter avec vos identifiants&nbsp;:</p>

                    <div class="button-container">
                        <a href="{{ $loginUrl }}" class="button">Se connecter</a>
                    </div>
                @endif

                <p style="margin-top: 20px;">Si vous n'êtes pas à l'origine de cette création de compte, merci de contacter l'administrateur de la plateforme.</p>
            </div>

            <div class="footer">
                <p>&copy; {{ date('Y') }} PSSP IMPACT+. Tous droits réservés.</p>
                @if($setPasswordUrl)
                    <div class="small-text">
                        Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
                        <a href="{{ $setPasswordUrl }}" style="color: #16a34a;">{{ $setPasswordUrl }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
