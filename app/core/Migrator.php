<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Migrator
{
    private PDO $db;

    public function __construct()
    {
        $host = DB_HOST;
        $name = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;
        
        $this->db = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $this->init();
    }

    private function init(): void
    {
        // Ensure migrations table exists
        $this->db->exec("CREATE TABLE IF NOT EXISTS `migrations` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `migration` varchar(255) NOT NULL,
            `batch` int(11) NOT NULL,
            `executed_at` datetime NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Ensure settings table exists
        $this->db->exec("CREATE TABLE IF NOT EXISTS `settings` (
            `key` varchar(50) NOT NULL,
            `value` text,
            PRIMARY KEY (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Init version
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM settings WHERE `key` = 'system_version'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $stmt = $this->db->prepare("INSERT INTO settings (`key`, `value`) VALUES ('system_version', '1.0.0')");
            $stmt->execute();
        }
    }

    public function getAppliedMigrations(): array
    {
        $stmt = $this->db->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function runMigrations(string $path): array
    {
        $applied = $this->getAppliedMigrations();
        $files = scandir($path);
        $batch = $this->getNextBatch();
        $executed = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'sql') continue;

            if (!in_array($file, $applied)) {
                // Run migration
                $sql = file_get_contents($path . '/' . $file);
                try {
                    // Start transaction if possible
                    try {
                        $this->db->beginTransaction();
                    } catch (\Exception $e) {
                        // Ignore if transaction already active or not supported
                    }

                    $this->db->exec($sql);
                    
                    $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch, executed_at) VALUES (:migration, :batch, NOW())");
                    $stmt->execute(['migration' => $file, 'batch' => $batch]);
                    
                    if ($this->db->inTransaction()) {
                        $this->db->commit();
                    }
                    $executed[] = $file;
                } catch (\Exception $e) {
                    // Handle "no active transaction" caused by MySQL DDL implicit commits
                    $msg = $e->getMessage();
                    if (strpos($msg, 'no active transaction') !== false || strpos($msg, 'There is no active transaction') !== false) {
                         $executed[] = $file;
                    } else {
                        if ($this->db->inTransaction()) {
                            try {
                                $this->db->rollBack();
                            } catch (\Exception $rollbackEx) {
                                // Ignore rollback error
                            }
                        }
                        throw $e;
                    }
                }
            }
        }
        
        return $executed;
    }

    private function getNextBatch(): int
    {
        $stmt = $this->db->query("SELECT MAX(batch) FROM migrations");
        return (int)$stmt->fetchColumn() + 1;
    }
}
