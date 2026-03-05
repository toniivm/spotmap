<?php
// auth-login.php - Endpoint de login local (fallback cuando Supabase no está disponible)

require __DIR__ . '/../src/Config.php';
require __DIR__ . '/../src/Database.php';

use SpotMap\Database;

header('Content-Type: application/json');

function base64UrlEncode(string $value): string {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $mode = strtolower(trim((string)($data['mode'] ?? 'login')));
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $name = trim((string)($data['name'] ?? ''));
    
    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Email y contraseña requeridos']);
        exit;
    }

    if (!in_array($mode, ['login', 'register'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Modo inválido']);
        exit;
    }
    
    $user = null;
    try {
        Database::init();
        $pdo = Database::pdo();

        // Buscar usuario (compatibilidad con esquemas viejos sin role/password_hash)
        try {
            $stmt = $pdo->prepare("SELECT id, email, username, full_name, role, password_hash FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $legacySelectError) {
            $stmt = $pdo->prepare("SELECT id, email, username, full_name FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($user) {
                $user['role'] = 'user';
                $user['password_hash'] = '';
            }
        }

        if ($mode === 'register') {
            if (strlen($password) < 6) {
                http_response_code(422);
                echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
                exit;
            }

            if ($user) {
                http_response_code(409);
                echo json_encode(['error' => 'El email ya está registrado']);
                exit;
            }

            $userId = 'local-' . uniqid();
            $displayName = $name !== '' ? $name : explode('@', $email)[0];
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (id, email, username, full_name, password_hash, role, provider, is_verified)
                 VALUES (?, ?, ?, ?, ?, 'user', 'local', true)"
            );
            $stmt->execute([$userId, $email, $email, $displayName, $passwordHash]);

            $user = [
                'id' => $userId,
                'email' => $email,
                'username' => $email,
                'full_name' => $displayName,
                'role' => 'user',
            ];
        } else {
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'Credenciales inválidas']);
                exit;
            }

            $storedHash = (string)($user['password_hash'] ?? '');
            if ($storedHash !== '' && !password_verify($password, $storedHash)) {
                http_response_code(401);
                echo json_encode(['error' => 'Credenciales inválidas']);
                exit;
            }

            if ($storedHash === '') {
                try {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$newHash, $user['id']]);
                } catch (\Throwable $hashError) {
                    error_log('[AUTH-LOGIN] Hash upgrade failed: ' . $hashError->getMessage());
                }
            }
        }
    } catch (\Throwable $dbError) {
        error_log('[AUTH-LOGIN] DB fallback: ' . $dbError->getMessage());
        $displayName = explode('@', $email)[0];
        $user = [
            'id' => 'local-' . uniqid(),
            'email' => $email,
            'username' => $email,
            'full_name' => $displayName,
            'role' => 'user',
        ];
    }

    $userRole = (string)($user['role'] ?? 'user');
    
    // Retornar JWT local simple (sin firma real) para compatibilidad con Auth::fetchUser fallback
    $header = [
        'alg' => 'HS256',
        'typ' => 'JWT'
    ];
    $payload = [
        'sub' => $user['id'],
        'email' => $user['email'],
        'role' => $userRole,
        'exp' => time() + 86400,
        'aud' => 'authenticated',
        'user_metadata' => [
            'name' => $user['full_name'] ?? ($user['username'] ?? $user['email']),
        ],
    ];
    $token = base64UrlEncode(json_encode($header))
        . '.' . base64UrlEncode(json_encode($payload))
        . '.localdev';
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'session' => [
            'access_token' => $token,
            'expires_at' => $payload['exp'],
            'user' => $user
        ]
    ]);
    
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
