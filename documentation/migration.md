# Migration/Stack Docker – EcoRide

Ce document décrit la stack de conteneurs actuelle et comment migrer/localiser votre environnement vers Docker (aligné sur ce dépôt).

---

## Prérequis

-   Docker Engine/Compose installés
-   Ce repo cloné localement

---

## Services (docker-compose.yml réel)

Nous utilisons 5 services:

-   web: Apache + PHP 8.2 (build local depuis `Dockerfile`), DocumentRoot sur `public/`.
-   db: MySQL 8.0, base initialisée via `init.sql`.
-   phpmyadmin: UI pour MySQL (port 8081).
-   mongo: MongoDB 6 (pour la modération des avis).

Et mongo-express: UI Mongo (port 8082).

Ports mappés:

-   Application: http://localhost:8080
-   phpMyAdmin: http://localhost:8081
-   Mongo Express: http://localhost:8082
-   MySQL: localhost:3307 (redirige vers 3306 dans le conteneur)

Volumes:

-   `./init.sql` chargé au premier démarrage MySQL
-   Volumes persistants `mysql_data`, `mongo_data`

Variables d’env MySQL (côté containers):

-   DB côté app: `DB_HOST=db`, `DB_NAME=ecoride`, `DB_USER=ecoride_user`, `DB_PASSWORD=ecoride_password`, `DB_PORT=3306`

---

## Dockerfile (réel)

Base `php:8.2-apache` + extensions: `mysqli`, `pdo_mysql`, `zip`, et l’extension PECL `mongodb` (pour les features de modération). Le VirtualHost pointe sur `/var/www/html/public` et le module `rewrite` est activé.

Note: Composer est installé dans l’image pour pouvoir installer/mettre à jour les dépendances si besoin.

---

## .env de l’application

`.env` et `.env.local` sont chargés par `public/index.php` via `vlucas/phpdotenv`.

Variables clés attendues:

```
DB_HOST=db
DB_NAME=ecoride
DB_USER=ecoride_user
DB_PASSWORD=ecoride_password
DB_PORT=3306

APP_ENV=dev
APP_TZ=Europe/Paris
```

Certaines constantes applicatives sont définies dans `config/constants.php` (ex: `RIDE_CREATE_FEE_CREDITS`, `DEFAULT_AVATAR_URL`).

---

## Initialiser la base

Placez votre dump sous `init.sql` à la racine. Au premier `up`, MySQL exécutera ce fichier. Pour réappliquer, supprimez le volume `mysql_data` ou exécutez manuellement vos scripts dans phpMyAdmin.

---

## Lancer les services

Depuis la racine du projet:

1. Démarrer les services (build auto au premier run)

```bash
docker compose up -d
```

2. Vérifier et consulter les logs

```bash
docker compose ps
docker compose logs web
```

3. Arrêter/relancer au besoin

```bash
docker compose restart
docker compose down
```

## Routage Apache/Router PHP

Le VirtualHost est configuré pour servir `public/`. Les requêtes non-fichier/non-dossier existent passent par `public/index.php` où notre Router PHP gère tout (voir `documentation/router_mvc_recap.md`).

---

## Astuces courantes

-   Port MySQL local déjà pris: changez le mapping `"3307:3306"` si besoin.
-   Extensions PHP: l’image embarque `mongodb`; côté Composer, si votre machine locale ne l’a pas, utilisez `--ignore-platform-req=ext-mongodb` pour installer les dev-deps.

Logs utiles:

```bash
docker compose logs web    # Apache/PHP
docker compose logs db     # MySQL
docker compose logs -f     # temps réel
```

---

### Backup/Restore MySQL

```bash
# Export (nom de base: ecoride)
docker compose exec db mysqldump -u ecoride_user -p ecoride > backup.sql

## Ce qui a changé vs WAMP

## Import
docker compose exec -T db mysql -u ecoride_user -p ecoride < backup.sql
```

-   URLs standardisées et reproductibles.
-   Fichier `init.sql` versionné pour initialiser la base.

### Migration d’une base existante

```bash
# 1) Export depuis l'ancien environnement
mysqldump -u root -p ecoride > export.sql

# 2) Import dans Docker (base: ecoride)
docker compose exec -T db mysql -u ecoride_user -p ecoride < export.sql
```

### Test rapide de connexion PDO (optionnel)

Créez `public/test_db.php` pour un check manuel (dev uniquement):

```php
<?php
require __DIR__ . '/../config/constants.php';
require APP_ROOT . '/vendor/autoload.php';

use App\Db\Mysql;

try {
    $pdo = Mysql::getInstance()->getPDO();
    $ok = $pdo->query('SELECT 1')->fetchColumn();
    echo ($ok == 1) ? 'OK' : 'NOK';
} catch (Throwable $e) {
    http_response_code(500);
    echo 'DB ERROR: ' . $e->getMessage();
}
```

## Commandes Docker utiles

```bash
# Gestion des services
docker compose up -d
docker compose down
docker compose restart
docker compose logs web

# Accès aux containers
docker compose exec web bash
docker compose exec db mysql -u root -p

# Monitoring
docker compose ps
docker stats
```

## Monitoring et maintenance

### Logs applicatifs

```bash
# Logs Apache
docker-compose logs web

# Logs MySQL
docker-compose logs db

# Logs en temps réel
docker-compose logs -f
```

### Backup de la base de données

```bash
# Export
docker-compose exec db mysqldump -u ecoride_user -p ecoride_db > backup.sql

# Import
docker-compose exec -T db mysql -u ecoride_user -p ecoride_db < backup.sql
```

## Try it (2 minutes)

1. Lance: `docker compose up -d`
2. Ouvre http://localhost:8080 et vérifie:
    - Page d’accueil et navigation.
    - Login (modale) et redirection.
    - Création/édition véhicule (formulaires).
3. Ouvre phpMyAdmin: http://localhost:8081 (hôte: db, base: ecoride).
