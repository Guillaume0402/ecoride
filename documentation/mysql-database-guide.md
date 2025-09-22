# Guide BDD (MySQL + PDO) – EcoRide

Ce guide explique comment on parle à MySQL dans EcoRide aujourd’hui: une connexion unique via `App\Db\Mysql` et des Repositories pour toutes les opérations (pas de « Model » actif). C’est simple, testable et aligné avec le code.

---

## Connexion: `App\Db\Mysql` (Singleton)

-   Fournit une unique instance PDO partagée dans l’appli.
-   Lit sa config via Dotenv (`public/index.php` charge `.env` et `.env.local`).
-   DSN MySQL classique, encodage UTF-8, exceptions activées.

Variables d’environnement utilisées (extraits du projet):

```
DB_HOST=db
DB_NAME=ecoride
DB_USER=ecoride_user
DB_PASSWORD=ecoride_password
DB_PORT=3306
```

Récupérer la connexion côté repository:

```php
$pdo = \App\Db\Mysql::getInstance()->getPDO();
```

---

## Pattern: Repository + Entity

-   Entity = objet de domaine léger (ex: `UserEntity`, `VehicleEntity`, `CovoiturageEntity`).
-   Repository = requêtes SQL et hydratation d’entités, via la connexion `Mysql`.

Avantages:

-   Séparation claire de la persistance.
-   Plus facile à tester (mock de PDO si besoin).
-   Requêtes centralisées et relues facilement.

---

## Exemples alignés avec le projet

### UserRepository (extraits)

```php
## TL;DR

-   1 connexion PDO via `Mysql` (Singleton).
-   Repositories pour parler SQL; Entities pour transporter les données.
-   Variables `.env` cohérentes avec `docker-compose.yml` (`ecoride`).

  public function updateStatus(int $id, string $status): bool
  {
    $allowed = ['en_attente','demarre','termine','annule'];
    if (!in_array($status, $allowed, true)) return false;
    $stmt = $this->conn->prepare("UPDATE covoiturages SET status = :s WHERE id = :id");
    return $stmt->execute([':s' => $status, ':id' => $id]);
  }
}
```

---

## Bonnes pratiques appliquées

-   Requêtes préparées partout; aucune concaténation dangereuse.
-   `mb_strtolower()` pour les emails; normalisation des plaques.
-   Transactions pour débiter/créditer de manière sûre quand nécessaire.
-   Champs optionnels traités avec `COALESCE` pour éviter d’écraser des valeurs.

---

## Où brancher ça dans les contrôleurs ?

-   Les contrôleurs héritent de `Controller` et utilisent les repositories.
-   Exemple côté `Controller::render()`, on rafraîchit des infos via `UserRepository` et on précharge des véhicules via `VehicleRepository` pour le header.

---

## Erreurs & logs

-   On laisse PDO lancer des exceptions. Elles sont catchées au besoin côté contrôleur.
-   En environnement `dev`, on voit l’exception. En prod, on renvoie une 500 propre.

---

## TL;DR

-   1 connexion PDO via `Mysql` (Singleton).
-   Repositories pour parler SQL; Entities pour transporter les données.
-   Variables `.env` cohérentes avec `docker-compose.yml` (`ecoride`).

return [
// Pages publiques
'/' => [PageController::class, 'home'],
'/contact' => [PageController::class, 'contact'],

    // Authentification
    '/login' => [AuthController::class, 'login'],
    '/register' => [AuthController::class, 'register'],
    '/logout' => [AuthController::class, 'logout'],

    // Covoiturages
    '/covoiturages' => [CovoiturageController::class, 'liste'],
    '/creation-covoiturage' => [CovoiturageController::class, 'create'],
    '/mes-covoiturages' => [CovoiturageController::class, 'mesCovoiturages'],

];

````

## Structure de base de données recommandée

```sql
-- Table users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table covoiturages
CREATE TABLE covoiturages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conducteur_id INT NOT NULL,
    ville_depart VARCHAR(100) NOT NULL,
    ville_arrivee VARCHAR(100) NOT NULL,
    date_depart DATE NOT NULL,
    heure_depart TIME NOT NULL,
    places_disponibles INT NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    description TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conducteur_id) REFERENCES users(id)
);
````
