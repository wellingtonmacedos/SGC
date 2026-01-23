<?php
// Standalone migration script to avoid config.php side effects
define('DB_HOST', 'localhost');
define('DB_NAME', 'sgc');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    echo "Conectado ao banco de dados.\n";

    // Create logs table
    $sql = "CREATE TABLE IF NOT EXISTS `logs` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `user_id` int(10) unsigned DEFAULT NULL,
      `action` varchar(50) NOT NULL,
      `description` text DEFAULT NULL,
      `ip_address` varchar(45) DEFAULT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `fk_logs_user` (`user_id`),
      KEY `idx_logs_action` (`action`),
      KEY `idx_logs_created_at` (`created_at`),
      CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    echo "Tabela 'logs' criada ou verificada com sucesso.\n";

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage() . "\n");
}
