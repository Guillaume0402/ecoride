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

-   getRideCreateFee() (helpers) :
-   Vérifie que la valeur retournée est de type entier et qu’elle est supérieure ou égale à zéro.

-   VehicleRepository::normalizePlate() :
-   Vérifie la normalisation des immatriculations sur différents formats (espaces, tirets, points, casse), à l’aide d’un data provider.

-   PasswordPolicy :
-   Vérifie qu’un mot de passe trop faible est refusé (notamment sur la longueur minimale) et qu’un mot de passe conforme est accepté.
-   vérifie le refus d’un mot de passe trop court (incluant le cas frontière 11/12 caractères) et l’acceptation d’un mot de passe conforme.

-   UserService :
-   Vérifie la validation basique des données utilisateur (pseudo et email).
-   Vérifie la gestion des crédits (ajout, débit avec contrôle du solde).
-   Vérifie la mise à jour de la note utilisateur (arrondi et bornage).
-   Vérifie la sérialisation des données utilisateur en tableau via toArray().
-   vérifie la gestion des crédits, y compris le cas frontière “débit exactement égal au solde”, et la mise à jour de la note avec tests aux bornes.'

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
