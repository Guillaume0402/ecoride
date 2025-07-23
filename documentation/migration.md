# Migration du projet EcoRide de WAMP vers Docker

## 📋 Prérequis

- Docker Desktop installé sur Windows
- Votre projet EcoRide actuel fonctionnel sous WAMP
- Accès à votre base de données via phpMyAdmin

## 🎯 Objectifs de la migration

- Environnement de développement reproductible
- Facilité de déploiement
- Isolation des dépendances
- Configuration versionnée avec le projet
- Architecture Entity/Model moderne

## 📂 Structure finale du projet

```
ecoride/
├── docker-compose.yml
├── Dockerfile
├── .env
├── .dockerignore
├── init.sql
├── config/
│   └── app.php
├── public/
│   ├── index.php
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
├── src/
│   ├── Controller/
│   │   ├── Controller.php
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   └── CovoiturageController.php
│   ├── Entity/
│   │   ├── User.php
│   │   ├── Covoiturage.php
│   │   └── Vehicle.php
│   ├── Model/
│   │   ├── UserModel.php
│   │   ├── CovoiturageModel.php
│   │   └── VehicleModel.php
│   ├── Db/
│   │   └── Mysql.php
│   ├── Routing/
│   │   └── Router.php
│   └── View/
│       ├── layout.php
│       ├── partials/
│       └── pages/
├── assets/
│   └── scss/
├── documentation/
└── vendor/
```

## 🔧 Étape 1 : Sauvegarde de votre base de données

### 1.1 Export depuis phpMyAdmin (WAMP)
1. Ouvrez phpMyAdmin : `http://localhost/phpmyadmin`
2. Sélectionnez votre base de données EcoRide
3. Cliquez sur l'onglet "Exporter"
4. Choisissez "Méthode rapide" et format "SQL"
5. Cliquez sur "Exécuter" et sauvegardez le fichier `ecoride_backup.sql`

### 1.2 Préparation du fichier d'initialisation
Renommez votre fichier exporté en `init.sql` et placez-le à la racine du projet.

## 🔧 Étape 2 : Configuration Docker

### 2.1 Création du Dockerfile

```dockerfile
FROM php:8.2-apache

# Installation des extensions PHP nécessaires
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Installation d'extensions supplémentaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Activation du module Apache rewrite
RUN a2enmod rewrite

# Configuration Apache pour EcoRide
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copie des fichiers du projet
COPY . /var/www/html/

# Permissions appropriées
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
```

### 2.2 Configuration Apache (docker/apache-config.conf)

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/public
    
    <Directory /var/www/html/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        # URL Rewriting pour le Router
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
    
    # Sécurité : Bloquer l'accès aux dossiers sensibles
    <Directory /var/www/html/src>
        Deny from all
    </Directory>
    
    <Directory /var/www/html/config>
        Deny from all
    </Directory>
</VirtualHost>
```

### 2.3 Création du docker-compose.yml

```yaml
version: '3.8'

services:
  # Service Web (Apache + PHP)
  web:
    build: .
    container_name: ecoride_web
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
      - ./logs:/var/log/apache2
    depends_on:
      - db
    environment:
      - APP_ENV=development
      - APP_DEBUG=true
      - DB_HOST=db
      - DB_NAME=ecoride_db
      - DB_USER=ecoride_user
      - DB_PASS=ecoride_password
      - DB_PORT=3306
    networks:
      - ecoride_network

  # Service Base de données MySQL
  db:
    image: mysql:8.0
    container_name: ecoride_db
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: ecoride_db
      MYSQL_USER: ecoride_user
      MYSQL_PASSWORD: ecoride_password
    ports:
      - "3307:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./init.sql:/docker-entrypoint-initdb.d/init.sql
      - ./docker/mysql-config.cnf:/etc/mysql/conf.d/custom.cnf
    networks:
      - ecoride_network

  # phpMyAdmin pour gérer la base de données
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: ecoride_phpmyadmin
    ports:
      - "8081:80"
    environment:
      PMA_HOST: db
      PMA_USER: ecoride_user
      PMA_PASSWORD: ecoride_password
      PMA_ROOT_PASSWORD: root_password
    depends_on:
      - db
    networks:
      - ecoride_network

volumes:
  mysql_data:

networks:
  ecoride_network:
    driver: bridge
```

### 2.4 Fichiers de configuration

#### .env
```env
# Configuration de la base de données
DB_HOST=db
DB_NAME=ecoride_db
DB_USER=ecoride_user
DB_PASSWORD=ecoride_password
DB_PORT=3306

# Configuration de l'application
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8080
APP_ROOT=/var/www/html

# Sécurité
SESSION_TIMEOUT=3600
HASH_SALT=your_random_salt_here
```

#### .dockerignore
```
.git
.gitignore
README.md
documentation/
node_modules
.env.local
logs/
vendor/
*.log
.vscode/
```

## 🔧 Étape 3 : Architecture Entity/Model moderne

### 3.1 Singleton de connexion base de données

```php
// src/Db/Mysql.php
<?php
namespace App\Db;

class Mysql
{
    private string $dbName;
    private string $dbUser;
    private string $dbPassword;
    private string $dbPort;
    private string $dbHost;
    private ?\PDO $pdo = null;
    private static ?self $_instance = null;
 
    private function __construct()
    {
        // Chargement de la configuration depuis les variables d'environnement
        $this->dbHost = $_ENV['DB_HOST'] ?? 'db';
        $this->dbName = $_ENV['DB_NAME'] ?? 'ecoride_db';
        $this->dbUser = $_ENV['DB_USER'] ?? 'ecoride_user';
        $this->dbPassword = $_ENV['DB_PASSWORD'] ?? 'ecoride_password';
        $this->dbPort = $_ENV['DB_PORT'] ?? '3306';
    }

    public static function getInstance(): self
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance; 
    }

    public function getPDO(): \PDO
    {
        if (is_null($this->pdo)) {
            $dsn = "mysql:host={$this->dbHost};charset=utf8;dbname={$this->dbName};port={$this->dbPort}";
            
            try {
                $this->pdo = new \PDO($dsn, $this->dbUser, $this->dbPassword, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ]);
            } catch (\PDOException $e) {
                throw new \Exception("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }
        return $this->pdo;
    }
}
```

### 3.2 Entity User moderne

```php
// src/Entity/User.php
<?php
namespace App\Entity;

class User 
{
    private ?int $id = null;
    private string $pseudo;
    private string $email;
    private string $password;
    private int $roleId = 1;
    private int $credits = 20;
    private float $note = 0.00;
    private ?string $photo = null;
    private ?\DateTime $createdAt = null;

    public function __construct(string $pseudo, string $email)
    {
        $this->pseudo = $pseudo;
        $this->email = $email;
        $this->createdAt = new \DateTime();
    }

    // Getters/Setters...
    
    public function hashPassword(string $plainPassword): void 
    {
        $this->password = password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $plainPassword): bool 
    {
        return password_verify($plainPassword, $this->password);
    }

    public function validate(): array 
    {
        $errors = [];
        
        if (empty($this->pseudo) || strlen($this->pseudo) < 3) {
            $errors[] = "Le pseudo doit contenir au moins 3 caractères";
        }
        
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide";
        }
        
        return $errors;
    }
}
```

### 3.3 Model User avec pattern Repository

```php
// src/Model/UserModel.php
<?php
namespace App\Model;

use App\Entity\User;
use App\Db\Mysql;

class UserModel 
{
    private \PDO $conn;
    private string $table = "users";

    public function __construct() 
    {
        $this->conn = Mysql::getInstance()->getPDO();
    }

    public function findByEmail(string $email): ?User 
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute([':email' => $email]);
        
        $data = $stmt->fetch();
        return $data ? $this->hydrate($data) : null;
    }

    public function save(User $user): bool 
    {
        $errors = $user->validate();
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Données invalides: ' . implode(', ', $errors));
        }

        return $user->getId() ? $this->update($user) : $this->create($user);
    }

    private function hydrate(array $data): User 
    {
        $user = new User($data['pseudo'], $data['email']);
        $user->setId((int)$data['id'])
             ->setPassword($data['password'])
             ->setRoleId((int)$data['role_id'])
             ->setCredits((int)$data['credits'])
             ->setNote((float)$data['note'])
             ->setPhoto($data['photo']);
        
        if ($data['created_at']) {
            $user->setCreatedAt(new \DateTime($data['created_at']));
        }
        
        return $user;
    }
}
```

## 🔧 Étape 4 : Configuration de l'application

### 4.1 Fichier de configuration principal

```php
// config/app.php
<?php

// Définition des constantes
define('APP_ROOT', dirname(__DIR__));
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', $_ENV['APP_DEBUG'] === 'true');

// Autoloader simple (en attendant Composer)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_ROOT . '/src/';
    
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Gestion des erreurs
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuration des sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();
```

### 4.2 Point d'entrée moderne

```php
// public/index.php
<?php
require_once __DIR__ . '/../config/app.php';

use App\Routing\Router;

try {
    $router = new Router();
    $router->handleRequest($_SERVER['REQUEST_URI']);
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo "Erreur : " . $e->getMessage();
    } else {
        http_response_code(500);
        echo "Une erreur est survenue.";
    }
}
```

## 🚀 Étape 5 : Lancement et tests

### 5.1 Construction et lancement

```bash
# Clone ou mise à jour du projet
git pull origin main

# Construction des images Docker
docker-compose build

# Lancement des services
docker-compose up -d

# Vérification du statut
docker-compose ps
```

### 5.2 Accès aux services

- **Application EcoRide** : http://localhost:8080
- **phpMyAdmin** : http://localhost:8081
- **Base de données** : localhost:3307

### 5.3 Tests de validation

#### Test de connexion base de données
```php
// test_connection.php
<?php
require_once 'config/app.php';

use App\Db\Mysql;

try {
    $db = Mysql::getInstance();
    $pdo = $db->getPDO();
    
    echo "✅ Connexion réussie !<br>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Tables trouvées :<br>";
    foreach ($tables as $table) {
        echo "- " . $table . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
```

#### Test Entity/Model
```php
// test_user.php
<?php
require_once 'config/app.php';

use App\Entity\User;
use App\Model\UserModel;

try {
    // Test Entity
    $user = new User('TestUser', 'test@example.com');
    $user->hashPassword('password123');
    
    echo "✅ Entity User créée<br>";
    echo "Pseudo: " . $user->getPseudo() . "<br>";
    echo "Email: " . $user->getEmail() . "<br>";
    
    // Test Model
    $userModel = new UserModel();
    
    if ($userModel->save($user)) {
        echo "✅ Utilisateur sauvegardé avec l'ID: " . $user->getId() . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
```

## 🛠️ Commandes Docker utiles

```bash
# Gestion des services
docker-compose up -d                    # Démarrer
docker-compose down                     # Arrêter
docker-compose restart                  # Redémarrer
docker-compose logs web                 # Logs du service web

# Accès aux containers
docker-compose exec web bash            # Shell dans le container web
docker-compose exec db mysql -u root -p # Accès MySQL

# Monitoring
docker-compose ps                       # État des services
docker stats                           # Utilisation des ressources
```

## 📊 Monitoring et maintenance

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

## 🎯 Avantages de cette architecture

✅ **Séparation des responsabilités** : Entity/Model/Controller bien définis
✅ **Singleton de connexion** : Une seule instance PDO réutilisée
✅ **Gestion d'erreurs robuste** : Try-catch et logging
✅ **Configuration centralisée** : Variables d'environnement
✅ **Sécurité renforcée** : Requêtes préparées, validation
✅ **Maintenabilité** : Code modulaire et testable

## 🔄 Migration de données existantes

Si vous avez des données dans votre ancienne base WAMP :

```bash
# 1. Export depuis WAMP
mysqldump -u root -p ecoride > wamp_export.sql

# 2. Import dans Docker
docker-compose exec -T db mysql -u ecoride_user -p ecoride_db < wamp_export.sql
```

## 📝 Prochaines étapes

1. **Compléter les Entities** : Covoiturage, Vehicle, etc.
2. **Implémenter les Models** : CRUD complet
3. **Développer les Controllers** : Logique métier
4. **Créer les vues** : Interface utilisateur
5. **Tests et optimisations** : Performance et sécurité

Cette migration vous donne une base solide et moderne pour votre ECF EcoRide ! 🚀