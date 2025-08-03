# Guide d'utilisation de la classe Mysql.php

## Vue d'ensemble

La classe `Mysql` est une implémentation du pattern **Singleton** qui gère la connexion à la base de données MySQL de votre application EcoRide. Elle utilise PDO (PHP Data Objects) pour une interface sécurisée et moderne avec la base de données.

## Architecture et Pattern Singleton

### Pourquoi le Singleton ?

-   **Une seule connexion** : Évite la création de multiples connexions à la base de données
-   **Économie de ressources** : Réutilise la même instance PDO
-   **Lazy Loading** : La connexion n'est créée qu'au moment où elle est nécessaire

### Structure de la classe

```php
<?php
namespace App\Db;

class Mysql
{
    // Propriétés de configuration
    private string $dbName;
    private string $dbUser;
    private string $dbPassword;
    private string $dbPort;
    private string $dbHost;

    // Instance PDO et Singleton
    private ?\PDO $pdo = null;
    private static ?self $_instance = null;
```

## Configuration

La classe charge automatiquement sa configuration depuis un fichier `.env` :

```ini
# Exemple de fichier .env.local
db_host=localhost
db_name=ecoride
db_user=root
db_password=motdepasse
db_port=3306
```

## Utilisation dans le modèle MVC

### 1. Modèle User étendu

Voici comment étendre le modèle User existant pour utiliser la classe Mysql :

```php
<?php

namespace App\Model;

use App\Db\Mysql;

class User
{
    private \PDO $conn;
    private string $table = "users";

    public function __construct()
    {
        // Récupération de l'instance unique de la base de données
        $this->conn = Mysql::getInstance()->getPDO();
    }

    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register(array $userData): bool
    {
        try {
            $sql = "INSERT INTO {$this->table} (nom, prenom, email, mot_de_passe, telephone, date_creation)
                    VALUES (:nom, :prenom, :email, :mot_de_passe, :telephone, NOW())";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':nom' => $userData['nom'],
                ':prenom' => $userData['prenom'],
                ':email' => $userData['email'],
                ':mot_de_passe' => password_hash($userData['mot_de_passe'], PASSWORD_DEFAULT),
                ':telephone' => $userData['telephone']
            ]);

        } catch (\PDOException $e) {
            // Log de l'erreur et retour false
            error_log("Erreur inscription: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Connexion d'un utilisateur
     */
    public function login(string $email, string $password): ?array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT id, nom, prenom, email, mot_de_passe, telephone
                 FROM {$this->table}
                 WHERE email = :email LIMIT 1"
            );

            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                // Supprimer le mot de passe du retour pour la sécurité
                unset($user['mot_de_passe']);
                return $user;
            }

            return null;

        } catch (\PDOException $e) {
            error_log("Erreur connexion: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Recherche par email
     */
    public function findByEmail(string $email): ?array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT id, nom, prenom, email, telephone, date_creation
                 FROM {$this->table}
                 WHERE email = :email LIMIT 1"
            );

            $stmt->execute([':email' => $email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        } catch (\PDOException $e) {
            error_log("Erreur recherche par email: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mise à jour du profil utilisateur
     */
    public function updateProfil(int $userId, array $data): bool
    {
        try {
            $sql = "UPDATE {$this->table}
                    SET nom = :nom, prenom = :prenom, telephone = :telephone
                    WHERE id = :id";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':telephone' => $data['telephone'],
                ':id' => $userId
            ]);

        } catch (\PDOException $e) {
            error_log("Erreur mise à jour profil: " . $e->getMessage());
            return false;
        }
    }
}
```

### 2. Modèle Covoiturage

```php
<?php

namespace App\Model;

use App\Db\Mysql;

class Covoiturage
{
    private \PDO $conn;
    private string $table = "covoiturages";

    public function __construct()
    {
        $this->conn = Mysql::getInstance()->getPDO();
    }

    /**
     * Récupérer tous les covoiturages disponibles
     */
    public function getAll(): array
    {
        try {
            $sql = "SELECT c.*, u.nom, u.prenom
                    FROM {$this->table} c
                    JOIN users u ON c.conducteur_id = u.id
                    WHERE c.date_depart >= CURDATE()
                    AND c.places_disponibles > 0
                    ORDER BY c.date_depart ASC";

            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("Erreur récupération covoiturages: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Créer un nouveau covoiturage
     */
    public function create(array $data): bool
    {
        try {
            $sql = "INSERT INTO {$this->table}
                    (conducteur_id, ville_depart, ville_arrivee, date_depart, heure_depart,
                     places_disponibles, prix, description, date_creation)
                    VALUES (:conducteur_id, :ville_depart, :ville_arrivee, :date_depart,
                            :heure_depart, :places_disponibles, :prix, :description, NOW())";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':conducteur_id' => $data['conducteur_id'],
                ':ville_depart' => $data['ville_depart'],
                ':ville_arrivee' => $data['ville_arrivee'],
                ':date_depart' => $data['date_depart'],
                ':heure_depart' => $data['heure_depart'],
                ':places_disponibles' => $data['places_disponibles'],
                ':prix' => $data['prix'],
                ':description' => $data['description']
            ]);

        } catch (\PDOException $e) {
            error_log("Erreur création covoiturage: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer les covoiturages d'un utilisateur
     */
    public function getByUserId(int $userId): array
    {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE conducteur_id = :userId
                    ORDER BY date_depart DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':userId' => $userId]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("Erreur récupération covoiturages utilisateur: " . $e->getMessage());
            return [];
        }
    }
}
```

### 3. Contrôleur Auth

```php
<?php

namespace App\Controller;

use App\Model\User;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new UserEntity();
    }

    /**
     * Traitement de l'inscription
     */
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'nom' => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'email' => $_POST['email'] ?? '',
                'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
                'telephone' => $_POST['telephone'] ?? ''
            ];

            // Validation basique
            if (empty($userData['email']) || empty($userData['mot_de_passe'])) {
                $this->render('pages/creation-profil', [
                    'error' => 'Email et mot de passe obligatoires'
                ]);
                return;
            }

            // Vérifier si l'email existe déjà
            if ($this->userModel->findByEmail($userData['email'])) {
                $this->render('pages/creation-profil', [
                    'error' => 'Cet email est déjà utilisé'
                ]);
                return;
            }

            // Créer l'utilisateur
            if ($this->userModel->register($userData)) {
                // Redirection vers la page de connexion
                header('Location: /login?success=1');
                exit;
            } else {
                $this->render('pages/creation-profil', [
                    'error' => 'Erreur lors de l\'inscription'
                ]);
            }
        } else {
            $this->render('pages/creation-profil');
        }
    }

    /**
     * Traitement de la connexion
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['mot_de_passe'] ?? '';

            $user = $this->userModel->login($email, $password);

            if ($user) {
                // Démarrer la session et stocker les données utilisateur
                session_start();
                $_SESSION['user'] = $user;

                // Redirection vers le tableau de bord
                header('Location: /mes-covoiturages');
                exit;
            } else {
                $this->render('pages/login', [
                    'error' => 'Email ou mot de passe incorrect'
                ]);
            }
        } else {
            $this->render('pages/login');
        }
    }

    /**
     * Déconnexion
     */
    public function logout(): void
    {
        session_start();
        session_destroy();
        header('Location: /');
        exit;
    }
}
```

### 4. Contrôleur Covoiturage

```php
<?php

namespace App\Controller;

use App\Model\Covoiturage;

class CovoiturageController extends Controller
{
    private Covoiturage $covoiturageModel;

    public function __construct()
    {
        $this->covoiturageModel = new Covoiturage();
    }

    /**
     * Afficher la liste des covoiturages
     */
    public function liste(): void
    {
        $covoiturages = $this->covoiturageModel->getAll();

        $this->render('pages/liste-covoiturages', [
            'covoiturages' => $covoiturages
        ]);
    }

    /**
     * Créer un nouveau covoiturage
     */
    public function create(): void
    {
        session_start();

        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'conducteur_id' => $_SESSION['user']['id'],
                'ville_depart' => $_POST['ville_depart'] ?? '',
                'ville_arrivee' => $_POST['ville_arrivee'] ?? '',
                'date_depart' => $_POST['date_depart'] ?? '',
                'heure_depart' => $_POST['heure_depart'] ?? '',
                'places_disponibles' => (int)($_POST['places_disponibles'] ?? 0),
                'prix' => (float)($_POST['prix'] ?? 0),
                'description' => $_POST['description'] ?? ''
            ];

            if ($this->covoiturageModel->create($data)) {
                header('Location: /mes-covoiturages?success=1');
                exit;
            } else {
                $this->render('pages/creation-covoiturage', [
                    'error' => 'Erreur lors de la création du covoiturage'
                ]);
            }
        } else {
            $this->render('pages/creation-covoiturage');
        }
    }

    /**
     * Afficher les covoiturages de l'utilisateur connecté
     */
    public function mesCovoiturages(): void
    {
        session_start();

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $covoiturages = $this->covoiturageModel->getByUserId($_SESSION['user']['id']);

        $this->render('pages/mes-covoiturages', [
            'covoiturages' => $covoiturages,
            'user' => $_SESSION['user']
        ]);
    }
}
```

## Avantages de cette architecture

### 1. **Séparation des responsabilités**

-   **Mysql.php** : Gestion de la connexion uniquement
-   **Models** : Logique métier et accès aux données
-   **Controllers** : Traitement des requêtes et orchestration

### 2. **Sécurité**

-   Utilisation de requêtes préparées (protection contre les injections SQL)
-   Hashage des mots de passe avec `password_hash()`
-   Gestion des sessions pour l'authentification

### 3. **Gestion d'erreurs**

-   Try-catch sur toutes les opérations de base de données
-   Logging des erreurs avec `error_log()`
-   Retour de valeurs appropriées (bool, array, null)

### 4. **Performance**

-   Pattern Singleton pour éviter les connexions multiples
-   Lazy loading de la connexion PDO
-   Requêtes optimisées avec LIMIT et INDEX

## Exemple de routage

Dans votre fichier `config/routes.php`, vous pourriez avoir :

```php
<?php

use App\Controller\AuthController;
use App\Controller\CovoiturageController;
use App\Controller\PageController;

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
```

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
```

Cette architecture vous offre une base solide pour développer votre application EcoRide avec une connexion à la base de données sécurisée et performante.
