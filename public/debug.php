<?php
define('APP_ROOT', dirname(__DIR__));

echo "<h1>DEBUG COMPLET</h1>";
echo "<p>APP_ROOT: " . APP_ROOT . "</p>";

$homePath = APP_ROOT . '/src/View/home.php';
echo "<p>Chemin home.php: " . $homePath . "</p>";
echo "<p>Fichier existe: " . (file_exists($homePath) ? "✅ OUI" : "❌ NON") . "</p>";

if (file_exists($homePath)) {
    echo "<p>Taille du fichier: " . filesize($homePath) . " octets</p>";
    echo "<hr><h2>CONTENU BRUT:</h2>";
    echo "<pre>" . htmlspecialchars(file_get_contents($homePath)) . "</pre>";
    echo "<hr><h2>RENDU:</h2>";
    include $homePath;
}