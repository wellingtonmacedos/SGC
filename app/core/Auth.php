<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function user(): ?array
    {
        if (isset($_SESSION['last_activity']) && is_int($_SESSION['last_activity'])) {
            if (defined('SESSION_IDLE_TIMEOUT_SECONDS') && SESSION_IDLE_TIMEOUT_SECONDS > 0) {
                if (time() - $_SESSION['last_activity'] > SESSION_IDLE_TIMEOUT_SECONDS) {
                    self::logout();
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_regenerate_id(true);
                    }
                    return null;
                }
            }
        }
        $_SESSION['last_activity'] = time();

        if (defined('SESSION_REGENERATE_SECONDS') && SESSION_REGENERATE_SECONDS > 0) {
            if (!isset($_SESSION['session_regenerated_at']) || !is_int($_SESSION['session_regenerated_at'])) {
                $_SESSION['session_regenerated_at'] = time();
            } elseif (time() - $_SESSION['session_regenerated_at'] > SESSION_REGENERATE_SECONDS) {
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }
                $_SESSION['session_regenerated_at'] = time();
            }
        }

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
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['last_activity'] = time();
        $_SESSION['session_regenerated_at'] = time();

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'photo' => $user['photo'] ?? null,
            'username' => $user['username'] ?? null,
            'status' => $user['status'] ?? 'active',
            'force_password_change' => $user['force_password_change'] ?? 0,
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        unset($_SESSION['last_activity'], $_SESSION['session_regenerated_at']);
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
