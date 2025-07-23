# 🌱 EcoRide – Plateforme de covoiturage écologique

**EcoRide** est une application web fullstack en PHP (Vanilla) avec architecture MVC moderne, conçue pour promouvoir le covoiturage responsable via une interface moderne et responsive.

---

## 🔧 Stack technique

| Front-end     | Back-end          | Base de données | DevOps & Outils           |
| ------------- | ----------------- | --------------- | ------------------------- |
| HTML5 / CSS3  | PHP 8.2 (Vanilla) | MySQL 8.0       | Docker + Docker Compose   |
| Bootstrap 5.3 | PDO / SQL         | phpMyAdmin      | Git + GitHub              |
| SASS          | Sessions PHP      |                 | npm (gestion dépendances) |
| JavaScript    | Architecture MVC  |                 | Sass (compilation CSS)    |

---

## ✅ Fonctionnalités principales

-   🔍 **Recherche avancée** : Covoiturages par ville, date et critères
-   🧭 **Affichage intelligent** : Trajets avec filtres et tri dynamique
-   👤 **Authentification sécurisée** : Connexion/inscription avec validation
-   🚗 **Gestion véhicules** : Publication et gestion des trajets
-   📋 **Profils utilisateurs** : Système de crédits et notation
-   💳 **Système de réservation** : Gestion des participations
-   📱 **Interface responsive** : Design adaptatif tous écrans
-   🎨 **UX moderne** : Animations CSS et interactions fluides

---

## 🏗️ Architecture moderne

### Pattern Entity-Model-Controller

```
src/
├── Entity/              # Objets métier (User, Covoiturage, Vehicle)
├── Model/               # Couche d'accès aux données (UserModel, etc.)
├── Controller/          # Logique applicative (AuthController, etc.)
├── Db/                  # Singleton de connexion base de données
├── Routing/             # Router MVC custom
└── View/                # Templates et vues
```

### Singleton de base de données

```php
// Connexion unique et sécurisée
$db = Mysql::getInstance();
$pdo = $db->getPDO();
```

### Entities typées

```php
// Objets métier avec validation
$user = new User('pseudo', 'email@domain.com');
$user->hashPassword('password');
$user->validate(); // Retourne les erreurs
```

---

## 🐳 Installation rapide (Docker recommandé)

### 1. Prérequis

-   **Docker Desktop** (Recommandé)
-   **Git**
-   **Node.js & npm** (pour SASS)

### 2. Installation complète

```bash
# Clonage du projet
git clone https://github.com/votre-username/ecoride.git
cd ecoride

# Installation des dépendances frontend
npm install

# Compilation SASS
npm run sass:build

# Lancement de l'environnement Docker
docker-compose up -d

# Vérification des services
docker-compose ps
```

### 3. Accès aux services

| Service       | URL                      | Identifiants              |
| ------------- | ------------------------ | ------------------------- |
| **Application** | http://localhost:8080    | -                         |
| **phpMyAdmin**  | http://localhost:8081    | ecoride_user / ecoride_password |
| **Base de données** | localhost:3307       | ecoride_user / ecoride_password |

### 4. Services Docker

| Container          | Service | Port | Description                    |
| ------------------ | ------- | ---- | ------------------------------ |
| ecoride_web        | Web     | 8080 | Apache 2.4 + PHP 8.2          |
| ecoride_db         | MySQL   | 3307 | MySQL 8.0 + données initiales |
| ecoride_phpmyadmin | Admin   | 8081 | Interface de gestion BDD       |

---

## 🎨 Développement CSS/SASS

### Scripts de développement

```bash
# Développement
npm run sass:watch         # Watch SASS en temps réel
npm run dev                # Mode développement complet

# Production
npm run sass:build         # Compilation optimisée
npm run build              # Build complet

# Docker
docker-compose up -d       # Démarrer les services
docker-compose down        # Stopper les services
docker-compose logs web    # Voir les logs
```

### Structure SASS organisée

```
assets/scss/
├── abstracts/
│   ├── _variables.scss    # Variables globales
│   ├── _mixins.scss       # Mixins réutilisables
│   └── _functions.scss    # Fonctions SASS
├── base/
│   ├── _reset.scss        # Reset CSS
│   ├── _typography.scss   # Polices et texte
│   └── _globals.scss      # Styles globaux
├── components/
│   ├── _buttons.scss      # Boutons
│   ├── _forms.scss        # Formulaires
│   ├── _modals.scss       # Modales
│   └── _cards.scss        # Cartes de contenu
├── layout/
│   ├── _header.scss       # En-tête
│   ├── _footer.scss       # Pied de page
│   ├── _navigation.scss   # Navigation
│   └── _grid.scss         # Système de grille
├── pages/
│   ├── _home.scss         # Page d'accueil
│   ├── _auth.scss         # Pages de connexion
│   └── _dashboard.scss    # Tableau de bord
└── main.scss              # Point d'entrée principal
```

---

## 📁 Architecture complète du projet

```
ecoride/
├── 📂 assets/
│   └── 📂 scss/              # Sources SASS
├── 📂 config/
│   └── app.php               # Configuration principale
├── 📂 documentation/
│   ├── mysql-database-guide.md
│   └── migration.md
├── 📂 public/                # Point d'entrée web
│   ├── index.php             # Router principal
│   ├── 📂 assets/
│   │   ├── 📂 css/           # CSS compilé
│   │   ├── 📂 js/            # JavaScript
│   │   └── 📂 images/        # Ressources images
├── 📂 src/
│   ├── 📂 Controller/        # Contrôleurs MVC
│   │   ├── Controller.php    # Contrôleur de base
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── CovoiturageController.php
│   │   └── ErrorController.php
│   ├── 📂 Entity/            # Entités métier
│   │   ├── User.php
│   │   ├── Covoiturage.php
│   │   ├── Vehicle.php
│   │   └── Participation.php
│   ├── 📂 Model/             # Couche d'accès données
│   │   ├── UserModel.php
│   │   ├── CovoiturageModel.php
│   │   └── VehicleModel.php
│   ├── 📂 Db/
│   │   └── Mysql.php         # Singleton PDO
│   ├── 📂 Routing/
│   │   └── Router.php        # Router MVC custom
│   └── 📂 View/              # Templates et vues
│       ├── layout.php        # Layout principal
│       ├── 📂 partials/
│       │   ├── header.php
│       │   ├── footer.php
│       │   └── navigation.php
│       └── 📂 pages/
│           ├── home.php
│           ├── auth/
│           ├── user/
│           └── covoiturage/
├── 📂 docker/                # Configuration Docker
├── 📄 docker-compose.yml     # Services Docker
├── 📄 Dockerfile            # Image web custom
├── 📄 init.sql              # Structure BDD
├── 📄 package.json          # Dépendances npm
└── 📄 .env                  # Variables d'environnement
```

---

## 🚀 Architecture MVC moderne & Router

### Routing intelligent

Le système de routing analyse automatiquement les URLs et charge les contrôleurs appropriés :

```php
// src/Routing/Router.php
$router = new App\Routing\Router();
$router->handleRequest($_SERVER['REQUEST_URI']);

// Exemples d'URLs gérées :
// /                     → PageController::home()
// /auth/login          → AuthController::login()
// /user/profile        → UserController::profile()
// /covoiturage/create  → CovoiturageController::create()
```

### Contrôleurs avec injection de dépendances

```php
// Exemple : AuthController
namespace App\Controller;

use App\Entity\User;
use App\Model\UserModel;

class AuthController extends Controller 
{
    private UserModel $userModel;

    public function __construct() 
    {
        $this->userModel = new UserModel();
    }

    public function register(): void 
    {
        if ($_POST) {
            $user = new User($_POST['pseudo'], $_POST['email']);
            $user->hashPassword($_POST['password']);
            
            if ($this->userModel->save($user)) {
                $this->redirect('/auth/login?success=1');
            }
        }
        
        $this->render('auth/register');
    }
}
```

### Entities avec logique métier

```php
// Entité User avec validation et logique métier
$user = new User('JohnDoe', 'john@example.com');
$user->hashPassword('secret123');
$user->addCredits(50);
$user->updateNote(4.5);

// Validation avant sauvegarde
$errors = $user->validate();
if (empty($errors)) {
    $userModel->save($user);
}
```

---

## 🎯 Fonctionnalités développées

### ✅ Authentification complète
-   **Inscription** : Validation côté serveur et client
-   **Connexion** : Hash sécurisé des mots de passe
-   **Sessions** : Gestion des utilisateurs connectés
-   **Rôles** : Visiteur, Utilisateur, Employé, Admin

### ✅ Gestion des utilisateurs
-   **Profils** : Informations personnelles et préférences
-   **Crédits** : Système de points pour les réservations
-   **Notation** : Système d'avis entre utilisateurs
-   **Avatar** : Upload et gestion des photos de profil

### ✅ Système de covoiturage
-   **Publication** : Création de trajets avec véhicule
-   **Recherche** : Filtres par ville, date, prix
-   **Réservation** : Gestion des places disponibles
-   **Historique** : Suivi des trajets effectués

### ✅ Interface utilisateur
-   **Design responsive** : Mobile-first approach
-   **Modales interactives** : Login/Register seamless
-   **Notifications** : Feedback utilisateur en temps réel
-   **Animations** : Transitions CSS fluides

---

## 🎯 Base de données optimisée

### Tables principales

```sql
-- Utilisateurs avec système de crédits et notation
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT DEFAULT 1,
    credits INT DEFAULT 20,
    note DECIMAL(4,2) DEFAULT 0.00,
    photo VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Covoiturages avec gestion des places
CREATE TABLE covoiturages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chauffeur_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    adresse_depart VARCHAR(255) NOT NULL,
    adresse_arrivee VARCHAR(255) NOT NULL,
    depart DATETIME NOT NULL,
    arrivee DATETIME NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    places_reservees INT DEFAULT 0,
    status ENUM('en_attente','demarre','termine','annule') DEFAULT 'en_attente',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chauffeur_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

-- Participations avec système de validation
CREATE TABLE participations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    covoiturage_id INT NOT NULL,
    passager_id INT NOT NULL,
    status ENUM('confirmee','annulee','en_attente_validation') DEFAULT 'confirmee',
    date_participation DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_participation (covoiturage_id, passager_id),
    FOREIGN KEY (covoiturage_id) REFERENCES covoiturages(id),
    FOREIGN KEY (passager_id) REFERENCES users(id)
);
```

---

## 👨‍💻 Workflow de développement

### Branches Git organisées

```bash
main                    # 🚀 Production stable
develop                 # 🧪 Intégration continue
feat/user-system       # ✨ Nouvelles fonctionnalités
feat/covoiturage-crud  # ✨ CRUD covoiturages
fix/auth-validation    # 🐛 Corrections de bugs
refactor/entity-model  # 🔧 Refactoring architecture
```

### Commits conventionnels

```bash
feat(auth): add user registration with validation
fix(db): resolve connection timeout in production
style(ui): improve responsive design for mobile
refactor(model): separate Entity from Model layer
docs(readme): update installation instructions
```

---

## 🛠️ Commandes utiles

### Développement quotidien

```bash
# Démarrage rapide
docker-compose up -d && npm run sass:watch

# Tests et debugging
docker-compose logs -f web              # Logs en temps réel
docker-compose exec web bash            # Shell dans le container
docker-compose exec db mysql -u root -p # Accès direct à MySQL

# Maintenance
docker-compose down && docker-compose up -d  # Redémarrage complet
docker system prune                          # Nettoyage Docker
```

### Base de données

```bash
# Backup
docker-compose exec db mysqldump -u ecoride_user -p ecoride_db > backup.sql

# Restore
docker-compose exec -T db mysql -u ecoride_user -p ecoride_db < backup.sql

# Reset complet
docker-compose down -v && docker-compose up -d
```

---

## 🧪 Qualité & Bonnes pratiques

### Standards respectés
-   **PSR-4** : Autoloading des classes
-   **PSR-12** : Style de code PHP
-   **HTML5 & CSS3** : Validation W3C
-   **Responsive Design** : Mobile-first
-   **Sécurité** : Requêtes préparées, validation, échappement

### Sécurité implémentée
-   **Hash des mots de passe** : password_hash() / password_verify()
-   **Requêtes préparées** : Protection contre l'injection SQL
-   **Validation** : Côté serveur et client
-   **Sessions sécurisées** : Configuration hardened
-   **Variables d'environnement** : Pas de données sensibles en dur

---

## 📌 Roadmap & Améliorations futures

### Phase 1 - Core Features ✅
-   [x] Architecture Entity/Model/Controller
-   [x] Système d'authentification
-   [x] CRUD utilisateurs
-   [x] Base de données optimisée
-   [x] Interface responsive

### Phase 2 - Business Logic 🚧
-   [x] CRUD covoiturages
-   [x] Système de réservation
-   [ ] Notifications en temps réel
-   [ ] Système de paiement
-   [ ] API REST pour mobile

### Phase 3 - Advanced Features 📋
-   [ ] Géolocalisation avec cartes
-   [ ] Chat entre utilisateurs
-   [ ] Application mobile (PWA)
-   [ ] Tests automatisés (PHPUnit)
-   [ ] CI/CD avec GitHub Actions

### Phase 4 - Performance & Scale 🎯
-   [ ] Cache Redis
-   [ ] CDN pour les assets
-   [ ] Load balancing
-   [ ] Monitoring avancé
-   [ ] Analytics utilisateurs

---

## 🤝 Contribution & Crédits

**Projet académique** réalisé dans le cadre de l'**ECF TP DWWM – Studi**.

### Ressources et inspiration
-   [PHP Official Documentation](https://www.php.net/docs.php)
-   [Docker Documentation](https://docs.docker.com/)
-   [Bootstrap 5](https://getbootstrap.com/)
-   [SASS Guidelines](https://sass-guidelin.es/)

### Usage
🎓 **Usage pédagogique uniquement** - Projet d'évaluation professionnelle

---

## 📞 Support & Contact

Pour toute question technique ou suggestion :

-   📧 **Email** : votre.email@domain.com
-   🐙 **GitHub** : [Issues](https://github.com/votre-username/ecoride/issues)
-   📝 **Documentation** : [Wiki](https://github.com/votre-username/ecoride/wiki)

---

**EcoRide** - *Covoiturage responsable pour un futur durable* 🌱

_Dernière mise à jour : Juillet 2025 - Version 2.0_