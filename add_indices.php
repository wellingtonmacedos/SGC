<?php
require_once 'config.php';
require_once 'app/core/Database.php';

use App\Core\Database;

$db = Database::getConnection();

echo "Verificando e criando índices...\n";

// Helper to add index if not exists
function addIndex($db, $table, $column, $indexName) {
    try {
        // Check if index exists
        $check = $db->query("SHOW INDEX FROM $table WHERE Key_name = '$indexName'");
        if ($check->rowCount() == 0) {
            $db->exec("CREATE INDEX $indexName ON $table ($column)");
            echo "Índice '$indexName' criado na tabela '$table'.\n";
        } else {
            echo "Índice '$indexName' já existe na tabela '$table'.\n";
        }
    } catch (PDOException $e) {
        echo "Erro ao criar índice '$indexName': " . $e->getMessage() . "\n";
    }
}

// Enrollments indices
addIndex($db, 'enrollments', 'course_id', 'idx_enrollments_course_id');
addIndex($db, 'enrollments', 'created_at', 'idx_enrollments_created_at');

// Courses indices
addIndex($db, 'courses', 'status', 'idx_courses_status');
addIndex($db, 'courses', 'created_at', 'idx_courses_created_at');
addIndex($db, 'courses', 'date', 'idx_courses_date');

echo "Concluído.\n";
