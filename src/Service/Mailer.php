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

    public function __construct(?string $from = null, ?string $fromName = null)
    {
        $this->from     = $from     ?: ($this->env('MAIL_FROM') ?: 'no-reply@example.com');
        $this->fromName = $fromName ?: ($this->env('MAIL_FROM_NAME') ?: 'EcoRide');

        $this->appEnv = $this->detectEnv();
        $this->smtp   = $this->buildSmtpConfig();

        // /tmp est writable sur Heroku
        $this->logFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/ecoride-mail.log';
    }

    public function send(string $to, string $subject, string $htmlBody): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log('[Mailer] Invalid recipient: ' . $to);
            return false;
        }
        // Anti header injection
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
        } catch (MailException $e) {
            $errInfo = '';
            if (($mailer instanceof PHPMailer) && !empty($mailer->ErrorInfo)) {
                $errInfo = ' ErrorInfo=' . $mailer->ErrorInfo;
            }
            error_log('[Mailer][SMTP] ' . $e->getMessage() . $errInfo);
            return $this->logFallback($to, $subject, $htmlBody);
        }
    }

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

        if (!empty($this->smtp['user'])) {
            $m->SMTPAuth = true;
            $m->Username = $this->smtp['user'];
            $m->Password = $this->smtp['pass'] ?? '';
        }

        $secure = strtolower((string)($this->smtp['secure'] ?? ''));
        if (in_array($secure, ['tls', 'ssl'], true)) {
            $m->SMTPSecure = $secure;
        }

        // Optionnel: Hostname (Message-ID / Received)
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

    private function detectEnv(): string
    {
        $env = (string)($this->env('APP_ENV') ?? '');
        if ($env === '') {
            $env = (defined('SITE_URL') && str_contains((string)SITE_URL, 'localhost')) ? 'dev' : 'prod';
        }
        return $env;
    }

    private function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        $v = getenv($key);
        return ($v !== false) ? $v : $default;
    }

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

    public function getLogFile(): string
    {
        return $this->logFile;
    }
}
