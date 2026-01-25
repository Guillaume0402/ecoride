# EcoRide — Installation locale (Docker) — Guide complet A → Z

Ce README explique uniquement comment installer et lancer EcoRide en local, du clonage au démarrage, avec toutes les commandes utiles. Pour le reste (fonctionnalités, sécurité, stratégie Git, délivrabilité e‑mail, etc.), voir le dossier `documentation/`.

## Prérequis

-   Docker Desktop + Docker Compose
-   Git
-   Optionnel (assets SCSS): Node.js ≥ 18 et npm

## 1 Cloner le projet

```bash
git clone https://github.com/Guillaume0402/ecoride.git
cd ecoride
```

## 2 Configurer l’environnement

Vous pouvez démarrer sans fichier `.env.local` (Docker injecte des valeurs par défaut pour MySQL), mais il est recommandé d’en créer un pour régler l’URL, l’horodatage, Mongo, SMTP, etc.

-   Copier le modèle et l’éditer:

```bash
cp .env.example .env.local
```

Points clés:

-   `APP_ENV=dev` active l’affichage des erreurs.
-   `SITE_URL` doit se terminer par un slash (ex: `http://localhost:8080/`).
-   SMTP est optionnel en local; sans SMTP, les e‑mails sont journalisés dans `/tmp/ecoride-mail.log` dans le conteneur web.

## 3 Lancer l’environnement Docker

```bash
docker compose up -d --build
```

Services et URLs:

-   Application (Apache + PHP): http://localhost:8080
-   MySQL 8: port hôte 3307 (3306 à l’intérieur du réseau Docker)
-   phpMyAdmin: http://localhost:8081 (serveur: `db`, user: `ecoride_user`, pass: `ecoride_password`)
-   MongoDB (optionnel): port 27017
-   Mongo Express (optionnel): http://localhost:8082 (basic auth: admin / admin)

La base est initialisée automatiquement uniquement si le volume MySQL est vide (premier démarrage ou après reset).

### Réinitialiser la base (rejouer init + seed MySQL)

(Recommandé avant une démonstration/soutenance pour repartir sur des données cohérentes.)

Attention : `-v` supprime le volume MySQL et efface les données locales.

```bash
docker compose down -v
docker compose up -d --build
```

Les scripts exécutés sont ceux montés dans `/docker-entrypoint-initdb.d` (ex: `01_init.sql`, `02_seed_demo.sql`).

## 4 Dépendances PHP (si besoin)

Le répertoire `vendor/` est déjà présent. Pour regénérer/optimiser l’autoload depuis le conteneur:

```bash
docker compose exec -u www-data web composer install -o
```

Vous pouvez aussi utiliser Composer en local si vous avez PHP/Composer sur votre machine.

## 5 Accéder à l’application

-   Application: http://localhost:8080
-   phpMyAdmin: http://localhost:8081
    -   serveur: `db`
    -   utilisateur: `ecoride_user`
    -   mot de passe: `ecoride_password`
-   Mongo Express: http://localhost:8082 (si Mongo est lancé)

## 6 Comptes de démonstration (seed)

Ces comptes sont injectés si la base a été initialisée via `02_seed_demo.sql` (voir reset ci-dessus).

-   Admin: admin@ecoride.local / EcoRide!234
-   Employé: employee@ecoride.local / EcoRide!234
-   Utilisateur: user@ecoride.local / EcoRide!234

Sinon, créez un compte via l’UI. Sans SMTP en local, les mails (confirmation, etc.) sont consignés dans `/tmp/ecoride-mail.log`.

## 7 Données de démo (optionnel)

-   Démo modération (Mongo) — nécessite que le service Mongo soit lancé :

```bash
docker compose exec web php scripts/seed_mongo.php
```

## 8 Assets SCSS (optionnel)

Construire une fois:

```bash
npm install
npm run build
```

Mode développement (watch):

```bash
npm run dev
```

Le CSS généré est écrit dans `public/assets/css/style.css`.

## 9 Dépannage rapide

-   Conflit MySQL local: ce projet expose MySQL sur le port hôte 3307 pour éviter les conflits (ex: WAMP/XAMPP).
-   Variables d’env.: `.env.local` est chargé par l’app (phpdotenv). Docker fournit aussi des valeurs par défaut compatibles.
-   E‑mails en local: sans SMTP, pas d’envoi; vérifiez `/tmp/ecoride-mail.log` dans le conteneur web.
-   Réinitialiser MySQL (efface les données locales) : voir la commande `docker compose down -v` ci-dessus.

## Références (documentation)

-   Démo modération Mongo: `documentation/demo-moderation-mongo.md`
-   Délivrabilité e‑mail (Gmail SMTP): utilisez un From @gmail.com aligné avec Gmail SMTP pour des tests à faible volume. Pour un domaine pro, configurez SPF/DKIM/DMARC chez votre fournisseur SMTP.
-   Déploiement Heroku: `documentation/deploiement-heroku.md`
-   Guide MySQL & connexions: `documentation/mysql-database-guide.md`
-   Rappels Router/MVC: `documentation/router_mvc_recap.md`
-   Stratégie Git et conventions: `documentation/git-strategie.md`

Licence: projet pédagogique — ECF DWWM.
