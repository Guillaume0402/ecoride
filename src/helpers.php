<?php
use App\Router;

function url(string $path = ''): string {
    return rtrim(Router::$basePath, '/') . '/' . ltrim($path, '/');
}
