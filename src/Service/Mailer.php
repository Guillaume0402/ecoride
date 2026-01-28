<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * Mailer (version minimale, stable)
 * - SMTP via PHPMailer si configuré (SMTP_HOST)
 * - fallback log sinon (dev/prod)
 * - fallback log si erreur SMTP
 * - env() robuste : $_ENV puis getenv()
 */
class Mailer
{
    private string $from;
    private string $fromName;
    private string $logFile;
    private string $appEnv;
    private ?array $smtp;

    // Constructeur avec options d'expéditeur (sinon variables d'environnement) 
    public function __construct(?string $from = null, ?string $fromName = null)
    {
        // Adresse et nom de l'expéditeur
        $this->from     = $from     ?: ($this->env('MAIL_FROM') ?: 'no-reply@example.com');
        $this->fromName = $fromName ?: ($this->env('MAIL_FROM_NAME') ?: 'EcoRide');
        // Détecte l'environnement et la config SMTP
        $this->appEnv = $this->detectEnv();
        $this->smtp   = $this->buildSmtpConfig();

        // Fichier de log pour le fallback
        $this->logFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/ecoride-mail.log';
    }

    // Envoie un email HTML, retourne true si succès (SMTP ou log)
    public function send(string $to, string $subject, string $htmlBody): bool
    {
        // Vérifier la validité de l'adresse email du destinataire
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log('[Mailer] Invalid recipient: ' . $to);
            return false;
        }
        // nettoyer le sujet des retours à la ligne pour éviter l'injection d'en-têtes
        $subject = trim(str_replace(["\r", "\n"], ' ', $subject));

        // 1) SMTP si configuré
        if ($this->smtp) {
            return $this->sendViaSmtp($to, $subject, $htmlBody);
        }
        // 2) Pas de SMTP -> fallback log (dev/prod)
        if ($this->appEnv === 'prod') {
            error_log('[Mailer] SMTP non configuré en production – message journalisé dans ' . $this->logFile);
        }
        return $this->logFallback($to, $subject, $htmlBody);
    }

    // Envoi via SMTP avec PHPMailer, retourne true si succès
    private function sendViaSmtp(string $to, string $subject, string $htmlBody): bool
    {
        $mailer = null;

        try {
            $mailer = $this->buildPhpMailer();

            $mailer->addAddress($to);
            $mailer->Subject = $subject;

            $mailer->isHTML(true);
            $mailer->Body    = $htmlBody;
            $mailer->AltBody = strip_tags($htmlBody);

            $mailer->send();
            return true;
            // Si échec, log dans le fichier de fallback
        } catch (MailException $e) {
            $errInfo = '';
            if (($mailer instanceof PHPMailer) && !empty($mailer->ErrorInfo)) {
                $errInfo = ' ErrorInfo=' . $mailer->ErrorInfo;
            }
            // Log l'erreur SMTP et utilise le fallback pour journaliser le message
            error_log('[Mailer][SMTP] ' . $e->getMessage() . $errInfo);
            return $this->logFallback($to, $subject, $htmlBody);
        }
    }

    // Construire et configurer une instance de PHPMailer
    private function buildPhpMailer(): PHPMailer
    {
        $m = new PHPMailer(true);

        // Debug SMTP optionnel (SMTP_DEBUG=1|2)
        $smtpDebug = $this->env('SMTP_DEBUG');
        if ($smtpDebug !== null && (string)$smtpDebug !== '' && (int)$smtpDebug > 0) {
            $m->SMTPDebug  = (int)$smtpDebug;
            $m->Debugoutput = 'error_log';
        }

        $m->isSMTP();

        // AutoTLS : ON par défaut (SMTP_AUTOTLS=0 pour désactiver)
        $m->SMTPAutoTLS = ((string)($this->env('SMTP_AUTOTLS', '1')) === '1');

        $m->Host = $this->smtp['host'];
        $m->Port = $this->smtp['port'];

        // Authentification si utilisateur défini
        if (!empty($this->smtp['user'])) {
            $m->SMTPAuth = true;
            $m->Username = $this->smtp['user'];
            $m->Password = $this->smtp['pass'] ?? '';
        }
        // Sécurité (tls/ssl) si définie
        $secure = strtolower((string)($this->smtp['secure'] ?? ''));
        if (in_array($secure, ['tls', 'ssl'], true)) {
            $m->SMTPSecure = $secure;
        }

        // Hostname personnalisé si défini dans les variables d'environnement (Mailpit, Mailhog, etc.)
        $hostname = $this->env('MAIL_HOSTNAME');
        if (is_string($hostname) && $hostname !== '') {
            $m->Hostname = $hostname;
        }

        $m->CharSet = 'UTF-8';
        $m->setFrom($this->from, $this->fromName);
        $m->Sender = $this->from;

        $this->configureReplyTo($m);

        return $m;
    }

    // Configure l'adresse Reply-To si définie dans les variables d'environnement
    private function configureReplyTo(PHPMailer $m): void
    {
        $replyTo = $this->env('MAIL_REPLY_TO');
        if (is_string($replyTo) && $replyTo !== '') {
            try {
                $m->addReplyTo($replyTo);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    // Construit la configuration SMTP depuis les variables d'environnement
    private function buildSmtpConfig(): ?array
    {
        $host = $this->env('SMTP_HOST');
        if (!$host) return null;

        return [
            'host'   => (string)$host,
            'port'   => (int)($this->env('SMTP_PORT') ?: 587),
            'user'   => $this->env('SMTP_USER'),
            'pass'   => $this->env('SMTP_PASS'),
            'secure' => (string)($this->env('SMTP_SECURE') ?? ''),
        ];
    }

    // Détecte l'environnement d'exécution (dev/prod)
    private function detectEnv(): string
    {
        $env = (string)($this->env('APP_ENV') ?? '');
        if ($env === '') {
            $env = (defined('SITE_URL') && str_contains((string)SITE_URL, 'localhost')) ? 'dev' : 'prod';
        }
        return $env;
    }

    // Récupère une variable d'environnement de manière robuste
    private function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        $v = getenv($key);
        return ($v !== false) ? $v : $default;
    }

    // Journalise le mail dans un fichier de log, retourne true si succès
    private function logFallback(string $to, string $subject, string $htmlBody): bool
    {
        $entry = sprintf("[%s] TO:%s SUBJECT:%s\n%s\n\n", date('Y-m-d H:i:s'), $to, $subject, $htmlBody);

        $bytes = @file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
        if ($bytes === false) {
            error_log('[Mailer] fallback log failed: unable to write ' . $this->logFile);
            return false;
        }
        return true;
    }

    // Retourne le chemin du fichier de log utilisé pour le fallback
    public function getLogFile(): string
    {
        return $this->logFile;
    }
}
