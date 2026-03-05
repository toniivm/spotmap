<?php
namespace SpotMap\Controllers;

use SpotMap\Auth;
use SpotMap\ApiResponse;
use SpotMap\DatabaseAdapter;
use SpotMap\Validator;
use SpotMap\Constants;

class NotificationController
{
    /**
     * Helper: Make a REST request to Supabase notifications table
     */
    private static function supabaseRequest(string $method, string $endpoint, $body = null, ?string $userToken = null): array
    {
        $supabaseUrl = $_ENV['SUPABASE_URL'] ?? '';
        $supabaseKey = $_ENV['SUPABASE_SERVICE_KEY'] ?? $_ENV['SUPABASE_ANON_KEY'] ?? '';
        
        $url = rtrim($supabaseUrl, '/') . '/rest/v1/notifications' . $endpoint;
        $token = $userToken ?: $supabaseKey;
        
        $headers = [
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'data' => json_decode($response, true) ?? []
        ];
    }
    /**
     * Get user notifications
     * GET /api/notifications?page=1&limit=20&unread_only=false
     */
    public function index(): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();

        $page = max(1, filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $limit = min(50, max(1, filter_var($_GET['limit'] ?? 20, FILTER_VALIDATE_INT) ?: 20));
        $unreadOnly = filter_var($_GET['unread_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $offset = ($page - 1) * $limit;

        if (!DatabaseAdapter::useSupabase()) {
            ApiResponse::error('Notifications require Supabase backend', 400);
        }

        $supabaseUrl = $_ENV['SUPABASE_URL'] ?? '';
        $supabaseKey = $_ENV['SUPABASE_SERVICE_KEY'] ?? $_ENV['SUPABASE_ANON_KEY'] ?? '';
        
        $params = [
            'user_id=eq.' . urlencode($user['sub']),
            'order=created_at.desc',
            'limit=' . $limit,
            'offset=' . $offset
        ];
        
        if ($unreadOnly) {
            $params[] = 'is_read=eq.false';
        }

        $url = rtrim($supabaseUrl, '/') . '/rest/v1/notifications?' . implode('&', $params);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            ApiResponse::error($error['message'] ?? 'Failed to fetch notifications', 500);
        }

        $notifications = json_decode($response, true) ?? [];

        ApiResponse::success([
            'notifications' => $notifications,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    /**
     * Get unread notification count
     * GET /api/notifications/unread-count
     */
    public function unreadCount(): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();

        if (!DatabaseAdapter::useSupabase()) {
            ApiResponse::success(['count' => 0]);
        }

        $supabaseUrl = $_ENV['SUPABASE_URL'] ?? '';
        $supabaseKey = $_ENV['SUPABASE_SERVICE_KEY'] ?? $_ENV['SUPABASE_ANON_KEY'] ?? '';
        
        $url = rtrim($supabaseUrl, '/') . '/rest/v1/notifications?user_id=eq.' . urlencode($user['sub']) . '&is_read=eq.false&select=id';
        
        $headers = [];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) return $len;
                $headers[strtolower(trim($header[0]))] = trim($header[1]);
                return $len;
            }
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Prefer: count=exact'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            ApiResponse::error('Failed to count notifications', 500);
        }

        // Extract count from content-range header (format: "0-9/total")
        $count = 0;
        if (isset($headers['content-range'])) {
            if (preg_match('/\/(\d+)$/', $headers['content-range'], $matches)) {
                $count = (int)$matches[1];
            }
        }

        ApiResponse::success(['count' => $count]);
    }

    /**
     * Mark notification as read
     * PATCH /api/notifications/:id/read
     */
    public function markAsRead(int $id): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();

        if ($id <= 0) {
            ApiResponse::error('Invalid notification ID', 400);
        }

        if (!DatabaseAdapter::useSupabase()) {
            ApiResponse::error('Notifications require Supabase backend', 400);
        }

        // Verify ownership first
        $check = self::supabaseRequest('GET', "?id=eq.{$id}&select=user_id", null, $token);
        
        if ($check['status'] >= 400 || empty($check['data'])) {
            ApiResponse::error('Notification not found', 404);
        }

        if ($check['data'][0]['user_id'] !== $user['sub']) {
            ApiResponse::unauthorized('Cannot modify someone else\'s notification');
        }

        // Mark as read
        $result = self::supabaseRequest('PATCH', "?id=eq.{$id}", ['is_read' => true, 'read_at' => date('c')], $token);

        if ($result['status'] >= 400) {
            ApiResponse::error('Failed to update notification', 500);
        }

        ApiResponse::success(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read
     * POST /api/notifications/mark-all-read
     */
    public function markAllAsRead(): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();

        if (!DatabaseAdapter::useSupabase()) {
            ApiResponse::error('Notifications require Supabase backend', 400);
        }

        $userId = urlencode($user['sub']);
        $result = self::supabaseRequest('PATCH', "?user_id=eq.{$userId}&is_read=eq.false", ['is_read' => true, 'read_at' => date('c')], $token);

        if ($result['status'] >= 400) {
            ApiResponse::error('Failed to update notifications', 500);
        }

        ApiResponse::success(['message' => 'All notifications marked as read']);
    }

    /**
     * Delete notification
     * DELETE /api/notifications/:id
     */
    public function delete(int $id): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();

        if ($id <= 0) {
            ApiResponse::error('Invalid notification ID', 400);
        }

        if (!DatabaseAdapter::useSupabase()) {
            ApiResponse::error('Notifications require Supabase backend', 400);
        }

        // Verify ownership
        $check = self::supabaseRequest('GET', "?id=eq.{$id}&select=user_id", null, $token);
        
        if ($check['status'] >= 400 || empty($check['data'])) {
            ApiResponse::error('Notification not found', 404);
        }

        if ($check['data'][0]['user_id'] !== $user['sub']) {
            ApiResponse::unauthorized('Cannot delete someone else\'s notification');
        }

        // Delete
        $result = self::supabaseRequest('DELETE', "?id=eq.{$id}", null, $token);

        if ($result['status'] >= 400) {
            ApiResponse::error('Failed to delete notification', 500);
        }

        ApiResponse::success(['message' => 'Notification deleted']);
    }

    /**
     * Create a notification (internal use only - called by other controllers)
     * @param string $userId User UUID
     * @param string $type Notification type (spot_approved, spot_rejected, etc.)
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array|null $data Additional metadata
     * @return bool Success status
     */
    public static function createNotification(
        string $userId,
        string $type,
        string $title,
        string $message,
        ?array $data = null
    ): bool {
        if (!DatabaseAdapter::useSupabase()) {
            return false;
        }

        try {
            $payload = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'is_read' => false
            ];
            
            $result = self::supabaseRequest('POST', '', [$payload]);

            return $result['status'] < 400;
        } catch (\Exception $e) {
            error_log("Failed to create notification: " . $e->getMessage());
            return false;
        }
    }
}
