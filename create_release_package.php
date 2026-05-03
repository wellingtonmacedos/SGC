<?php
/**
 * Script auxiliar para gerar pacote de atualização (Release Package)
 * Uso: php create_release_package.php
 */

$filename = "release_update_" . date('Ymd_Hi') . ".zip";
$zip = new ZipArchive();

if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("Erro: Não foi possível criar o arquivo <$filename>\n");
}

echo "Criando pacote de atualização: $filename\n";

// Lista de arquivos modificados/criados recentemente
$files = [
    'index.php',

    // Core
    'app/core/Auth.php',
    'app/core/Controller.php',
    'app/core/Migrator.php',

    // Controllers
    'app/controllers/AuthController.php',
    'app/controllers/AdminController.php',
    'app/controllers/CandidateController.php',
    'app/controllers/OrganizationController.php',
    'app/controllers/PrivacyPolicyController.php',
    'app/controllers/ReportController.php',
    'app/controllers/SuperAdminController.php',
    
    // Models
    'app/models/Course.php',
    'app/models/Enrollment.php',
    'app/models/Log.php',
    'app/models/User.php',
    
    // Views
    'app/views/admin/courses_v2.php',
    'app/views/admin/certificates.php',
    'app/views/admin/candidates.php',
    'app/views/admin/candidate_create.php',
    'app/views/admin/candidate_edit.php',
    'app/views/admin/enrollments.php',
    'app/views/admin/organization.php',
    'app/views/auth/login.php',
    'app/views/auth/forgot.php',
    'app/views/auth/reset.php',
    'app/views/auth/register.php',
    'app/views/candidate/dashboard.php',
    'app/views/candidate/course_details.php',
    'app/views/candidate/enrollments.php',
    'app/views/candidate/change_password.php',
    'app/views/candidate/profile.php',
    'app/views/candidate/export_data_print.php',
    'app/views/privacy_policy/index.php',
    'app/views/super_admin/admins/form.php',
    'app/views/super_admin/login_settings.php',
    'app/views/super_admin/updates/index.php',
    
    // CSS
    'public/css/login.css',
    
    // Migrations (Essenciais)
    'database/migrations/20260206_001_add_min_age_and_birth_date.sql',
    'database/migrations/20260206_002_add_has_certificate.sql',
    'database/migrations/20260503_001_add_lgpd_consent.sql',
];

$missing = 0;
foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $zip->addFile(__DIR__ . '/' . $file, $file);
        echo "[OK] Adicionado: $file\n";
    } else {
        echo "[ERRO] Arquivo não encontrado: $file\n";
        $missing++;
    }
}

$zip->close();

if ($missing > 0) {
    echo "\nATENÇÃO: $missing arquivos não foram encontrados!\n";
} else {
    echo "\nPacote criado com sucesso!\n";
    echo "Arquivo: " . realpath($filename) . "\n";
}
