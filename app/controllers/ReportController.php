<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Report;
use App\Models\Organization;
use App\Models\Course;

class ReportController extends Controller
{
    private Report $reportModel;
    private Organization $organizationModel;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->reportModel = new Report();
        $this->organizationModel = new Organization();
    }

    public function dashboard(): void
    {
        $stats = $this->reportModel->getDashboardStats();
        $this->render('admin/reports/dashboard', ['stats' => $stats]);
    }

    public function courses(): void
    {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $status = $_GET['status'] ?? null;

        $stats = $this->reportModel->getCourseStats($startDate, $endDate, $status);
        $courses = $this->reportModel->getCoursesList($startDate, $endDate, $status);

        if (isset($_GET['export'])) {
            $this->exportReport('courses', ['stats' => $stats, 'courses' => $courses], $_GET['export']);
            return;
        }

        $this->render('admin/reports/courses', [
            'stats' => $stats, 
            'courses' => $courses,
            'filters' => ['start_date' => $startDate, 'end_date' => $endDate, 'status' => $status]
        ]);
    }

    public function enrollmentsByCourse(): void
    {
        $data = $this->reportModel->getEnrollmentsByCourse();

        if (isset($_GET['export'])) {
            $this->exportReport('enrollments_course', ['data' => $data], $_GET['export']);
            return;
        }

        $this->render('admin/reports/enrollments_course', ['data' => $data]);
    }

    public function enrollmentsHistory(): void
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $groupBy = $_GET['group_by'] ?? 'day';
        $courseId = !empty($_GET['course_id']) ? (int)$_GET['course_id'] : null;

        $data = $this->reportModel->getEnrollmentsByPeriod($startDate, $endDate, $groupBy, $courseId);
        $average = $this->reportModel->getDailyAverage($startDate, $endDate);
        
        $courseModel = new Course();
        $courses = $courseModel->all();

        if (isset($_GET['export'])) {
            $this->exportReport('history', [
                'data' => $data, 
                'average' => $average, 
                'startDate' => $startDate, 
                'endDate' => $endDate
            ], $_GET['export']);
            return;
        }

        $this->render('admin/reports/history', [
            'data' => $data,
            'average' => $average,
            'courses' => $courses,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'group_by' => $groupBy,
                'course_id' => $courseId
            ]
        ]);
    }

    private function exportReport(string $type, array $data, string $format): void
    {
        $orgSettings = $this->organizationModel->getSettings();
        $filename = 'relatorio_' . $type . '_' . date('Y-m-d_H-i');

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
            
            // Header Institucional
            fputcsv($output, [$orgSettings['organization_name'] ?? 'Sistema de Cursos'], ';');
            fputcsv($output, ['Relatório Gerado em: ' . date('d/m/Y H:i')], ';');
            fputcsv($output, [], ';'); // Empty line

            if ($type === 'courses') {
                fputcsv($output, ['Resumo'], ';');
                fputcsv($output, ['Total', 'Ativos', 'Inativos'], ';');
                fputcsv($output, [$data['stats']['total'], $data['stats']['active'], $data['stats']['inactive']], ';');
                fputcsv($output, [], ';');
                fputcsv($output, ['Detalhes dos Cursos'], ';');
                fputcsv($output, ['ID', 'Nome', 'Status', 'Criado em'], ';');
                foreach ($data['courses'] as $row) {
                    fputcsv($output, [$row['id'], $row['name'], $row['status'], $row['created_at']], ';');
                }
            } elseif ($type === 'enrollments_course') {
                fputcsv($output, ['Curso', 'Total Inscritos', 'Limite', 'Vagas Restantes'], ';');
                foreach ($data['data'] as $row) {
                    fputcsv($output, [
                        $row['name'], 
                        $row['total_enrollments'], 
                        $row['max_enrollments'] > 0 ? $row['max_enrollments'] : 'Ilimitado',
                        $row['remaining_seats'] !== null ? $row['remaining_seats'] : '-'
                    ], ';');
                }
            } elseif ($type === 'history') {
                fputcsv($output, ['Período', 'Total de Inscrições'], ';');
                foreach ($data['data'] as $row) {
                    fputcsv($output, [$row['period'], $row['total']], ';');
                }
                fputcsv($output, [], ';');
                fputcsv($output, ['Média Diária', $data['average']], ';');
            }

            fclose($output);
            exit;
        } elseif ($format === 'pdf') {
            // For PDF, we render a specific view designed for printing
            // Reuse logic or create a generic print view
            $this->render('admin/reports/print_' . $type, array_merge($data, ['orgSettings' => $orgSettings]));
            exit;
        }
    }
}
