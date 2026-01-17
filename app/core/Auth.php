<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function user(): ?array
    {
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }

    public static function login(array $user): void
    {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
    }

    public static function requireGuest(): void
    {
        $user = self::user();
        if ($user) {
            if ($user['role'] === 'admin') {
                header('Location: index.php?r=admin/dashboard');
            } else {
                header('Location: index.php?r=candidate/dashboard');
            }
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        $user = self::user();
        if (!$user || $user['role'] !== 'admin') {
            header('Location: index.php?r=auth/login');
            exit;
        }
    }

    public static function requireCandidate(): void
    {
        $user = self::user();
        if (!$user || $user['role'] !== 'candidate') {
            header('Location: index.php?r=auth/login');
            exit;
        }
    }

    public static function ensureAdminUserExists(): void
    {
        $userModel = new User();
        $userModel->ensureDefaultAdmin();
    }
}

