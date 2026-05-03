<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Log extends Model
{
    public function create(string $action, ?string $description = null, ?int $userId = null): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        
        // If user_id is not provided, try to get from session if available (though Model doesn't know about Auth directly, usually passed in)
        // Better to rely on passed userId.
        
        $stmt = $this->db->prepare('INSERT INTO logs (user_id, action, description, ip_address, created_at) VALUES (:user_id, :action, :description, :ip_address, NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ip
        ]);
    }

    public function countRecentByIpAction(string $action, string $ip, int $windowSeconds): int
    {
        $since = date('Y-m-d H:i:s', time() - max(0, $windowSeconds));
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM logs WHERE action = :action AND ip_address = :ip AND created_at >= :since');
        $stmt->execute([
            'action' => $action,
            'ip' => $ip,
            'since' => $since,
        ]);
        return (int)$stmt->fetchColumn();
    }

    public function getLatest(int $limit = 100): array
    {
        $sql = "SELECT l.*, u.name as user_name, u.email as user_email, u.role as user_role 
                FROM logs l 
                LEFT JOIN users u ON l.user_id = u.id 
                ORDER BY l.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) as total FROM logs');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }
}
