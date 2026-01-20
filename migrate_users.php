<?php
require_once __DIR__ . '/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Iniciando migração de tabela de usuários...\n";

    // 1. Check and Add 'username'
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'username'");
    if (!$stmt->fetch()) {
        echo "Adicionando coluna 'username'...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) DEFAULT NULL AFTER email");
        $pdo->exec("ALTER TABLE users ADD UNIQUE KEY uniq_users_username (username)");
        echo "Coluna 'username' adicionada.\n";
    }

    // 2. Check and Add 'phone'
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
    if (!$stmt->fetch()) {
        echo "Adicionando coluna 'phone'...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER cpf");
        echo "Coluna 'phone' adicionada.\n";
    }

    // 3. Check and Add 'address'
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'address'");
    if (!$stmt->fetch()) {
        echo "Adicionando coluna 'address'...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL AFTER phone");
        echo "Coluna 'address' adicionada.\n";
    }

    // 4. Check and Add 'photo'
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'photo'");
    if (!$stmt->fetch()) {
        echo "Adicionando coluna 'photo'...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN photo VARCHAR(255) DEFAULT NULL AFTER address");
        echo "Coluna 'photo' adicionada.\n";
    }

    echo "Migração concluída com sucesso!\n";

} catch (PDOException $e) {
    echo "Erro na migração: " . $e->getMessage() . "\n";
    exit(1);
}
