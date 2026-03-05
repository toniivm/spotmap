<?php
namespace SpotMap\Controllers;

use SpotMap\ApiResponse;
use SpotMap\Database;
use SpotMap\Security;

/**
 * OAuthController - Gestiona autenticación con redes sociales
 * Soporta: Google, Facebook, Instagram, Twitter/X
 */
class OAuthController
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::pdo();
    }

    /**
     * Iniciar flujo OAuth con Google
     */
    public function loginWithGoogle()
    {
        try {
            $clientId = getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID';
            $redirectUri = $this->getRedirectUri('google');
            
            $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'openid email profile',
                'state' => $this->generateState(),
            ]);

            return ApiResponse::success('OAuth redirect', ['url' => $authUrl]);
        } catch (\Exception $e) {
            return ApiResponse::error('Google OAuth init failed: ' . $e->getMessage());
        }
    }

    /**
     * Callback de Google OAuth
     */
    public function googleCallback()
    {
        try {
            $code = $_GET['code'] ?? null;
            if (!$code) {
                return ApiResponse::error('Authorization code missing');
            }

            // Intercambiar código por token
            $token = $this->exchangeCodeForToken('google', $code);
            
            // Obtener información del usuario
            $userInfo = $this->getUserInfoGoogle($token);
            
            // Crear o actualizar usuario
            $user = $this->upsertUser($userInfo, 'google');
            
            return ApiResponse::success('Login successful', [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['full_name'],
                'provider' => 'google'
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Google callback failed: ' . $e->getMessage());
        }
    }

    /**
     * Iniciar flujo OAuth con Facebook
     */
    public function loginWithFacebook()
    {
        try {
            $appId = getenv('FACEBOOK_APP_ID') ?: 'YOUR_FACEBOOK_APP_ID';
            $redirectUri = $this->getRedirectUri('facebook');
            
            $authUrl = 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query([
                'client_id' => $appId,
                'redirect_uri' => $redirectUri,
                'scope' => 'email,public_profile',
                'state' => $this->generateState(),
            ]);

            return ApiResponse::success('OAuth redirect', ['url' => $authUrl]);
        } catch (\Exception $e) {
            return ApiResponse::error('Facebook OAuth init failed: ' . $e->getMessage());
        }
    }

    /**
     * Callback de Facebook OAuth
     */
    public function facebookCallback()
    {
        try {
            $code = $_GET['code'] ?? null;
            if (!$code) {
                return ApiResponse::error('Authorization code missing');
            }

            $token = $this->exchangeCodeForToken('facebook', $code);
            $userInfo = $this->getUserInfoFacebook($token);
            $user = $this->upsertUser($userInfo, 'facebook');
            
            return ApiResponse::success('Login successful', [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['full_name'],
                'provider' => 'facebook'
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Facebook callback failed: ' . $e->getMessage());
        }
    }

    /**
     * Iniciar flujo OAuth con Twitter/X
     */
    public function loginWithTwitter()
    {
        try {
            $clientId = getenv('TWITTER_CLIENT_ID') ?: 'YOUR_TWITTER_CLIENT_ID';
            $redirectUri = $this->getRedirectUri('twitter');
            $state = $this->generateState();
            $_SESSION['oauth_state'] = $state;
            
            $authUrl = 'https://twitter.com/i/oauth2/authorize?' . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'tweet.read users.read follows.read follows.write',
                'state' => $state,
                'code_challenge' => bin2hex(random_bytes(32)),
                'code_challenge_method' => 'plain',
            ]);

            return ApiResponse::success('OAuth redirect', ['url' => $authUrl]);
        } catch (\Exception $e) {
            return ApiResponse::error('Twitter OAuth init failed: ' . $e->getMessage());
        }
    }

    /**
     * Callback de Twitter/X OAuth
     */
    public function twitterCallback()
    {
        try {
            $code = $_GET['code'] ?? null;
            if (!$code) {
                return ApiResponse::error('Authorization code missing');
            }

            $token = $this->exchangeCodeForToken('twitter', $code);
            $userInfo = $this->getUserInfoTwitter($token);
            $user = $this->upsertUser($userInfo, 'twitter');
            
            return ApiResponse::success('Login successful', [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['full_name'],
                'provider' => 'twitter'
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Twitter callback failed: ' . $e->getMessage());
        }
    }

    /**
     * Iniciar flujo OAuth con Instagram
     */
    public function loginWithInstagram()
    {
        try {
            $appId = getenv('INSTAGRAM_APP_ID') ?: 'YOUR_INSTAGRAM_APP_ID';
            $redirectUri = $this->getRedirectUri('instagram');
            
            $authUrl = 'https://api.instagram.com/oauth/authorize?' . http_build_query([
                'client_id' => $appId,
                'redirect_uri' => $redirectUri,
                'scope' => 'user_profile',
                'response_type' => 'code',
                'state' => $this->generateState(),
            ]);

            return ApiResponse::success('OAuth redirect', ['url' => $authUrl]);
        } catch (\Exception $e) {
            return ApiResponse::error('Instagram OAuth init failed: ' . $e->getMessage());
        }
    }

    /**
     * Callback de Instagram OAuth
     */
    public function instagramCallback()
    {
        try {
            $code = $_GET['code'] ?? null;
            if (!$code) {
                return ApiResponse::error('Authorization code missing');
            }

            $token = $this->exchangeCodeForToken('instagram', $code);
            $userInfo = $this->getUserInfoInstagram($token);
            $user = $this->upsertUser($userInfo, 'instagram');
            
            return ApiResponse::success('Login successful', [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['full_name'],
                'provider' => 'instagram'
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Instagram callback failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Intercambiar código por token
     */
    private function exchangeCodeForToken(string $provider, string $code): string
    {
        $endpoints = [
            'google' => 'https://oauth2.googleapis.com/token',
            'facebook' => 'https://graph.facebook.com/v18.0/oauth/access_token',
            'twitter' => 'https://twitter.com/2/oauth2/token',
            'instagram' => 'https://graph.instagram.com/v18.0/access_token',
        ];

        $clientSecret = getenv(strtoupper($provider) . '_CLIENT_SECRET');
        if (!$clientSecret) {
            throw new \Exception("Missing ${provider} client secret");
        }

        $ch = curl_init($endpoints[$provider]);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'code' => $code,
                'client_id' => getenv(strtoupper($provider) . '_CLIENT_ID'),
                'client_secret' => $clientSecret,
                'redirect_uri' => $this->getRedirectUri($provider),
                'grant_type' => 'authorization_code',
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['access_token'] ?? throw new \Exception("Failed to get access token: $response");
    }

    /**
     * Helper: Obtener info de usuario desde Google
     */
    private function getUserInfoGoogle(string $token): array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return [
            'provider_id' => $response['id'] ?? null,
            'email' => $response['email'] ?? null,
            'full_name' => $response['name'] ?? null,
            'avatar_url' => $response['picture'] ?? null,
        ];
    }

    /**
     * Helper: Obtener info de usuario desde Facebook
     */
    private function getUserInfoFacebook(string $token): array
    {
        $ch = curl_init('https://graph.facebook.com/v18.0/me?fields=id,name,email,picture');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return [
            'provider_id' => $response['id'] ?? null,
            'email' => $response['email'] ?? null,
            'full_name' => $response['name'] ?? null,
            'avatar_url' => $response['picture']['data']['url'] ?? null,
        ];
    }

    /**
     * Helper: Obtener info de usuario desde Twitter/X
     */
    private function getUserInfoTwitter(string $token): array
    {
        $ch = curl_init('https://api.twitter.com/2/users/me?user.fields=created_at,description,profile_image_url,verified');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $user = $response['data'] ?? [];
        return [
            'provider_id' => $user['id'] ?? null,
            'email' => null, // Twitter no proporciona email en v2
            'full_name' => $user['name'] ?? null,
            'avatar_url' => $user['profile_image_url'] ?? null,
        ];
    }

    /**
     * Helper: Obtener info de usuario desde Instagram
     */
    private function getUserInfoInstagram(string $token): array
    {
        $ch = curl_init('https://graph.instagram.com/me?fields=id,username,name,picture');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return [
            'provider_id' => $response['id'] ?? null,
            'email' => null, // Instagram no proporciona email
            'full_name' => $response['username'] ?? null,
            'avatar_url' => $response['picture']['data']['url'] ?? null,
        ];
    }

    /**
     * Crear o actualizar usuario desde datos OAuth
     */
    private function upsertUser(array $userInfo, string $provider): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE provider = ? AND provider_id = ? LIMIT 1'
        );
        $stmt->execute([$provider, $userInfo['provider_id']]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            // Actualizar usuario existente
            $update = $this->db->prepare(
                'UPDATE users SET avatar_url = ?, updated_at = NOW() WHERE id = ?'
            );
            $update->execute([$userInfo['avatar_url'], $existingUser['id']]);
            return $existingUser;
        }

        // Crear nuevo usuario
        $userId = bin2hex(random_bytes(18));
        $username = $this->generateUsername($userInfo['full_name']);
        
        $insert = $this->db->prepare(
            'INSERT INTO users (id, username, email, full_name, avatar_url, provider, provider_id, is_verified, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1)'
        );
        
        $insert->execute([
            $userId,
            $username,
            $userInfo['email'],
            $userInfo['full_name'],
            $userInfo['avatar_url'],
            $provider,
            $userInfo['provider_id'],
        ]);

        return [
            'id' => $userId,
            'username' => $username,
            'email' => $userInfo['email'],
            'full_name' => $userInfo['full_name'],
            'avatar_url' => $userInfo['avatar_url'],
        ];
    }

    /**
     * Helper: Generar URI de redirección
     */
    private function getRedirectUri(string $provider): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "{$protocol}://{$host}/backend/public/api.php?action=oauth_callback&provider={$provider}";
    }

    /**
     * Helper: Generar estado CSRF
     */
    private function generateState(): string
    {
        $state = bin2hex(random_bytes(32));
        $_SESSION['oauth_state'] = $state;
        return $state;
    }

    /**
     * Helper: Generar nombre de usuario único
     */
    private function generateUsername(string $name): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]/i', '', substr($name, 0, 20)));
        $username = $base ?: 'user';
        
        $counter = 1;
        while ($this->userExists($username)) {
            $username = $base . $counter++;
        }
        
        return $username;
    }

    /**
     * Helper: Verificar si usuario existe
     */
    private function userExists(string $username): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch()['count'] > 0;
    }
}
