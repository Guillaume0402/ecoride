<?php

namespace App\Controller;

use App\Service\Mailer;
use App\Service\Flash;
use App\Security\Csrf;

class ContactController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function send(): void
    {
        // Sécurité basique
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            abort(405, 'Méthode HTTP non autorisée.');
        }

        if (!Csrf::check($_POST['csrf'] ?? null)) {
            Flash::add('Requête invalide (CSRF).', 'danger');
            redirect('/contact');
        }

        $name    = trim((string)($_POST['name'] ?? ''));
        $email   = trim((string)($_POST['email'] ?? ''));
        $subject = trim((string)($_POST['subject'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($message) < 10) {
            Flash::add('Formulaire invalide (vérifie ton email et ton message).', 'danger');
            redirect('/contact');
        }

        $to = (string)($_ENV['CONTACT_TO'] ?? '');
        if ($to === '') {
            error_log('[contact] CONTACT_TO manquant');
            Flash::add('Configuration email manquante (CONTACT_TO).', 'danger');
            redirect('/contact');
        }

        $mailSubject = 'Contact EcoRide' . ($subject !== '' ? ' — ' . $subject : '');
        $htmlBody = nl2br(htmlspecialchars(
            "Nouveau message depuis le formulaire EcoRide\n\n"
            . "Nom: {$name}\n"
            . "Email: {$email}\n"
            . "Sujet: {$subject}\n\n"
            . $message,
            ENT_QUOTES,
            'UTF-8'
        ));

        try {
            $mailer = new Mailer();
            $mailer->send($to, $mailSubject, $htmlBody);

            Flash::add('Merci, ton message a bien été envoyé. On te répond dès que possible.', 'success');
        } catch (\Throwable $e) {
            error_log('[contact] ' . $e->getMessage());
            Flash::add("Oups, l’envoi a échoué. Réessaie dans quelques minutes.", 'danger');
        }

        redirect('/contact');
    }
}
