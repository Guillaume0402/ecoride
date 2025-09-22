<?php


// Autoloader PSR-4 minimal pour l'espace de noms App\ → répertoire src/
spl_autoload_register(function ($class) {
    $prefix = 'App\\'; // préfixe d'espace de noms géré
    $baseDir = APP_ROOT . '/src/'; // base des classes applicatives

    // Ignore les classes hors de l'espace de noms App\
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return; // laisse un autre autoloader potentiellement gérer
    }

    // Transforme App\Foo\Bar → Foo/Bar.php
    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file; // inclut la classe correspondante
    }
});

// Démarre la session si aucune n'est active (utile pour les flash messages, auth, etc.)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
