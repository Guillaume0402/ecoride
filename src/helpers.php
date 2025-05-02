<?php
use App\Router;

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return rtrim(Router::$basePath, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('view')) {
    function view(string $viewName, array $data = []): void {
        $viewPath = __DIR__ . "/view/{$viewName}.php";
        if (!file_exists($viewPath)) {
            http_response_code(500);
            // En production, ne rien afficher ici :
            // echo "Vue '{$viewName}' introuvable.";
            // À la place, charge une page d'erreur :
            require __DIR__ . '/view/error-500.php'; // (optionnel)
            return;
        }
        

        extract($data);
        unset($message); // ← évite qu’il traîne dans le layout
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require __DIR__ . '/view/layout.php';
    }
}
if (!function_exists('asset')) {
    function asset(string $path): string {
        return url('assets/' . ltrim($path, '/'));
    }
}

