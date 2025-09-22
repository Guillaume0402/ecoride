# EcoRide — Plateforme de covoiturage éco-responsable

## Démo modération (Mongo)

Cette démo montre la modération d’avis/signalements (stockés dans MongoDB) côté Employé.

1. Seed Mongo (avis + signalements)

Exécuter le script:

```bash
php scripts/seed_mongo.php
```

Le script insère 4 documents pending dans la collection `reviews` (DB `ecoride`):

- 2 avis (`kind: review`),
- 2 signalements (`kind: report`).

2. Parcours Employé

- Connectez-vous avec un compte employé (role_id = 2), puis ouvrez `/employe`.
- Vous voyez:
    - “Avis en attente”: liste des reviews (commentaire + note) avec boutons Valider/Refuser (CSRF inclus).
    - “Trajets signalés”: liste des reports (raison + commentaire + date).
- En validant un “report”, des emails sont envoyés au conducteur et au passager concernés (Mailer SMTP si configuré, sinon fallback).

3. Parcours Passager (optionnel)

- Quand un conducteur termine un trajet, chaque passager reçoit un email l’invitant à “Valider” ou “Signaler”.
- “Valider” crédite le conducteur (idempotent par motif), “Signaler” crée un document Mongo pending.

## Seed SQL rapide (trajet terminé + participation confirmée)

Pour rejouer facilement la validation passager, insérez un trajet terminé et une participation confirmée:

```sql
-- À exécuter dans MySQL (par ex. via phpMyAdmin)
-- Supposons que vous avez déjà un driver (id=11) et un passager (id=21) existants et un véhicule id=1 appartenant au driver.

INSERT INTO covoiturages (driver_id, vehicle_id, adresse_depart, adresse_arrivee, depart, arrivee, prix, status)
VALUES (11, 1, 'Paris', 'Lyon', NOW() - INTERVAL 3 HOUR, NOW() - INTERVAL 1 HOUR, 5.00, 'termine');

SET @covoit_id = LAST_INSERT_ID();

INSERT INTO participations (covoiturage_id, passager_id, status)
VALUES (@covoit_id, 21, 'confirmee');
```

Ensuite, connectez-vous en tant que passager (id=21), allez sur “Mes trajets” et utilisez “Valider” pour déclencher le crédit du conducteur.

Un script prêt à l’emploi est disponible: `scripts/seed_finished_trip.sql` (éditez les IDs au besoin puis exécutez-le dans MySQL).

Projet réalisé dans le cadre de la certification **Développeur Web et Web Mobile (DWWM)** — ECF final.  
EcoRide est une application web de covoiturage éco-responsable permettant aux utilisateurs de proposer ou de réserver des trajets en toute sécurité.

---

## Fonctionnalités principales

- Inscription et connexion sécurisées (CSRF, sessions, hashage)
- **Politique de mot de passe robuste** (≥12, maj/min/chiffre/spécial, sans espace, pas de pseudo/e-mail)
- **Rehash automatique** des anciens mots de passe au login (mise à niveau transparente)
- Rôles utilisateurs (passager, chauffeur, employé, admin)
- Gestion des véhicules pour les chauffeurs
- Réservation de covoiturage
    - Bouton "Participer" visible si: connecté en rôle Utilisateur, trajets à venir, places restantes, crédits suffisants
    - Double confirmation côté front (2 étapes) avant soumission
    - Vérifications serveur: CSRF, non conducteur, non dupliqué, capacité, crédits suffisants
    - Débit des crédits du passager selon le prix du trajet (arrondi, min 1) et participation confirmée immédiatement
    - Historique des transactions consultable dans "Mes crédits"
- Pages d’erreurs personnalisées (404, 405, 500)
- Interface responsive avec **Bootstrap 5 + SCSS**
- API JSON pour login/register (AJAX avec fetch)
    - Confirmation e-mail à l’inscription (lien valable 24h)
    - En dev, si l’envoi e-mail échoue, un fallback est écrit dans `/tmp/ecoride-mail.log` (dans le conteneur)

---

## Stack technique

- **Backend :** PHP 8.2 (MVC maison, PDO MySQL, Composer autoload)
- **Frontend :** HTML5, Bootstrap 5, SCSS, JavaScript (vanilla, fetch API)
- **Base de données :**
    - MySQL 8 (données relationnelles : users, véhicules, covoiturages…)
    - MongoDB (optionnel, pour stocker les avis utilisateurs flexibles)
- **Outils :**
    - phpMyAdmin (gestion MySQL)
    - Mongo Express (gestion MongoDB)
    - Docker + Docker Compose (environnement reproductible)
    - phpdotenv (gestion des variables d’environnement)

---

## Architecture du projet

```
src/
 ├─ Controller/     # Logique applicative (AuthController, PageController, …)
 ├─ Entity/         # Entités PHP (UserEntity, VehicleEntity, …)
 ├─ Repository/     # Accès aux données (UserRepository, VehicleRepository, …)
 ├─ Db/             # Connexion PDO (Mysql)
 ├─ Routing/        # Router + config des routes
 ├─ Security/       # Helpers de sécurité (CSRF, PasswordPolicy)
 └─ View/           # Vues PHP (pages, layouts, erreurs)
public/             # index.php, assets, JS/CSS compilés
config/             # routes.php, constants.php, env
```

---

## ▶ Installation locale avec Docker

### 1. Cloner le projet

```bash
git clone https://github.com/Guillaume0402/ecoride.git
cd ecoride
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Lancer les conteneurs

```bash
docker compose up -d --build
```

### 4. Accès aux services

- Application : http://localhost:8080
- phpMyAdmin : http://localhost:8081
    - host : `db`
    - user : valeur définie dans `.env.local` (`DB_USER`)
    - password : valeur définie dans `.env.local` (`DB_PASSWORD`)

---

## Variables d’environnement

Exemple de fichier `.env.local` :

```env
DB_HOST=db
DB_NAME=ecoride
DB_USER=ecoride_user
DB_PASSWORD=ecoride_password
DB_PORT=3306

APP_ENV=dev

# URL du site (avec slash final) utilisée dans les liens e-mails
SITE_URL=http://localhost:8080/

# E-mail / SMTP (optionnel mais recommandé en prod)
MAIL_FROM=no-reply@localhost
MAIL_FROM_NAME=EcoRide
# Active l'envoi SMTP si défini
SMTP_HOST=
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
SMTP_SECURE=tls
# Optionnel
MAIL_REPLY_TO=
SMTP_DEBUG=0

# Notes e-mail
# - En production, si aucune configuration SMTP n'est fournie (SMTP_HOST vide),
#   l'application n'essaiera pas d'utiliser mail() (souvent inopérant sur PaaS) et journalisera les emails dans un fichier de log.
# - Emplacement du log emails (tous environnements): ${TMPDIR}/ecoride-mail.log (ex: /tmp/ecoride-mail.log sur Linux/Heroku).

# Frais plateforme (crédits)
# Nombre de crédits débités au conducteur lors de la création d'un trajet
# Par défaut: 2 si non défini
RIDE_CREATE_FEE_CREDITS=2
```

Ne pas versionner vos vrais identifiants de production.

---

## Crédits et transactions

- Débit passager: au moment où le conducteur accepte la participation, le passager est débité du prix (arrondi à l'entier supérieur, min 1).
- Crédit conducteur: quand le passager valide le trajet terminé, le conducteur est crédité (idempotent par motif).
- Frais de création: lorsque le conducteur crée un covoiturage, la plateforme prélève `RIDE_CREATE_FEE_CREDITS` crédits immédiatement. Si la création échoue techniquement, les crédits sont remboursés.

---

## Sécurité mise en place

### Politique de mot de passe (PasswordPolicy)

- Au moins **1 minuscule**, **1 majuscule**, **1 chiffre**, **1 caractère spécial**
- **Aucun espace**
- Interdiction de contenir le **pseudo** ou la **partie locale** de l’**e-mail**
- Validation **côté serveur** (source de vérité) + _hint_ HTML côté **inscription** / **changement de mot de passe** (`pattern`/`minlength`)  
    ↳ Le **formulaire de connexion** n’impose **aucun pattern** pour laisser passer les anciens comptes ; la vérification est faite **au serveur**.

### Hashage

- `password_hash()` avec **Argon2id** si disponible, sinon **bcrypt** (cost 12)
- **Rehash automatique** au login si l’algo/le coût/la config changent (mise à niveau transparente)

### E-mail

- **Normalisé en minuscule** à l’inscription et au login (recherche cohérente)
- _(Optionnel recommandé)_ **Index unique** en BDD :

```sql
ALTER TABLE users ADD UNIQUE INDEX uniq_users_email (email);
```

### Autres protections

- CSRF sur les endpoints JSON `register` et `login`
- Sessions sécurisées : `session_regenerate_id(true)` à la connexion/déconnexion
- PDO + requêtes préparées (anti-injection)
- Codes d’erreur et pages 404/405/500 personnalisées

---

## Accès de test (exemple)

- Employé : employe@example.com / Employe!234
- Utilisateur : user@example.com / User!234

---

## Checklist ECF

- [x] Déploiement local reproductible avec Docker
- [x] Sécurité mots de passe robuste + hash + rehash auto + CSRF + PDO
- [x] Documentation d’installation (README)
- [x] Pages d’erreurs personnalisées
- [x] Emails OK en prod (via SendGrid)

---

## Délivrabilité e-mail (SendGrid)

Pour une livraison fiable des e-mails en production, EcoRide utilise un relais SMTP (SendGrid recommandé).

- Vérification d’expéditeur rapide: Single Sender Verification (Settings → Sender Authentication → Single Sender). Utiliser la même adresse en `MAIL_FROM`.
- Meilleure pratique: Domain Authentication (SPF/DKIM) sur votre domaine pour améliorer la réputation et sortir des spams durablement.
- Variables utiles (optionnelles):
    - `MAIL_REPLY_TO` pour diriger les réponses vers une boîte support
    - `LIST_UNSUBSCRIBE_URL` et/ou `LIST_UNSUBSCRIBE_MAILTO` (+ `LIST_UNSUBSCRIBE_POST=1` pour One-Click)
- Diagnostic:
    - Suivre les envois dans SendGrid → Activity
    - En cas d’échec SMTP ou d’absence de config, un fallback est journalisé: `/tmp/ecoride-mail.log`
- Test CLI (depuis l’app): `php scripts/send_test_email.php destinataire@example.com`

Astuce: Si un premier envoi tombe en SPAM, marquez comme « Non-spam » et authentifiez votre domaine (SPF/DKIM) pour stabiliser.

---

## Convention de branches (Git)

Branche principale: `main` (stable). Branche d’intégration: `dev`.

Conventions: `feature/…` ou `feat/…`, `fix/…`, `refactor/…`, `chore/…`, `docs/…`.

Exemples conservés pour l’ECF: `feature/modal-auth`, `feat/csrf-login`, `fix/login-error-message`, `refactor/javascript`, `chore/error-handler`, `docs/readme-ecf`.

Stratégie complète et commandes: voir `documentation/git-strategie.md`.

---

## Licence

Projet pédagogique — libre pour l’ECF **DWWM Studi**.
```
