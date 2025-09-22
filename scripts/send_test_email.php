<?php
// scripts/send_test_email.php
// Envoi d'un e-mail de test via le service Mailer (utilise les variables d'environnement)

declare(strict_types=1);

// Autoload Composer et constantes
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/constants.php';

// Optionnel: charger .env si présent (ne plante pas si absent)
try {
    if (class_exists(\Dotenv\Dotenv::class)) {
        $dotenv = \Dotenv\Dotenv::createMutable(dirname(__DIR__), ['.env', '.env.local']);
        $dotenv->safeLoad();
    }
} catch (\Throwable $e) {
    // silencieux en prod
}

$to = $argv[1] ?? ($_ENV['TEST_EMAIL_TO'] ?? getenv('TEST_EMAIL_TO') ?: null);
if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php scripts/send_test_email.php <destinataire@exemple.com>\n".
        "(ou définir TEST_EMAIL_TO dans l'env)\n");
    exit(1);
}

$subject = $argv[2] ?? 'EcoRide: Test SMTP';
$body = '<p>Ceci est un e-mail de test envoyé par EcoRide.</p>'
      . '<p>Date: ' . date('Y-m-d H:i:s') . "</p>";

$mailer = new \App\Service\Mailer();
$ok = $mailer->send($to, $subject, $body);

if ($ok) {
    echo "OK: e-mail de test traité.\n";
} else {
    echo "ECHEC: l\'e-mail de test n\'a pas pu être traité.\n";
}

if (method_exists($mailer, 'getLogFile')) {
    echo 'Log: ' . $mailer->getLogFile() . "\n";
}
