<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;
use App\Models\Backup;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Log;
use App\Models\Organization;
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
                        $newId = $userModel->createAdmin($name, $email, $cpf, $username, $password);
                        
                        $logModel = new Log();
                        $logModel->create('admin_created', "Novo administrador criado: $username ($email)", (int)Auth::user()['id']);
                        
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
                
                $logDetails = "Admin atualizado: $username (ID: $id)";
                if (!empty($password)) {
                    $userModel->updatePassword($id, $password);
                    $logDetails .= " - Senha alterada";
                    if ($forceChange) {
                        $userModel->forcePasswordChange($id);
                        $logDetails .= " (Forçar troca)";
                    }
                }
                
                $logModel = new Log();
                $logModel->create('admin_updated', $logDetails, (int)Auth::user()['id']);
                
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
            
            $logModel = new Log();
            $logModel->create('admin_status_changed', "Status do admin ID $id alterado para $newStatus", (int)Auth::user()['id']);
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
            
            $logModel = new Log();
            $logModel->create('admin_deleted', "Admin removido: {$admin['username']} (ID: $id)", (int)Auth::user()['id']);
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
            
            $logModel = new Log();
            $logModel->create('backup_created', "Backup criado: $filename ($type)", (int)$user['id']);

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
            
            $logModel = new Log();
            $logModel->create('backup_deleted', "Backup removido: {$backup['filename']} (ID: $id)", (int)Auth::user()['id']);
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
            
            $logModel = new Log();
            $logModel->create('backup_restored', "Backup restaurado: {$backup['filename']} ({$backup['type']})", (int)Auth::user()['id']);
            
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

        // Filters
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
        $courseId = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : null;
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;

        $stats = [
            'total_candidates' => $userModel->countCandidates(),
            'total_admins' => $userModel->countAdmins(),
            'total_courses' => $courseModel->countAll(),
            'active_courses' => $courseModel->countByStatus('active'),
            'ended_courses' => $courseModel->countByStatus('inactive'),
            'total_enrollments' => $enrollmentModel->countAll(),
            'monthly_enrollments' => $enrollmentModel->getMonthlyStats(),
        ];
        
        // Filtered Report Data
        $reportData = $enrollmentModel->getReportStats($startDate, $endDate, $courseId, $status);
        $courses = $courseModel->all();

        $this->render('super_admin/reports/index', [
            'stats' => $stats,
            'reportData' => $reportData,
            'courses' => $courses,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'course_id' => $courseId,
                'status' => $status
            ]
        ]);
    }

    public function exportReports(): void
    {
        $format = isset($_GET['format']) ? $_GET['format'] : 'csv';
        $enrollmentModel = new Enrollment();
        $courseModel = new Course();
        
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
        $courseId = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : null;
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
        
        $data = $enrollmentModel->getReportStats($startDate, $endDate, $courseId, $status);

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="relatorio_inscricoes_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF"); // BOM
            
            // Header
            fputcsv($output, ['Relatório de Inscrições'], ';');
            fputcsv($output, ["Período: $startDate a $endDate"], ';');
            fputcsv($output, [], ';');
            
            fputcsv($output, ['ID', 'Curso', 'Candidato', 'Email', 'CPF', 'Status', 'Data Inscrição'], ';');
            
            foreach ($data as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['course_name'],
                    $row['user_name'],
                    $row['email'],
                    $row['cpf'],
                    $row['status'],
                    $row['created_at']
                ], ';');
            }
            
            fclose($output);
            exit;
        } elseif ($format === 'pdf') {
             $this->render('super_admin/reports/print', [
                 'data' => $data,
                 'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                 ]
             ]);
             exit;
        }
        
        header('Location: index.php?r=superAdmin/reports');
        exit;
    }

    // --- Logs ---
    public function logs(): void
    {
        $logModel = new Log();
        $logs = $logModel->getLatest(100);
        $this->render('super_admin/logs/index', ['logs' => $logs]);
    }

    // --- Updates ---
    public function updates(): void
    {
        $version = 'Desconhecida';
        $versionFile = dirname(__DIR__, 2) . '/version.txt';
        if (file_exists($versionFile)) {
            $version = trim(file_get_contents($versionFile));
        }
        
        $this->render('super_admin/updates/index', ['version' => $version]);
    }

    // --- Login Settings ---
    public function loginSettings(): void
    {
        $organizationModel = new Organization();
        $settings = $organizationModel->getSettings();
        $error = null;

        if ($this->isPost()) {
            $data = [
                'login_title' => $this->getPostString('login_title'),
                'login_subtitle' => $this->getPostString('login_subtitle'),
                'login_primary_color' => $this->getPostString('login_primary_color'),
                'login_background_color' => $this->getPostString('login_background_color'),
                'login_icon' => $this->getPostString('login_icon'),
            ];

            // Handle Logo Upload
            if (isset($_FILES['login_logo']) && $_FILES['login_logo']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['login_logo'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                    $filename = 'login_logo_' . time() . '.' . $ext;
                    if (!is_dir(ORGANIZATION_LOGO_PATH)) {
                        mkdir(ORGANIZATION_LOGO_PATH, 0755, true);
                    }
                    if (move_uploaded_file($file['tmp_name'], ORGANIZATION_LOGO_PATH . '/' . $filename)) {
                        $data['login_logo'] = $filename;
                        // Remove old logo if exists and different
                        if (!empty($settings['login_logo']) && file_exists(ORGANIZATION_LOGO_PATH . '/' . $settings['login_logo'])) {
                            unlink(ORGANIZATION_LOGO_PATH . '/' . $settings['login_logo']);
                        }
                    } else {
                        $error = 'Erro ao salvar a logo.';
                    }
                } else {
                    $error = 'Formato de logo inválido.';
                }
            }

            // Handle Background Image Upload
            if (isset($_FILES['login_background_image']) && $_FILES['login_background_image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['login_background_image'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $filename = 'login_bg_' . time() . '.' . $ext;
                    if (!is_dir(ORGANIZATION_LOGO_PATH)) {
                        mkdir(ORGANIZATION_LOGO_PATH, 0755, true);
                    }
                    if (move_uploaded_file($file['tmp_name'], ORGANIZATION_LOGO_PATH . '/' . $filename)) {
                        $data['login_background_image'] = $filename;
                        // Remove old background if exists and different
                        if (!empty($settings['login_background_image']) && file_exists(ORGANIZATION_LOGO_PATH . '/' . $settings['login_background_image'])) {
                            unlink(ORGANIZATION_LOGO_PATH . '/' . $settings['login_background_image']);
                        }
                    } else {
                        $error = 'Erro ao salvar a imagem de fundo.';
                    }
                } else {
                    $error = 'Formato de imagem de fundo inválido.';
                }
            }

            if (!$error) {
                try {
                    $organizationModel->updateLoginSettings($data);
                    
                    $logModel = new Log();
                    $logModel->create('settings_update', 'Configurações de login atualizadas', (int)Auth::user()['id']);
                    
                    header('Location: index.php?r=superAdmin/loginSettings&success=1');
                    exit;
                } catch (\Exception $e) {
                    $error = 'Erro ao salvar configurações: ' . $e->getMessage();
                }
            }
            
            // If error, refresh settings with posted data for form repopulation (simplified, just reloading from DB + posted error)
        }

        $this->render('super_admin/login_settings', [
            'settings' => $organizationModel->getSettings(),
            'error' => $error
        ]);
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
