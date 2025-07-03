<?php

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "✅ Connexion réussie à la BDD.";
} else {
    echo "❌ Échec de connexion.";
}
// Pour fermer la connexion, si nécessaire
// $db->disconnect();