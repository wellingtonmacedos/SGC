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

    public function updatePassword(int $id, string $passwordHash): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        $stmt->execute([
            'password_hash' => $passwordHash,
            'id' => $id,
        ]);
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
