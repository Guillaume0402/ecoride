<?php

namespace App\Service;

class Mailer
{
    private string $from;
    private string $fromName;
    private string $logFile;

    public function __construct(?string $from = null, ?string $fromName = null)
    {
        $this->from = $from ?: (getenv('MAIL_FROM') ?: 'no-reply@localhost');
        $this->fromName = $fromName ?: (getenv('MAIL_FROM_NAME') ?: 'EcoRide');
        $this->logFile = APP_ROOT . '/mail.log';
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

        // Tentative d’envoi via mail()
        $ok = @mail($to, $subject, $htmlBody, $headersStr);
        if ($ok) {
            return true;
        }
        // Fallback: journaliser le message pour inspection en dev
        $entry = sprintf("[%s] TO:%s SUBJECT:%s\n%s\n\n", date('Y-m-d H:i:s'), $to, $subject, $htmlBody);
        try {
            file_put_contents($this->logFile, $entry, FILE_APPEND);
            return true;
        } catch (\Throwable $e) {
            error_log('[Mailer] fallback log failed: ' . $e->getMessage());
            return false;
        }
    }
}
