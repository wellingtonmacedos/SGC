<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\User;

class CandidateController extends Controller
{
    public function profile(): void
    {
        Auth::requireCandidate();
        $user = Auth::user();
        // Refresh user data from DB to get all fields
        $userModel = new User();
        $user = $userModel->findById((int)$user['id']);
        
        $error = '';
        $success = '';

        if ($this->isPost()) {
            $this->requireCsrf();
            $name = $this->getPostString('name');
            $email = $this->getPostString('email');
            $username = $this->getPostString('username');
            $phone = $this->getPostString('phone');
            $address = $this->getPostString('address');
            $birthDate = $this->getPostString('birth_date');
            $password = $this->getPostString('password');
            $confirmPassword = $this->getPostString('confirm_password');

            // Validations
            if (!$name || !$email || !$username || !$address) {
                $error = 'Nome, E-mail, Usuário e Endereço são obrigatórios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'E-mail inválido.';
            } elseif (preg_match('/\s/', $username)) {
                $error = 'O nome de usuário não pode conter espaços.';
            } else {
                // Check uniqueness
                $existingUserEmail = $userModel->findByEmail($email);
                if ($existingUserEmail && (int)$existingUserEmail['id'] !== (int)$user['id']) {
                    $error = 'Este e-mail já está em uso.';
                }
                
                $existingUserUsername = $userModel->findByUsername($username);
                if ($existingUserUsername && (int)$existingUserUsername['id'] !== (int)$user['id']) {
                    $error = 'Este nome de usuário já está em uso.';
                }

                if (!$error) {
                    // Password update
                    if (!empty($password)) {
                        if ($password !== $confirmPassword) {
                            $error = 'As senhas não conferem.';
                        } else {
                            $userModel->updatePassword((int)$user['id'], password_hash($password, PASSWORD_DEFAULT));
                        }
                    }

                    // Photo Upload
                    $photoPath = $user['photo'];
                    if (!$error && isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['photo'];
                        if ($file['size'] > CANDIDATE_PHOTO_MAX_SIZE) {
                            $error = 'A foto excede o tamanho máximo permitido (2MB)';
                        } else {
                            $finfo = new \finfo(FILEINFO_MIME_TYPE);
                            $mimeType = $finfo->file($file['tmp_name']);
                            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
                            
                            if (!isset($allowed[$mimeType])) {
                                $error = 'Apenas imagens JPG ou PNG são permitidas';
                            } else {
                                if (!is_dir(CANDIDATE_PHOTO_PATH)) {
                                    mkdir(CANDIDATE_PHOTO_PATH, 0775, true);
                                }
                                $ext = $allowed[$mimeType];
                                $fileName = sha1(uniqid((string)time(), true)) . '.' . $ext;
                                $target = CANDIDATE_PHOTO_PATH . '/' . $fileName;
                                
                                if (move_uploaded_file($file['tmp_name'], $target)) {
                                    // Remove old photo if exists
                                    if ($user['photo'] && file_exists(CANDIDATE_PHOTO_PATH . '/' . $user['photo'])) {
                                        unlink(CANDIDATE_PHOTO_PATH . '/' . $user['photo']);
                                    }
                                    $photoPath = $fileName;
                                } else {
                                    $error = 'Falha ao salvar a foto';
                                }
                            }
                        }
                    }

                    if (!$error) {
                        $userModel->updateProfile(
                            (int)$user['id'],
                            $name,
                            $email,
                            $username,
                            $phone,
                            $address,
                            $photoPath,
                            $birthDate
                        );
                        
                        // Update session
                        $user['name'] = $name;
                        $user['email'] = $email;
                        $user['username'] = $username;
                        $user['phone'] = $phone;
                        $user['address'] = $address;
                        $user['photo'] = $photoPath;
                        $user['birth_date'] = $birthDate;
                        Auth::login($user);
                        
                        $success = 'Perfil atualizado com sucesso!';
                        $logModel = new \App\Models\Log();
                        $logModel->create('profile_updated', 'Alteração de cadastro do candidato', (int)$user['id']);
                        // Refresh data
                        $user = $userModel->findById((int)$user['id']);
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

        $filterDate = isset($_GET['date']) ? trim((string)$_GET['date']) : '';
        $filterSearch = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $error = '';

        if ($this->isPost() && isset($_POST['course_id'])) {
            $this->requireCsrf();
            $courseId = $this->getPostInt('course_id');
            if ($courseId > 0) {
                if ($enrollmentModel->exists($user['id'], $courseId)) {
                    $error = 'Você já está inscrito neste curso.';
                } else {
                    $course = $courseModel->find($courseId);
                    
                    if ($course) {
                        $maxEnrollments = (int)($course['max_enrollments'] ?? 0);
                        // Check actual count from db to be safe
                        $currentCount = $enrollmentModel->countByCourse($courseId);
                        
                        // Age Validation
                        $minAge = (int)($course['min_age'] ?? 0);
                        if ($minAge > 0) {
                            $userModel = new User();
                            $userFull = $userModel->findById((int)$user['id']);
                            
                            if (empty($userFull['birth_date'])) {
                                $error = 'Este curso exige idade mínima. Por favor, atualize seu perfil com sua data de nascimento para se inscrever.';
                            } else {
                                $age = $this->calculateAge($userFull['birth_date']);
                                if ($age < $minAge) {
                                    $error = "Este curso exige idade mínima de {$minAge} anos. Sua idade atual não atende ao requisito.";
                                    
                                    // Log blocked attempt
                                    $logModel = new \App\Models\Log();
                                    $logModel->create(
                                        'enrollment_blocked_age',
                                        "Tentativa de inscrição bloqueada por idade: {$user['name']} (ID: {$user['id']}) em {$course['name']} (ID: {$courseId}). Idade: {$age}, Mínima: {$minAge}",
                                        (int)$user['id'] // Log as user action? Or maybe system? Using user id as actor.
                                    );
                                }
                            }
                        }

                        if (!$error && $maxEnrollments > 0 && $currentCount >= $maxEnrollments) {
                            $error = 'As inscrições para este curso estão encerradas.';
                        } elseif (!$error) {
                            $enrollmentModel->create($user['id'], $courseId);

                            // Send emails
                            $subject = 'Confirmação de Inscrição: ' . $course['name'];
                            $message = "Olá " . htmlspecialchars($user['name']) . ",\n\n";
                            $message .= "Sua inscrição no curso \"" . htmlspecialchars($course['name']) . "\" foi realizada com sucesso.\n";
                            $message .= "Bons estudos!";
                            
                            Mailer::send($user['email'], $subject, $message);
                            
                            // Notify Admin
                            $adminSubject = 'Nova Inscrição: ' . $course['name'];
                            $adminMessage = "O candidato " . htmlspecialchars($user['name']) . " (" . $user['email'] . ") se inscreveu no curso \"" . htmlspecialchars($course['name']) . "\".";
                            Mailer::send(MAIL_ADMIN_ADDRESS, $adminSubject, $adminMessage);

                            // Redirect to avoid form resubmission and show success
                            $this->redirect('candidate/dashboard&success=' . urlencode('Inscrição realizada com sucesso! Você pode visualizar seus cursos no menu "Minhas Inscrições".'));
                        }
                    }
                }
            }
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 6;

        $filters = [];
        if ($filterDate !== '') $filters['date'] = $filterDate;
        if ($filterSearch !== '') $filters['search'] = $filterSearch;

        $totalCourses = $courseModel->countAvailableFiltered($filters);
        $totalPages = ceil($totalCourses / $perPage);
        $availableCourses = $courseModel->paginateAvailable($page, $perPage, $filters);
        
        $enrollments = $enrollmentModel->listByUser($user['id']);
        $enrolledCourseIds = array_column($enrollments, 'course_id');

        $this->render('candidate/dashboard', [
            'user' => $user,
            'availableCourses' => $availableCourses,
            'enrollments' => $enrollments,
            'enrolledCourseIds' => $enrolledCourseIds,
            'filterDate' => $filterDate,
            'filterSearch' => $filterSearch,
            'error' => $error,
            'pageTitle' => 'Painel do Candidato',
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCourses' => $totalCourses
        ]);
    }

    public function courseDetails(): void
    {
        Auth::requireCandidate();
        $user = Auth::user();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?r=candidate/dashboard');
            exit;
        }

        $courseModel = new Course();
        $course = $courseModel->find($id);

        if (!$course) {
            header('Location: index.php?r=candidate/dashboard');
            exit;
        }

        $enrollmentModel = new Enrollment();
        $isEnrolled = $enrollmentModel->exists((int)$user['id'], $id);
        
        // Check if course is full
        $isFull = false;
        if (!empty($course['max_enrollments']) && $course['max_enrollments'] > 0) {
            if ($course['enrollments_count'] >= $course['max_enrollments']) {
                $isFull = true;
            }
        }

        $this->render('candidate/course_details', [
            'user' => $user,
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'isFull' => $isFull,
            'pageTitle' => $course['name']
        ]);
    }

    public function enrollments(): void
    {
        Auth::requireCandidate();
        $user = Auth::user();

        $enrollmentModel = new Enrollment();
        $certificateModel = new Certificate();

        // Handle cancellation
        if ($this->isPost() && isset($_POST['action']) && $_POST['action'] === 'cancel') {
            $this->requireCsrf();
            $enrollmentId = $this->getPostInt('enrollment_id');
            $enrollment = $enrollmentModel->find($enrollmentId);

            if ($enrollment && (int)$enrollment['user_id'] === (int)$user['id']) {
                if ($enrollment['status'] !== 'certificate_available' && $enrollment['status'] !== 'completed') {
                    $enrollmentModel->delete($enrollmentId);
                    $this->redirect('candidate/enrollments&success=Inscrição cancelada com sucesso.');
                } else {
                    $this->redirect('candidate/enrollments&error=Não é possível cancelar uma inscrição concluída ou com certificado.');
                }
            } else {
                $this->redirect('candidate/enrollments&error=Inscrição não encontrada.');
            }
        }

        $enrollments = $enrollmentModel->listByUser($user['id']);
        $certificates = $certificateModel->listByUser($user['id']);

        $this->render('candidate/enrollments', [
            'user' => $user,
            'enrollments' => $enrollments,
            'certificates' => $certificates,
            'pageTitle' => 'Minhas Inscrições'
        ]);
    }

    public function certificates(): void
    {
        Auth::requireCandidate();
        $user = Auth::user();

        $certificateModel = new Certificate();
        $certificates = $certificateModel->listByUser($user['id']);
        
        // Enhance certificate data with course info if needed, 
        // but listByUser likely provides course_name etc. based on typical implementation.
        // Assuming certificates array has course details.

        $this->render('candidate/certificates', [
            'user' => $user,
            'certificates' => $certificates,
            'pageTitle' => 'Meus Certificados'
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

    public function changePassword(): void
    {
        Auth::requireCandidate();
        $user = Auth::user();
        $error = '';

        if ($this->isPost()) {
            $this->requireCsrf();
            $password = $this->getPostString('password');
            $confirmPassword = $this->getPostString('confirm_password');

            if (!$password || !$confirmPassword) {
                $error = 'Preencha todos os campos.';
            } elseif ($password !== $confirmPassword) {
                $error = 'As senhas não conferem.';
            } else {
                $userModel = new User();
                $userModel->updatePassword((int)$user['id'], password_hash($password, PASSWORD_DEFAULT), false);
                
                // Update session to remove force flag
                if (isset($_SESSION['user'])) {
                    $_SESSION['user']['force_password_change'] = 0;
                }
                
                $this->redirect('candidate/dashboard');
            }
        }

        $this->render('candidate/change_password', ['error' => $error]);
    }

    public function exportData(): void
    {
        Auth::requireCandidate();
        $user = Auth::user();
        $format = isset($_GET['format']) ? (string)$_GET['format'] : 'csv';

        $userModel = new User();
        $enrollmentModel = new Enrollment();
        $certificateModel = new Certificate();

        $userFull = $userModel->findById((int)$user['id']);
        $enrollments = $enrollmentModel->listByUser((int)$user['id']);
        $certificates = $certificateModel->listByUser((int)$user['id']);

        $logModel = new \App\Models\Log();
        $logModel->create('data_export', "Exportação de dados do titular (Formato: $format)", (int)$user['id']);

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="meus_dados_' . date('Y-m-d') . '.csv"');
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");

            fputcsv($output, ['Dados do Titular'], ';');
            fputcsv($output, ['Gerado em', date('Y-m-d H:i:s')], ';');
            fputcsv($output, [], ';');

            if ($userFull) {
                fputcsv($output, ['Cadastro'], ';');
                fputcsv($output, ['Nome', $userFull['name'] ?? ''], ';');
                fputcsv($output, ['Email', $userFull['email'] ?? ''], ';');
                fputcsv($output, ['Usuário', $userFull['username'] ?? ''], ';');
                fputcsv($output, ['CPF', $userFull['cpf'] ?? ''], ';');
                fputcsv($output, ['Telefone', $userFull['phone'] ?? ''], ';');
                fputcsv($output, ['Endereço', $userFull['address'] ?? ''], ';');
                fputcsv($output, ['Data de Nascimento', $userFull['birth_date'] ?? ''], ';');
                fputcsv($output, [], ';');
                fputcsv($output, ['Consentimento'], ';');
                fputcsv($output, ['LGPD', (int)($userFull['lgpd_consent'] ?? 0) === 1 ? 'Sim' : 'Não'], ';');
                fputcsv($output, ['Aceito em', $userFull['lgpd_consent_at'] ?? ''], ';');
                fputcsv($output, ['IP', $userFull['lgpd_consent_ip'] ?? ''], ';');
                fputcsv($output, ['Versão', $userFull['privacy_policy_version'] ?? ''], ';');
                fputcsv($output, [], ';');
            }

            fputcsv($output, ['Inscrições'], ';');
            fputcsv($output, ['Curso', 'Status', 'Data Inscrição', 'Certificado'], ';');
            foreach ($enrollments as $row) {
                fputcsv($output, [
                    $row['course_name'] ?? '',
                    $row['status'] ?? '',
                    $row['created_at'] ?? '',
                    !empty($row['has_certificate']) ? 'Sim' : 'Não',
                ], ';');
            }
            fputcsv($output, [], ';');

            fputcsv($output, ['Certificados'], ';');
            fputcsv($output, ['Curso', 'Arquivo', 'Data'], ';');
            foreach ($certificates as $row) {
                fputcsv($output, [
                    $row['course_name'] ?? '',
                    $row['original_name'] ?? '',
                    $row['created_at'] ?? '',
                ], ';');
            }
            fclose($output);
            exit;
        }

        $this->render('candidate/export_data_print', [
            'user' => $userFull,
            'enrollments' => $enrollments,
            'certificates' => $certificates,
            'pageTitle' => 'Exportação de Dados'
        ]);
    }

    private function calculateAge(string $birthDate): int
    {
        $today = new \DateTime();
        $birth = new \DateTime($birthDate);
        return $today->diff($birth)->y;
    }
}
