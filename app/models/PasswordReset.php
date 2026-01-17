<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class PasswordReset extends Model
{
    public function create(int $userId, string $token, string $expiresAt): void
    {
        $stmt = $this->db->prepare('INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (:user_id, :token, :expires_at, NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findValidByToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT pr.*, u.email FROM password_resets pr JOIN users u ON u.id = pr.user_id WHERE pr.token = :token AND pr.expires_at > NOW() LIMIT 1');
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function deleteByUser(int $userId): void
    {
        $stmt = $this->db->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
    }

    public function deleteById(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM password_resets WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

