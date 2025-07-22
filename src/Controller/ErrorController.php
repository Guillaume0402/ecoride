<?php

namespace App\Controller;

class ErrorController extends Controller
{
    public function show(string $errorMessage): void
    {
        // Rendu du template d'erreur avec le message
        $this->render("errors/default", [
            'message' => $errorMessage  // Changé de 'errorMessage' vers 'message'
        ]);
    }

    public function show404(string $message = "Page non trouvée"): void
    {
        http_response_code(404);
        $this->render("errors/error-404", [
            'message' => $message
        ]);
    }

    public function show500(string $message = "Erreur interne du serveur"): void
    {
        http_response_code(500);
        $this->render("errors/error-500", [
            'message' => $message
        ]);
    }
}