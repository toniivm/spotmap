<?php
namespace SpotMap;

/**
 * Auth helper para validar JWT de Supabase.
 * Estrategia simple: llamar al endpoint /auth/v1/user.
 * En producción se puede cachear la respuesta del usuario por jti/sub.
 */
class Auth
{
    private static function base64UrlDecode(string $value): ?string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        return $decoded === false ? null : $decoded;
    }

    private static function verifyHs256Signature(string $headerPart, string $payloadPart, string $signaturePart, string $secret): bool
    {
        if ($secret === '') {
            return false;
        }

        $providedSignature = self::base64UrlDecode($signaturePart);
        if ($providedSignature === null) {
            return false;
        }

        $signingInput = $headerPart . '.' . $payloadPart;
        $expectedSignature = hash_hmac('sha256', $signingInput, $secret, true);
        return hash_equals($expectedSignature, $providedSignature);
    }

    public static function getBearerToken(): ?string
    {
        $hdr = $_SERVER['HTTP_AUTHORIZATION']
            ?? (function_exists('apache_request_headers') ? (apache_request_headers()['Authorization'] ?? '') : '')
            ?? (function_exists('getallheaders') ? (getallheaders()['Authorization'] ?? '') : '')
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (!str_starts_with($hdr, 'Bearer ')) {
            return null;
        }
        return substr($hdr, 7);
    }

    public static function requireUser(): array
    {
        $token = self::getBearerToken();
        if (!$token) {
            ApiResponse::unauthorized('Missing bearer token');
        }
        $user = self::fetchUser($token);
        if (!$user) {
            ApiResponse::unauthorized('Invalid or expired token');
        }

        // Resolver rol de forma robusta:
        // 1) role directo
        // 2) app_metadata/user_metadata
        // 3) tabla profiles (con token de usuario)
        // 4) fallback user
        $resolvedRole = null;

        if (isset($user['role']) && \SpotMap\Constants::isValidRole((string)$user['role'])) {
            $resolvedRole = (string)$user['role'];
        }

        if (!$resolvedRole) {
            $metaRole = $user['app_metadata']['role'] ?? $user['user_metadata']['role'] ?? null;
            if ($metaRole && \SpotMap\Constants::isValidRole((string)$metaRole)) {
                $resolvedRole = (string)$metaRole;
            }
        }

        if (!$resolvedRole && isset($user['id'])) {
            $profileRole = self::loadUserRole($user['id'], $token);
            if ($profileRole && \SpotMap\Constants::isValidRole((string)$profileRole)) {
                $resolvedRole = (string)$profileRole;
            }
        }

        // Break-glass admin override by explicit email allowlist.
        $email = strtolower(trim((string)($user['email'] ?? '')));
        $adminEmails = array_values(array_filter(array_map(
            static fn ($value) => strtolower(trim((string)$value)),
            explode(',', (string)Config::get('ADMIN_EMAILS', ''))
        )));
        if ($email !== '' && in_array($email, $adminEmails, true)) {
            $resolvedRole = 'admin';
        }

        $user['role'] = $resolvedRole ?? \SpotMap\Constants::DEFAULT_ROLE;
        
        return $user;
    }

    /**
     * Cargar rol del usuario desde tabla profiles
     */
    public static function loadUserRole(string $userId, ?string $userToken = null): ?string
    {
        try {
            // Intentar cargar desde Supabase
            if (\SpotMap\DatabaseAdapter::useSupabase()) {
                return \SpotMap\DatabaseAdapter::getProfileRole($userId, $userToken);
            }
        } catch (\Throwable $e) {
            error_log('[AUTH] Error cargando rol de Supabase: ' . $e->getMessage());
        }
        
        // Fallback: consultar BD local si existe
        return null;
    }

    public static function fetchUser(string $token): ?array
    {
        // Intentar validar contra Supabase primero
        $supabaseUrl = trim((string)Config::get('SUPABASE_URL', ''));
        $url = $supabaseUrl !== '' ? rtrim($supabaseUrl, '/') . '/auth/v1/user' : '';
        $service = (string)Config::get('SUPABASE_SERVICE_KEY', '');
        $anon = (string)Config::get('SUPABASE_ANON_KEY', '');
        $key = $service ?: $anon;
        
        if ($url && $key) {
            $headers = [
                'apikey: ' . $key,
                'Authorization: Bearer ' . $token,
                'Accept: application/json'
            ];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            
            if ($code === 200 && $resp) {
                return json_decode($resp, true) ?: null;
            }
            
            // Log para debugging
            if ($code !== 200) {
                error_log("[AUTH] Supabase validation failed: HTTP $code");
            }
            if ($err) {
                error_log("[AUTH] Supabase curl error: $err");
            }
        }
        
        // Fallback JWT local (solo para desarrollo o con secreto explícito)
        // JWT format: header.payload.signature
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            error_log("[AUTH] Invalid JWT format");
            return null;
        }

        $allowInsecureFallback = Config::getBool('ALLOW_INSECURE_JWT_FALLBACK', false);
        if (Config::isProd() && !$allowInsecureFallback) {
            error_log('[AUTH] Insecure JWT fallback disabled in production');
            return null;
        }
        
        // Decodificar header/payload
        try {
            $headerRaw = self::base64UrlDecode($parts[0]);
            $payloadRaw = self::base64UrlDecode($parts[1]);
            if ($headerRaw === null || $payloadRaw === null) {
                error_log('[AUTH] Invalid JWT base64 encoding');
                return null;
            }

            $header = json_decode($headerRaw, true);
            $payload = json_decode($payloadRaw, true);
            
            if (!$payload || !isset($payload['sub'])) {
                error_log("[AUTH] Invalid JWT payload");
                return null;
            }

            $alg = strtoupper((string)($header['alg'] ?? ''));
            if ($alg !== 'HS256') {
                error_log('[AUTH] Unsupported JWT alg in fallback mode');
                return null;
            }

            $jwtSecret = trim((string)Config::get('SUPABASE_JWT_SECRET', ''));
            if ($jwtSecret !== '') {
                if (!self::verifyHs256Signature($parts[0], $parts[1], $parts[2], $jwtSecret)) {
                    error_log('[AUTH] JWT signature validation failed');
                    return null;
                }
            } elseif (!$allowInsecureFallback) {
                error_log('[AUTH] JWT fallback requires SUPABASE_JWT_SECRET or ALLOW_INSECURE_JWT_FALLBACK=true');
                return null;
            }
            
            // Validar expiración si existe
            if (isset($payload['exp'])) {
                $now = time();
                if ($payload['exp'] < $now) {
                    error_log("[AUTH] JWT token expired: exp={$payload['exp']}, now={$now}");
                    return null;
                }
            }
            
            // Retornar usuario autenticado para modo fallback
            return [
                'id' => $payload['sub'],
                'email' => $payload['email'] ?? 'user@example.com',
                'user_metadata' => [
                    'name' => $payload['user_metadata']['name'] ?? 'User'
                ],
                'role' => 'authenticated',
                'aud' => $payload['aud'] ?? null
            ];
        } catch (\Throwable $e) {
            error_log("[AUTH] JWT decode error: " . $e->getMessage());
            return null;
        }
    }
}
