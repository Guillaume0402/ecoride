# 🌱 EcoRide – Plateforme de covoiturage écologique

**EcoRide** est une application web fullstack développée en PHP Vanilla avec architecture MVC.  
Elle a été conçue pour promouvoir le covoiturage responsable à travers une interface moderne, responsive et fonctionnelle.

---

## 🔧 Stack technique utilisée

| Front-end     | Back-end           | Base de données | DevOps & Outils           |
| ------------- | ------------------ | --------------- | ------------------------- |
| HTML5 / CSS3  | PHP 8.2 (Vanilla)  | MySQL 8.0       | Docker + Docker Compose   |
| Bootstrap 5.3 | PDO / SQL          | phpMyAdmin      | Git + GitHub              |
| SASS          | Sessions PHP       |                 | Sass (compilation CSS)    |
| JavaScript    | Routing PHP custom |                 | npm (gestion dépendances) |

---

## ✅ Fonctionnalités principales

-   🔍 Recherche de covoiturages par ville et date
-   🧭 Affichage des trajets avec filtres et tri
-   👤 Système d'authentification (connexion/inscription)
-   🚗 Publication de trajets pour les conducteurs
-   📋 Gestion des profils utilisateurs
-   📱 Interface responsive (desktop + mobile)
-   🎨 Design moderne avec animations CSS

---

## 🐳 Installation avec Docker (Recommandé)

### 1. Prérequis

-   Docker Desktop installé
-   Git installé
-   Node.js et npm installés

### 2. Cloner et démarrer le projet

```bash
# Cloner le projet
git clone <votre-repo>
cd ecoride

# Installer les dépendances npm
npm install

# Compiler le CSS
npm run sass:build

# Démarrer les services Docker
docker-compose up -d
```

### 3. Accéder à l'application

-   **Application** : http://localhost:8080
-   **phpMyAdmin** : http://localhost:8081
-   **Base de données** : localhost:3307

### 4. Services disponibles

| Service    | Container          | Port | Description              |
| ---------- | ------------------ | ---- | ------------------------ |
| Web        | ecoride_web        | 8080 | Apache + PHP 8.2         |
| Database   | ecoride_db         | 3307 | MySQL 8.0                |
| phpMyAdmin | ecoride_phpmyadmin | 8081 | Interface de gestion BDD |

---

## 🎨 Développement CSS/SASS

### Scripts disponibles

```bash
# Compilation CSS
npm run sass:build         # Compilation unique
npm run sass:watch         # Compilation automatique (développement)
npm run dev                 # Mode développement complet

# Gestion Docker
docker-compose up -d        # Démarrer les services
docker-compose down         # Arrêter les services
docker-compose exec web bash # Accéder au container web
```

### Structure SASS

```
assets/scss/                # Sources SASS (développement)
├── abstracts/              # Variables, mixins, fonctions
├── base/                   # Reset, globals, typography
├── components/             # Boutons, formulaires, cards
├── layout/                 # Header, footer, navigation
├── pages/                  # Styles spécifiques aux pages
└── main.scss              # Fichier principal

public/assets/css/          # CSS compilé (production)
└── style.css              # Fichier final (Bootstrap + custom)
```

---

## 📁 Arborescence du projet

```
EcoRide/
├── assets/                 # Sources de développement
│   └── scss/              # Fichiers SASS sources
├── public/                # Dossier public (DocumentRoot Docker)
│   ├── assets/            # Images, CSS compilé
│   ├── .htaccess          # Rewrite URL
│   └── index.php          # Point d'entrée
├── src/                   # Code source PHP
│   ├── Controller/        # Contrôleurs MVC
│   ├── Model/             # Modèles (vide pour l'instant)
│   ├── View/              # Vues et templates
│   │   ├── partials/      # Header, footer, modales
│   │   └── layout.php     # Template principal
│   ├── Router.php         # Routeur custom
│   └── helpers.php        # Fonctions utilitaires
├── config/                # Configuration
│   └── database.php       # Connexion BDD
├── documentation/         # Documentation projet
├── vendor/                # Dépendances Composer
├── node_modules/          # Dépendances npm
├── docker-compose.yml     # Configuration Docker
├── Dockerfile             # Image Docker custom
└── package.json           # Scripts npm et dépendances
```

---

## 🚀 Architecture technique

### Routing PHP custom

```php
// Exemples de routes définies
$router->get('/', 'HomeController@index');
$router->get('/liste-covoiturages', 'ListeCovoituragesController@index');
$router->get('/contact', 'ContactController@index');
$router->get('/login', 'LoginController@index');
```

### Pattern MVC

```
src/Controller/
├── HomeController.php
├── ContactController.php
├── ListeCovoituragesController.php
└── LoginController.php

src/View/
├── home.php
├── contact.php
├── liste-covoiturages.php
└── login.php
```

### Helpers et utilitaires

```php
// Fonctions disponibles
url('/path')              // Génère une URL absolue
asset('css/style.css')    // Génère le chemin vers les assets
view('template', $data)   // Charge une vue avec des données
```

---

## 🎯 Fonctionnalités développées

### ✅ Pages implémentées

-   **Accueil** : Présentation du service avec hero section
-   **Liste covoiturages** : Affichage des trajets disponibles
-   **Contact** : Formulaire de contact avec validation
-   **Connexion/Inscription** : Modales d'authentification
-   **Profil** : Gestion des informations utilisateur
-   **Création covoiturage** : Publication de nouveaux trajets

### 🎨 Design et UX

-   Interface responsive (mobile-first)
-   Animations CSS fluides
-   Système de couleurs cohérent
-   Bootstrap customisé via SASS
-   Icons Bootstrap intégrés

---

## 👨‍💻 Git & Organisation

### Stratégie de branches

```bash
main                    # Version stable production
develop                 # Intégration des fonctionnalités
feat/nom-fonctionnalite # Développement feature
fix/nom-correction      # Corrections de bugs
```

### Convention de commits

```bash
feat: ajout de la page de contact
fix: correction des erreurs 404 CSS
style: amélioration du design des cartes
refactor: optimisation du routeur PHP
```

---

## 🛠️ Configuration Docker

### Services configurés

```yaml
# docker-compose.yml (simplifié)
services:
    web: # Apache + PHP 8.2
        build: .
        ports: ["8080:80"]

    db: # MySQL 8.0
        image: mysql:8.0
        ports: ["3307:3306"]

    phpmyadmin: # Interface BDD
        image: phpmyadmin/phpmyadmin
        ports: ["8081:80"]
```

### Configuration Apache

```dockerfile
# Dockerfile (extrait)
FROM php:8.2-apache
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite
```

---

## 📋 Base de données

### Structure MySQL

```sql
-- Tables principales (exemple)
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

## 🧪 Tests et qualité

### Validation du code

-   Code PHP respectant les standards PSR
-   HTML5 valide et sémantique
-   CSS compilé et optimisé
-   Tests manuels sur différents navigateurs

### Performance

-   CSS minifié en production
-   Images optimisées
-   Requêtes SQL optimisées
-   Cache approprié

---

## 📌 Améliorations futures

### Fonctionnalités prévues

-   [ ] Système de réservation complet
-   [ ] Notifications en temps réel
-   [ ] API REST pour mobile
-   [ ] Système de paiement
-   [ ] Géolocalisation avancée
-   [ ] Tests automatisés

### Optimisations techniques

-   [ ] Cache Redis
-   [ ] CDN pour les assets
-   [ ] Monitoring et logs
-   [ ] CI/CD Pipeline
-   [ ] Tests unitaires PHPUnit

---

## 🤝 Contribution

Ce projet est réalisé dans le cadre de l'**ECF TP DWWM – Studi**.  
Usage pédagogique uniquement.

### Workflow de développement

```bash
# Créer une nouvelle fonctionnalité
git checkout develop
git pull origin develop
git checkout -b feat/ma-nouvelle-fonctionnalite

# Développer et commiter
git add .
git commit -m "feat: description de la fonctionnalité"

# Pousser et merger
git push origin feat/ma-nouvelle-fonctionnalite
# Puis merge request vers develop
```

---

## 🧾 Licence

Projet réalisé dans le cadre de l'**ECF TP DWWM – Studi**.  
Usage pédagogique uniquement. Tous droits réservés © 2025.

---

## 🙏 Remerciements

Merci à Studi, à la communauté open source (PHP, Bootstrap, SASS, Docker),  
et aux formateurs pour leur accompagnement tout au long du projet.

---

_Dernière mise à jour : Juillet 2025_
