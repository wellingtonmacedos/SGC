<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

// Temporary Migration Logic
if (isset($_GET['migrate_db'])) {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        // Courses Limit
        $stmt = $pdo->query("SHOW COLUMNS FROM courses LIKE 'max_enrollments'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE courses ADD COLUMN max_enrollments INT UNSIGNED DEFAULT 0 AFTER allow_enrollment");
            echo "<h1>Sucesso: Coluna 'max_enrollments' criada!</h1>";
        } else {
            echo "<h1>Info: Coluna 'max_enrollments' já existe.</h1>";
        }

        // Users Updates
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'username'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) DEFAULT NULL AFTER email");
            $pdo->exec("ALTER TABLE users ADD UNIQUE KEY uniq_users_username (username)");
            echo "<h1>Sucesso: Coluna 'username' criada!</h1>";
        }
        
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER cpf");
            echo "<h1>Sucesso: Coluna 'phone' criada!</h1>";
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'address'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL AFTER phone");
            echo "<h1>Sucesso: Coluna 'address' criada!</h1>";
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'photo'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE users ADD COLUMN photo VARCHAR(255) DEFAULT NULL AFTER address");
            echo "<h1>Sucesso: Coluna 'photo' criada!</h1>";
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'force_password_change'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE users ADD COLUMN force_password_change TINYINT(1) DEFAULT 0");
            echo "<h1>Sucesso: Coluna 'force_password_change' criada!</h1>";
        }

        // Organization Settings Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS organization_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            organization_name VARCHAR(255) NOT NULL,
            cnpj VARCHAR(20) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            address TEXT DEFAULT NULL,
            city VARCHAR(100) DEFAULT NULL,
            state VARCHAR(2) DEFAULT NULL,
            zip_code VARCHAR(10) DEFAULT NULL,
            logo VARCHAR(255) DEFAULT NULL,
            institutional_text TEXT DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Insert default record if not exists
        $stmt = $pdo->query("SELECT COUNT(*) FROM organization_settings");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO organization_settings (organization_name) VALUES ('Nome da Instituição')");
            echo "<h1>Sucesso: Tabela 'organization_settings' criada e inicializada!</h1>";
        }

        echo '<p><a href="index.php?r=auth/register">Ir para Cadastro</a></p>';
        exit;
    } catch (Exception $e) {
        die("Erro: " . $e->getMessage());
    }
}

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

