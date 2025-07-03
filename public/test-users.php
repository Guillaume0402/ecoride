<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$sql = "
    SELECT users.pseudo, users.email, roles.role_name AS role
    FROM users
    LEFT JOIN roles ON users.role_id = roles.id
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Liste des utilisateurs :</h2><ul>";
foreach ($users as $user) {
    echo "<li>{$user['pseudo']} – {$user['email']} ({$user['role']})</li>";
}
echo "</ul>";
?>

