<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Backup extends Model
{
    public function create(string $type, string $filename, string $filepath, int $size, int $createdBy): int
    {
        $stmt = $this->db->prepare('INSERT INTO backups (type, filename, filepath, size, created_by, created_at) VALUES (:type, :filename, :filepath, :size, :created_by, NOW())');
        $stmt->execute([
            'type' => $type,
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => $size,
            'created_by' => $createdBy,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT b.*, u.name as creator_name FROM backups b LEFT JOIN users u ON b.created_by = u.id ORDER BY b.created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM backups WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM backups WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        // Check if table exists first? No, migration guaranteed it.
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM backups');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }
}
