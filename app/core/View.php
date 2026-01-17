<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $view, array $params = []): void
    {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'View não encontrada';
            return;
        }

        extract($params);
        require __DIR__ . '/../views/layouts/main.php';
    }
}

