<?php
require_once __DIR__ . '/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Verificando coluna 'max_enrollments' na tabela 'courses'...\n";

    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM courses LIKE 'max_enrollments'");
    $column = $stmt->fetch();

    if (!$column) {
        echo "Adicionando coluna 'max_enrollments'...\n";
        $pdo->exec("ALTER TABLE courses ADD COLUMN max_enrollments INT UNSIGNED DEFAULT 0 AFTER allow_enrollment");
        echo "Coluna adicionada com sucesso!\n";
    } else {
        echo "A coluna 'max_enrollments' já existe.\n";
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
