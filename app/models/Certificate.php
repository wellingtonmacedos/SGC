<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Certificate extends Model
{
    public function create(int $enrollmentId, string $fileName, string $originalName, int $fileSize, string $mimeType): int
    {
        $stmt = $this->db->prepare('INSERT INTO certificates (enrollment_id, file_name, original_name, file_size, mime_type, created_at) VALUES (:enrollment_id, :file_name, :original_name, :file_size, :mime_type, NOW())');
        $stmt->execute([
            'enrollment_id' => $enrollmentId,
            'file_name' => $fileName,
            'original_name' => $originalName,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function listByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT cert.id, cert.original_name, cert.file_name, cert.mime_type, cert.created_at, cert.validation_code, cert.issued_at, c.name AS course_name, c.id AS course_id FROM certificates cert JOIN enrollments e ON e.id = cert.enrollment_id JOIN courses c ON c.id = e.course_id WHERE e.user_id = :user_id ORDER BY cert.created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT cert.*, e.user_id FROM certificates cert JOIN enrollments e ON e.id = cert.enrollment_id WHERE cert.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function delete(int $id): ?array
    {
        $cert = $this->find($id);
        if (!$cert) {
            return null;
        }
        $stmt = $this->db->prepare('DELETE FROM certificates WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $cert;
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM certificates');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }
}
