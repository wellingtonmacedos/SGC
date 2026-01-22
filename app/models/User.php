<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findByCpf(string $cpf): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE cpf = :cpf LIMIT 1');
        $stmt->execute(['cpf' => $cpf]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findByIdentifier(string $identifier): ?array
    {
        $cleanCpf = preg_replace('/[^0-9]/', '', $identifier);
        
        // Use unique placeholders to avoid PDO issues with parameter reuse
        $sql = 'SELECT * FROM users WHERE email = :identifier_email OR username = :identifier_username';
        $params = [
            'identifier_email' => $identifier,
            'identifier_username' => $identifier
        ];
        
        if (!empty($cleanCpf) && strlen($cleanCpf) === 11) {
             $sql .= ' OR cpf = :cleanCpf';
             $params['cleanCpf'] = $cleanCpf;
        }

        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function createCandidate(string $name, string $cpf, string $email, string $passwordHash, string $username, ?string $phone = null, ?string $address = null, ?string $photo = null): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, cpf, email, password_hash, username, phone, address, photo, role, created_at) VALUES (:name, :cpf, :email, :password_hash, :username, :phone, :address, :photo, :role, NOW())');
        $stmt->execute([
            'name' => $name,
            'cpf' => $cpf,
            'email' => $email,
            'password_hash' => $passwordHash,
            'username' => $username,
            'phone' => $phone,
            'address' => $address,
            'photo' => $photo,
            'role' => 'candidate',
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function ensureDefaultAdmin(): void
    {
        $stmt = $this->db->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            return;
        }

        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);

        $stmt = $this->db->prepare('INSERT INTO users (name, cpf, email, password_hash, role, created_at) VALUES (:name, :cpf, :email, :password_hash, :role, NOW())');
        $stmt->execute([
            'name' => 'Administrador',
            'cpf' => '00000000000',
            'email' => MAIL_ADMIN_ADDRESS,
            'password_hash' => $passwordHash,
            'role' => 'admin',
        ]);
    }

    public function countAdmins(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function countCandidates(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'candidate'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function listCandidates(): array
    {
        $stmt = $this->db->query("SELECT id, name, email, cpf, username, phone, address, photo FROM users WHERE role = 'candidate' ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listAdmins(): array
    {
        $stmt = $this->db->query("SELECT id, name, email, cpf, username, phone, address, photo, status, created_at FROM users WHERE role = 'admin' ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAdmin(string $name, string $cpf, string $email, string $passwordHash, string $username): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, cpf, email, password_hash, username, role, created_at) VALUES (:name, :cpf, :email, :password_hash, :username, "admin", NOW())');
        $stmt->execute([
            'name' => $name,
            'cpf' => $cpf,
            'email' => $email,
            'password_hash' => $passwordHash,
            'username' => $username,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateAdmin(int $id, string $name, string $cpf, string $email, string $username, string $status = 'active'): void
    {
        $stmt = $this->db->prepare('UPDATE users SET name = :name, cpf = :cpf, email = :email, username = :username, status = :status WHERE id = :id AND role = "admin"');
        $stmt->execute([
            'name' => $name,
            'cpf' => $cpf,
            'email' => $email,
            'username' => $username,
            'status' => $status,
            'id' => $id,
        ]);
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE users SET status = :status WHERE id = :id');
        $stmt->execute([
            'status' => $status,
            'id' => $id,
        ]);
    }

    public function deleteAdmin(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id AND role = "admin"');
        $stmt->execute(['id' => $id]);
    }


    public function updatePassword(int $id, string $passwordHash, bool $forceChange = false): void
    {
        $sql = 'UPDATE users SET password_hash = :password_hash';
        $params = [
            'password_hash' => $passwordHash,
            'id' => $id,
        ];

        if ($forceChange) {
            $sql .= ', force_password_change = 1';
        } else {
            // Optional: reset if admin changes password but doesn't force change? 
            // Usually if admin resets, they might want to force change. 
            // If just updating, maybe not. 
            // But let's stick to the parameter. 
            // If the column exists, we can update it. 
            // To be safe with the migration, let's assume the column exists now.
            $sql .= ', force_password_change = :force_change';
            $params['force_change'] = 0;
        }

        $sql .= ' WHERE id = :id';
        
        // Actually, simpler query:
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :password_hash, force_password_change = :force_change WHERE id = :id');
        $stmt->execute([
            'password_hash' => $passwordHash,
            'force_change' => $forceChange ? 1 : 0,
            'id' => $id,
        ]);
    }

    public function updateCandidateCompleto(int $id, string $name, string $cpf, string $email, string $username, ?string $phone, ?string $address, ?string $photo): void
    {
        $sql = 'UPDATE users SET name = :name, cpf = :cpf, email = :email, username = :username, phone = :phone, address = :address';
        $params = [
            'name' => $name,
            'cpf' => $cpf,
            'email' => $email,
            'username' => $username,
            'phone' => $phone,
            'address' => $address,
            'id' => $id,
        ];

        if ($photo !== null) {
            $sql .= ', photo = :photo';
            $params['photo'] = $photo;
        }

        $sql .= ' WHERE id = :id AND role = "candidate"';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function updateProfile(int $id, string $name, string $email, string $username, ?string $phone, string $address, ?string $photo): void
    {
        $sql = 'UPDATE users SET name = :name, email = :email, username = :username, phone = :phone, address = :address';
        $params = [
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'phone' => $phone,
            'address' => $address,
            'id' => $id,
        ];

        if ($photo !== null) {
            $sql .= ', photo = :photo';
            $params['photo'] = $photo;
        }

        $sql .= ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function update(int $id, string $name, string $email): void
    {
        $stmt = $this->db->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'id' => $id,
        ]);
    }
}
