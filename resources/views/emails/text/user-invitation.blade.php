PSSP IMPACT+ - Invitation à rejoindre la plateforme
====================================================

Bonjour,

{{ $inviterName }} vous invite à rejoindre la plateforme PSSP IMPACT+
(gestion des déchets biomédicaux) avec le profil : {{ $role }}.
@if($siteName)
Site concerné : {{ $siteName }}@if($isSiteResponsable) (en tant que responsable du site)@endif
@endif

Pour accepter l'invitation et créer votre compte, ouvrez ce lien :

{{ route('invitation.accept', $token) }}

Cette invitation expire sous 48 heures.

Si vous ne vous attendiez pas à recevoir cette invitation, vous pouvez
ignorer cet e-mail.

--
© {{ date('Y') }} PSSP IMPACT+. Tous droits réservés.
