<?php
// Script para criar a tabela de logs via navegador
require_once __DIR__ . '/config.php';
use App\Core\Database;

try {
    $db = Database::getConnection();
    
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
    
    echo "<h1>Sucesso!</h1>";
    echo "<p>Tabela 'logs' verificada/criada com sucesso.</p>";
    echo "<p><a href='index.php?r=superAdmin/dashboard'>Voltar ao Dashboard</a></p>";

} catch (Exception $e) {
    echo "<h1>Erro</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
