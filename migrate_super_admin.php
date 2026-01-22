<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "<h1>Iniciando Migração: Super Usuário (Root)</h1>";

    // 1. Atualizar ENUM da tabela users
    // Primeiro verificamos se já existe 'super_admin' para evitar erro
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    $type = $column['Type'];

    if (strpos($type, 'super_admin') === false) {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'candidate') NOT NULL DEFAULT 'candidate'");
        echo "<p>Tabela 'users' atualizada: Role ENUM expandido.</p>";
    } else {
        echo "<p>Tabela 'users' já possui role 'super_admin'.</p>";
    }

    // 1.1 Adicionar coluna 'status' se não existir
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'status'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active' AFTER role");
        echo "<p>Coluna 'status' adicionada à tabela users.</p>";
    } else {
        echo "<p>Coluna 'status' já existe.</p>";
    }

    // 2. Criar tabela de backups
    $pdo->exec("CREATE TABLE IF NOT EXISTS backups (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type ENUM('db', 'files', 'full') NOT NULL,
        filename VARCHAR(255) NOT NULL,
        filepath VARCHAR(255) NOT NULL,
        size BIGINT UNSIGNED NOT NULL DEFAULT 0,
        created_by INT UNSIGNED DEFAULT NULL,
        created_at DATETIME NOT NULL,
        CONSTRAINT fk_backups_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "<p>Tabela 'backups' verificada/criada.</p>";

    // 3. Criar diretório de backups protegido
    $backupDir = __DIR__ . '/storage/backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
        // Criar .htaccess para proteção
        file_put_contents($backupDir . '/.htaccess', "Order Deny,Allow\nDeny from all");
        echo "<p>Diretório de backups criado e protegido.</p>";
    }

    // 4. Criar/Garantir Usuário ROOT
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'super_admin' LIMIT 1");
    $stmt->execute();
    $root = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$root) {
        $name = 'Super Usuário';
        $email = 'root@sistema.leg';
        $cpf = '00000000001'; // CPF fictício diferente do admin padrão
        $password = 'root@123'; // Senha inicial
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $username = 'root';

        // Verificar conflitos de e-mail/cpf/username
        $pdo->exec("DELETE FROM users WHERE email = '$email' OR cpf = '$cpf' OR username = '$username'");

        $stmt = $pdo->prepare("INSERT INTO users (name, cpf, email, password_hash, username, role, created_at) VALUES (:name, :cpf, :email, :password_hash, :username, :role, NOW())");
        $stmt->execute([
            'name' => $name,
            'cpf' => $cpf,
            'email' => $email,
            'password_hash' => $passwordHash,
            'username' => $username,
            'role' => 'super_admin'
        ]);
        
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-top: 20px;'>
            <strong>Sucesso!</strong> Usuário ROOT criado.<br>
            Usuário: root (ou root@sistema.leg)<br>
            Senha: $password<br>
            <small>Por favor, altere esta senha imediatamente após o login.</small>
        </div>";
    } else {
        echo "<p>Usuário ROOT já existe.</p>";
    }

    echo "<p><a href='index.php?r=auth/login'>Ir para Login</a></p>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px;'>Erro: " . $e->getMessage() . "</div>";
}
