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
use App\Models\Log;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireAdmin();
        $userModel = new User();
        $courseModel = new Course();
        $certificateModel = new Certificate();
        $enrollmentModel = new Enrollment();

        $totalCandidates = $userModel->countCandidates();
        $totalCourses = $courseModel->countAll();
        $totalCertificates = $certificateModel->countAll();
        $totalCertificateStatus = $enrollmentModel->countWithCertificateStatus();

        $this->render('admin/dashboard', [
            'totalCandidates' => $totalCandidates,
            'totalCourses' => $totalCourses,
            'totalCertificates' => $totalCertificates,
            'totalCertificateStatus' => $totalCertificateStatus,
        ]);
    }

    public function courses(): void
    {
        Auth::requireAdmin();
        $courseModel = new Course();
        $error = '';
        $success = isset($_GET['success']) ? 'Operação realizada com sucesso!' : '';

        // Pagination & Filtering
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $searchName = isset($_GET['search']) ? trim($_GET['search']) : '';
        $filterStatus = isset($_GET['filter_status']) ? trim($_GET['filter_status']) : '';
        
        $filters = [];
        if ($searchName) $filters['name'] = $searchName;
        if ($filterStatus) $filters['status'] = $filterStatus;
        
        $totalCourses = $courseModel->countFiltered($filters);
        $totalPages = ceil($totalCourses / $perPage);
        $courses = $courseModel->paginate($page, $perPage, $filters);

        if ($this->isPost()) {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $name = $this->getPostString('name');
            $description = $this->getPostString('description');
            $targetAudience = $this->getPostString('target_audience');
            $workload = $this->getPostString('workload');
            $instructor = $this->getPostString('instructor');
            $period = $this->getPostString('period');
            $date = $this->getPostString('date'); // YYYY-MM-DD
            $time = $this->getPostString('time');
            $location = $this->getPostString('location');
            $status = $this->getPostString('status');
            $allowEnrollment = isset($_POST['allow_enrollment']);
            $maxEnrollments = isset($_POST['max_enrollments']) ? (int)$_POST['max_enrollments'] : 0;

            if (empty($name) || empty($description)) {
                $error = 'Nome e descrição são obrigatórios.';
            } else {
                $coverImage = null;
                if ($id > 0) {
                    $existing = $courseModel->findById($id);
                    if ($existing) {
                        $coverImage = $existing['cover_image'];
                    }
                }

                // Handle Cover Image Upload
                if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['cover_image'];
                    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($file['tmp_name']);

                    if (!array_key_exists($mimeType, $allowed)) {
                        $error = 'Formato de imagem inválido. Use JPG, PNG ou WebP.';
                    } elseif ($file['size'] > COURSE_COVER_MAX_SIZE) {
                        $error = 'A imagem excede o tamanho máximo de 2MB.';
                    } else {
                        if (!is_dir(COURSE_COVER_PATH)) {
                            mkdir(COURSE_COVER_PATH, 0775, true);
                        }
                        $extension = $allowed[$mimeType];
                        $fileName = sha1_file($file['tmp_name']) . '_' . time() . '.' . $extension;
                        $targetPath = COURSE_COVER_PATH . '/' . $fileName;
                        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                            $error = 'Falha ao salvar a capa do curso';
                        } else {
                            if ($coverImage) {
                                $oldPath = COURSE_COVER_PATH . '/' . $coverImage;
                                if (is_file($oldPath)) {
                                    unlink($oldPath);
                                }
                            }
                            $coverImage = $fileName;
                        }
                    }
                }

                if ($error) {
                    $this->render('admin/courses_v2', [
                        'courses' => $courses,
                        'error' => $error,
                        'success' => $success,
                        'currentPage' => $page,
                        'totalPages' => $totalPages,
                        'searchName' => $searchName,
                        'filterStatus' => $filterStatus,
                        'totalCourses' => $totalCourses
                    ]);
                    return;
                }

                $data = [
                    'name' => $name,
                    'description' => $description,
                    'target_audience' => $targetAudience,
                    'workload' => $workload,
                    'instructor' => $instructor,
                    'period' => $period,
                    'date' => $date !== '' ? $date : null,
                    'time' => $time !== '' ? $time : null,
                    'location' => $location,
                    'cover_image' => $coverImage,
                    'status' => $status,
                    'allow_enrollment' => $allowEnrollment,
                    'max_enrollments' => $maxEnrollments,
                ];

                if ($id > 0) {
                    $courseModel->update($id, $data);
                    
                    $logModel = new Log();
                    $logModel->create('course_updated', "Curso atualizado: $name (ID: $id)", (int)Auth::user()['id']);
                } else {
                    $newId = $courseModel->create($data);
                    
                    $logModel = new Log();
                    $logModel->create('course_created', "Novo curso criado: $name (ID: $newId)", (int)Auth::user()['id']);
                }
                
                $this->redirect('admin/courses&success=1');
            }
        }

        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            if ($id > 0) {
                $course = $courseModel->findById($id);
                if ($course) {
                    $courseModel->delete($id);
                    
                    $logModel = new Log();
                    $logModel->create('course_deleted', "Curso removido: {$course['name']} (ID: $id)", (int)Auth::user()['id']);
                }
            }
            $this->redirect('admin/courses');
        }

        $this->render('admin/courses_v2', [
            'courses' => $courses,
            'error' => $error,
            'success' => $success,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'searchName' => $searchName,
            'filterStatus' => $filterStatus,
            'totalCourses' => $totalCourses
        ]);
    }

    public function candidates(): void
    {
        Auth::requireAdmin();
        $userModel = new User();
        $enrollmentModel = new Enrollment();

        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            if ($id > 0) {
                $candidate = $userModel->findById($id);
                if ($candidate && $candidate['role'] === 'candidate') {
                    $userModel->deleteCandidate($id);
                    
                    if (!empty($candidate['photo']) && file_exists(CANDIDATE_PHOTO_PATH . '/' . $candidate['photo'])) {
                        unlink(CANDIDATE_PHOTO_PATH . '/' . $candidate['photo']);
                    }

                    $logModel = new Log();
                    $logModel->create('candidate_deleted', "Candidato removido: {$candidate['name']} (ID: $id)", (int)Auth::user()['id']);
                    
                    $this->redirect('admin/candidates&success=Candidato removido com sucesso');
                    return;
                }
            }
            $this->redirect('admin/candidates');
            return;
        }

        $candidates = $userModel->listCandidates();
        $success = isset($_GET['success']) ? $_GET['success'] : '';

        $this->render('admin/candidates', [
            'candidates' => $candidates,
            'success' => $success
        ]);
    }

    public function createCandidate(): void
    {
        Auth::requireAdmin();
        $userModel = new User();
        $error = '';

        if ($this->isPost()) {
            $name = $this->getPostString('name');
            $username = $this->getPostString('username');
            $cpf = preg_replace('/[^0-9]/', '', $this->getPostString('cpf'));
            $email = $this->getPostString('email');
            $password = $this->getPostString('password');
            $confirmPassword = $this->getPostString('confirm_password');
            $phone = $this->getPostString('phone');
            $address = $this->getPostString('address');

            if (!$name || !$username || !$cpf || !$email || !$password || !$address) {
                $error = 'Preencha todos os campos obrigatórios';
            } elseif (strlen($name) < 3 || preg_match('/\d/', $name)) {
                $error = 'Nome inválido (mínimo 3 caracteres, sem números)';
            } elseif (preg_match('/\s/', $username)) {
                $error = 'O nome de usuário não pode conter espaços';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'E-mail inválido';
            } elseif (!$this->validateCpf($cpf)) {
                $error = 'CPF inválido';
            } elseif ($password !== $confirmPassword) {
                $error = 'As senhas não conferem';
            } elseif (strlen($password) < 6) {
                $error = 'A senha deve ter no mínimo 6 caracteres';
            } else {
                $checkEmail = $userModel->findByEmail($email);
                if ($checkEmail) {
                    $error = 'E-mail já está em uso';
                }

                $checkCpf = $userModel->findByCpf($cpf);
                if ($checkCpf) {
                    $error = 'CPF já está em uso';
                }

                $checkUsername = $userModel->findByUsername($username);
                if ($checkUsername) {
                    $error = 'Nome de usuário já está em uso';
                }

                if (!$error) {
                    $photoPath = null;
                    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
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
                                    $photoPath = $fileName;
                                } else {
                                    $error = 'Falha ao salvar a foto';
                                }
                            }
                        }
                    }

                    if (!$error) {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $id = $userModel->createCandidate($name, $cpf, $email, $passwordHash, $username, $phone, $address, $photoPath);
                        
                        $logModel = new Log();
                        $logModel->create('candidate_created_by_admin', "Novo candidato criado: $name (ID: $id)", (int)Auth::user()['id']);
                        
                        $this->redirect('admin/candidates&success=Candidato criado com sucesso');
                    }
                }
            }
        }

        $this->render('admin/candidate_create', [
            'error' => $error
        ]);
    }

    public function editCandidate(): void
    {
        Auth::requireAdmin();
        $userModel = new User();
        $error = '';
        $success = '';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $this->redirect('admin/candidates');
        }

        $candidate = $userModel->findById($id);
        if (!$candidate || $candidate['role'] !== 'candidate') {
            $this->redirect('admin/candidates');
        }

        if ($this->isPost()) {
            $name = $this->getPostString('name');
            $username = $this->getPostString('username');
            $cpf = preg_replace('/[^0-9]/', '', $this->getPostString('cpf'));
            $email = $this->getPostString('email');
            $phone = $this->getPostString('phone');
            $address = $this->getPostString('address');
            $newPassword = $this->getPostString('new_password');
            $forcePasswordChange = isset($_POST['force_password_change']);

            if (!$name || !$username || !$cpf || !$email || !$address) {
                $error = 'Preencha todos os campos obrigatórios';
            } elseif (strlen($name) < 3 || preg_match('/\d/', $name)) {
                $error = 'Nome inválido (mínimo 3 caracteres, sem números)';
            } elseif (preg_match('/\s/', $username)) {
                $error = 'O nome de usuário não pode conter espaços';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'E-mail inválido';
            } elseif (!$this->validateCpf($cpf)) {
                $error = 'CPF inválido';
            } else {
                $checkEmail = $userModel->findByEmail($email);
                if ($checkEmail && $checkEmail['id'] !== $id) {
                    $error = 'E-mail já está em uso por outro usuário';
                }

                $checkCpf = $userModel->findByCpf($cpf);
                if ($checkCpf && $checkCpf['id'] !== $id) {
                    $error = 'CPF já está em uso por outro usuário';
                }

                $checkUsername = $userModel->findByUsername($username);
                if ($checkUsername && $checkUsername['id'] !== $id) {
                    $error = 'Nome de usuário já está em uso';
                }

                if (!$error) {
                    $photoPath = $candidate['photo'];
                    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
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
                                    if ($photoPath && file_exists(CANDIDATE_PHOTO_PATH . '/' . $photoPath)) {
                                        unlink(CANDIDATE_PHOTO_PATH . '/' . $photoPath);
                                    }
                                    $photoPath = $fileName;
                                } else {
                                    $error = 'Falha ao salvar a foto';
                                }
                            }
                        }
                    }

                    if (!$error) {
                        $userModel->updateCandidateCompleto($id, $name, $cpf, $email, $username, $phone, $address, $photoPath);
                        
                        $logDetails = "Candidato atualizado: $username (ID: $id)";

                        if ($newPassword) {
                            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                            $userModel->updatePassword($id, $passwordHash, $forcePasswordChange);
                            $logDetails .= " - Senha redefinida";
                            
                            $subject = 'Senha Redefinida pelo Administrador';
                            $message = "Olá " . htmlspecialchars($name) . ",\n\n";
                            $message .= "Sua senha de acesso ao SGC foi redefinida pelo administrador.\n";
                            $message .= "Caso não reconheça esta ação, entre em contato com a instituição imediatamente.\n";
                            
                            Mailer::send($email, $subject, $message);
                        }

                        $logModel = new Log();
                        $logModel->create('candidate_updated_by_admin', $logDetails, (int)Auth::user()['id']);

                        $success = 'Dados atualizados com sucesso';
                        $candidate = $userModel->findById($id);
                    }
                }
            }
        }

        $this->render('admin/candidate_edit', [
            'candidate' => $candidate,
            'error' => $error,
            'success' => $success
        ]);
    }

    private function validateCpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }

    public function enrollments(): void
    {
        Auth::requireAdmin();
        $courseModel = new Course();
        $enrollmentModel = new Enrollment();
        $userModel = new User();

        // Handle Delete Enrollment
        if (isset($_GET['delete'])) {
            $deleteId = (int)$_GET['delete'];
            $courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
            
            if ($deleteId > 0) {
                $enrollment = $enrollmentModel->find($deleteId);
                if ($enrollment) {
                    // Log action
                    $user = $userModel->findById($enrollment['user_id']);
                    $course = $courseModel->findById($enrollment['course_id']);
                    $logModel = new Log();
                    $logModel->create('enrollment_deleted_by_admin', "Inscrição cancelada: {$user['name']} em {$course['name']}", (int)Auth::user()['id']);

                    $enrollmentModel->delete($deleteId);
                    $redirectUrl = 'admin/enrollments';
                    if ($courseId > 0) {
                        $redirectUrl .= '&course_id=' . $courseId;
                    }
                    $this->redirect($redirectUrl . '&success=Inscrição cancelada com sucesso');
                }
            }
        }

        // Handle Manual Enrollment
        if ($this->isPost() && isset($_GET['action']) && $_GET['action'] === 'create') {
            $userId = $this->getPostInt('user_id');
            $courseId = $this->getPostInt('course_id');

            if ($userId > 0 && $courseId > 0) {
                // Check if already enrolled
                if ($enrollmentModel->exists($userId, $courseId)) {
                    // Already enrolled, maybe redirect with message
                    $this->redirect('admin/enrollments&course_id=' . $courseId . '&error=Candidato já inscrito neste curso');
                } else {
                    $enrollmentModel->create($userId, $courseId);
                    
                    $course = $courseModel->findById($courseId);
                    $user = $userModel->findById($userId);
                    
                    $logModel = new Log();
                    $logModel->create('enrollment_created_by_admin', "Inscrição manual: {$user['name']} em {$course['name']}", (int)Auth::user()['id']);

                    $this->redirect('admin/enrollments&course_id=' . $courseId . '&success=Inscrição realizada com sucesso');
                }
            } else {
                 $this->redirect('admin/enrollments&error=Dados inválidos');
            }
            return;
        }

        $courses = $courseModel->all();
        $candidates = $userModel->listCandidates(); // For the modal
        $selectedCourseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
        $enrollments = [];

        if ($selectedCourseId > 0) {
            $enrollments = $enrollmentModel->listByCourse($selectedCourseId);
            
            if (isset($_GET['export'])) {
                $organizationModel = new Organization();
                $orgSettings = $organizationModel->getSettings();
                $course = $courseModel->find($selectedCourseId);

                if ($_GET['export'] === 'csv') {
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename="inscritos_curso_' . $selectedCourseId . '.csv"');
                    $output = fopen('php://output', 'w');
                    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel
                    
                    // Organization Header
                    if (!empty($orgSettings['organization_name'])) {
                        fputcsv($output, [$orgSettings['organization_name']], ';');
                    }
                    
                    $details = [];
                    if (!empty($orgSettings['address'])) $details[] = $orgSettings['address'];
                    if (!empty($orgSettings['city']) && !empty($orgSettings['state'])) $details[] = $orgSettings['city'] . ' - ' . $orgSettings['state'];
                    if (!empty($orgSettings['phone'])) $details[] = 'Tel: ' . $orgSettings['phone'];
                    if (!empty($orgSettings['email'])) $details[] = 'E-mail: ' . $orgSettings['email'];
                    if (!empty($orgSettings['cnpj'])) $details[] = 'CNPJ: ' . $orgSettings['cnpj'];
                    
                    if (!empty($details)) {
                        fputcsv($output, [implode(' | ', $details)], ';');
                    }
                    fputcsv($output, [], ';'); // Empty line

                    // Course Info
                    fputcsv($output, ['Curso: ' . $course['name']], ';');
                    fputcsv($output, ['Instrutor: ' . $course['instructor'], 'Período: ' . $course['period']], ';');
                    fputcsv($output, [], ';'); // Empty line

                    // Data Table
                    fputcsv($output, ['Nome', 'Email', 'CPF', 'Status'], ';');
                    foreach ($enrollments as $row) {
                        $status = match($row['status']) {
                            'certificate_available' => 'Certificado disponível',
                            'completed' => 'Concluído',
                            default => 'Inscrito'
                        };
                        fputcsv($output, [
                            $row['user_name'], 
                            $row['email'], 
                            $row['cpf'] ?? '', 
                            $status
                        ], ';');
                    }
                    fclose($output);
                    exit;
                } elseif ($_GET['export'] === 'pdf') {
                    $this->render('admin/enrollments_pdf', [
                        'course' => $course,
                        'enrollments' => $enrollments,
                        'orgSettings' => $orgSettings
                    ]);
                    return;
                }
            }
        }

        $this->render('admin/enrollments', [
            'courses' => $courses,
            'selectedCourseId' => $selectedCourseId,
            'enrollments' => $enrollments,
            'candidates' => $candidates
        ]);
    }

    public function certificates(): void
    {
        Auth::requireAdmin();
        $courseModel = new Course();
        $enrollmentModel = new Enrollment();
        $userModel = new User();
        $certificateModel = new Certificate();

        $courses = $courseModel->all();
        $candidates = $userModel->listCandidates();

        // AJAX request for courses by user
        if (isset($_GET['ajax']) && $_GET['ajax'] === 'courses' && isset($_GET['user_id'])) {
            $userId = (int)$_GET['user_id'];
            if ($userId > 0) {
                $enrolledCourses = $enrollmentModel->listByUser($userId);
                $result = array_map(function($item) {
                    return [
                        'id' => $item['course_id'],
                        'name' => $item['course_name']
                    ];
                }, $enrolledCourses);
                
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            }
            echo json_encode([]);
            exit;
        }

        $error = '';
        $success = '';

        if ($this->isPost()) {
            $userId = $this->getPostInt('user_id');
            $courseId = $this->getPostInt('course_id');

            if ($userId <= 0 || $courseId <= 0) {
                $error = 'Selecione candidato e curso';
            } else {
                $enrollment = $enrollmentModel->findByUserAndCourse($userId, $courseId);
                if (!$enrollment) {
                    $enrollmentId = $enrollmentModel->create($userId, $courseId);
                    $enrollment = $enrollmentModel->find($enrollmentId);
                } else {
                    $enrollmentId = (int)$enrollment['id'];
                }

                if (!$enrollment) {
                    $error = 'Inscrição não encontrada';
                } elseif (!isset($_FILES['certificate']) || $_FILES['certificate']['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Envio de arquivo inválido';
                } else {
                    $file = $_FILES['certificate'];
                    if ($file['size'] > CERTIFICATE_MAX_SIZE) {
                        $error = 'Arquivo excede o tamanho máximo permitido';
                    } else {
                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $finfo->file($file['tmp_name']);
                        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                        if ($extension !== 'pdf' || $mimeType !== 'application/pdf') {
                            $error = 'Apenas arquivos PDF são permitidos';
                        } else {
                            if (!is_dir(CERTIFICATE_PATH)) {
                                mkdir(CERTIFICATE_PATH, 0775, true);
                            }

                            $fileName = sha1_file($file['tmp_name']) . '_' . time() . '.pdf';
                            $targetPath = CERTIFICATE_PATH . '/' . $fileName;

                            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                                $error = 'Falha ao salvar o arquivo';
                            } else {
                                $certificateModel->create(
                                    $enrollmentId,
                                    $fileName,
                                    $file['name'],
                                    (int)$file['size'],
                                    $mimeType
                                );

                                $enrollmentModel->updateStatus($enrollmentId, 'certificate_available');

                                $user = $userModel->findById((int)$enrollment['user_id']);
                                if ($user) {
                                    Mailer::send($user['email'], 'Certificado disponível', 'Seu certificado está disponível no painel do candidato.');
                                }

                                $success = 'Certificado enviado com sucesso';
                            }
                        }
                    }
                }
            }
        }

        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            if ($id > 0) {
                $cert = $certificateModel->delete($id);
                if ($cert) {
                    $filePath = CERTIFICATE_PATH . '/' . $cert['file_name'];
                    if (is_file($filePath)) {
                        unlink($filePath);
                    }
                }
            }
            $this->redirect('admin/certificates');
        }

        $certificatesByUser = [];
        foreach ($candidates as $candidate) {
            $certificatesByUser[$candidate['id']] = $certificateModel->listByUser((int)$candidate['id']);
        }

        $candidatesById = [];
        foreach ($candidates as $candidate) {
            $candidatesById[$candidate['id']] = $candidate;
        }

        $this->render('admin/certificates', [
            'courses' => $courses,
            'candidates' => $candidates,
            'error' => $error,
            'success' => $success,
            'certificatesByUser' => $certificatesByUser,
            'candidatesById' => $candidatesById,
        ]);
    }

    public function migrate(): void
    {
        Auth::requireAdmin();
        $pdo = \App\Core\Database::getConnection();
        
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM courses LIKE 'max_enrollments'");
            $column = $stmt->fetch();

            if (!$column) {
                $pdo->exec("ALTER TABLE courses ADD COLUMN max_enrollments INT UNSIGNED DEFAULT 0 AFTER allow_enrollment");
                echo "<h3>Sucesso!</h3><p>Coluna 'max_enrollments' adicionada com sucesso.</p>";
            } else {
                echo "<h3>Info</h3><p>A coluna 'max_enrollments' já existe.</p>";
            }
            echo '<p><a href="index.php?r=admin/courses">Voltar para Cursos</a></p>';
        } catch (\PDOException $e) {
            echo "<h3>Erro</h3><p>" . $e->getMessage() . "</p>";
        }
    }
}
