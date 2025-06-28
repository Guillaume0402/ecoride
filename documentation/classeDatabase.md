# 📦 Classe `Database.php`

Cette classe PHP permet d’établir une connexion à une base de données **MySQL** en utilisant **PDO**, tout en s’appuyant sur des variables d’environnement pour une configuration flexible, notamment dans un contexte Docker.

---

## 🧠 Objectif

Permettre de :
- Se connecter proprement à MySQL avec PDO
- Centraliser la configuration via des variables `.env`
- Afficher les erreurs de manière sécurisée selon l’environnement (`APP_DEBUG`)
- Fermer la connexion manuellement si nécessaire

---

## 🗂️ Contenu de la classe

### 🧱 Attributs privés

| Attribut     | Description                                      |
|--------------|--------------------------------------------------|
| `$host`      | Adresse du serveur MySQL                         |
| `$db_name`   | Nom de la base de données                        |
| `$username`  | Identifiant de connexion                         |
| `$password`  | Mot de passe de connexion                        |
| `$port`      | Port MySQL (par défaut : `3306`)                 |
| `$conn`      | Objet PDO représentant la connexion              |

---

## 🔧 Constructeur

```php
public function __construct()
```

Le constructeur initialise les paramètres de connexion en récupérant les **variables d’environnement** avec la fonction `getenv()`, en utilisant des valeurs par défaut si elles ne sont pas définies.

### Variables utilisées :

| Clé `.env`       | Par défaut        |
|------------------|-------------------|
| `DB_HOST`        | `db`              |
| `DB_NAME`        | `ecoride`         |
| `DB_USER`        | `ecoride_user`    |
| `DB_PASSWORD`    | `ecoride_password`|
| `DB_PORT`        | `3306`            |

---

## 🔌 Méthode `getConnection()`

```php
public function getConnection()
```

Tente d'établir une connexion à la base de données avec **PDO** en UTF-8 et gère les erreurs selon la configuration.

### Étapes de la méthode :
1. Crée le DSN :  
   ```php
   mysql:host=HOST;port=PORT;dbname=DB_NAME;charset=utf8
   ```

2. Tente la connexion avec PDO :
   ```php
   $this->conn = new PDO($dsn, $this->username, $this->password, [...]);
   ```

3. Active les options suivantes :
   - `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
   - `PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"`

4. Gère les erreurs :
   ```php
   if (getenv('APP_DEBUG') === 'true') {
       echo "Erreur de connexion : " . $exception->getMessage();
   } else {
       echo "Erreur de connexion à la base de données.";
   }
   ```

---

## ❌ Méthode `disconnect()`

```php
public function disconnect()
```

Permet de manuellement **fermer la connexion à la base** :
```php
$this->conn = null;
```

---

## ✅ Exemple d’utilisation

```php
require_once 'src/Database.php';

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "✅ Connexion réussie.";
} else {
    echo "❌ Connexion échouée.";
}
```

---

## 📄 Exemple de fichier `.env`

```env
DB_HOST=db
DB_NAME=ecoride
DB_USER=ecoride_user
DB_PASSWORD=ecoride_password
DB_PORT=3306

APP_DEBUG=true
APP_ENV=development
```

---

## 🛡️ Bonnes pratiques

- ✅ Ne jamais afficher d’erreur complète en production (`APP_DEBUG=false`)
- ✅ Toujours utiliser `PDO` avec `ERRMODE_EXCEPTION`
- ✅ Préférer `getenv()` à `$_ENV` dans les projets Dockerisés
- ✅ Centraliser les variables dans un fichier `.env` ignoré par Git

---

## 🧪 Test rapide

Ajoute un fichier `test-db.php` à la racine de ton projet :

```php
<?php
require_once 'src/Database.php';

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "<span style='color: green;'>✅ Connexion réussie.</span>";
} else {
    echo "<span style='color: red;'>❌ Échec de la connexion.</span>";
}
```

---

## 📂 Emplacement recommandé

Place `Database.php` dans un dossier logique :

```
src/
└── Database.php
```

Et configure l’autoload si tu utilises Composer :
```json
"autoload": {
  "psr-4": {
    "App\\": "src/"
  }
}
```

---

## 📌 À retenir

> **La classe `Database` est un composant central de ton back-end. Elle doit être claire, sécurisée, et facilement réutilisable dans tout ton projet.**
