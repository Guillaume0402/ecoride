<?php

namespace App\Controller;

class ErrorController extends Controller
{
    public function show(string $message = "Une erreur est survenue"): void
    {
        $this->render("errors/default", ['message' => $message]);
    }

    public function show404(string $message = "Page non trouvée"): void
    {
        http_response_code(404);
        $this->render("errors/error-404", ['message' => $message]);
    }

    public function show500(string $message = "Erreur interne du serveur"): void
    {
        http_response_code(500);
        $this->render("errors/error-500", ['message' => $message]);
    }

    public function show405(string $message = "Méthode non autorisée"): void
    {
        http_response_code(405);
        $this->render("errors/error-405", ['message' => $message]);
    }
}
