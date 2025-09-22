# Déployer EcoRide sur Heroku (guide humain)

Ce guide explique simplement comment mettre en ligne le projet EcoRide sur Heroku.

## Pré-requis

-   Un compte GitHub avec le dépôt du projet (branche `main` à jour)
-   Un compte Heroku et la CLI installée
    -   Installer la CLI (Linux):
        -   Script officiel: `curl https://cli-assets.heroku.com/install.sh | sh`
-   Avoir Git installé et fonctionnel

## 1) Connexion Heroku

```bash
heroku login
```

Accepte l’ouverture du navigateur, connecte-toi, puis reviens au terminal.

## 2) Créer l’application Heroku

```bash
heroku create <nom-unique-app> --region eu
```

La commande affiche:

-   l’URL publique: `https://<nom-unique-app>.herokuapp.com/`
-   le remote Git: `https://git.heroku.com/<nom-unique-app>.git`

Option: si l’app existe déjà, lie simplement le remote:

```bash
heroku git:remote -a <nom-unique-app>
```

## 3) Buildpack PHP et Procfile

Heroku détecte l’app PHP automatiquement, mais on pose le buildpack si besoin:

```bash
heroku buildpacks:set heroku/php -a <nom-unique-app>
```

Le projet contient un `Procfile`:

```
web: heroku-php-apache2 public/
```

Cela demande à Heroku de servir le dossier `public/`.

## 4) Variables d’environnement nécessaires

Définir l’environnement et l’URL du site (avec le `/` final):

```bash
heroku config:set APP_ENV=prod SITE_URL=https://<nom-unique-app>.herokuapp.com/ -a <nom-unique-app>
```

Configurer la base MySQL (voir étape 5) puis compléter:

```bash
heroku config:set \
  DB_HOST=<host> DB_NAME=<db> DB_USER=<user> DB_PASSWORD=<pass> DB_PORT=3306 \
  -a <nom-unique-app>
```

SMTP (optionnel, pour les e-mails):

```bash
heroku config:set MAIL_FROM=no-reply@ecoride.app MAIL_FROM_NAME="EcoRide" -a <nom-unique-app>
# heroku config:set SMTP_HOST=... SMTP_PORT=587 SMTP_USER=... SMTP_PASS=... SMTP_SECURE=tls -a <nom-unique-app>
```

## 5) Base MySQL managée (JawsDB)

Créer l’add-on gratuit:

```bash
heroku addons:create jawsdb:kitefin -a <nom-unique-app>
heroku config -a <nom-unique-app> | grep JAWSDB_URL
```

Tu obtiens une URL de ce type:

```
JAWSDB_URL=mysql://USER:PASS@HOST:3306/DBNAME
```

Renseigne les variables `DB_*` correspondantes (remplace les valeurs):

```bash
heroku config:set \
  DB_HOST=HOST DB_NAME=DBNAME DB_USER=USER DB_PASSWORD=PASS DB_PORT=3306 \
  -a <nom-unique-app>
```

## 6) Déploiement

En partant de ta branche `main`:

```bash
git push heroku main
```

Heroku va builder et lancer l’app. Si tout va bien, l’URL sera accessible.

## 7) Initialiser la base (schéma)

Le projet fournit `init.sql`. Pour l’exécuter sur Heroku, on utilise le script CLI inclus:

```bash
heroku run -a <nom-unique-app> "php scripts/db_import.php init.sql"
```

## 8) Feuilles de style et assets

Le CSS compilé `public/assets/css/style.css` est versionné pour simplifier le déploiement. Si tu préfères construire sur Heroku, ajoute un buildpack Node et un script `heroku-postbuild` dans `package.json`.

## 9) Dépannage rapide

-   Erreur 500 / Dotenv: la prod ne fournit pas de `.env`. Le code utilise `safeLoad()` et lit les variables Heroku via `getenv()`. Vérifie `APP_ENV`, `SITE_URL` et `DB_*`.
-   DB connexion refusée: vérifie les `DB_*` et que l’add-on JawsDB est en état `created`.
-   CSS absent: vérifie que `public/assets/css/style.css` est bien présent dans le dépôt et accessible via `/assets/css/style.css`.

## 10) Commandes utiles

```bash
# Ouvrir l’app
heroku open -a <nom-unique-app>

# Logs temps réel
heroku logs --tail -a <nom-unique-app>

# Lister les config vars
heroku config -a <nom-unique-app>
```

Bon déploiement !
