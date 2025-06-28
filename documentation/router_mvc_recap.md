
# 🧠 RÉCAP COMPLET DU ROUTEUR MVC EN PHP

## 🗂️ STRUCTURE MVC SIMPLIFIÉE

**MVC = Modèle / Vue / Contrôleur**

| Composant     | Rôle |
|---------------|------|
| `Router.php`  | Gère les routes (URLs) |
| `Controller`  | Traite la logique selon la route |
| `view/*.php`  | Affiche l’interface à l’utilisateur |
| `helpers.php` | Fonctions utiles : `url()`, `view()`, `asset()` |
| `index.php`   | Point d’entrée de l’application (exécuté à chaque visite) |

---

## 1. 🧭 `Router.php` – Le cœur du système de routage

### ✅ Le `namespace`

- `namespace App;` indique que cette classe appartient au groupe logique "App".
- Cela évite les conflits si d'autres classes avec le même nom existent ailleurs.

### 🧱 Attributs

```php
public static string $basePath = '';
```
- `public` = accessible partout
- `static` = on peut y accéder sans instancier l'objet
- `string` = typage strict (PHP 7+)

```php
protected array $routes = [];
```
- `protected` = accessible dans la classe ou ses enfants uniquement
- `array` = contient toutes les routes classées par méthode (`GET`, `POST`, etc.)

### 📥 Méthodes `get()` et `post()`

Enregistrent une route en appelant `addRoute()` avec la bonne méthode HTTP.

---

### 🚦 `dispatch()`

Traite la requête réelle :
1. Récupère la méthode HTTP
2. Nettoie l'URL (`basePath`, query strings)
3. Cherche la route
4. Exécute la méthode du contrôleur OU le callback
5. Sinon ➜ erreur 404 ou 500

---

## 2. 🛠️ Fichier `helpers.php`

Contient les fonctions globales utilisées dans les vues.

### 🔗 `url($path)`

```php
function url(string $path = ''): string {
    return rtrim(Router::$basePath, '/') . '/' . ltrim($path, '/');
}
```

#### 🔍 Explication détaillée :
- `Router::$basePath` contient un éventuel sous-dossier comme `/monprojet`
- `rtrim()` supprime les `/` à droite
- `ltrim()` supprime les `/` à gauche de `$path`
- Résultat : une URL propre même si ton site est dans un sous-dossier

#### Exemple :
```php
<a href="<?= url('login') ?>">Connexion</a>
```

➡ Donne `/login` ou `/monprojet/login` selon ton `$basePath`.

---

### 🖼️ `asset($path)`

```php
function asset(string $path): string {
    return url('assets/' . ltrim($path, '/'));
}
```

#### 🔍 Explication détaillée :
- On ajoute `'assets/'` au chemin demandé
- `ltrim($path, '/')` évite les doubles slashs
- On utilise `url()` pour s'assurer que le chemin est correct

#### Exemple :
```php
<img src="<?= asset('images/logo.png') ?>" alt="Logo">
```

➡ Donne `/assets/images/logo.png` ou `/monprojet/assets/images/logo.png`

---

### 👁️ `view()`

Charge un fichier de vue avec des données :
1. `extract($data)` ➜ transforme `['title' => 'Bienvenue']` en `$title = 'Bienvenue'`
2. Capture le contenu avec `ob_start()`
3. Affiche la vue dans un layout commun

---

## 3. 🧑‍🏫 `HomeController.php`

```php
namespace App\controller;

class HomeController {
    public function index(): void {
        view('home');
    }
}
```

Affiche `view/home.php` quand l'utilisateur visite `/`.

---

## 4. 📄 `view/home.php`

HTML affiché à l'utilisateur. Utilise :

- `include` pour le header et footer
- `url()` pour les liens
- `asset()` pour les images
- Un formulaire de recherche

---

## 5. 🚪 `index.php`

Point d'entrée du site.

1. Charge tous les fichiers nécessaires
2. Définit `$basePath`
3. Déclare toutes les routes
4. Lance `$router->dispatch()`

---

## 🔚 Résumé final

| Élément        | Explication |
|----------------|-------------|
| `public`       | Accessible partout |
| `private`      | Accessible seulement dans la classe |
| `protected`    | Accessible dans la classe et ses enfants |
| `static`       | Accessible sans créer d’objet |
| `namespace`    | Organise ton code par "dossier logique" |
| `dispatch()`   | Détecte l’URL actuelle et appelle la bonne méthode |
| `view()`       | Charge une page HTML avec les données |
| `url()`        | Crée des liens dynamiques en tenant compte du sous-dossier |
| `asset()`      | Crée le chemin vers les fichiers statiques |
| `call_user_func()` | Appelle dynamiquement une fonction ou méthode |

---

## 📦 Exemple complet

```php
// index.php
$router->get('/', 'HomeController@index');

// HomeController.php
public function index() {
    view('home', ['title' => 'Bienvenue sur EcoRide']);
}

// home.php
<h1><?= $title ?></h1>
```
