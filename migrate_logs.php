<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    echo "Iniciando migração da tabela de logs...\n";

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

    $db->exec($sql);
    echo "Tabela 'logs' criada ou já existente.\n";

    echo "Migração concluída com sucesso!\n";

} catch (PDOException $e) {
    die("Erro na migração: " . $e->getMessage() . "\n");
}
