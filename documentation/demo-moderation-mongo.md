# Démo modération (Mongo)

Cette démo illustre la modération d’avis et de signalements stockés dans MongoDB, côté Employé.

## 1. Seed Mongo (avis + signalements)

```bash
php scripts/seed_mongo.php
```

Le script insère 4 documents `pending` dans la collection `reviews` (DB `ecoride`):

-   2 avis (`kind: review`)
-   2 signalements (`kind: report`)

Variables utiles (dans `.env.local`):

-   `MONGO_DSN` (par défaut: `mongodb://mongo:27017`)
-   `MONGO_DB` (par défaut: `ecoride`)

## 2. Parcours Employé

-   Connectez‑vous avec un compte Employé (role_id = 2), puis ouvrez `/employe`.
-   Vous verrez:
    -   « Avis en attente »: liste des reviews (commentaire + note) avec boutons Valider/Refuser (CSRF inclus)
    -   « Trajets signalés »: liste des reports (raison + commentaire + date)
-   En validant un « report », des e‑mails sont envoyés au conducteur et au passager (SMTP si configuré, sinon fallback journalisé).

## 3. Parcours Passager (optionnel)

-   Quand un conducteur termine un trajet, chaque passager reçoit un e‑mail l’invitant à « Valider » ou « Signaler ».
-   « Valider » crédite le conducteur (idempotent par motif), « Signaler » crée un document Mongo `pending`.
