<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Models\User;
use App\Models\PasswordReset;

class AuthController extends Controller
{
    public function login(): void
    {
        Auth::requireGuest();
        $error = '';

        if ($this->isPost()) {
            $email = $this->getPostString('email');
            $password = $this->getPostString('password');

            $userModel = new User();
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password_hash'])) {
                Auth::login($user);
                if ($user['role'] === 'admin') {
                    $this->redirect('admin/dashboard');
                } else {
                    $this->redirect('candidate/dashboard');
                }
            } else {
                $error = 'Credenciais inválidas';
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
            $cpf = $this->getPostString('cpf');
            $email = $this->getPostString('email');
            $password = $this->getPostString('password');
            $confirmPassword = $this->getPostString('confirm_password');

            if (!$name || !$cpf || !$email || !$password) {
                $error = 'Preencha todos os campos obrigatórios';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'E-mail inválido';
            } elseif ($password !== $confirmPassword) {
                $error = 'As senhas não conferem';
            } else {
                $userModel = new User();
                if ($userModel->findByEmail($email) || $userModel->findByCpf($cpf)) {
                    $error = 'Já existe um usuário com este CPF ou e-mail';
                } else {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $userId = $userModel->createCandidate($name, $cpf, $email, $passwordHash);
                    $user = $userModel->findById($userId);

                    Mailer::send(MAIL_ADMIN_ADDRESS, 'Novo cadastro de candidato', 'Novo candidato cadastrado: ' . htmlspecialchars($name));
                    Mailer::send($email, 'Cadastro realizado com sucesso', 'Seu cadastro no SGC foi realizado com sucesso.');

                    $success = true;
                }
            }
        }

        $this->render('auth/register', [
            'error' => $error,
            'success' => $success,
        ]);
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
