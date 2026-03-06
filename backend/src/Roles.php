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

    /**
     * Cache entry shape:
     * [
     *   'role' => string,
     *   'expires_at' => int
     * ]
     */

    private static function isRoleCacheEnabled(): bool
    {
        return Config::getBool('ROLE_CACHE_ENABLED', true);
    }

    private static function getRoleCacheTtl(): int
    {
        $ttl = (int) Config::get('ROLE_CACHE_TTL', 300);
        return max(1, $ttl);
    }

    private static function getCachedRole(string $userId): ?string
    {
        if (!isset(self::$roleCache[$userId]) || !is_array(self::$roleCache[$userId])) {
            return null;
        }

        $entry = self::$roleCache[$userId];
        $role = $entry['role'] ?? null;
        $expiresAt = isset($entry['expires_at']) ? (int) $entry['expires_at'] : 0;

        if (!is_string($role) || !isset(self::$hierarchy[$role])) {
            unset(self::$roleCache[$userId]);
            return null;
        }

        if ($expiresAt > 0 && $expiresAt <= time()) {
            unset(self::$roleCache[$userId]);
            return null;
        }

        return $role;
    }

    private static function cacheRole(string $userId, string $role): void
    {
        self::$roleCache[$userId] = [
            'role' => $role,
            'expires_at' => time() + self::getRoleCacheTtl(),
        ];
    }

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
            if (self::isRoleCacheEnabled()) {
                $cachedRole = self::getCachedRole($userId);
                if ($cachedRole !== null) {
                    return $cachedRole;
                }
            }

            try {
                $profileRole = DatabaseAdapter::getProfileRole($userId);
                if ($profileRole && isset(self::$hierarchy[$profileRole])) {
                    if (self::isRoleCacheEnabled()) {
                        self::cacheRole($userId, $profileRole);
                    }
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

    public static function atLeast($roleOrUser, string $threshold): bool
    {
        $role = is_array($roleOrUser) ? self::getUserRole($roleOrUser) : (string) $roleOrUser;
        return (self::$hierarchy[$role] ?? 0) >= (self::$hierarchy[$threshold] ?? 999);
    }

    public static function clearRoleCache(?string $userId = null): void
    {
        if ($userId === null || $userId === '') {
            self::$roleCache = [];
            return;
        }

        unset(self::$roleCache[$userId]);
    }
}
