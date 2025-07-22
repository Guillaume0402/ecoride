# 🌱 EcoRide – Plateforme de covoiturage écologique

**EcoRide** est une application web fullstack en PHP (Vanilla) avec architecture MVC, conçue pour promouvoir le covoiturage responsable via une interface moderne et responsive.

---

## 🔧 Stack technique

| Front-end     | Back-end          | Base de données | DevOps & Outils           |
| ------------- | ----------------- | --------------- | ------------------------- |
| HTML5 / CSS3  | PHP 8.2 (Vanilla) | MySQL 8.0       | Docker + Docker Compose   |
| Bootstrap 5.3 | PDO / SQL         | phpMyAdmin      | Git + GitHub              |
| SASS          | Sessions PHP      |                 | npm (gestion dépendances) |
| JavaScript    | Router MVC custom |                 | Sass (compilation CSS)    |

---

## ✅ Fonctionnalités principales

-   🔍 Recherche de covoiturages par ville et date
-   🧭 Affichage des trajets avec filtres et tri
-   👤 Authentification (connexion/inscription)
-   🚗 Publication de trajets pour conducteurs
-   📋 Gestion des profils utilisateurs
-   📱 Interface responsive (desktop + mobile)
-   🎨 Design moderne avec animations CSS

---

## 🐳 Installation rapide (Docker recommandé)

### 1. Prérequis

-   Docker Desktop
-   Git
-   Node.js & npm

### 2. Clonage et démarrage

```bash
git clone <votre-repo>
cd ecoride
npm install
npm run sass:build
docker-compose up -d
```

### 3. Accès

-   **App** : http://localhost:8080
-   **phpMyAdmin** : http://localhost:8081
-   **BDD** : localhost:3307

### 4. Services Docker

| Service    | Container          | Port | Description              |
| ---------- | ------------------ | ---- | ------------------------ |
| Web        | ecoride_web        | 8080 | Apache + PHP 8.2         |
| Database   | ecoride_db         | 3307 | MySQL 8.0                |
| phpMyAdmin | ecoride_phpmyadmin | 8081 | Interface de gestion BDD |

---

## 🎨 Développement CSS/SASS

### Scripts utiles

```bash
npm run sass:build         # Compilation unique
npm run sass:watch         # Watch SASS (dev)
npm run dev                # Mode dev complet
docker-compose up -d       # Démarrer les services
docker-compose down        # Stopper les services
docker-compose exec web bash # Shell dans le container web
```

### Structure SASS

```
assets/scss/                # Sources SASS
├── abstracts/              # Variables, mixins
├── base/                   # Reset, globals
├── components/             # Boutons, formulaires
├── layout/                 # Header, footer
├── pages/                  # Styles pages
└── main.scss               # Entrée principale

public/assets/css/          # CSS compilé
└── style.css               # Fichier final
```

---

## 📁 Arborescence du projet (extrait)

```
ecoride/
├── assets/
│   └── scss/
├── public/
│   ├── assets/
│   └── index.php
├── src/
│   ├── Controller/
│   │   ├── Controller.php
│   │   ├── ErrorController.php
│   │   └── PageController.php
│   ├── Db/
│   ├── Model/
│   ├── Routing/
│   │   └── Router.php
│   └── View/
│       ├── layout.php
│       ├── home.php
│       ├── partials/
│       │   ├── header.php
│       │   └── footer.php
│       └── pages/
├── config/
├── documentation/
├── vendor/
├── docker-compose.yml
├── Dockerfile
└── package.json
```

---

## 🚀 Architecture technique & Router

### Routing MVC custom

Le routing est géré par la classe `Router` : `src/Routing/Router.php`.

**Exemple d'utilisation :**

```php
$router = new App\Routing\Router();
$router->handleRequest($uri);
```

Le Router analyse l'URL, sélectionne le contrôleur et la méthode à appeler, puis charge la vue correspondante.

**Organisation typique :**

-   Contrôleurs : `src/Controller/`
-   Vues : `src/View/`
-   Layout principal : `src/View/layout.php`
-   Partials (header/footer) : `src/View/partials/`

**Exemple de contrôleur :**

```php
namespace App\Controller;

class PageController extends Controller {
    public function home() {
        $this->render('home');
    }

    public function contact() {
        $this->render('pages/contact');
    }
}
```

**Exemple de vue :**

```php
// src/View/home.php
<?php require_once __DIR__ . '/partials/header.php'; ?>
<main>
    <h1>Bienvenue sur EcoRide !</h1>
    <!-- contenu -->
</main>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
```

---

## 🎯 Fonctionnalités développées

-   **Accueil** : Présentation du service
-   **Liste covoiturages** : Affichage des trajets
-   **Contact** : Formulaire de contact
-   **Connexion/Inscription** : Modales d'authentification
-   **Profil utilisateur** : Gestion du compte
-   **Création de covoiturage** : Publication de trajets

---

## 👨‍💻 Git & Organisation

### Branches

```bash
main                    # Production
develop                 # Intégration
feat/xxx                # Nouvelle fonctionnalité
fix/xxx                 # Correction
refactor/xxx            # Refactoring
```

### Commits

```bash
feat: ajout fonctionnalité
fix: correction bug
style: amélioration design
refactor: refonte code
```

---

## 🛠️ Docker & Apache

### docker-compose.yml (extrait)

```yaml
services:
    web:
        build: .
        ports: ["8080:80"]
        volumes:
            - .:/var/www/html
    db:
        image: mysql:8.0
        ports: ["3307:3306"]
        environment:
            MYSQL_ROOT_PASSWORD: root
            MYSQL_DATABASE: ecoride
    phpmyadmin:
        image: phpmyadmin/phpmyadmin
        ports: ["8081:80"]
```

### Dockerfile (extrait)

```dockerfile
FROM php:8.2-apache
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite
COPY . /var/www/html/
```

---

## 📋 Base de données (exemple)

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE covoiturages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    departure VARCHAR(100),
    destination VARCHAR(100),
    date_time DATETIME,
    price DECIMAL(10,2),
    seats_available INT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## 🧪 Qualité & tests

-   Respect des standards PSR
-   HTML5/CSS3 valides
-   Tests manuels navigateurs
-   CSS minifié, images optimisées
-   Architecture MVC propre

---

## 📌 Améliorations futures

-   [ ] Système de réservation complet
-   [ ] Notifications temps réel
-   [ ] API REST mobile
-   [ ] Paiement en ligne
-   [ ] Géolocalisation avancée
-   [ ] Tests automatisés

---

## 🤝 Contribution

Projet réalisé dans le cadre de l'**ECF TP DWWM – Studi**. Usage pédagogique uniquement.

---

_Dernière mise à jour : Juillet 2025_
