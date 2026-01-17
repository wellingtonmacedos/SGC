<?php
declare(strict_types=1);

session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'sgc');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_URL', '');

define('MAIL_FROM_ADDRESS', 'no-reply@exemplo.com');
define('MAIL_FROM_NAME', 'SGC');
define('MAIL_ADMIN_ADDRESS', 'admin@exemplo.com');

define('CERTIFICATE_PATH', __DIR__ . '/storage/certificates');
define('CERTIFICATE_MAX_SIZE', 5 * 1024 * 1024);

define('COURSE_COVER_PATH', __DIR__ . '/storage/covers');
define('COURSE_COVER_MAX_SIZE', 2 * 1024 * 1024);

require __DIR__ . '/app/core/Autoloader.php';
App\Core\Autoloader::register();
App\Core\Auth::ensureAdminUserExists();

