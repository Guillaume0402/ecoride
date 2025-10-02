<?php

namespace App\Controller;

use App\Service\Flash;

class MailController extends Controller
{
    public function testForm(): void
    {
        // Restriction légère: visible uniquement en dev
        $appEnv = $_ENV['APP_ENV'] ?? 'prod';
        if ($appEnv !== 'dev') {
            abort(404);
        }

        $this->render('pages/mail-test', [
            'pageTitle' => 'Test e-mail',
            'metaDescription' => 'Formulaire de test pour l’envoi d’e-mails (dev)' .
                ' — utilise la configuration SMTP/DKIM de votre .env.local.',
        ]);
    }

    public function sendTest(): void
    {
        $appEnv = $_ENV['APP_ENV'] ?? 'prod';
        if ($appEnv !== 'dev') {
            abort(404);
        }

        $to = isset($_POST['to']) ? trim((string)$_POST['to']) : '';
        $subject = isset($_POST['subject']) ? trim((string)$_POST['subject']) : 'EcoRide: Test SMTP';
        $body = isset($_POST['body']) && $_POST['body'] !== ''
            ? (string) $_POST['body']
            : '<p>Ceci est un e‑mail de test envoyé par EcoRide (dev).</p><p>Date: ' . date('Y-m-d H:i:s') . '</p>';

        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Flash::add('Adresse e‑mail invalide.', 'danger');
            redirect('/mail/test');
            return;
        }

        try {
            $mailer = new \App\Service\Mailer();
            $ok = $mailer->send($to, $subject, $body);
            if ($ok) {
                $msg = 'E‑mail de test traité. ';
                if (method_exists($mailer, 'getLogFile')) {
                    $msg .= 'Log: ' . $mailer->getLogFile();
                }
                Flash::add($msg, 'success');
            } else {
                Flash::add('Échec de l’envoi (voir logs).', 'danger');
            }
        } catch (\Throwable $e) {
            error_log('[MailController] sendTest failed: ' . $e->getMessage());
            Flash::add('Erreur: ' . $e->getMessage(), 'danger');
        }

        redirect('/mail/test');
    }
}
