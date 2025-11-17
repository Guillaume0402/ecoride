# Tests unitaires (PHPUnit)

Ce projet utilise PHPUnit pour les tests unitaires.

## Installation

-   Assurez-vous d'avoir installé les dépendances du projet avec Composer.
-   L'extension `ext-mongodb` peut ne pas être présente en local. Les tests n'en ont pas besoin. Si besoin d'installer PHPUnit manuellement, on peut ignorer cette extension côté CLI.

## Lancer les tests

-   Commande recommandée:
    -   `composer run test`
-   La configuration PHPUnit est dans `phpunit.xml.dist`.
-   Le bootstrap des tests charge automatiquement l'autoloader Composer, les constantes d'application (`config/constants.php`) et les helpers (`src/helpers.php`).

## Ce que couvrent les tests actuels

-   `getRideCreateFee()` (helpers):
    -   Vérifie que la valeur retournée est un entier.
    -   Vérifie qu'elle est supérieure ou égale à zéro.
-   `VehicleRepository::normalizePlate()`:
    -   Vérifie la normalisation d'immatriculation sur plusieurs formats (espaces, tirets, points, casse), avec un data provider.
-   `PasswordPolicy`:
    -   Vérifie la robustesse des mots de passe (validation de règles complexes).

-   `UserService`:
    -   Validation des données utilisateur (pseudos, emails, rôles, crédits).
    -   Gestion des crédits et notes des utilisateurs.
    -   Sérialisation des données utilisateur en tableau.

## Ajouter de nouveaux tests

-   Créez un fichier dans `tests/` avec le suffixe `Test.php`.
-   Étendez `PHPUnit\Framework\TestCase` et ajoutez des méthodes `public function testXxx(): void`(sans mot-clé public).
-   Utilisez des data providers pour couvrir plusieurs cas facilement.
-   Évitez de coupler les tests à la base de données pour les tests unitaires.
-   Exécutez `composer run test` pour vérifier.

## Bonnes pratiques

-   Tester des fonctions pures et des règles métiers sans I/O quand c'est possible.
-   Éviter de coupler les tests à la base de données pour les cas unitaires.
-   Nommer clairement les méthodes de test.
-   Garder les tests rapides: feedback en quelques secondes.
