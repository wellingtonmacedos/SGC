<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Course extends Model
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO courses (name, description, target_audience, workload, instructor, period, date, time, location, cover_image, status, allow_enrollment, max_enrollments, created_at) VALUES (:name, :description, :target_audience, :workload, :instructor, :period, :date, :time, :location, :cover_image, :status, :allow_enrollment, :max_enrollments, NOW())');
        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'target_audience' => $data['target_audience'] ?? null,
            'workload' => $data['workload'],
            'instructor' => $data['instructor'],
            'period' => $data['period'],
            'date' => $data['date'],
            'time' => $data['time'],
            'location' => $data['location'],
            'cover_image' => $data['cover_image'],
            'status' => $data['status'],
            'allow_enrollment' => $data['allow_enrollment'],
            'max_enrollments' => !empty($data['max_enrollments']) ? $data['max_enrollments'] : 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE courses SET name = :name, description = :description, target_audience = :target_audience, workload = :workload, instructor = :instructor, period = :period, date = :date, time = :time, location = :location, cover_image = :cover_image, status = :status, allow_enrollment = :allow_enrollment, max_enrollments = :max_enrollments WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'],
            'target_audience' => $data['target_audience'] ?? null,
            'workload' => $data['workload'],
            'instructor' => $data['instructor'],
            'period' => $data['period'],
            'date' => $data['date'],
            'time' => $data['time'],
            'location' => $data['location'],
            'cover_image' => $data['cover_image'],
            'status' => $data['status'],
            'allow_enrollment' => $data['allow_enrollment'],
            'max_enrollments' => !empty($data['max_enrollments']) ? $data['max_enrollments'] : 0,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM courses WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT c.*, (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as enrollments_count FROM courses c WHERE c.id = :id');
        $stmt->execute(['id' => $id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        return $course ?: null;
    }
    
    public function findById(int $id): ?array
    {
        return $this->find($id);
    }

    public function all(): array
    {
        $sql = 'SELECT c.*, COUNT(e.id) as enrollments_count 
                FROM courses c 
                LEFT JOIN enrollments e ON c.id = e.course_id 
                GROUP BY c.id 
                ORDER BY c.name';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginate(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        $where = [];
        $params = [];
        
        if (!empty($filters['name'])) {
            $where[] = 'c.name LIKE :name';
            $params['name'] = '%' . $filters['name'] . '%';
        }
        
        if (!empty($filters['status'])) {
            $where[] = 'c.status = :status';
            $params['status'] = $filters['status'];
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }
        
        // Main query
        $sql = "SELECT c.*, COUNT(e.id) as enrollments_count 
                FROM courses c 
                LEFT JOIN enrollments e ON c.id = e.course_id 
                $whereSql
                GROUP BY c.id 
                ORDER BY c.created_at DESC 
                LIMIT :limit OFFSET :offset";
                
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countFiltered(array $filters = []): int
    {
        $where = [];
        $params = [];
        
        if (!empty($filters['name'])) {
            $where[] = 'name LIKE :name';
            $params['name'] = '%' . $filters['name'] . '%';
        }
        
        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "SELECT COUNT(*) as total FROM courses $whereSql";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function availableForEnrollment(): array
    {
        $sql = "SELECT c.*, COUNT(e.id) as enrollments_count 
                FROM courses c 
                LEFT JOIN enrollments e ON c.id = e.course_id 
                WHERE c.status = 'active' AND c.allow_enrollment = 1 
                GROUP BY c.id 
                ORDER BY c.name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginateAvailable(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        $where = ["c.status = 'active'", "c.allow_enrollment = 1"];
        $params = [];
        
        if (!empty($filters['search'])) {
            $where[] = '(c.name LIKE :search OR c.location LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['date'])) {
            $where[] = 'c.date = :date';
            $params['date'] = $filters['date'];
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        
        $sql = "SELECT c.*, COUNT(e.id) as enrollments_count 
                FROM courses c 
                LEFT JOIN enrollments e ON c.id = e.course_id 
                $whereSql
                GROUP BY c.id 
                ORDER BY c.date ASC, c.name ASC
                LIMIT :limit OFFSET :offset";
                
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAvailableFiltered(array $filters = []): int
    {
        $where = ["status = 'active'", "allow_enrollment = 1"];
        $params = [];
        
        if (!empty($filters['search'])) {
            $where[] = '(name LIKE :search OR location LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['date'])) {
            $where[] = 'date = :date';
            $params['date'] = $filters['date'];
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT COUNT(*) as total FROM courses $whereSql";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM courses');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM courses WHERE status = :status');
        $stmt->execute(['status' => $status]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }
}
