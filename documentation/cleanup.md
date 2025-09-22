# Nettoyage du code (chore/cleanup-codebase-2025-09)

Objectif: retirer le code mort, clarifier les responsabilités et éviter le bruit en prod.

## Ce qui a été fait

- Routes et contrôleurs
  - Suppression de la route obsolète `/profil/edit`.
  - Suppression du contrôleur `UserController.php` (non référencé).
  - Mise à jour de `ParticipationController` (redirection vers `/creation-profil`).
  - Ajustements mineurs dans `PageController` (suppression de méthodes non routées).
- Vues et assets
  - Suppression de la vue non atteinte `src/View/pages/creation-covoiturage.php`.
  - Nettoyage d’un doublon d’import SCSS (`assets/scss/pages/index.scss`).
- Sécurité et logs
  - Suppression de l’ancien `Security.php` à la racine (remplacé par `src/Security/*`).
  - Journalisation du routeur limitée à l’environnement dev (`APP_ENV === 'dev'`).
- Qualité et stabilité
  - Ajout et configuration de PHPStan (niveau 5) + corrections ciblées jusqu’à 0 erreur.
  - Correctifs `VehicleController`: parsing de date robuste (retour `Y-m-d` ou `null`).
  - Helper `getRideCreateFee()` pour encapsuler la constante et simplifier les contrôles.
  - Corrections mineures dans `AuthController` et vues (`login`, `my-profil`).
- Tests
  - Ajout de PHPUnit + tests unitaires: `getRideCreateFee()` et `normalizePlate()`.
  - Script Composer `test`.

## Comment rejouer la démarche

1. Créer la branche
   - `git checkout -b chore/cleanup-codebase-2025-09`
2. Nettoyer par itérations courtes
   - Repérer routes/contrôleurs non utilisés, supprimer ce qui est mort.
   - Limiter les logs verbeux à l’environnement dev.
   - Balayer les vues orphelines.
3. Stabiliser
   - Installer PHPStan (ignorer ext-mongodb si non dispo côté CLI).
   - Ajouter `phpstan.neon.dist` avec bootstrap de `config/constants.php` et régler le bruit (`treatPhpDocTypesAsCertain: false`).
   - Corriger les erreurs évidentes (types toujours vrais/faux, variables potentiellement indéfinies, etc.).
4. Valider
   - Lancer PHPStan jusqu’à 0 erreur.
   - Ajouter des tests unitaires ciblés sur les helpers et fonctions pures.
   - `composer run test`.
5. Committer et pousser
   - Commits atomiques avec messages explicites.
   - Pousser la branche si besoin.

## Points d’attention

- Éviter les changements risqués dans la même passe (séparer refactor et cleanup).
- Toujours vérifier les redirections après suppression de routes.
- Les vues doivent recevoir les variables attendues; simplifier la logique dans la vue si nécessaire.
- Pour les constantes utilisées côté code, prévoir un helper (ex: `getRideCreateFee()`), ça simplifie l’analyse statique et les tests.

## Étapes suivantes possibles

- Script Composer pour PHPStan (`composer run stan`).
- Petite passe pour uniformiser la nomenclature des routes (ex: `employe` vs `employee` si souhaité).
- Étendre la couverture de tests aux services (`PasswordPolicy`, `Csrf`, etc.).
