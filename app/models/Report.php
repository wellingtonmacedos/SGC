<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Report extends Model
{
    // 1. RELATÓRIO – NÚMERO DE CURSOS
    public function getCourseStats(string $periodStart = null, string $periodEnd = null, string $status = null): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' OR status = 'closed' THEN 1 ELSE 0 END) as inactive
                FROM courses WHERE 1=1";
        
        $params = [];

        if ($periodStart) {
            $sql .= " AND created_at >= :start";
            $params['start'] = $periodStart;
        }
        if ($periodEnd) {
            $sql .= " AND created_at <= :end";
            $params['end'] = $periodEnd . ' 23:59:59';
        }
        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCoursesList(string $periodStart = null, string $periodEnd = null, string $status = null): array
    {
        $sql = "SELECT * FROM courses WHERE 1=1";
        $params = [];

        if ($periodStart) {
            $sql .= " AND created_at >= :start";
            $params['start'] = $periodStart;
        }
        if ($periodEnd) {
            $sql .= " AND created_at <= :end";
            $params['end'] = $periodEnd . ' 23:59:59';
        }
        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. RELATÓRIO – INSCRITOS POR CURSO
    public function getEnrollmentsByCourse(): array
    {
        $sql = "SELECT 
                    c.id, 
                    c.name, 
                    c.max_enrollments, 
                    COUNT(e.id) as total_enrollments,
                    (CASE 
                        WHEN c.max_enrollments > 0 THEN GREATEST(0, c.max_enrollments - COUNT(e.id)) 
                        ELSE NULL 
                    END) as remaining_seats
                FROM courses c
                LEFT JOIN enrollments e ON c.id = e.course_id
                GROUP BY c.id
                ORDER BY total_enrollments DESC, c.name ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. RELATÓRIO – INSCRIÇÕES POR PERÍODO
    public function getEnrollmentsByPeriod(string $startDate, string $endDate, string $groupBy = 'day', ?int $courseId = null): array
    {
        $dateFormat = match($groupBy) {
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $sql = "SELECT 
                    DATE_FORMAT(e.created_at, :format) as period,
                    COUNT(e.id) as total,
                    c.name as course_name
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.created_at BETWEEN :start AND :end";
        
        $params = [
            'format' => $dateFormat,
            'start' => $startDate . ' 00:00:00',
            'end' => $endDate . ' 23:59:59'
        ];

        if ($courseId) {
            $sql .= " AND e.course_id = :course_id";
            $params['course_id'] = $courseId;
        }

        $sql .= " GROUP BY period";
        if ($groupBy === 'course' || !$groupBy) {
             // Logic adjustment if grouping by course is needed in a specific way, 
             // but usually period reports group by time. 
             // If group by course is requested within period:
             // $sql .= ", c.id"; 
        }

        $sql .= " ORDER BY period ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDailyAverage(string $startDate, string $endDate): float
    {
        $sql = "SELECT COUNT(*) as total FROM enrollments WHERE created_at BETWEEN :start AND :end";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'start' => $startDate . ' 00:00:00',
            'end' => $endDate . ' 23:59:59'
        ]);
        $total = (int)$stmt->fetchColumn();

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $days = $start->diff($end)->days + 1;

        return $days > 0 ? round($total / $days, 2) : 0;
    }

    // 4. DASHBOARD
    public function getDashboardStats(): array
    {
        $stats = [];
        
        // Active Courses
        $stmt = $this->db->query("SELECT COUNT(*) FROM courses WHERE status = 'active'");
        $stats['active_courses'] = (int)$stmt->fetchColumn();

        // Total Enrollments
        $stmt = $this->db->query("SELECT COUNT(*) FROM enrollments");
        $stats['total_enrollments'] = (int)$stmt->fetchColumn();

        // Enrollments this month
        $stmt = $this->db->query("SELECT COUNT(*) FROM enrollments WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $stats['enrollments_this_month'] = (int)$stmt->fetchColumn();

        return $stats;
    }
}
