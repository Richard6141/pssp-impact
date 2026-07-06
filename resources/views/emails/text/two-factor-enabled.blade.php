PSSP IMPACT+ - Double authentification activée
===============================================

Bonjour {{ $user->firstname }},

La double authentification (2FA) vient d'être activée sur votre compte
PSSP IMPACT+ ({{ $user->email }}).

@if(!empty($recoveryCodes))
Vos codes de récupération (conservez-les en lieu sûr, chacun n'est utilisable
qu'une seule fois) :

@foreach($recoveryCodes as $code)
- {{ $code }}
@endforeach
@endif

Si vous n'êtes pas à l'origine de cette activation, contactez immédiatement
l'administrateur de la plateforme.

--
© {{ date('Y') }} PSSP IMPACT+. Tous droits réservés.
