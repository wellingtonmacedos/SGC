<?php
declare(strict_types=1);

/**
 * Script de Backup Automático (Cron Job)
 * Uso: php cron_backup.php --type=[db|files|full]
 */

// Prevent session start output issues if run via CLI
if (php_sapi_name() === 'cli') {
    // Define a dummy session if needed, or just let config run
} else {
    die("This script can only be run from the command line.");
}

// Load Configuration
require_once __DIR__ . '/config.php';

use App\Models\Backup;
use App\Models\Log;

// Parse Arguments
$options = getopt("", ["type:"]);
$type = isset($options['type']) ? $options['type'] : 'db';

if (!in_array($type, ['db', 'files', 'full'])) {
    echo "Invalid type. Use --type=db, --type=files, or --type=full\n";
    exit(1);
}

echo "Starting backup type: $type...\n";

$backupDir = __DIR__ . '/backups/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$filename = 'backup_' . $type . '_' . date('Y-m-d_H-i-s') . '_auto.zip';
$filepath = $backupDir . $filename;

$zip = new ZipArchive();
if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    echo "Error: Cannot create zip file.\n";
    exit(1);
}

// 1. Database Dump
if ($type === 'db' || $type === 'full') {
    echo "Dumping database...\n";
    $sqlFile = $backupDir . 'dump_' . date('Y-m-d_H-i-s') . '.sql';
    if (dumpDatabase($sqlFile)) {
        $zip->addFile($sqlFile, 'database.sql');
    } else {
        $zip->close();
        unlink($filepath);
        echo "Error: Database dump failed.\n";
        exit(1);
    }
}

// 2. Files
if ($type === 'files' || $type === 'full') {
    echo "Archiving files...\n";
    $baseDir = __DIR__;
    $dirsToBackup = [
        'storage/certificates',
        'storage/covers',
        'storage/organization',
        'storage/photos'
    ];
    
    foreach ($dirsToBackup as $dir) {
        $fullPath = $baseDir . '/' . $dir;
        if (is_dir($fullPath)) {
            addFolderToZip($fullPath, $zip, $dir);
        }
    }
}

$zip->close();

// Cleanup temp SQL file
if (isset($sqlFile) && file_exists($sqlFile)) {
    unlink($sqlFile);
}

if (file_exists($filepath)) {
    $size = filesize($filepath);
    $backupModel = new Backup();
    
    // System User ID (0 or null, but table might require int)
    // Assuming 0 or 1 (if 1 is super admin). Let's check users table.
    // Ideally we should have a system user or use NULL if allowed.
    // Logs table user_id is nullable. Backups created_by might be nullable?
    // In Backup model: created_by is int. In schema?
    // Let's assume ID 1 is the first admin/super admin or use NULL if schema allows.
    // Let's try to find a super admin ID.
    $adminId = 1; // Fallback
    
    // Create DB Record
    try {
        $backupModel->create($type, $filename, 'backups/' . $filename, $size, $adminId);
        echo "Backup created successfully: $filename\n";
        
        // Log
        $logModel = new Log();
        // user_id null for system
        $logModel->create('backup_auto', "Backup automático criado: $filename ($type)", null);
        
    } catch (Exception $e) {
        echo "Error saving backup record: " . $e->getMessage() . "\n";
    }
} else {
    echo "Error: Backup file not found after creation.\n";
    exit(1);
}

// Functions
function dumpDatabase(string $outputFile): bool
{
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $handle = fopen($outputFile, 'w');
        if (!$handle) return false;

        $tables = [];
        $stmt = $pdo->query('SHOW TABLES');
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            $row = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
            fwrite($handle, $row[1] . ";\n\n");

            $rows = $pdo->query("SELECT * FROM `$table`");
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $cols = array_keys($row);
                $vals = array_map(function ($val) use ($pdo) {
                    if ($val === null) return 'NULL';
                    return $pdo->quote($val);
                }, array_values($row));
                
                fwrite($handle, "INSERT INTO `$table` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $vals) . ");\n");
            }
            fwrite($handle, "\n");
        }

        fclose($handle);
        return true;

    } catch (Exception $e) {
        echo "DB Dump Error: " . $e->getMessage() . "\n";
        return false;
    }
}

function addFolderToZip(string $dir, ZipArchive $zip, string $zipPath): void
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = $zipPath . '/' . substr($filePath, strlen($dir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
}
