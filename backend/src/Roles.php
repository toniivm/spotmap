<?php
namespace SpotMap;

class Roles
{
    // Allowed roles hierarchy
    private static array $hierarchy = [
        'user' => 1,
        'moderator' => 5,
        'admin' => 10,
    ];

    private static array $roleCache = [];

    public static function getUserRole(array $user): string
    {
        $email = strtolower(trim((string)($user['email'] ?? '')));
        $adminEmails = array_values(array_filter(array_map(
            static fn ($value) => strtolower(trim((string)$value)),
            explode(',', (string)Config::get('ADMIN_EMAILS', ''))
        )));
        if ($email !== '' && in_array($email, $adminEmails, true)) {
            return 'admin';
        }

        if (isset($user['role']) && isset(self::$hierarchy[$user['role']])) {
            return $user['role'];
        }
        $metaRole = $user['app_metadata']['role'] ?? $user['user_metadata']['role'] ?? null;
        if ($metaRole && isset(self::$hierarchy[$metaRole])) {
            return $metaRole;
        }

        $userId = $user['id'] ?? '';
        if ($userId !== '') {
            if (isset(self::$roleCache[$userId])) {
                return self::$roleCache[$userId];
            }
            try {
                $profileRole = DatabaseAdapter::getProfileRole($userId);
                if ($profileRole && isset(self::$hierarchy[$profileRole])) {
                    self::$roleCache[$userId] = $profileRole;
                    return $profileRole;
                }
            } catch (\Throwable $e) {
                // Ignore lookup errors and fall back to user
            }
        }

        return 'user';
    }

    public static function requireRole(array $user, array $allowed): void
    {
        $role = self::getUserRole($user);
        if (!in_array($role, $allowed, true)) {
            ApiResponse::unauthorized('Insufficient role');
        }
    }

    public static function atLeast(string $role, string $threshold): bool
    {
        return (self::$hierarchy[$role] ?? 0) >= (self::$hierarchy[$threshold] ?? 999);
    }
}
