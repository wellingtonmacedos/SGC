<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Enrollment extends Model
{
    public function create(int $userId, int $courseId): int
    {
        $stmt = $this->db->prepare('INSERT INTO enrollments (user_id, course_id, status, created_at, updated_at) VALUES (:user_id, :course_id, :status, NOW(), NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
            'status' => 'enrolled',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM enrollments');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function exists(int $userId, int $courseId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM enrollments WHERE user_id = :user_id AND course_id = :course_id LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? true : false;
    }

    public function findByUserAndCourse(int $userId, int $courseId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM enrollments WHERE user_id = :user_id AND course_id = :course_id LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function listByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT e.id, e.status, e.course_id, c.name AS course_name, c.workload, c.instructor FROM enrollments e JOIN courses c ON c.id = e.course_id WHERE e.user_id = :user_id ORDER BY c.name');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByCourse(int $courseId): array
    {
        $stmt = $this->db->prepare('SELECT e.id, e.status, u.name AS user_name, u.email, u.cpf FROM enrollments e JOIN users u ON u.id = e.user_id WHERE e.course_id = :course_id ORDER BY u.name');
        $stmt->execute(['course_id' => $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByCourse(int $courseId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM enrollments WHERE course_id = :course_id');
        $stmt->execute(['course_id' => $courseId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function updateStatus(int $enrollmentId, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE enrollments SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'id' => $enrollmentId,
            'status' => $status,
        ]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM enrollments WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function countWithCertificateStatus(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM enrollments WHERE status = 'certificate_available'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function getMonthlyStats(): array
    {
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total 
            FROM enrollments 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY month 
            ORDER BY month ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
