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
        // Refresh user data from DB to get all fields
        $userModel = new User();
        $user = $userModel->findById((int)$user['id']);
        
        $error = '';
        $success = '';

        if ($this->isPost()) {
            $name = $this->getPostString('name');
            $email = $this->getPostString('email');
            $username = $this->getPostString('username');
            $phone = $this->getPostString('phone');
            $address = $this->getPostString('address');
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
                            $photoPath
                        );
                        
                        // Update session
                        $user['name'] = $name;
                        $user['email'] = $email;
                        $user['username'] = $username;
                        $user['photo'] = $photoPath;
                        Auth::login($user);
                        
                        $success = 'Perfil atualizado com sucesso!';
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
        $certificateModel = new Certificate();

        $filterDate = isset($_GET['date']) ? trim((string)$_GET['date']) : '';
        $filterSearch = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $error = '';

        if ($this->isPost() && isset($_POST['course_id'])) {
            $courseId = $this->getPostInt('course_id');
            if ($courseId > 0 && !$enrollmentModel->exists($user['id'], $courseId)) {
                $course = $courseModel->find($courseId);
                
                if ($course) {
                    $maxEnrollments = (int)($course['max_enrollments'] ?? 0);
                    // Check actual count from db to be safe
                    $currentCount = $enrollmentModel->countByCourse($courseId);
                    
                    if ($maxEnrollments > 0 && $currentCount >= $maxEnrollments) {
                        $error = 'As inscrições para este curso estão encerradas.';
                    } else {
                        $enrollmentModel->create($user['id'], $courseId);
                        // Redirect to avoid form resubmission and show success
                        $this->redirect('candidate/dashboard&section=enrollments#enrollments');
                    }
                }
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
            'error' => $error,
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
