<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Course extends Model
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO courses (name, description, workload, instructor, period, date, time, location, cover_image, status, allow_enrollment, created_at) VALUES (:name, :description, :workload, :instructor, :period, :date, :time, :location, :cover_image, :status, :allow_enrollment, NOW())');
        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'workload' => $data['workload'],
            'instructor' => $data['instructor'],
            'period' => $data['period'],
            'date' => $data['date'],
            'time' => $data['time'],
            'location' => $data['location'],
            'cover_image' => $data['cover_image'],
            'status' => $data['status'],
            'allow_enrollment' => $data['allow_enrollment'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE courses SET name = :name, description = :description, workload = :workload, instructor = :instructor, period = :period, date = :date, time = :time, location = :location, cover_image = :cover_image, status = :status, allow_enrollment = :allow_enrollment WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'],
            'workload' => $data['workload'],
            'instructor' => $data['instructor'],
            'period' => $data['period'],
            'date' => $data['date'],
            'time' => $data['time'],
            'location' => $data['location'],
            'cover_image' => $data['cover_image'],
            'status' => $data['status'],
            'allow_enrollment' => $data['allow_enrollment'],
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM courses WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM courses WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        return $course ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM courses ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function availableForEnrollment(): array
    {
        $stmt = $this->db->query("SELECT * FROM courses WHERE status = 'active' AND allow_enrollment = 1 ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM courses');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }
}
