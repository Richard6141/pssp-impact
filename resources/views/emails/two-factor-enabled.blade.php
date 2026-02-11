<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Double authentification activee</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Bonjour {{ $user->firstname ?? 'utilisateur' }},</p>

    <p>La double authentification (2FA) a ete activee sur votre compte PSSP IMPACT+.</p>

    <p>Si vous n'etes pas a l'origine de cette action, contactez immediatement l'administrateur.</p>

    @if(!empty($recoveryCodes))
        <p><strong>Codes de recuperation:</strong></p>
        <ul>
            @foreach($recoveryCodes as $code)
                <li><code>{{ $code }}</code></li>
            @endforeach
        </ul>
        <p>Conservez ces codes dans un endroit securise.</p>
    @endif

    <p>Cordialement,<br>L'equipe PSSP IMPACT+</p>
</body>
</html>

