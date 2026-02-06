<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

// Route dispatcher logic continues below...

use App\Core\Auth;

$route = isset($_GET['r']) ? trim((string)$_GET['r'], '/') : '';

if ($route === '') {
    $user = Auth::user();
    if ($user && $user['role'] === 'admin') {
        $route = 'admin/dashboard';
    } elseif ($user) {
        $route = 'candidate/dashboard';
    } else {
        $route = 'auth/login';
    }
}

[$controllerName, $action] = array_pad(explode('/', $route, 2), 2, 'index');

$controllerClass = 'App\\Controllers\\' . ucfirst($controllerName) . 'Controller';
$actionMethod = str_replace('-', '', $action);

if (!class_exists($controllerClass)) {
    http_response_code(404);
    echo 'Página não encontrada';
    exit;
}

$controller = new $controllerClass();

if (!method_exists($controller, $actionMethod)) {
    http_response_code(404);
    echo 'Página não encontrada';
    exit;
}

call_user_func([$controller, $actionMethod]);

