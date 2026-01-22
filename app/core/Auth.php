<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function user(): ?array
    {
        if (isset($_SESSION['user'])) {
            // Self-healing: Ensure photo and username are present in session
            if (!array_key_exists('photo', $_SESSION['user']) || !array_key_exists('username', $_SESSION['user'])) {
                $userModel = new User();
                $freshUser = $userModel->findById((int)$_SESSION['user']['id']);
                if ($freshUser) {
                    self::login($freshUser); // Update session with fresh data
                    return $_SESSION['user'];
                }
            }
            return $_SESSION['user'];
        }
        return null;
    }

    public static function login(array $user): void
    {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'photo' => $user['photo'] ?? null,
            'username' => $user['username'] ?? null,
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

