# Récap pratique: notre Router MVC (EcoRide)

Ce fichier résume comment nos pages sont dispatchées avec le Router « maison » et comment on rend les vues. C’est volontairement simple et fidèle au code actuel (pas de pseudo-helpers inventés).

---

## Architecture en deux mots

-   Le Router est `App\Routing\Router` et lit la table des routes dans `config/routes.php`.
-   Les contrôleurs sont sous `App\Controller\...` et héritent du contrôleur de base `Controller` qui expose `render()` pour afficher une vue dans le layout.
-   Les vues sont sous `src/View/...` (ex: `src/View/pages/login.php`) et sont injectées dans `src/View/layout.php`.
-   Le point d’entrée est `public/index.php` qui charge les constantes, l’autoload Composer, `.env`, puis appelle `Router->handleRequest()`.

---

## Router: fonctionnement réel

Fichier: `src/Routing/Router.php`

-   Charge les routes via: `require APP_ROOT . '/config/routes.php'`.
-   Normalise l’URI, détecte la méthode HTTP, et tente de faire matcher un pattern.
-   Les paramètres entre accolades sont numériques par défaut: `{id}` correspond à `(\d+)`.
-   En cas d’absence de route: 404 via `ErrorController`. En erreur: 500.
-   Environnement « dev »: log basique des routes chargées et du chemin recherché.

Extrait (simplifié) des routes:

```php
return [
    '/login' => [
        'GET' => ['controller' => App\Controller\AuthController::class, 'action' => 'showLogin']
    ],
    '/covoiturages/{id}' => [
        'GET' => ['controller' => App\Controller\PageController::class, 'action' => 'showCovoiturage']
    ],
    '/participations/accept/{id}' => [
        'POST' => ['controller' => App\Controller\ParticipationController::class, 'action' => 'accept']
    ],
];
```

Note: si vous voulez des slugs non numériques (ex: `{slug}`), il faut adapter `Router` (remplacer la regex `(\d+)` par `[^/]+` ou gérer au cas par cas).

---

## Rendu des vues: `Controller::render()`

Fichier: `src/Controller/Controller.php`

-   Démarre la session au besoin, prépare des infos globales (utilisateur, véhicules, compteurs).
-   `render('pages/login', $data)` va:
    -   extraire `$data` en variables,
    -   inclure la vue `src/View/pages/login.php` dans un buffer,
    -   injecter ce contenu dans `src/View/layout.php`.

On n’utilise pas de helpers `url()`, `asset()` ou `view()` génériques. Tout passe par `render()` côté contrôleurs.

---

## Point d’entrée: `public/index.php`

1. Définit les constantes (`config/constants.php`), charge l’autoload Composer et les variables d’environnement via Dotenv.
2. Inclut `src/helpers.php` (avec `redirect()`, `abort()`, `getRideCreateFee()`).
3. Instancie le Router et appelle `handleRequest($_SERVER['REQUEST_URI'])`.

En dev, les exceptions sont relancées; en prod, on affiche une 500 propre.

---

## Mini « contrat » d’une route

-   Entrée: un chemin (ex: `/admin/users/toggle/{id}`) + méthode HTTP (`GET`/`POST`).
-   Sortie: appel de `Controller::action($params...)` avec les paramètres capturés.
-   Erreurs: 404 si route introuvable, 405 si méthode non autorisée, 500 si classe ou méthode absente.

---

## Exemples concrets

1. Page de détail d’un covoiturage:

```php
// config/routes.php
'/covoiturages/{id}' => [
    'GET' => ['controller' => App\Controller\PageController::class, 'action' => 'showCovoiturage']
]

// src/Controller/PageController.php
public function showCovoiturage(int $id): void {
    // récupérer les infos et rendre la vue
    $this->render('pages/covoiturage-detail', ['id' => $id]);
}
```

2. Action POST protégée (acceptation d’une demande):

```php
// config/routes.php
'/participations/accept/{id}' => [
    'POST' => ['controller' => App\Controller\ParticipationController::class, 'action' => 'accept']
]
```

---

## À retenir

-   Routes déclarées dans `config/routes.php` (structure tableau par méthode HTTP).
-   Router strict sur les paramètres numériques par défaut.
-   Rendu via `Controller::render()`; pas de helpers de template spéciaux.
-   `helpers.php` expose `redirect()`, `abort()` et `getRideCreateFee()`.
