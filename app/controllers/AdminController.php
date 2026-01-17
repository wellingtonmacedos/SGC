<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

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

        $filterDate = isset($_GET['date']) ? trim((string)$_GET['date']) : '';
        $filterLocation = isset($_GET['location']) ? trim((string)$_GET['location']) : '';

        if ($this->isPost()) {
            $id = $this->getPostInt('id');
            $name = $this->getPostString('name');
            $description = $this->getPostString('description');
            $workload = $this->getPostInt('workload');
            $instructor = $this->getPostString('instructor');
            $period = $this->getPostString('period');
            $date = $this->getPostString('date');
            $time = $this->getPostString('time');
            $location = $this->getPostString('location');
            $status = $this->getPostString('status') === 'inactive' ? 'inactive' : 'active';
            $allowEnrollment = isset($_POST['allow_enrollment']) ? 1 : 0;

            if (!$name || !$description || !$instructor) {
                $error = 'Preencha os campos obrigatórios';
            } else {
                $coverImage = null;
                if ($id > 0) {
                    $existing = $courseModel->find($id);
                    if ($existing && isset($existing['cover_image'])) {
                        $coverImage = $existing['cover_image'];
                    }
                }

                if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['cover'];
                    if ($file['size'] > COURSE_COVER_MAX_SIZE) {
                        $error = 'Capa excede o tamanho máximo permitido';
                    } else {
                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $finfo->file($file['tmp_name']);
                        $allowed = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp',
                        ];
                        if (!isset($allowed[$mimeType])) {
                            $error = 'Apenas imagens JPG, PNG ou WEBP são permitidas para a capa';
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
                }

                if ($error) {
                    $courses = $courseModel->all();
                    $this->render('admin/courses', [
                        'courses' => $courses,
                        'error' => $error,
                    ]);
                    return;
                }

                $data = [
                    'name' => $name,
                    'description' => $description,
                    'workload' => $workload,
                    'instructor' => $instructor,
                    'period' => $period,
                    'date' => $date !== '' ? $date : null,
                    'time' => $time !== '' ? $time : null,
                    'location' => $location,
                    'cover_image' => $coverImage,
                    'status' => $status,
                    'allow_enrollment' => $allowEnrollment,
                ];

                if ($id > 0) {
                    $courseModel->update($id, $data);
                } else {
                    $courseModel->create($data);
                }
            }
        }

        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            if ($id > 0) {
                $courseModel->delete($id);
            }
            $this->redirect('admin/courses');
        }

        $courses = $courseModel->all();
        if ($filterDate !== '' || $filterLocation !== '') {
            $courses = array_values(array_filter($courses, static function (array $course) use ($filterDate, $filterLocation): bool {
                if ($filterDate !== '' && (!isset($course['date']) || $course['date'] !== $filterDate)) {
                    return false;
                }
                if ($filterLocation !== '') {
                    $location = isset($course['location']) ? (string)$course['location'] : '';
                    if ($location === '' || stripos($location, $filterLocation) === false) {
                        return false;
                    }
                }
                return true;
            }));
        }

        $this->render('admin/courses', [
            'courses' => $courses,
            'error' => $error,
            'filterDate' => $filterDate,
            'filterLocation' => $filterLocation,
        ]);
    }

    public function candidates(): void
    {
        Auth::requireAdmin();
        $userModel = new User();
        $enrollmentModel = new Enrollment();

        $candidates = $userModel->listCandidates();

        $this->render('admin/candidates', [
            'candidates' => $candidates,
        ]);
    }

    public function enrollments(): void
    {
        Auth::requireAdmin();
        $courseModel = new Course();
        $enrollmentModel = new Enrollment();

        $courses = $courseModel->all();
        $selectedCourseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
        $enrollments = [];

        if ($selectedCourseId > 0) {
            $enrollments = $enrollmentModel->listByCourse($selectedCourseId);
        }

        $this->render('admin/enrollments', [
            'courses' => $courses,
            'selectedCourseId' => $selectedCourseId,
            'enrollments' => $enrollments,
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
}
