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

        // Fichier de log: utiliser un répertoire toujours accessible (Heroku → /tmp)
        $this->logFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/ecoride-mail.log';
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
                // Pré-déclare $mailer pour l'utiliser en sécurité dans le catch
                $mailer = null;
                $mailer = new PHPMailer(true);
                // Debug SMTP optionnel activable via SMTP_DEBUG=1|2 (journalisé via error_log)
                $smtpDebug = getenv('SMTP_DEBUG') ?: ($_ENV['SMTP_DEBUG'] ?? null);
                if ($smtpDebug !== null && (string)$smtpDebug !== '' && (int)$smtpDebug > 0) {
                    $mailer->SMTPDebug = (int) $smtpDebug; // 1 = client, 2 = client+server
                    $mailer->Debugoutput = 'error_log';
                }
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
                // Envelope-From (Return-Path) – peut être ignoré par le relais SMTP mais utile si supporté
                $mailer->Sender = $this->from;
                // Reply-To optionnel
                $replyTo = getenv('MAIL_REPLY_TO') ?: ($_ENV['MAIL_REPLY_TO'] ?? null);
                if (is_string($replyTo) && $replyTo !== '') {
                    try {
                        $mailer->addReplyTo($replyTo);
                    } catch (\Throwable $e) { /* ignore */
                    }
                }
                // En-têtes utiles pour réduire les auto-réponses et clarifier la nature du message
                $mailer->addCustomHeader('Auto-Submitted', 'auto-generated');
                $mailer->addCustomHeader('X-Auto-Response-Suppress', 'All');
                // List-Unsubscribe (meilleure délivrabilité pour emails non strictement transactionnels)
                $luParts = [];
                $luUrl = getenv('LIST_UNSUBSCRIBE_URL') ?: ($_ENV['LIST_UNSUBSCRIBE_URL'] ?? null);
                $luMailto = getenv('LIST_UNSUBSCRIBE_MAILTO') ?: ($_ENV['LIST_UNSUBSCRIBE_MAILTO'] ?? null);
                if (is_string($luUrl) && $luUrl !== '') {
                    $luParts[] = '<' . $luUrl . '>';
                }
                if (is_string($luMailto) && $luMailto !== '') {
                    $luParts[] = '<mailto:' . $luMailto . '>';
                }
                if ($luParts) {
                    $mailer->addCustomHeader('List-Unsubscribe', implode(', ', $luParts));
                    $luPost = getenv('LIST_UNSUBSCRIBE_POST') ?: ($_ENV['LIST_UNSUBSCRIBE_POST'] ?? null);
                    if ((string)$luPost === '1') {
                        $mailer->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
                    }
                }
                $mailer->addAddress($to);
                $mailer->Subject = $subject;
                $mailer->isHTML(true);
                $mailer->Body = $htmlBody;
                $mailer->AltBody = strip_tags($htmlBody);
                $mailer->send();
                return true;
            } catch (MailException $e) {
                // Essayer de récupérer des informations d'erreur supplémentaires
                $errInfo = '';
                if (($mailer instanceof PHPMailer) && !empty($mailer->ErrorInfo)) {
                    $errInfo = ' ErrorInfo=' . $mailer->ErrorInfo;
                }
                error_log('[Mailer][SMTP] ' . $e->getMessage() . $errInfo);
                // fallback log
                return $this->logFallback($to, $subject, $htmlBody);
            }
        }

        // En dev sans SMTP, on ne tente pas d'envoyer un vrai mail: on journalise directement
        if ($this->appEnv === 'dev') {
            return $this->logFallback($to, $subject, $htmlBody);
        }

        // En prod, si aucun SMTP n'est configuré, éviter mail() (souvent bloqué sur PaaS) → journalisation
        if ($this->appEnv === 'prod') {
            error_log('[Mailer] SMTP non configuré en production – message journalisé dans ' . $this->logFile);
            return $this->logFallback($to, $subject, $htmlBody);
        }

        // En autres cas (sécurité), tenter mail() puis fallback
        $ok = @mail($to, $subject, $htmlBody, $headersStr);
        if ($ok) {
            return true;
        }
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

    /**
     * Expose le fichier de log utilisé (utile pour les diagnostics/tests CLI)
     */
    public function getLogFile(): string
    {
        return $this->logFile;
    }
}
