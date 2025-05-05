# 🌱 EcoRide – Plateforme de covoiturage écologique

**EcoRide** est une application web fullstack développée en PHP Vanilla, HTML5, CSS3, Bootstrap et SASS.  
Elle a été conçue pour promouvoir le covoiturage responsable à travers une interface moderne, responsive et fonctionnelle.

---

## 🔧 Stack technique utilisée

| Front-end        | Back-end             | Bases de données       | Outils & Services         |
|------------------|----------------------|-------------------------|---------------------------|
| HTML5 / CSS3     | PHP (Vanilla)        | MySQL (relationnelle)   | Git + GitHub              |
| Bootstrap 5      | PDO / SQL            | MongoDB (NoSQL)         | Trello / Notion (Kanban)  |
| SASS             | Sessions, sécurité   |                         | Figma (maquettes)         |
| JavaScript       | Routing PHP custom   |                         | Fly.io / Vercel (prod)    |

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

## 📦 Installation en local avec VirtualHost (WAMP recommandé)

### 1. Cloner le projet

```bash
git clone https://github.com/ton-user/ecoride.git
cd ecoride
```

### 2. Créer un VirtualHost Apache

Modifier `httpd-vhosts.conf` :

```apache
<VirtualHost *:80>
    ServerName ecoride.local
    DocumentRoot "C:/wamp64/www/ecoride/public"
    <Directory "C:/wamp64/www/ecoride/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. Modifier le fichier `hosts` (en admin)

```text
127.0.0.1    ecoride.local
```

### 4. Redémarrer Apache

---

### 5. Créer la base de données

- Nom : `ecoride`
- Importer le fichier : `database/ecoride.sql`

### 6. Configurer la BDD dans `config/db.php`

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecoride');
define('DB_USER', 'root');
define('DB_PASS', '');
```

---

## 🎨 Compilation SCSS

### 1. Installer les dépendances

```bash
npm install
```

### 2. Scripts disponibles

| Commande              | Description                              |
|-----------------------|------------------------------------------|
| `npm run sass:watch`  | Compilation automatique en développement |
| `npm run sass:build`  | Compilation minifiée pour production     |
| `npm run dev`         | Démarre tous les watchers (ex. Sass)     |

> Le fichier compilé est généré dans :  
> `public/assets/css/style.css`

---

## 📁 Arborescence du projet

```
EcoRide/
├── public/                # Dossier public (DocumentRoot)
│   ├── assets/            # Images, icônes, CSS final
│   ├── scss/              # SASS structuré (abstracts, layout, pages…)
│   ├── .htaccess          # Rewrite URL
│   └── index.php          # Point d’entrée
├── src/
│   ├── controller/        # Contrôleurs PHP
│   ├── view/              # Vues HTML
│   ├── Router.php         # Routeur maison
│   └── helpers.php        # Fonctions utilitaires : url(), asset(), view()
├── includes/              # header.php / footer.php partagés
├── config/                # Connexion à la base de données
├── database/              # Scripts SQL (MySQL, MongoDB)
├── README.md
└── package.json
```

---

## 🖼️ Maquettes et charte graphique

- 🎨 Couleur principale : `#00A86B` (vert)
- 🖥️ 3 maquettes desktop
- 📱 3 maquettes mobile
- 🔗 [Voir sur Figma](https://www.figma.com/file/FO8Ms3N8CaLOpCgiJTD5VS/EcoRide-Maquettes)

---

## 👨‍💻 Git & Organisation du projet

- **Branches Git** :
  - `main` : version stable en production
  - `develop` : développement principal
  - `feature/*` : une fonctionnalité par branche

- **Kanban projet** :
  - To Do → En cours → En test → Terminé → Mergé

---

## 🗂️ Stratégie Git (résumé)

- `main` → version stable (prod)
- `develop` → version testée et en cours de validation
- `feat/*` → nouvelles fonctionnalités
- `fix/*` → corrections de bugs
- `doc/*` → documentation

**Workflow recommandé :**

```bash
git checkout develop
git pull origin develop
git checkout -b feat/ma-fonctionnalité
# ... coder, puis :
git add .
git commit -m "feat: ajout de la modale de connexion"
git push origin feat/ma-fonctionnalité
```

Une fois testé : merge vers `develop`, puis vers `main` quand c’est prêt pour la prod.

📄 Voir le fichier [git-strategie.md](./git-strategie.md) pour plus de détails.

---

## 📌 Liens utiles

- 🔗 Démo en ligne : [https://ecoride.fly.dev](https://...)
- 📁 GitHub : [https://github.com/ton-user/ecoride](https://...)
- 🗂️ Kanban Notion : [https://notion.so/ecoride-kanban](https://...)

---

## 🧾 Licence

Projet réalisé dans le cadre de l’**ECF TP DWWM – Studi**.  
Usage pédagogique uniquement. Tous droits réservés © 2025.

---

## 🤝 Remerciements

Merci à Studi, à la communauté open source (PHP, Bootstrap, SASS, etc.),  
et à mes formateurs pour leur accompagnement tout au long du projet.
