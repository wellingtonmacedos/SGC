<?php
require_once 'config.php';
require_once 'app/core/Database.php';

use App\Core\Database;

$db = Database::getConnection();

echo "Migrating login settings...\n";

try {
    // Add columns if they don't exist
    $columns = [
        'login_title' => "VARCHAR(255) DEFAULT 'Bem-vindo ao SGC'",
        'login_subtitle' => "VARCHAR(255) DEFAULT 'Faça login para continuar'",
        'login_primary_color' => "VARCHAR(7) DEFAULT '#0d1b2a'",
        'login_background_color' => "VARCHAR(7) DEFAULT '#0d1b2a'",
        'login_background_image' => "VARCHAR(255) DEFAULT NULL",
        'login_icon' => "VARCHAR(50) DEFAULT 'fas fa-graduation-cap'",
        'login_logo' => "VARCHAR(255) DEFAULT NULL" // Path to custom logo image on login screen
    ];

    foreach ($columns as $col => $def) {
        try {
            $db->exec("ALTER TABLE organization_settings ADD COLUMN $col $def");
            echo "Added column $col\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "Column $col already exists\n";
            } else {
                throw $e;
            }
        }
    }

    echo "Migration completed successfully.\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
