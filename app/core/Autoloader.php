<?php
declare(strict_types=1);

namespace App\Core;

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([static::class, 'autoload']);
    }

    public static function autoload(string $class): void
    {
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/../';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        
        // Ajuste para pastas minúsculas em servidor Linux
        $parts = explode('\\', $relativeClass);
        // Transforma pastas em minúsculas (Controllers -> controllers)
        // Mantém o nome do arquivo original (HomeController -> HomeController)
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $parts[$i] = strtolower($parts[$i]);
        }
        
        $file = $baseDir . implode('/', $parts) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    }
}
