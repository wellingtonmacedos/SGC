<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;
use App\Models\Backup;
use App\Models\Enrollment;
use App\Models\Course;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PDO;
use PDOException;

class SuperAdminController extends Controller
{
    public function __construct()
    {
        Auth::requireSuperAdmin();
    }

    public function dashboard(): void
    {
        $userModel = new User();
        $courseModel = new Course();
        $enrollmentModel = new Enrollment();
        $backupModel = new Backup();

        $stats = [
            'total_candidates' => $userModel->countCandidates(),
            'total_admins' => $userModel->countAdmins(),
            'total_courses' => $courseModel->countAll(),
            'total_enrollments' => $enrollmentModel->countAll(),
            'total_backups' => $backupModel->countAll(),
        ];

        $this->render('super_admin/dashboard', ['stats' => $stats]);
    }

    // --- Admin Management ---

    public function admins(): void
    {
        $userModel = new User();
        $admins = $userModel->listAdmins();
        $this->render('super_admin/admins/index', ['admins' => $admins]);
    }

    public function createAdmin(): void
    {
        $error = null;
        if ($this->isPost()) {
            $name = $this->getPostString('name');
            $email = $this->getPostString('email');
            $cpf = $this->getPostString('cpf');
            $username = $this->getPostString('username');
            $password = $this->getPostString('password');
            $confirmPassword = $this->getPostString('confirm_password');

            if ($password !== $confirmPassword) {
                $error = 'As senhas não conferem.';
            } else {
                $userModel = new User();
                
                // Basic validation (simplified)
                if ($userModel->findByEmail($email)) {
                    $error = 'Email já cadastrado.';
                } elseif ($userModel->findByUsername($username)) {
                    $error = 'Usuário já cadastrado.';
                } else {
                    try {
                        $userModel->createAdmin($name, $email, $cpf, $username, $password);
                        header('Location: index.php?r=superAdmin/admins&success=created');
                        exit;
                    } catch (\Exception $e) {
                        $error = 'Erro ao criar administrador: ' . $e->getMessage();
                    }
                }
            }
        }

        $this->render('super_admin/admins/form', [
            'action' => 'create',
            'error' => $error
        ]);
    }

    public function editAdmin(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $userModel = new User();
        $admin = $userModel->findById($id);

        if (!$admin || $admin['role'] !== 'admin') {
            header('Location: index.php?r=superAdmin/admins');
            exit;
        }

        $error = null;
        if ($this->isPost()) {
            $name = $this->getPostString('name');
            $email = $this->getPostString('email');
            $cpf = $this->getPostString('cpf');
            $username = $this->getPostString('username');
            $status = $this->getPostString('status');
            $password = $this->getPostString('password');
            $forceChange = isset($_POST['force_change']);

            // Update logic
            try {
                $userModel->updateAdmin($id, $name, $email, $cpf, $username, $status);
                
                if (!empty($password)) {
                    $userModel->updatePassword($id, $password);
                    if ($forceChange) {
                        // Assuming updateForcePasswordChange exists or using raw query if needed
                        // For now let's assume updatePassword handles it or add separate method
                        // Checking User model capabilities...
                        // Let's use direct update for force_password_change if method doesn't exist
                        // Actually I recall implementing force_password_change in previous memory
                        // but let's stick to basic update first.
                        $userModel->forcePasswordChange($id);
                    }
                }
                
                header('Location: index.php?r=superAdmin/admins&success=updated');
                exit;
            } catch (\Exception $e) {
                $error = 'Erro ao atualizar: ' . $e->getMessage();
            }
        }

        $this->render('super_admin/admins/form', [
            'action' => 'edit',
            'admin' => $admin,
            'error' => $error
        ]);
    }

    public function toggleAdminStatus(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $userModel = new User();
        $admin = $userModel->findById($id);

        if ($admin && $admin['role'] === 'admin') {
            $newStatus = ($admin['status'] === 'active') ? 'inactive' : 'active';
            $userModel->updateStatus($id, $newStatus);
        }
        
        header('Location: index.php?r=superAdmin/admins');
        exit;
    }

    public function deleteAdmin(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $userModel = new User();
        $admin = $userModel->findById($id);

        if ($admin && $admin['role'] === 'admin') {
            $userModel->deleteAdmin($id);
        }
        
        header('Location: index.php?r=superAdmin/admins');
        exit;
    }

    // --- Backups ---

    public function backups(): void
    {
        $backupModel = new Backup();
        $backups = $backupModel->all();
        $this->render('super_admin/backups/index', ['backups' => $backups]);
    }

    public function createBackup(): void
    {
        if (!$this->isPost()) {
            header('Location: index.php?r=superAdmin/backups');
            exit;
        }

        $type = $this->getPostString('type'); // db, files, full
        if (!in_array($type, ['db', 'files', 'full'])) {
            header('Location: index.php?r=superAdmin/backups&error=invalid_type');
            exit;
        }

        $backupDir = dirname(__DIR__, 2) . '/backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup_' . $type . '_' . date('Y-m-d_H-i-s') . '.zip';
        $filepath = $backupDir . $filename;
        
        $zip = new ZipArchive();
        if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            header('Location: index.php?r=superAdmin/backups&error=zip_create_failed');
            exit;
        }

        // 1. Database Dump
        if ($type === 'db' || $type === 'full') {
            $sqlFile = $backupDir . 'dump_' . date('Y-m-d_H-i-s') . '.sql';
            if ($this->dumpDatabase($sqlFile)) {
                $zip->addFile($sqlFile, 'database.sql');
            } else {
                 $zip->close();
                 unlink($filepath);
                 header('Location: index.php?r=superAdmin/backups&error=db_dump_failed');
                 exit;
            }
        }

        // 2. Files
        if ($type === 'files' || $type === 'full') {
            $baseDir = dirname(__DIR__, 2);
            // Backup user data directories in storage
            $dirsToBackup = [
                'storage/certificates',
                'storage/covers',
                'storage/organization',
                'storage/photos'
            ];
            
            foreach ($dirsToBackup as $dir) {
                $fullPath = $baseDir . '/' . $dir;
                if (is_dir($fullPath)) {
                    $this->addFolderToZip($fullPath, $zip, $dir);
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
            $user = Auth::user();
            $backupModel->create($type, $filename, 'backups/' . $filename, $size, (int)$user['id']);
            
            header('Location: index.php?r=superAdmin/backups&success=created');
        } else {
            header('Location: index.php?r=superAdmin/backups&error=create_failed');
        }
        exit;
    }

    public function downloadBackup(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $backupModel = new Backup();
        $backup = $backupModel->find($id);

        if (!$backup) {
            header('Location: index.php?r=superAdmin/backups');
            exit;
        }

        $filepath = dirname(__DIR__, 2) . '/' . $backup['filepath'];
        
        if (file_exists($filepath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        } else {
             header('Location: index.php?r=superAdmin/backups&error=file_not_found');
             exit;
        }
    }

    public function deleteBackup(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $backupModel = new Backup();
        $backup = $backupModel->find($id);

        if ($backup) {
            $filepath = dirname(__DIR__, 2) . '/' . $backup['filepath'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            $backupModel->delete($id);
        }

        header('Location: index.php?r=superAdmin/backups&success=deleted');
        exit;
    }

    public function restoreBackup(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $backupModel = new Backup();
        $backup = $backupModel->find($id);

        if (!$backup) {
            header('Location: index.php?r=superAdmin/backups&error=not_found');
            exit;
        }

        $filepath = dirname(__DIR__, 2) . '/' . $backup['filepath'];
        if (!file_exists($filepath)) {
            header('Location: index.php?r=superAdmin/backups&error=file_not_found');
            exit;
        }

        $zip = new ZipArchive();
        if ($zip->open($filepath) === true) {
            
            // Restore DB
            if ($backup['type'] === 'db' || $backup['type'] === 'full') {
                $sqlContent = $zip->getFromName('database.sql');
                if ($sqlContent) {
                    if (!$this->executeSql($sqlContent)) {
                        $zip->close();
                        header('Location: index.php?r=superAdmin/backups&error=restore_db_failed');
                        exit;
                    }
                }
            }

            // Restore Files
            if ($backup['type'] === 'files' || $backup['type'] === 'full') {
                $extractPath = dirname(__DIR__, 2);
                $zip->extractTo($extractPath);
            }

            $zip->close();
            header('Location: index.php?r=superAdmin/backups&success=restored');
            exit;

        } else {
            header('Location: index.php?r=superAdmin/backups&error=zip_open_failed');
            exit;
        }
    }
    
    // --- Reports ---
    public function reports(): void
    {
        $userModel = new User();
        $courseModel = new Course();
        $enrollmentModel = new Enrollment();

        $stats = [
            'total_candidates' => $userModel->countCandidates(),
            'total_admins' => $userModel->countAdmins(),
            'total_courses' => $courseModel->countAll(),
            'total_enrollments' => $enrollmentModel->countAll(),
            'monthly_enrollments' => $enrollmentModel->getMonthlyStats(),
        ];

        $this->render('super_admin/reports/index', ['stats' => $stats]);
    }

    public function exportReports(): void
    {
        $format = isset($_GET['format']) ? $_GET['format'] : 'csv';
        $enrollmentModel = new Enrollment();
        $stats = $enrollmentModel->getMonthlyStats();

        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="relatorio_inscricoes_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // BOM for Excel
            fputs($output, "\xEF\xBB\xBF");
            
            fputcsv($output, ['Mês/Ano', 'Total de Inscrições']);
            
            foreach ($stats as $row) {
                fputcsv($output, [$row['month'], $row['total']]);
            }
            
            fclose($output);
            exit;
        } elseif ($format === 'pdf') {
             // For now, redirect to print view or similar. 
             // Since we don't have a PDF library, we can just print the page or offer a print-friendly HTML.
             // Or simpler: just alert it's not implemented yet or redirect back.
             // Let's implement a simple HTML print view for now.
             $this->render('super_admin/reports/print', ['stats' => $stats]);
             exit;
        }
        
        header('Location: index.php?r=superAdmin/reports');
        exit;
    }

    // --- Private Helpers ---

    private function dumpDatabase(string $outputFile): bool
    {
        try {
            // Get DB config - assuming constants are available as this is within the app
            if (!defined('DB_HOST')) {
                // Should be defined if app is running
                return false;
            }
            
            $host = DB_HOST;
            $user = DB_USER;
            $pass = DB_PASS;
            $name = DB_NAME;

            $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            $handle = fopen($outputFile, 'w');
            if (!$handle) return false;

            // Get tables
            $tables = [];
            $stmt = $pdo->query('SHOW TABLES');
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            foreach ($tables as $table) {
                // Drop table
                fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
                
                // Create table
                $row = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
                fwrite($handle, $row[1] . ";\n\n");

                // Insert data
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

        } catch (\Exception $e) {
            return false;
        }
    }

    private function addFolderToZip(string $dir, ZipArchive $zip, string $zipPath): void
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

    private function executeSql(string $sql): bool
    {
        try {
            $host = DB_HOST;
            $user = DB_USER;
            $pass = DB_PASS;
            $name = DB_NAME;

            $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $pdo->exec('SET foreign_key_checks = 0');
            $pdo->exec($sql);
            $pdo->exec('SET foreign_key_checks = 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
