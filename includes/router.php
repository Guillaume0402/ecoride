<?php
// Récupère la page demandée ou 'home' par défaut
$page = preg_replace('/[^a-z0-9_-]/i', '', $_GET['page'] ?? 'home');

// Liste blanche des pages autorisées
$availablePages = ['home', 'covoiturages', 'login'];

// Génère le chemin du fichier à inclure
$file = __DIR__ . '/../pages/' . $page . '.php';

// Vérifie si la page demandée est autorisée et le fichier existe
if (in_array($page, $availablePages) && file_exists($file)) {
    require_once $file;
} else {
    // Message d'erreur personnalisé en cas de page introuvable
    echo "<h2>404 – Page introuvable</h2>";
}
?>
