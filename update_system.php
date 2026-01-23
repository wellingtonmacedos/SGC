<?php
declare(strict_types=1);

/**
 * Script de Atualização Controlada
 * Executa backups, migrações e atualizações de versão.
 */

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

require_once __DIR__ . '/config.php';

use App\Core\Migrator;
use App\Models\Log;
use App\Models\Backup;

echo "Iniciando atualização do sistema...\n";

// 1. Backup Automático
echo "Executando backup pré-atualização...\n";
// Reuse cron_backup logic or call it
// To allow reuse, we should have extracted it.
// For now, let's call the php script if possible, or duplicate logic.
// Duplicating logic for safety and independence.

$backupDir = __DIR__ . '/backups/';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

$filename = 'backup_full_pre_update_' . date('Y-m-d_H-i-s') . '.zip';
$filepath = $backupDir . $filename;
$zip = new ZipArchive();

if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    // DB Dump
    $sqlFile = $backupDir . 'dump_temp.sql';
    if (dumpDatabase($sqlFile)) {
        $zip->addFile($sqlFile, 'database.sql');
    }
    
    // Files
    $dirsToBackup = ['storage/certificates', 'storage/covers', 'storage/organization', 'storage/photos'];
    foreach ($dirsToBackup as $dir) {
        if (is_dir(__DIR__ . '/' . $dir)) {
            addFolderToZip(__DIR__ . '/' . $dir, $zip, $dir);
        }
    }
    
    $zip->close();
    if (file_exists($sqlFile)) unlink($sqlFile);
    
    // Register Backup
    $backupModel = new Backup();
    $backupModel->create('full', $filename, 'backups/' . $filename, filesize($filepath), 1); // 1 = System/Admin
    echo "Backup criado: $filename\n";
} else {
    echo "Erro ao criar backup. Abortando atualização.\n";
    exit(1);
}

// 2. Migrações
echo "Verificando migrações...\n";
try {
    $migrator = new Migrator();
    $executed = $migrator->runMigrations(__DIR__ . '/database/migrations');
    
    if (count($executed) > 0) {
        echo "Migrações executadas:\n";
        foreach ($executed as $mig) {
            echo "- $mig\n";
        }
    } else {
        echo "Nenhuma nova migração encontrada.\n";
    }
} catch (Exception $e) {
    echo "Erro na migração: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Atualizar Versão
$versionFile = __DIR__ . '/version.txt';
if (file_exists($versionFile)) {
    $newVersion = trim(file_get_contents($versionFile));
    
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $stmt = $pdo->prepare("UPDATE settings SET value = :version WHERE `key` = 'system_version'");
    $stmt->execute(['version' => $newVersion]);
    
    echo "Versão do sistema atualizada para: $newVersion\n";
}

// 4. Limpeza de Cache (Placeholder)
echo "Limpando cache (se aplicável)...\n";
// Implement cache clearing if specific cache system is added.

// 5. Log
$logModel = new Log();
$logModel->create('system_update', "Sistema atualizado para versão " . ($newVersion ?? 'unknown'), null);

echo "Atualização concluída com sucesso!\n";


// --- Helpers (Duplicated for standalone execution) ---

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
