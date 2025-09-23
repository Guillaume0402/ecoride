# MongoDB (prod) — Configuration, debug et données de démo

Ce document explique comment connecter l’application à une base MongoDB managée (Atlas), diagnostiquer la connexion en production, et peupler des données de démo.

## 1) Pré-requis Atlas

1. Créez un cluster gratuit (Shared / M0).
2. Security → Database Access: créez un utilisateur (ex: `ecoride_user`) avec un mot de passe fort et le rôle `readWrite` sur la base `ecoride` (ou `readWriteAnyDatabase`).
3. Security → Network Access: autorisez `0.0.0.0/0` pour accepter les connexions Heroku (ou restreignez si vous avez une stratégie IP adaptée).
4. Récupérez l’URI « mongodb+srv://… » dans Atlas → Connect → Drivers.

Note: si le mot de passe contient des caractères spéciaux (@:/?#&=%…), il doit être URL‑encodé.

## 2) Variables d’environnement Heroku

Définissez dans Settings → Config Vars:

- `MONGODB_URI` = `mongodb+srv://<user>:<mdp-encodé>@<cluster>.mongodb.net/?retryWrites=true&w=majority`
- `MONGO_DB` = `ecoride`

Le code accepte aussi `MONGO_DSN`, mais `MONGODB_URI` est la voie recommandée (prise en charge en fallback partout).

## 3) Vérifier la connexion en prod

Connectez‑vous avec un compte Employé (role_id = 2) ou Admin (role_id = 3), puis ouvrez:

`/debug/mongo`

Réponse attendue:

```json
{
  "env": { "APP_ENV": "prod", "MONGODB_URI": "[set]", "MONGO_DB": "ecoride" },
  "status": "connected",
  "db": "ecoride",
  "collection_count": 4,
  "pending_count": 4
}
```

En cas d’erreur:
- `Failed to resolve 'mongo'` → variables manquantes (l’app essaie `mongodb://mongo:27017`, valable seulement en Docker local).
- `bad auth : authentication failed` → user/MDP ou encodage du MDP incorrect; éventuellement ajouter `&authSource=admin` à l’URI.

## 4) Données de démo (seed)

Deux scripts existent dans `scripts/`:

- `seed_mongo.php` → insère 4 documents `pending` dans `reviews` (2 avis + 2 signalements).
- `mongo_create_indexes.php` → crée des index utiles: unicité `doc_id`, `status+created_at_ms`, `driver_id`, `passager_id`.

Usage en local (fichier `.env.local`):

```
MONGODB_URI=mongodb+srv://…
MONGO_DB=ecoride
```

Puis exécuter les scripts avec PHP 8.x:

```
php scripts/mongo_create_indexes.php
php scripts/seed_mongo.php
```

Vous pouvez aussi insérer des documents via Atlas/Compass. Exemple minimal pour un avis:

```
{
  "doc_id": "abc123",
  "kind": "review",
  "status": "pending",
  "driver_id": 12,
  "passager_id": 34,
  "rating": 5,
  "comment": "Très bien",
  "created_at_ms": 1695490000000
}
```

## 5) Intégration dans l’app

- Le header affiche, pour un Employé, un badge du nombre de documents `pending`.
- Le dashboard Employé (`/employe`) liste les avis/signalements à traiter et permet d’approuver/rejeter (`status`: `approved`/`rejected`).
