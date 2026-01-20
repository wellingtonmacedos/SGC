<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class FileController extends Controller
{
    public function photo(): void
    {
        // Allow access if logged in (Admin or Candidate)
        $user = Auth::user();
        if (!$user) {
            http_response_code(403);
            exit;
        }

        $this->serveFile(CANDIDATE_PHOTO_PATH);
    }

    public function logo(): void
    {
        // Public access
        $this->serveFile(ORGANIZATION_LOGO_PATH);
    }

    private function serveFile(string $basePath): void
    {
        $file = $_GET['file'] ?? '';
        
        // Basic validation to prevent directory traversal
        if (!$file || strpos($file, '..') !== false || strpos($file, '/') !== false || strpos($file, '\\') !== false) {
            http_response_code(400);
            echo 'Arquivo inválido';
            exit;
        }

        $path = $basePath . '/' . $file;

        if (!file_exists($path)) {
            http_response_code(404);
            echo 'Arquivo não encontrado';
            exit;
        }

        $mimeType = mime_content_type($path);
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
