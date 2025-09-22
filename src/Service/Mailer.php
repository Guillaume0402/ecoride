<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class Mailer
{
    private string $from;
    private string $fromName;
    private string $logFile;
    private string $appEnv;
    private ?array $smtp = null;

    public function __construct(?string $from = null, ?string $fromName = null)
    {
        // Préférence à $_ENV (chargé par phpdotenv) puis fallback getenv, sinon valeur par défaut
        $envFrom = $_ENV['MAIL_FROM'] ?? (getenv('MAIL_FROM') ?: null);
        $envFromName = $_ENV['MAIL_FROM_NAME'] ?? (getenv('MAIL_FROM_NAME') ?: null);
        $this->from = $from ?: ($envFrom ?: 'no-reply@example.com');
        $this->fromName = $fromName ?: ($envFromName ?: 'EcoRide');
        // Déduction d'environnement: variable APP_ENV sinon heuristique sur SITE_URL
        $env = null;
        if (isset($_ENV['APP_ENV'])) {
            $env = (string) $_ENV['APP_ENV'];
        } elseif (getenv('APP_ENV') !== false) {
            $env = (string) getenv('APP_ENV');
        }
        if (!$env) {
            $env = (defined('SITE_URL') && str_contains(SITE_URL, 'localhost')) ? 'dev' : 'prod';
        }
        $this->appEnv = $env;
        // SMTP config si fournie
        $host = getenv('SMTP_HOST') ?: ($_ENV['SMTP_HOST'] ?? null);
        if ($host) {
            $this->smtp = [
                'host' => $host,
                'port' => (int) (getenv('SMTP_PORT') ?: ($_ENV['SMTP_PORT'] ?? 587)),
                'user' => getenv('SMTP_USER') ?: ($_ENV['SMTP_USER'] ?? null),
                'pass' => getenv('SMTP_PASS') ?: ($_ENV['SMTP_PASS'] ?? null),
                'secure' => getenv('SMTP_SECURE') ?: ($_ENV['SMTP_SECURE'] ?? 'tls'), // 'tls' | 'ssl' | ''
            ];
        }

        // Fichier de log: en dev, utiliser un répertoire toujours accessible (/tmp)
        $this->logFile = $this->appEnv === 'dev'
            ? rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/ecoride-mail.log'
            : APP_ROOT . '/mail.log';
    }

    /**
     * Envoi simple en texte/HTML. Retourne true si mail() a renvoyé true
     * ou si le fallback de log a réussi.
     */
    public function send(string $to, string $subject, string $htmlBody): bool
    {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $this->fromName . ' <' . $this->from . '>';
        $headersStr = implode("\r\n", $headers);

        // Si config SMTP présente → utiliser SMTP prioritairement (dev ou prod)
        if ($this->smtp) {
            try {
                $mailer = new PHPMailer(true);
                $mailer->isSMTP();
                $mailer->Host = $this->smtp['host'];
                $mailer->Port = $this->smtp['port'];
                if (!empty($this->smtp['user'])) {
                    $mailer->SMTPAuth = true;
                    $mailer->Username = $this->smtp['user'];
                    $mailer->Password = $this->smtp['pass'] ?? '';
                }
                $secure = strtolower((string) ($this->smtp['secure'] ?? 'tls'));
                if (in_array($secure, ['tls', 'ssl'], true)) {
                    $mailer->SMTPSecure = $secure;
                }
                $mailer->CharSet = 'UTF-8';
                $mailer->setFrom($this->from, $this->fromName);
                $mailer->addAddress($to);
                $mailer->Subject = $subject;
                $mailer->isHTML(true);
                $mailer->Body = $htmlBody;
                $mailer->AltBody = strip_tags($htmlBody);
                $mailer->send();
                return true;
            } catch (MailException $e) {
                error_log('[Mailer][SMTP] ' . $e->getMessage());
                // fallback log
                return $this->logFallback($to, $subject, $htmlBody);
            }
        }

        // En dev sans SMTP, on ne tente pas d'envoyer un vrai mail: on journalise directement
        if ($this->appEnv === 'dev') {
            return $this->logFallback($to, $subject, $htmlBody);
        }

        // En prod, tentative d’envoi via mail(), sinon fallback log
        $ok = @mail($to, $subject, $htmlBody, $headersStr);
        if ($ok) {
            return true;
        }
        // Fallback: journaliser le message pour inspection
        return $this->logFallback($to, $subject, $htmlBody);
    }

    private function logFallback(string $to, string $subject, string $htmlBody): bool
    {
        $entry = sprintf("[%s] TO:%s SUBJECT:%s\n%s\n\n", date('Y-m-d H:i:s'), $to, $subject, $htmlBody);
        try {
            // Supprime les warnings potentiels d'IO hors JSON (les erreurs seront loguées via error_log)
            @file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
            return true;
        } catch (\Throwable $e) {
            error_log('[Mailer] fallback log failed: ' . $e->getMessage());
            return false;
        }
    }
}
