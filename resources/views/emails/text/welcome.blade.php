PSSP IMPACT+ - Plateforme de gestion des déchets biomédicaux
=============================================================

Bonjour {{ $user->firstname }} {{ $user->lastname }},

Votre compte vient d'être créé sur la plateforme PSSP IMPACT+. Bienvenue !

Vos informations :
- Nom d'utilisateur : {{ $user->username }}
- Adresse e-mail : {{ $user->email }}
@if($roleName)
- Profil : {{ $roleName }}
@endif

@if($setPasswordUrl)
Pour activer votre accès, définissez votre mot de passe personnel en ouvrant ce lien :

{{ $setPasswordUrl }}

Ce lien est valide pendant 60 minutes. Passé ce délai, utilisez la fonction
« Mot de passe oublié » sur la page de connexion pour recevoir un nouveau lien.
@else
Vous pouvez dès maintenant vous connecter avec vos identifiants :

{{ $loginUrl }}
@endif

Si vous n'êtes pas à l'origine de cette création de compte, merci de contacter
l'administrateur de la plateforme.

--
© {{ date('Y') }} PSSP IMPACT+. Tous droits réservés.
