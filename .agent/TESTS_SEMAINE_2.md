# 🧪 PLAN DE TEST - SEMAINE 2 (VALIDATION)

Ce document liste les étapes pour valider les fonctionnalités implémentées durant la Semaine 2 (Gestion Utilisateurs Avancée & Reporting).

## 1. 📧 Invitations & Inscriptions
- [ ] **Envoyer une invitation**
  - Aller dans `Administration > Utilisateurs > Invitations`.
  - Cliquer sur "Inviter un utilisateur".
  - Saisir email: `test.agent@demo.com`, Rôle: `Agent`.
  - Vérifier que l'invitation apparaît en statut "En attente".
- [ ] **Accepter l'invitation**
  - Récupérer le lien d'invitation (depuis Mailtrap ou les logs).
  - Ouvrir le lien dans un navigateur privé.
  - Saisir : Nom, Prénom, Mot de passe.
  - Valider l'inscription -> Redirection vers le Dashboard.

## 2. 👤 Profils Enrichis & Multi-sites
- [ ] **Modifier son profil professionnel**
  - Aller dans `Mon Profil > Infos Pro`.
  - Changer la disponibilité (ex: "Occupé").
  - Ajouter une "Zone d'intervention" (ex: "Zone Nord").
  - Enregistrer et vérifier que les infos sont conservées.
- [ ] **Gestion Multi-sites (Admin)**
  - Aller dans `Administration > Utilisateurs`.
  - Éditer un utilisateur (ex: l'agent créé).
  - Dans "Sites affectés", sélectionner plusieurs sites.
  - Enregistrer.

## 3. 📂 Import / Export de Données
- [ ] **Export Utilisateurs**
  - Aller dans `Administration > Utilisateurs`.
  - Menu Actions > "Exporter (CSV)".
  - Vérifier que le fichier `.csv` contient bien la liste et les colonnes attendues (Nom, Email, Rôle...).
- [ ] **Import Utilisateurs**
  - Aller dans `Administration > Utilisateurs > Import`.
  - Télécharger le modèle CSV.
  - Ajouter 2 lignes d'utilisateurs fictifs dans le CSV.
  - Uploader le fichier.
  - Vérifier que les utilisateurs sont créés (mot de passe par défaut envoyé par mail/log).

## 4. 📊 Rapports & Statistiques
- [ ] **Rapport des Collectes**
  - Aller dans `Rapports > Collectes`.
  - Filtrer par date (Mois en cours).
  - Cliquer sur le bouton **PDF**.
  - Vérifier l'ouverture/téléchargement du PDF et la cohérence des chiffres.
- [ ] **Rapport Financier**
  - Aller dans `Rapports > Financier`.
  - Vérifier l'affichage des graphiques (Revenus, Paiements).
  - Tester l'export **PDF**.
- [ ] **Rapport par Site (Nouveau)**
  - Aller dans `Rapports > Par Site`.
  - Vérifier la liste des sites et leurs KPI (Poids, Collectes).
  - Tester l'export **PDF**.

## 5. 🛡️ Sécurité & Permissions
- [ ] **Vérification Audits**
  - Aller dans `Administration > Audit`.
  - Vérifier que vos actions récentes (Export, Update Profil) sont bien logguées.

---

### ✅ Validation Finale
Une fois ces tests passés, la Semaine 2 peut être considérée comme **TERMINÉE**.
