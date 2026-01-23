<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Models\User;
use App\Models\PasswordReset;
use App\Models\Log;

class AuthController extends Controller
{
    public function login(): void
    {
        Auth::requireGuest();
        $error = '';

        if ($this->isPost()) {
            $identifier = $this->getPostString('identifier');
            $password = $this->getPostString('password');

            $userModel = new User();
            $user = $userModel->findByIdentifier($identifier);

            if ($user && password_verify($password, $user['password_hash'])) {
                if (isset($user['status']) && $user['status'] === 'inactive') {
                    $error = 'Usuário inativo. Entre em contato com o administrador.';
                } else {
                    Auth::login($user);
                    
                    // Log Login Success
                    $logModel = new Log();
                    $logModel->create('login', 'Login realizado com sucesso', (int)$user['id']);
                    
                    if (isset($user['force_password_change']) && $user['force_password_change']) {
                        $this->redirect('candidate/change-password');
                    }

                    if ($user['role'] === 'super_admin') {
                        $this->redirect('superAdmin/dashboard');
                    } elseif ($user['role'] === 'admin') {
                        $this->redirect('admin/dashboard');
                    } else {
                        $this->redirect('candidate/dashboard');
                    }
                }
            } else {
                $error = 'Credenciais inválidas';
                // Log Login Failure (Optional: might spam DB, but requested)
                // Need to be careful not to log passwords.
                // Since we don't have user ID, we log null.
                $logModel = new Log();
                $logModel->create('login_failed', "Tentativa de login falha para identificador: " . substr($identifier, 0, 50), null);
            }
        }

        $this->render('auth/login', ['error' => $error]);
    }

    public function register(): void
    {
        Auth::requireGuest();
        $error = '';
        $success = false;

        if ($this->isPost()) {
            $name = $this->getPostString('name');
            $username = $this->getPostString('username');
            $cpf = preg_replace('/[^0-9]/', '', $this->getPostString('cpf'));
            $email = $this->getPostString('email');
            $phone = $this->getPostString('phone');
            $address = $this->getPostString('address');
            $password = $this->getPostString('password');
            $confirmPassword = $this->getPostString('confirm_password');

            // Validations
            if (!$name || !$username || !$cpf || !$email || !$address || !$password) {
                $error = 'Preencha todos os campos obrigatórios';
            } elseif (strlen($name) < 3 || preg_match('/\d/', $name)) {
                $error = 'Nome inválido (mínimo 3 caracteres, sem números)';
            } elseif (preg_match('/\s/', $username)) {
                $error = 'O nome de usuário não pode conter espaços';
            } elseif (!$this->validateCpf($cpf)) {
                $error = 'CPF inválido';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'E-mail inválido';
            } elseif ($password !== $confirmPassword) {
                $error = 'As senhas não conferem';
            } else {
                $userModel = new User();
                if ($userModel->findByEmail($email)) {
                    $error = 'Já existe um usuário com este e-mail';
                } elseif ($userModel->findByCpf($cpf)) {
                    $error = 'Já existe um usuário com este CPF';
                } elseif ($userModel->findByUsername($username)) {
                    $error = 'Este nome de usuário já está em uso';
                } else {
                    // Photo Upload
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
                        $userId = $userModel->createCandidate(
                            $name, 
                            $cpf, 
                            $email, 
                            $passwordHash,
                            $username,
                            $phone,
                            $address,
                            $photoPath
                        );
                        
                        // Mailer calls...
                        Mailer::send(MAIL_ADMIN_ADDRESS, 'Novo cadastro de candidato', 'Novo candidato cadastrado: ' . htmlspecialchars($name));
                        Mailer::send($email, 'Cadastro realizado com sucesso', 'Seu cadastro no SGC foi realizado com sucesso.');

                        $success = true;
                    }
                }
            }
        }

        $this->render('auth/register', [
            'error' => $error,
            'success' => $success,
        ]);
    }

    private function validateCpf(string $cpf): bool
    {
        // Extract numbers only
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        // Check for repeated digits
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Calculate verification digits
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

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('auth/login');
    }

    public function forgot(): void
    {
        Auth::requireGuest();
        $error = '';
        $success = false;

        if ($this->isPost()) {
            $email = $this->getPostString('email');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Informe um e-mail válido';
            } else {
                $userModel = new User();
                $user = $userModel->findByEmail($email);
                if (!$user) {
                    $error = 'Nenhum usuário encontrado com este e-mail';
                } else {
                    $token = bin2hex(random_bytes(32));
                    $expiresAt = date('Y-m-d H:i:s', time() + 3600);

                    $resetModel = new PasswordReset();
                    $resetModel->deleteByUser((int)$user['id']);
                    $resetModel->create((int)$user['id'], $token, $expiresAt);

                    $baseUrl = APP_URL !== '' ? rtrim(APP_URL, '/') : '';
                    if ($baseUrl === '' && isset($_SERVER['HTTP_HOST'])) {
                        $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https';
                        $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
                    }
                    $link = $baseUrl . '/index.php?r=auth/reset&token=' . urlencode($token);

                    Mailer::send($user['email'], 'Recuperação de senha', 'Para redefinir sua senha, acesse: ' . $link);
                    $success = true;
                }
            }
        }

        $this->render('auth/forgot', [
            'error' => $error,
            'success' => $success,
        ]);
    }

    public function reset(): void
    {
        Auth::requireGuest();
        $token = isset($_GET['token']) ? (string)$_GET['token'] : '';
        $error = '';
        $success = false;

        if ($token === '') {
            $error = 'Token inválido';
            $this->render('auth/reset', [
                'error' => $error,
                'success' => $success,
                'token' => $token,
            ]);
            return;
        }

        $resetModel = new PasswordReset();
        $reset = $resetModel->findValidByToken($token);
        if (!$reset) {
            $error = 'Token expirado ou inválido';
            $this->render('auth/reset', [
                'error' => $error,
                'success' => $success,
                'token' => $token,
            ]);
            return;
        }

        if ($this->isPost()) {
            $password = $this->getPostString('password');
            $confirmPassword = $this->getPostString('confirm_password');

            if (!$password) {
                $error = 'Informe a nova senha';
            } elseif ($password !== $confirmPassword) {
                $error = 'As senhas não conferem';
            } else {
                $userModel = new User();
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $userModel->updatePassword((int)$reset['user_id'], $passwordHash);

                $resetModel->deleteById((int)$reset['id']);
                $success = true;
            }
        }

        $this->render('auth/reset', [
            'error' => $error,
            'success' => $success,
            'token' => $token,
        ]);
    }
}
