# Meta et SEO

avoir une base propre pour l’ECF.

## Ce qui a été ajouté

-   Title et meta description dynamiques.
-   Lien canonical (évite le contenu dupliqué).
-   Robots auto: noindex en dev, index en prod.
-   Open Graph / Twitter (aperçus de partage).
-   Favicon, theme-color et un petit manifest.

## Où ça se passe

-   Fichier: `src/View/layout.php` (dans la balise `<head>`).
-   Manifest: `public/site.webmanifest`.

## Comment personnaliser une page

Dans un contrôleur, on peut passer des infos au rendu:

```php
$this->render('pages/ma-page', [
  'pageTitle' => 'Titre de la page',
  'metaDescription' => 'Description courte (150–160 caractères).',
  // Optionnel:
  // 'metaImage' => SITE_URL . 'assets/images/mon-visuel.png',
  // 'canonical' => SITE_URL . 'ma-page',
  // 'noindex' => false,
]);
```

Si on ne passe rien:

-   Titre = "EcoRide".
-   Description par défaut (générique).
-   Image OG par défaut: `/assets/images/Avatar.png`.

## Environnements

-   Dev/staging: `noindex` automatique.
-   Prod: `index,follow`.
-   Penser à définir `SITE_URL` en prod (ex: `https://monapp.example/`).

## TL;DR

-   L’essentiel (title, description, viewport, lang, favicon) est en place.
-   Pour une page, on peux juste définir `pageTitle` et `metaDescription`.
-   Le reste est du bonus qui ne gêne pas.
