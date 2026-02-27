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
    // Core
    'app/core/Migrator.php',

    // Controllers
    'app/controllers/AdminController.php',
    'app/controllers/SuperAdminController.php',
    
    // Models
    'app/models/Course.php',
    'app/models/User.php',
    
    // Views
    'app/views/admin/courses_v2.php',
    'app/views/admin/certificates.php',
    'app/views/auth/register.php',
    'app/views/candidate/dashboard.php',
    'app/views/super_admin/updates/index.php',
    
    // CSS
    'public/css/login.css',
    
    // Migrations (Essenciais)
    'database/migrations/20260206_001_add_min_age_and_birth_date.sql',
    'database/migrations/20260206_002_add_has_certificate.sql',
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
