# EcoRide — Plateforme de covoiturage éco-responsable

Projet réalisé dans le cadre de la certification **Développeur Web et Web Mobile (DWWM)** — ECF final.  
EcoRide est une application web de covoiturage éco-responsable permettant aux utilisateurs de proposer ou de réserver des trajets en toute sécurité.

---

## Fonctionnalités principales

-   Inscription et connexion sécurisées (CSRF, sessions, hashage)
-   **Politique de mot de passe robuste** (≥12, maj/min/chiffre/spécial, sans espace, pas de pseudo/e-mail)
-   **Rehash automatique** des anciens mots de passe au login (mise à niveau transparente)
-   Rôles utilisateurs (passager, chauffeur, employé, admin)
-   Gestion des véhicules pour les chauffeurs
-   Réservation de covoiturage
    -   Bouton "Participer" visible si: connecté en rôle Utilisateur, trajets à venir, places restantes, crédits suffisants
    -   Double confirmation côté front (2 étapes) avant soumission
    -   Vérifications serveur: CSRF, non conducteur, non dupliqué, capacité, crédits suffisants
    -   Débit des crédits du passager selon le prix du trajet (arrondi, min 1) et participation confirmée immédiatement
    -   Historique des transactions consultable dans "Mes crédits"
-   Pages d’erreurs personnalisées (404, 405, 500)
-   Interface responsive avec **Bootstrap 5 + SCSS**
-   API JSON pour login/register (AJAX avec fetch)
    -   Confirmation e-mail à l’inscription (lien valable 24h)
    -   En dev, si l’envoi e-mail échoue, un fallback est écrit dans `/tmp/ecoride-mail.log` (dans le conteneur)

---

## Stack technique

-   **Backend :** PHP 8.2 (MVC maison, PDO MySQL, Composer autoload)
-   **Frontend :** HTML5, Bootstrap 5, SCSS, JavaScript (vanilla, fetch API)
-   **Base de données :**
    -   MySQL 8 (données relationnelles : users, véhicules, covoiturages…)
    -   MongoDB (optionnel, pour stocker les avis utilisateurs flexibles)
-   **Outils :**
    -   phpMyAdmin (gestion MySQL)
    -   Mongo Express (gestion MongoDB)
    -   Docker + Docker Compose (environnement reproductible)
    -   phpdotenv (gestion des variables d’environnement)

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

## ▶Installation locale avec Docker

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

-   Application : [http://localhost:8080](http://localhost:8080)
-   phpMyAdmin : [http://localhost:8081](http://localhost:8081)
    -   **host** : `db`
    -   **user** : user : valeur définie dans .env.local (DB_USER)
    -   **password** : password : valeur définie dans .env.local (DB_PASSWORD)
-   Mongo Express (optionnel) : [http://localhost:8082](http://localhost:8082)

---

## Variables d’environnement

Exemple de fichier `.env.local` :

```
DB_HOST=db
DB_NAME=ecoride
DB_USER=ecoride_user
DB_PASSWORD=ecoride_password
DB_PORT=3306

APP_ENV=dev

# E-mail / SMTP (optionnel mais recommandé en prod)
MAIL_FROM=no-reply@localhost
MAIL_FROM_NAME=EcoRide
# Active l'envoi SMTP si défini
SMTP_HOST=
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
SMTP_SECURE=tls
```

Ne pas versionner vos vrais identifiants de production.

---

## Sécurité mise en place

### Politique de mot de passe (PasswordPolicy)

-   Longueur **≥ 12** et **≤ 72** (compat. bcrypt)
-   Au moins **1 minuscule**, **1 majuscule**, **1 chiffre**, **1 caractère spécial**
-   **Aucun espace**
-   Interdiction de contenir le **pseudo** ou la **partie locale** de l’**e-mail**
-   Validation **côté serveur** (source de vérité) + _hint_ HTML côté **inscription** / **changement de mot de passe** (`pattern`/`minlength`)  
    ↳ Le **formulaire de connexion** n’impose **aucun pattern** pour laisser passer les anciens comptes ; la vérification est faite **au serveur**.

### Hashage

-   `password_hash()` avec **Argon2id** si disponible, sinon **bcrypt** (cost 12)
-   **Rehash automatique** au login si l’algo/le coût/la config changent (mise à niveau transparente)

### E-mail

-   **Normalisé en minuscule** à l’inscription et au login (recherche cohérente)
-   _(Optionnel recommandé)_ **Index unique** en BDD :
    ```sql
    ALTER TABLE users ADD UNIQUE INDEX uniq_users_email (email);
    ```

### Autres protections

-   CSRF sur les endpoints JSON `register` et `login`
-   Sessions sécurisées : `session_regenerate_id(true)` à la connexion/déconnexion
-   PDO + requêtes préparées (anti-injection)
-   Codes d’erreur et pages 404/405/500 personnalisées

---

## Accès de test (exemple)

À créer en BDD pour tester :

```
Admin : admin@example.com / Admin!234
Employé : employe@example.com / Employe!234
User : user@example.com / User!234
```

---

## Checklist ECF

-   [x] Projet versionné sur GitHub (branche `dev`)
-   [x] Gestion de projet (branches par fonctionnalités)
-   [x] Déploiement local reproductible avec Docker
-   [x] Sécurité mots de passe robuste + hash + rehash auto + CSRF + PDO
-   [x] Documentation d’installation (README)
-   [x] Pages d’erreurs personnalisées

---

## Licence

Projet pédagogique — libre pour l’ECF **DWWM Studi**.
