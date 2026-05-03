<?php
declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function render(string $view, array $params = []): void
    {
        View::render($view, $params);
    }

    protected function redirect(string $route): void
    {
        $url = APP_URL !== '' ? rtrim(APP_URL, '/') . '/index.php?r=' . $route : 'index.php?r=' . $route;
        header('Location: ' . $url);
        exit;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function requireCsrf(): void
    {
        if (!$this->isPost()) {
            return;
        }
        $token = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
        $sessionToken = isset($_SESSION['csrf_token']) ? (string)$_SESSION['csrf_token'] : '';
        if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
            http_response_code(403);
            echo 'Requisição inválida';
            exit;
        }
    }

    protected function getClientIp(): ?string
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($parts[0] ?? '');
            if ($ip !== '') {
                return $ip;
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    protected function getPostString(string $key, string $default = ''): string
    {
        return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
    }

    protected function getPostInt(string $key, int $default = 0): int
    {
        return isset($_POST[$key]) ? (int)$_POST[$key] : $default;
    }
}
