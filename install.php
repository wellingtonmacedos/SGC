<?php
declare(strict_types=1);

// Disable error reporting for cleaner output during installation (handle manually)
error_reporting(E_ALL);
ini_set('display_errors', '1');

$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;
$message = '';
$error = '';
$phpVersion = phpversion();
$isPhpCompatible = version_compare($phpVersion, '8.1.0', '>=');
$extensions = [
    'pdo' => extension_loaded('pdo'),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'mbstring' => extension_loaded('mbstring'),
    'gd' => extension_loaded('gd'),
];
$allExtensionsLoaded = !in_array(false, $extensions, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    try {
        $dbHost = $_POST['db_host'] ?? 'localhost';
        $dbName = $_POST['db_name'] ?? 'sgc';
        $dbUser = $_POST['db_user'] ?? 'root';
        $dbPass = $_POST['db_pass'] ?? '';
        
        $adminName = $_POST['admin_name'] ?? 'Super Usuário';
        $adminEmail = $_POST['admin_email'] ?? 'admin@example.com';
        $adminUser = $_POST['admin_user'] ?? 'admin';
        $adminPass = $_POST['admin_pass'] ?? '';

        if (empty($adminPass) || strlen($adminPass) < 6) {
            throw new Exception("A senha do administrador deve ter pelo menos 6 caracteres.");
        }

        // 1. Test Connection
        $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 2. Create Database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbName`");

        // 3. Import Schema
        $schemaFile = __DIR__ . '/database/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("Arquivo de schema não encontrado: $schemaFile");
        }
        $sql = file_get_contents($schemaFile);
        
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/#.*$/m', '', $sql);
        
        // Split by semicolon
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $stmt) {
            if (!empty($stmt)) {
                $pdo->exec($stmt);
            }
        }

        // 4. Create Super Admin
        $passwordHash = password_hash($adminPass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
        $stmt->execute(['email' => $adminEmail, 'username' => $adminUser]);
        
        if ($stmt->rowCount() == 0) {
            $insert = $pdo->prepare("INSERT INTO users (name, cpf, email, username, password_hash, role, status, created_at, force_password_change) VALUES (:name, '00000000000', :email, :username, :password, 'super_admin', 'active', NOW(), 0)");
            $insert->execute([
                'name' => $adminName,
                'email' => $adminEmail,
                'username' => $adminUser,
                'password' => $passwordHash
            ]);
        }

        // 5. Create config.php
        $configFile = __DIR__ . '/config.php';
        $configContent = "<?php\n\n";
        $configContent .= "define('DB_HOST', '" . addslashes($dbHost) . "');\n";
        $configContent .= "define('DB_NAME', '" . addslashes($dbName) . "');\n";
        $configContent .= "define('DB_USER', '" . addslashes($dbUser) . "');\n";
        $configContent .= "define('DB_PASS', '" . addslashes($dbPass) . "');\n";
        $configContent .= "define('DB_CHARSET', 'utf8mb4');\n\n";
        $configContent .= "// App Configuration\n";
        $configContent .= "define('APP_URL', 'http://' . \$_SERVER['HTTP_HOST'] . dirname(\$_SERVER['PHP_SELF']));\n";
        $configContent .= "define('APP_NAME', 'SGC - Sistema de Gestão de Cursos');\n";
        
        if (file_put_contents($configFile, $configContent) === false) {
             throw new Exception("Não foi possível criar o arquivo config.php. Verifique as permissões.");
        }

        $step = 3; // Success
        $message = "Instalação concluída com sucesso!";

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalação SGC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .install-card { max-width: 800px; width: 100%; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); background: white; overflow: hidden; }
        .card-header { background: #2c3e50; color: white; padding: 20px; text-align: center; border: none; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; position: relative; }
        .step-indicator::before { content: ''; position: absolute; top: 15px; left: 0; right: 0; height: 2px; background: #e9ecef; z-index: 1; }
        .step { position: relative; z-index: 2; background: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #e9ecef; color: #adb5bd; font-weight: bold; }
        .step.active { border-color: #3498db; background: #3498db; color: white; }
        .step.completed { border-color: #2ecc71; background: #2ecc71; color: white; }
    </style>
</head>
<body>

<div class="install-card">
    <div class="card-header">
        <h3 class="mb-0"><i class="fas fa-cogs me-2"></i>Instalação do SGC</h3>
        <p class="mb-0 opacity-75">Sistema de Gestão de Cursos</p>
    </div>
    <div class="p-4 p-md-5">
        
        <!-- Step Indicator -->
        <div class="step-indicator px-5">
            <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">1</div>
            <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">2</div>
            <div class="step <?php echo $step >= 3 ? 'completed' : ''; ?>"><i class="fas fa-check"></i></div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger shadow-sm border-0 mb-4">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <h4 class="mb-4">Verificação de Ambiente</h4>
            <div class="list-group mb-4">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Versão do PHP</strong>
                        <div class="text-muted small">Mínimo requerido: 8.1.0</div>
                    </div>
                    <span class="badge <?php echo $isPhpCompatible ? 'bg-success' : 'bg-danger'; ?> rounded-pill">
                        <?php echo $phpVersion; ?>
                    </span>
                </div>
                <?php foreach ($extensions as $ext => $loaded): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Extensão <?php echo strtoupper($ext); ?></strong>
                    </div>
                    <span class="badge <?php echo $loaded ? 'bg-success' : 'bg-danger'; ?> rounded-pill">
                        <?php echo $loaded ? 'Instalada' : 'Ausente'; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-end">
                <?php if ($isPhpCompatible && $allExtensionsLoaded): ?>
                    <form method="post">
                        <input type="hidden" name="step" value="2">
                        <button type="submit" class="btn btn-primary btn-lg px-5">Próximo <i class="fas fa-arrow-right ms-2"></i></button>
                    </form>
                <?php else: ?>
                    <button disabled class="btn btn-secondary btn-lg px-5">Corrija os erros acima</button>
                <?php endif; ?>
            </div>

        <?php elseif ($step === 2): ?>
            <h4 class="mb-4">Configuração do Sistema</h4>
            <form method="post">
                <input type="hidden" name="step" value="2">
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100 border-light shadow-sm">
                            <div class="card-header bg-white fw-bold"><i class="fas fa-database me-2 text-primary"></i>Banco de Dados</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Servidor (Host)</label>
                                    <input type="text" name="db_host" class="form-control" value="localhost" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nome do Banco</label>
                                    <input type="text" name="db_name" class="form-control" value="sgc" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Usuário</label>
                                    <input type="text" name="db_user" class="form-control" value="root" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Senha</label>
                                    <input type="password" name="db_pass" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100 border-light shadow-sm">
                            <div class="card-header bg-white fw-bold"><i class="fas fa-user-shield me-2 text-danger"></i>Super Usuário</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Nome Completo</label>
                                    <input type="text" name="admin_name" class="form-control" value="Super Admin" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="admin_email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Usuário (Login)</label>
                                    <input type="text" name="admin_user" class="form-control" value="admin" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Senha</label>
                                    <input type="password" name="admin_pass" class="form-control" required minlength="6">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success btn-lg px-5">Instalar Sistema <i class="fas fa-check ms-2"></i></button>
                </div>
            </form>

        <?php elseif ($step === 3): ?>
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                </div>
                <h2 class="mb-3">Instalação Concluída!</h2>
                <p class="lead text-muted mb-5">O sistema foi configurado e o banco de dados inicializado com sucesso.</p>
                
                <div class="alert alert-warning d-inline-block text-start mb-4" style="max-width: 500px;">
                    <i class="fas fa-exclamation-triangle me-2"></i><strong>Importante:</strong>
                    Por segurança, recomendamos remover ou renomear este arquivo <code>install.php</code> antes de colocar o sistema em produção.
                </div>
                
                <br>
                <a href="index.php" class="btn btn-primary btn-lg px-5">Acessar Sistema</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="text-center mt-4 text-muted small">
    &copy; <?php echo date('Y'); ?> SGC - Sistema de Gestão de Cursos
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
