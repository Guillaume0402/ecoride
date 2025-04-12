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
            echo "Vue '{$viewName}' introuvable.";
            return;
        }

        extract($data);

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

