<?php
declare(strict_types=1);

// Configuration for installation
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'sgc';

$message = '';
$error = '';

if (isset($_POST['install'])) {
    try {
        // 1. Connect to MySQL (without selecting DB)
        $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 2. Create Database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $message .= "<div>✅ Banco de dados '$dbName' criado ou verificado.</div>";

        // 3. Select Database
        $pdo->exec("USE `$dbName`");

        // 4. Read and Execute Schema
        $schemaFile = __DIR__ . '/database/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("Arquivo de schema não encontrado: $schemaFile");
        }

        $sql = file_get_contents($schemaFile);
        
        // Split SQL by semicolons to execute statements individually (basic split)
        // Note: This is a simple splitter and might break on semicolons inside strings, 
        // but for the current schema it should be fine.
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $stmt) {
            if (!empty($stmt)) {
                $pdo->exec($stmt);
            }
        }
        $message .= "<div>✅ Tabelas criadas com sucesso.</div>";

        // 5. Create Default Admin User
        // We replicate the logic from User::ensureDefaultAdmin here to avoid dependency on App structure before DB exists
        $adminEmail = 'admin@exemplo.com';
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        
        if (!$stmt->fetch()) {
            $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, cpf, email, password_hash, role, created_at) VALUES (:name, :cpf, :email, :password_hash, :role, NOW())");
            $stmt->execute([
                'name' => 'Administrador',
                'cpf' => '00000000000',
                'email' => $adminEmail,
                'password_hash' => $passwordHash,
                'role' => 'admin',
            ]);
            $message .= "<div>✅ Usuário admin criado (Email: $adminEmail, Senha: admin123).</div>";
        } else {
            $message .= "<div>ℹ️ Usuário admin já existe.</div>";
        }

        $message .= "<div class='mt-3 alert alert-success'><strong>Instalação concluída!</strong> <a href='index.php'>Acessar o Sistema</a></div>";

    } catch (PDOException $e) {
        $error = "Erro de Banco de Dados: " . $e->getMessage();
    } catch (Exception $e) {
        $error = "Erro: " . $e->getMessage();
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
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .install-card { max-width: 500px; width: 100%; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); background: white; }
    </style>
</head>
<body>
    <div class="install-card">
        <h2 class="text-center mb-4">Instalação do SGC</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php else: ?>
            <p class="text-muted text-center mb-4">
                Este assistente irá criar o banco de dados <strong><?php echo $dbName; ?></strong> e as tabelas necessárias no seu servidor local (XAMPP).
            </p>
            
            <div class="mb-3">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Host
                        <span class="badge bg-primary rounded-pill"><?php echo $dbHost; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Usuário
                        <span class="badge bg-primary rounded-pill"><?php echo $dbUser; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Banco de Dados
                        <span class="badge bg-primary rounded-pill"><?php echo $dbName; ?></span>
                    </li>
                </ul>
            </div>

            <form method="post">
                <button type="submit" name="install" class="btn btn-primary w-100 btn-lg">Instalar Sistema</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>