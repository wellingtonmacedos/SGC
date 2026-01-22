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
            // Also check if user is still active
            if (!array_key_exists('photo', $_SESSION['user']) || !array_key_exists('username', $_SESSION['user']) || !array_key_exists('status', $_SESSION['user'])) {
                $userModel = new User();
                $freshUser = $userModel->findById((int)$_SESSION['user']['id']);
                
                if ($freshUser) {
                    if (isset($freshUser['status']) && $freshUser['status'] === 'inactive') {
                        self::logout();
                        return null;
                    }
                    self::login($freshUser); // Update session with fresh data
                    return $_SESSION['user'];
                } else {
                    // User deleted?
                    self::logout();
                    return null;
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
            'status' => $user['status'] ?? 'active',
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
            if ($user['role'] === 'super_admin') {
                header('Location: index.php?r=superAdmin/dashboard');
            } elseif ($user['role'] === 'admin') {
                header('Location: index.php?r=admin/dashboard');
            } else {
                header('Location: index.php?r=candidate/dashboard');
            }
            exit;
        }
    }

    public static function requireSuperAdmin(): void
    {
        $user = self::user();
        if (!$user || $user['role'] !== 'super_admin') {
            header('Location: index.php?r=auth/login');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        $user = self::user();
        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'super_admin')) {
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

