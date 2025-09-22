<?php
// scripts/db_import.php
// Usage: php scripts/db_import.php /path/to/init.sql
// Se connecte via variables d'environnement: DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

if ($argc < 2) {
    fwrite(STDERR, "Usage: php scripts/db_import.php <sql-file>\n");
    exit(1);
}

$file = $argv[1];
if (!is_file($file)) {
    fwrite(STDERR, "SQL file not found: {$file}\n");
    exit(1);
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASSWORD') ?: '';

if ($db === '' || $user === '') {
    fwrite(STDERR, "Missing DB_NAME/DB_USER in environment.\n");
    exit(1);
}

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $db);

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "Connection failed: " . $e->getMessage() . "\n");
    exit(2);
}

$sql = file_get_contents($file);
if ($sql === false) {
    fwrite(STDERR, "Unable to read SQL file: {$file}\n");
    exit(1);
}

// Fallback de collation pour compat MySQL < 8.0.11
$sql = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_general_ci', $sql);

try {
    $pdo->exec('SET NAMES utf8mb4');
} catch (Throwable $e) {
    // ignore
}

try {
    $pdo->exec($sql);
    fwrite(STDOUT, "Import succeeded using single exec().\n");
    exit(0);
} catch (Throwable $e) {
    // Fallback: exécuter statement par statement
    fwrite(STDERR, "Bulk exec failed: " . $e->getMessage() . "\nFalling back to split statements...\n");
    $statements = preg_split('/;\s*\n/', $sql);
    $count = 0; $errors = 0;
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '' || str_starts_with($stmt, '--') || str_starts_with($stmt, '/*')) {
            continue;
        }
        try {
            $pdo->exec($stmt);
            $count++;
        } catch (Throwable $se) {
            $errors++;
            fwrite(STDERR, "Failed statement: " . $se->getMessage() . "\n" . $stmt . "\n---\n");
        }
    }
    fwrite(STDOUT, "Executed {$count} statements with {$errors} errors.\n");
    exit($errors > 0 ? 3 : 0);
}
