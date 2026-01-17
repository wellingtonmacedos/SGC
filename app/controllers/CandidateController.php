<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

class CandidateController extends Controller
{
    public function profile(): void
    {
        Auth::requireCandidate();
        $user = Auth::user();
        $userModel = new User();
        
        $error = '';
        $success = '';

        if ($this->isPost()) {
            $name = $this->getPostString('name');
            $email = $this->getPostString('email');
            $password = $this->getPostString('password');
            $confirmPassword = $this->getPostString('confirm_password');

            if (!$name || !$email) {
                $error = 'Nome e E-mail são obrigatórios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'E-mail inválido.';
            } else {
                // Check if email is already taken by another user
                $existingUser = $userModel->findByEmail($email);
                if ($existingUser && (int)$existingUser['id'] !== (int)$user['id']) {
                    $error = 'Este e-mail já está em uso por outro usuário.';
                } else {
                    // Update password if provided
                    if (!empty($password)) {
                        if ($password !== $confirmPassword) {
                            $error = 'As senhas não conferem.';
                        } else {
                            $userModel->updatePassword((int)$user['id'], password_hash($password, PASSWORD_DEFAULT));
                        }
                    }

                    if (!$error) {
                        $userModel->update((int)$user['id'], $name, $email);
                        
                        // Update session
                        $user['name'] = $name;
                        $user['email'] = $email;
                        Auth::login($user); // Re-login to update session data
                        
                        $success = 'Perfil atualizado com sucesso!';
                    }
                }
            }
        }

        $this->render('candidate/profile', [
            'user' => $user,
            'error' => $error,
            'success' => $success
        ]);
    }

    public function dashboard(): void
    {
        Auth::requireCandidate();
        $user = Auth::user();

        $courseModel = new Course();
        $enrollmentModel = new Enrollment();
        $certificateModel = new Certificate();

        $filterDate = isset($_GET['date']) ? trim((string)$_GET['date']) : '';
        $filterSearch = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

        if ($this->isPost() && isset($_POST['course_id'])) {
            $courseId = $this->getPostInt('course_id');
            if ($courseId > 0 && !$enrollmentModel->exists($user['id'], $courseId)) {
                $enrollmentModel->create($user['id'], $courseId);
            }
        }

        $availableCourses = $courseModel->availableForEnrollment();
        
        if ($filterDate !== '' || $filterSearch !== '') {
            $availableCourses = array_values(array_filter($availableCourses, static function (array $course) use ($filterDate, $filterSearch): bool {
                if ($filterDate !== '' && (!isset($course['date']) || $course['date'] !== $filterDate)) {
                    return false;
                }
                if ($filterSearch !== '') {
                    $search = mb_strtolower($filterSearch);
                    $name = isset($course['name']) ? mb_strtolower($course['name']) : '';
                    $location = isset($course['location']) ? mb_strtolower($course['location']) : '';
                    
                    if (strpos($name, $search) === false && strpos($location, $search) === false) {
                        return false;
                    }
                }
                return true;
            }));
        }
        $enrollments = $enrollmentModel->listByUser($user['id']);
        $certificates = $certificateModel->listByUser($user['id']);

        $this->render('candidate/dashboard', [
            'user' => $user,
            'availableCourses' => $availableCourses,
            'enrollments' => $enrollments,
            'certificates' => $certificates,
            'filterDate' => $filterDate,
            'filterSearch' => $filterSearch,
        ]);
    }

    public function downloadCertificate(): void
    {
        Auth::requireCandidate();
        $user = Auth::user();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $certificateModel = new Certificate();
        $cert = $certificateModel->find($id);
        if (!$cert || (int)$cert['user_id'] !== (int)$user['id']) {
            http_response_code(404);
            echo 'Certificado não encontrado';
            return;
        }

        $filePath = CERTIFICATE_PATH . '/' . $cert['file_name'];
        if (!is_file($filePath)) {
            http_response_code(404);
            echo 'Arquivo não encontrado';
            return;
        }

        header('Content-Type: ' . $cert['mime_type']);
        header('Content-Disposition: inline; filename="' . basename($cert['original_name']) . '"');
        header('Content-Length: ' . (string)$cert['file_size']);
        readfile($filePath);
        exit;
    }
}
