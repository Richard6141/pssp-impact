# 📧 SEMAINE 2 : SYSTÈME D'INVITATIONS & GESTION AVANCÉE UTILISATEURS

## 📅 Objectifs
Permettre aux administrateurs d'inviter de nouveaux utilisateurs via email plutôt que de créer les comptes manuellement. Le futur utilisateur reçoit un lien unique, définit son mot de passe et complète son profil.

## 🛠️ Tâches à réaliser

### 1. Base de données & Modèles
- [ ] Migration `create_user_invitations_table`
  - Columns: id, email, token, role_id, inviter_id, registered_at, expires_at, created_at, updated_at
- [ ] Modèle `UserInvitation`
- [ ] Relation avec `User` (inviter)

### 2. Logique Métier (Service)
- [ ] Service `UserInvitationService`
  - `createInvitation(email, role, inviter)`
  - `acceptInvitation(token, password, userData)`
  - `cancelInvitation(id)`
  - `resendInvitation(id)`

### 3. Interface Administration
- [ ] Controller `Admin\UserInvitationController`
  - `index()`: Liste des invitations (en attente, acceptées, expirées)
  - `create()`: Formulaire d'invitation
  - `store()`: Traitement de l'envoi
  - `destroy()`: Annuler une invitation
- [ ] Vues
  - `resources/views/admin/users/invitations/index.blade.php`
  - `resources/views/admin/users/invitations/create.blade.php`

### 4. Interface Publique (Nouvel Utilisateur)
- [ ] Controller `Auth\InvitationController`
  - `show(token)`: Page de définition du mot de passe
  - `accept(token, request)`: Création du compte
- [ ] Vue
  - `resources/views/auth/invitation/accept.blade.php`

### 5. Emails & Notifications
- [ ] Mailable `InvitationEmail`
- [ ] Vue `resources/views/emails/invitation.blade.php`

---

## 📝 Commandes à exécuter

```bash
# Migration
php artisan make:migration create_user_invitations_table

# Modèle
php artisan make:model UserInvitation

# Controllers
php artisan make:controller Admin/UserInvitationController
php artisan make:controller Auth/InvitationController

# Mail
php artisan make:mail InvitationEmail
```

## 🔒 Sécurité
- Token unique et aléatoire (32 chars)
- Expiration du token (48h par défaut)
- Validation que l'email n'existe pas déjà
- Révocation automatique après usage

## 🔄 Flux Utilisateur
1. **Admin** va sur "Utilisateurs > Inviter"
2. Entre l'email et le rôle (ex: Gestionnaire)
3. **Système** génère un token et envoie un email
4. **Utilisateur** reçoit l'email et clique sur le lien
5. Arrive sur "Bienvenue, définissez votre mot de passe"
6. Valide le formulaire
7. **Système** crée le User, marque l'invitation comme acceptée, connecte l'utilisateur
8. Redirection vers Dashboard
