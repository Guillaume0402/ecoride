<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

// Service d'envoi d'e-mails avec support SMTP, fallback logging, et configuration flexible via variables d'environnement.
// Utilise PHPMailer pour la gestion SMTP avancée.
// Supporte Mailpit en dev et SendGrid en prod.
class Mailer
{
    private string $from;
    private string $fromName;
    private string $logFile;
    private string $appEnv;
    private ?array $smtp = null;

    // Constructeur avec options d'expéditeur personnalisées (sinon depuis MAIL_FROM / MAIL_FROM_NAME)
    public function __construct(?string $from = null, ?string $fromName = null)
    {
        $envFrom = $this->env('MAIL_FROM');
        $envFromName = $this->env('MAIL_FROM_NAME');

        $this->from = $from ?: ($envFrom ?: 'no-reply@example.com');
        $this->fromName = $fromName ?: ($envFromName ?: 'EcoRide');

        $this->appEnv = $this->detectEnv();
        $this->smtp = $this->buildSmtpConfig();

        // Fichier de log: répertoire toujours accessible (ex: /tmp sur Heroku)
        $this->logFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/ecoride-mail.log';
    }

    // Envoie un e-mail HTML, retourne true si OK, false sinon
    public function send(string $to, string $subject, string $htmlBody): bool
    {
        // Validation minimale
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log('[Mailer] Invalid recipient: ' . $to);
            return false;
        }

        // Anti header-injection / sujet cassé
        $subject = trim(str_replace(["\r", "\n"], ' ', $subject));

        // 1) SMTP si configuré (Mailpit en dev, SendGrid en prod)
        if ($this->smtp) {
            return $this->sendViaSmtp($to, $subject, $htmlBody);
        }

        // 2) Pas de SMTP -> dev: log direct
        if ($this->appEnv === 'dev') {
            return $this->logFallback($to, $subject, $htmlBody);
        }

        // 3) Pas de SMTP -> prod: éviter mail() sur PaaS, log
        if ($this->appEnv === 'prod') {
            error_log('[Mailer] SMTP non configuré en production – message journalisé dans ' . $this->logFile);
            return $this->logFallback($to, $subject, $htmlBody);
        }

        // 4) Autres environnements: tentative mail() puis fallback
        $ok = @mail($to, $subject, $htmlBody, $this->buildPhpMailHeaders());
        return $ok ? true : $this->logFallback($to, $subject, $htmlBody);
    }

    // Envoi via SMTP avec PHPMailer
    private function sendViaSmtp(string $to, string $subject, string $htmlBody): bool
    {
        $mailer = null;

        try {
            $mailer = $this->buildPhpMailer();

            $mailer->addAddress($to);
            $mailer->Subject = $subject;

            $mailer->isHTML(true);
            $mailer->Body = $htmlBody;
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

    // Configure et retourne une instance PHPMailer prête à l'emploi
    private function buildPhpMailer(): PHPMailer
    {
        $m = new PHPMailer(true);

        // Debug SMTP optionnel via SMTP_DEBUG=1|2 (journalisé via error_log)
        $smtpDebug = $this->env('SMTP_DEBUG');
        if ($smtpDebug !== null && (string)$smtpDebug !== '' && (int)$smtpDebug > 0) {
            $m->SMTPDebug = (int) $smtpDebug; // 1 = client, 2 = client+server
            $m->Debugoutput = 'error_log';
        }

        $m->isSMTP();

        // AutoTLS pilotable: par défaut ON (plus robuste avec SendGrid)
        $autoTls = $this->env('SMTP_AUTOTLS');
        if ($autoTls === null) {
            $m->SMTPAutoTLS = true;
        } else {
            $m->SMTPAutoTLS = ((string)$autoTls === '1');
        }

        $m->Host = $this->smtp['host'];
        $m->Port = $this->smtp['port'];

        if (!empty($this->smtp['user'])) {
            $m->SMTPAuth = true;
            $m->Username = $this->smtp['user'];
            $m->Password = $this->smtp['pass'] ?? '';
        }

        $secure = strtolower((string) ($this->smtp['secure'] ?? ''));
        if (in_array($secure, ['tls', 'ssl'], true)) {
            $m->SMTPSecure = $secure;
        }

        // Optionnel: Hostname (Received/Message-ID)
        $hostname = $this->env('MAIL_HOSTNAME');
        if (is_string($hostname) && $hostname !== '') {
            $m->Hostname = $hostname;
        }

        $m->CharSet = 'UTF-8';

        $m->setFrom($this->from, $this->fromName);
        $m->Sender = $this->from;

        $this->configureReplyTo($m);
        // $this->configureDkim($m);
        // $this->configureDeliverabilityHeaders($m);

        return $m;
    }

    // Configure l'adresse Reply-To si définie via MAIL_REPLY_TO
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

    // Configure DKIM si les variables d'environnement sont définies
    private function configureDkim(PHPMailer $m): void
    {
        // Avec SendGrid, DKIM est normalement géré côté SendGrid (Domain Authentication).
        // Donc on le coupe automatiquement, sauf si tu forces DKIM_ENABLE=1.
        $force = (string)($this->env('DKIM_ENABLE') ?? '0');
        $host = (string)($this->smtp['host'] ?? '');
        if ($force !== '1' && stripos($host, 'sendgrid.net') !== false) {
            return;
        }

        $dkimDomain = $this->env('DKIM_DOMAIN');
        $dkimSelector = $this->env('DKIM_SELECTOR');
        $dkimPrivateKey = $this->env('DKIM_PRIVATE_KEY');
        $dkimPassphrase = $this->env('DKIM_PASSPHRASE');

        if (!is_string($dkimDomain) || $dkimDomain === '') return;
        if (!is_string($dkimSelector) || $dkimSelector === '') return;
        if (!is_string($dkimPrivateKey) || $dkimPrivateKey === '') return;

        $keyContent = $dkimPrivateKey;
        if (str_starts_with($dkimPrivateKey, 'file://')) {
            $path = substr($dkimPrivateKey, 7);
            if (is_readable($path)) {
                $keyContent = @file_get_contents($path) ?: $dkimPrivateKey;
            }
        }

        $m->DKIM_domain = $dkimDomain;
        $m->DKIM_selector = $dkimSelector;
        $m->DKIM_private = $keyContent;

        if (is_string($dkimPassphrase) && $dkimPassphrase !== '') {
            $m->DKIM_passphrase = $dkimPassphrase;
        }

        // Identité DKIM alignée sur From
        $m->DKIM_identity = $this->from;
    }

    // Configure les en-têtes pour améliorer la délivrabilité
    private function configureDeliverabilityHeaders(PHPMailer $m): void
    {
        // Ajoute des en-têtes pour améliorer la délivrabilité et la gestion des réponses automatiques
        $m->addCustomHeader('Auto-Submitted', 'auto-generated');
        $m->addCustomHeader('X-Auto-Response-Suppress', 'All');

        $luParts = [];
        $luUrl = $this->env('LIST_UNSUBSCRIBE_URL');
        $luMailto = $this->env('LIST_UNSUBSCRIBE_MAILTO');

        if (is_string($luUrl) && $luUrl !== '') {
            $luParts[] = '<' . $luUrl . '>';
        }
        if (is_string($luMailto) && $luMailto !== '') {
            $luParts[] = '<mailto:' . $luMailto . '>';
        }
        if ($luParts) {
            $m->addCustomHeader('List-Unsubscribe', implode(', ', $luParts));
            $luPost = $this->env('LIST_UNSUBSCRIBE_POST');
            if ((string)$luPost === '1') {
                $m->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            }
        }
    }

    // Configure les paramètres SMTP depuis les variables d'environnement
    private function buildSmtpConfig(): ?array
    {
        $host = $this->env('SMTP_HOST');
        if (!$host) {
            return null;
        }

        return [
            'host' => $host,
            'port' => (int) ($this->env('SMTP_PORT') ?: 587),
            'user' => $this->env('SMTP_USER'),
            'pass' => $this->env('SMTP_PASS'),
            'secure' => (string) ($this->env('SMTP_SECURE') ?? ''),
        ];
    }

    // En-têtes pour la fonction mail() native
    private function buildPhpMailHeaders(): string
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->from . '>',
        ];
        return implode("\r\n", $headers);
    }

    // Détecte l'environnement d'exécution (dev/prod) via APP_ENV ou SITE_URL
    private function detectEnv(): string
    {
        $env = $this->env('APP_ENV');
        if (!$env) {
            $env = (defined('SITE_URL') && str_contains((string)SITE_URL, 'localhost')) ? 'dev' : 'prod';
        }
        return (string) $env;
    }

   // Lecture env robuste: d'abord $_ENV, puis getenv()
    private function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        $v = getenv($key);
        if ($v !== false) {
            return $v;
        }
        return $default;
    }

    // Journalise l'e-mail dans un fichier de log (fallback)
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

    // Retourne le chemin du fichier de log utilisé en fallback
    public function getLogFile(): string
    {
        return $this->logFile;
    }
}
