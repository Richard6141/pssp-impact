# Checklist de déploiement — PSSP IMPACT+

## À chaque mise en production

```bash
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force        # applique aussi les permissions Agent santé
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan permission:cache-reset # vide le cache Spatie après changement de rôles
php artisan up
```

## E-mails : vérification de la délivrabilité

Le domaine `pssp-impactplus.com` a déjà SPF, DKIM (Hostinger) et DMARC configurés.

1. **Tester l'envoi réel depuis la prod** (Gmail, Yahoo ET un e-mail professionnel) :
   ```bash
   php artisan mail:test destinataire@yahoo.fr
   ```
   - Si la commande échoue → problème SMTP (identifiants, port). Vérifier `MAIL_*` dans `.env` :
     `MAIL_MAILER=smtp`, `MAIL_HOST=smtp.hostinger.com`, `MAIL_PORT=465`, `MAIL_SCHEME=smtps`.
   - Si la commande réussit mais rien n'arrive (même en spam) → blocage côté fournisseur
     destinataire ; surveiller `storage/logs/laravel.log` (chaque envoi est journalisé).

2. **Surveiller les échecs d'envoi** : rechercher `Échec envoi e-mail` dans
   `storage/logs/laravel.log`. L'application ne bloque plus jamais une action métier
   sur un échec SMTP, mais l'échec est tracé.

3. **Renforcer DMARC (recommandé, après 2-3 semaines d'observation)** : passer
   l'enregistrement DNS `_dmarc` de `p=none` à `p=quarantine` pour améliorer la
   réputation du domaine auprès de Yahoo/Outlook.

4. **Si des blocages persistent vers Yahoo/e-mails professionnels** : envisager un
   service transactionnel dédié (Brevo, Resend, Postmark) — meilleure réputation IP
   que le SMTP mutualisé Hostinger. Seul le bloc `MAIL_*` du `.env` est à changer.

## Sécurité du reset mot de passe

- Les tokens sont désormais hachés (SHA-256) en base et expirent après **60 minutes**.
- Les anciens liens de réinitialisation émis avant cette version sont invalides
  (les utilisateurs doivent refaire une demande — comportement attendu).

## Nouveaux comportements

- **Création de compte par un admin** : l'utilisateur reçoit un e-mail de bienvenue
  avec un lien (60 min) pour définir son propre mot de passe.
- **Auto-inscription** : e-mail de confirmation de création de compte.
- **Agent santé** : peut valider (signer) les quantités de DBM enlevées sur les sites
  auxquels il est rattaché (site principal ou sites affectés), en plus du responsable
  de site. La validation finale reste réservée au Coordonnateur/Administrateur.
