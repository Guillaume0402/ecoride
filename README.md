# 🌱 EcoRide – Plateforme de covoiturage écologique

**EcoRide** est une application web fullstack développée en PHP Vanilla, HTML5, CSS3, Bootstrap et SASS.  
Elle a été conçue pour promouvoir le covoiturage responsable à travers une interface moderne, responsive et fonctionnelle.

---

## 🔧 Stack technique utilisée

| Front-end        | Back-end         | Bases de données     | Outils & Services        |
|------------------|------------------|-----------------------|--------------------------|
| HTML5 / CSS3     | PHP (Vanilla)    | MySQL (relationnelle) | Git + GitHub             |
| Bootstrap 5      | PDO / SQL        | MongoDB (NoSQL)       | Trello (Kanban)          |
| SASS             | Sessions, sécurité |                     | Figma (maquettes)        |
| JavaScript       | Routing PHP simple |                     | Fly.io / Vercel (prod)   |

---

## 🚀 Déploiement du projet

- 🔗 **Site en ligne** : [https://ecoride.fly.dev](https://...)
- 📁 **Dépôt GitHub public** : [https://github.com/ton-user/ecoride](https://...)
- 📌 **Kanban Notion / Trello** : [https://notion.so/ecoride-kanban](https://...)
- 🖼️ **Maquettes Figma** : [https://www.figma.com/file/FO8Ms3N8CaLOpCgiJTD5VS/EcoRide-Maquettes](https://...)

---

## 📦 Installation en local

### 1. Cloner le projet

```bash
git clone https://github.com/ton-user/ecoride.git
cd ecoride
```

### 2. Démarrer Apache + MySQL (WAMP/XAMPP)

### 3. Créer la base de données

- Nom : `ecoride`
- Importer : `database/ecoride.sql`

### 4. Configurer la connexion à la BDD

```php
// fichier config/db.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecoride');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Compiler le SCSS

```bash
npm install
npm run sass:watch
```

### 6. Lancer le projet

[http://localhost/EcoRide/public/index.php](http://localhost/EcoRide/public/index.php)

---

## 📁 Arborescence du projet

```
EcoRide/
├── public/             # HTML, CSS, JS
│   ├── css/
│   ├── js/
│   ├── images/
│   └── index.php
├── scss/               # SASS (abstracts, layout, pages…)
├── includes/           # Composants PHP réutilisables
├── config/             # Connexion BDD
├── database/           # Fichiers SQL / Mongo
├── README.md
├── .gitignore
└── package.json
```

---

## ✅ Fonctionnalités principales

- 🔍 Recherche d’itinéraires par ville + date
- 🧭 Résultats filtrables (écologique, prix, durée, note)
- 👤 Authentification + gestion des crédits
- 🚗 Publication de trajets (conducteur)
- 📋 Historique covoiturages
- 📊 Espace employé + admin (avis + stats)
- 📱 Responsive desktop + mobile

---

## 🖼️ Maquettes et charte graphique

- 🎨 Couleur principale : #00A86B (vert)
- 🖥️ 3 maquettes desktop
- 📱 3 maquettes mobile
- 🔗 [Voir sur Figma](https://www.figma.com/file/FO8Ms3N8CaLOpCgiJTD5VS/EcoRide-Maquettes)

---

## 👨‍💻 Gestion de projet (Git + Kanban)

- Branches Git :
  - `main` : production
  - `develop` : développement global
  - `feature/*` : fonctionnalités isolées

- Kanban :
  - À faire / En cours / En test / Terminé / Mergé

---

## 🧾 Licence

Projet réalisé pour l’**ECF TP DWWM – Studi**.  
Usage pédagogique uniquement. Tous droits réservés © 2025.

---

## 🤝 Remerciements

Merci à Studi, à la communauté open source (PHP, Bootstrap, Sass, etc.)  
et à mes formateurs pour leur accompagnement.
