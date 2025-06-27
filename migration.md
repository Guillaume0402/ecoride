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

## 📂 Structure finale du projet

```
ecoride/
├── docker-compose.yml
├── Dockerfile
├── .env
├── .dockerignore
├── init.sql
├── config/
│   └── database.php
├── public/
│   ├── index.php
│   ├── css/
│   ├── js/
│   └── images/
└── src/
    ├── controllers/
    ├── models/
    └── views/
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

Créez un fichier `Dockerfile` à la racine :

```dockerfile
FROM php:8.2-apache

# Installation des extensions PHP nécessaires pour votre ECF
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Installation d'extensions supplémentaires si nécessaire
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install zip

# Activation du module Apache rewrite (pour les URL propres)
RUN a2enmod rewrite

# Configuration Apache pour votre ECF
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/ecoride.conf \
    && a2enconf ecoride

# Copie des fichiers du projet
COPY . /var/www/html/

# Permissions appropriées
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
```

### 2.2 Création du docker-compose.yml

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
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=ecoride
      - DB_USER=ecoride_user
      - DB_PASS=ecoride_password
    networks:
      - ecoride_network

  # Service Base de données MySQL
  db:
    image: mysql:8.0
    container_name: ecoride_db
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: ecoride
      MYSQL_USER: ecoride_user
      MYSQL_PASSWORD: ecoride_password
    ports:
      - "3307:3306"  # Port 3307 pour éviter conflits avec WAMP
    volumes:
      - mysql_data:/var/lib/mysql
      - ./init.sql:/docker-entrypoint-initdb.d/init.sql
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

### 2.3 Création du fichier .env

```env
# Configuration de la base de données
DB_HOST=db
DB_NAME=ecoride
DB_USER=ecoride_user
DB_PASSWORD=ecoride_password
DB_PORT=3306

# Configuration de l'application
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8080
```

### 2.4 Création du .dockerignore

```
.git
.gitignore
README.md
MIGRATION_WAMP_TO_DOCKER.md
node_modules
.env.local
```

## 🔧 Étape 3 : Adaptation du code PHP

### 3.1 Création du fichier de configuration de base de données

Créez `config/database.php` :

```php
<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'] ?? 'db';
        $this->db_name = $_ENV['DB_NAME'] ?? 'ecoride';
        $this->username = $_ENV['DB_USER'] ?? 'ecoride_user';
        $this->password = $_ENV['DB_PASSWORD'] ?? 'ecoride_password';
        $this->port = $_ENV['DB_PORT'] ?? '3306';
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Erreur de connexion : " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
```

### 3.2 Mise à jour de votre code existant

Si vous aviez des connexions en dur, remplacez-les par :

```php
// Ancien code WAMP (à remplacer)
// $conn = new mysqli("localhost", "root", "", "ecoride");

// Nouveau code Docker
require_once 'config/database.php';
$database = new Database();
$conn = $database->getConnection();
```

## 🔧 Étape 4 : Structure recommandée pour votre ECF

### 4.1 Organisation MVC suggérée

```
src/
├── controllers/
│   ├── HomeController.php
│   ├── UserController.php
│   ├── RideController.php
│   └── AuthController.php
├── models/
│   ├── User.php
│   ├── Ride.php
│   └── Booking.php
└── views/
    ├── layouts/
    │   ├── header.php
    │   └── footer.php
    ├── home/
    ├── user/
    └── ride/
```

### 4.2 Exemple de contrôleur de base

```php
<?php
// src/controllers/BaseController.php
class BaseController {
    protected $db;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    protected function render($view, $data = []) {
        extract($data);
        require_once __DIR__ . '/../views/' . $view . '.php';
    }
}
?>
```

## 🚀 Étape 5 : Lancement de l'environnement Docker

### 5.1 Arrêt de WAMP
1. Arrêtez tous les services WAMP
2. Assurez-vous que les ports 80, 3306 ne sont pas utilisés

### 5.2 Construction et lancement

Ouvrez un terminal dans le dossier de votre projet et exécutez :

```bash
# Construction des images
docker-compose build

# Lancement des services
docker-compose up -d

# Vérification du statut
docker-compose ps
```

### 5.3 Accès aux services

- **Application EcoRide** : http://localhost:8080
- **phpMyAdmin** : http://localhost:8081
- **Base de données** : localhost:3307 (depuis votre machine)

## 🔍 Étape 6 : Vérification et tests

### 6.1 Test de l'application
1. Accédez à http://localhost:8080
2. Vérifiez que votre page d'accueil s'affiche
3. Testez vos différentes pages

### 6.2 Test de la base de données
1. Accédez à phpMyAdmin : http://localhost:8081
2. Connectez-vous avec `ecoride_user` / `ecoride_password`
3. Vérifiez que vos tables sont présentes et les données importées

### 6.3 Test de connexion PHP-MySQL

Créez un fichier `test_db.php` temporaire :

```php
<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Connexion à la base de données réussie !<br>";
        
        // Test d'une requête simple
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "📋 Tables trouvées :<br>";
        foreach ($tables as $table) {
            echo "- " . $table . "<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>
```

## 🛠️ Commandes Docker utiles

### Gestion des services
```bash
# Démarrer les services
docker-compose up -d

# Arrêter les services
docker-compose down

# Redémarrer les services
docker-compose restart

# Voir les logs
docker-compose logs

# Voir les logs d'un service spécifique
docker-compose logs web
```

### Accès aux containers
```bash
# Accéder au container web
docker-compose exec web bash

# Accéder au container de base de données
docker-compose exec db mysql -u ecoride_user -p ecoride
```

## 🎯 Avantages obtenus

✅ **Environnement isolé** : Pas de conflit avec d'autres projets
✅ **Reproductibilité** : Même environnement sur toutes les machines
✅ **Facilité de déploiement** : Configuration portable
✅ **Gestion des versions** : PHP, MySQL versionnés
✅ **Backup simple** : Export/import de volumes Docker

## 🚨 Points d'attention pour votre ECF

1. **Documentation** : Cette migration montre votre capacité d'adaptation
2. **Bonnes pratiques** : Utilisation de Docker est un plus professionnel
3. **Sécurité** : Variables d'environnement pour les mots de passe
4. **Architecture** : Structure MVC plus claire

## 🔄 Retour en arrière (si nécessaire)

Si vous devez revenir à WAMP temporairement :
1. `docker-compose down`
2. Redémarrez WAMP
3. Vos fichiers ne sont pas modifiés, tout fonctionne comme avant

## 📝 Prochaines étapes

Après la migration réussie :
1. Développer votre backend avec la nouvelle architecture
2. Implémenter l'authentification
3. Créer vos CRUD pour les trajets
4. Ajouter les fonctionnalités de réservation

Cette migration vous positionne parfaitement pour la suite de votre ECF !