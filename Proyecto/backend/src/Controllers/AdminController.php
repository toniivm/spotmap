<?php
namespace SpotMap\Controllers;

use SpotMap\Auth;
use SpotMap\Roles;
use SpotMap\ApiResponse;
use SpotMap\DatabaseAdapter;
use SpotMap\SupabaseClient;
use SpotMap\Config;

class AdminController
{
    public function stats(): void
    {
        $user = Auth::requireUser();
        $role = Roles::getUserRole($user);
        if (!in_array($role, ['moderator','admin'])) ApiResponse::unauthorized('Moderator only');
        if (!DatabaseAdapter::useSupabase()) {
            ApiResponse::error('Stats require Supabase backend', 400);
        }
        $client = DatabaseAdapter::getClient(); // SupabaseClient
        $data = [
            'spots_total' => $client->countSpots(),
            'favorites_total' => $client->countTable('favorites'),
            'comments_total' => $client->countTable('comments'),
            'ratings_total' => $client->countTable('ratings'),
            'reports_pending' => $client->countReportsByStatus('pending'),
            'average_rating_global' => $client->averageRatingAll(),
            'timestamp' => time(),
            'env' => Config::get('ENV')
        ];
        ApiResponse::success($data);
    }

    public function pendingSpots(): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();
        $role = Roles::getUserRole($user);
        if (!in_array($role, ['moderator','admin'])) ApiResponse::unauthorized('Moderator only');
        $page = max(1, filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $limit = min(100, max(1, filter_var($_GET['limit'] ?? 50, FILTER_VALIDATE_INT) ?: 50));
        $offset = ($page - 1) * $limit;

        $result = DatabaseAdapter::listSpotsByStatus('pending', $limit, $offset, $token);
        if (isset($result['error'])) {
            ApiResponse::error($result['error'], 500);
        }
        ApiResponse::success(["spots" => $result['spots'], "total" => $result['total']]);
    }

    public function approveSpot(int $id): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();
        $role = Roles::getUserRole($user);
        if (!in_array($role, ['moderator','admin'])) ApiResponse::unauthorized('Moderator only');
        if ($id <= 0) ApiResponse::error('Invalid ID', 400);

        if (!DatabaseAdapter::useSupabase()) {
            $result = DatabaseAdapter::updateSpot($id, ['status' => 'approved'], $token);
            if (isset($result['error'])) {
                ApiResponse::error($result['error'], 500);
            }
            ApiResponse::success($result, 'Spot approved');
        }

        // Get spot details via REST API
        $supabaseUrl = $_ENV['SUPABASE_URL'] ?? '';
        $supabaseKey = $_ENV['SUPABASE_SERVICE_KEY'] ?? $_ENV['SUPABASE_ANON_KEY'] ?? '';
        
        $url = rtrim($supabaseUrl, '/') . '/rest/v1/spots?id=eq.' . $id . '&select=id,user_id,title';
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
            ApiResponse::error('Spot not found', 404);
        }

        $spots = json_decode($response, true);
        if (empty($spots)) {
            ApiResponse::error('Spot not found', 404);
        }
        
        $spotData = $spots[0];
        
        // Update spot status
        $result = DatabaseAdapter::updateSpot($id, ['status' => 'approved'], $token);
        if (isset($result['error'])) {
            ApiResponse::error($result['error'], 500);
        }

        // Create notification for spot owner
        NotificationController::createNotification(
            $spotData['user_id'],
            'spot_approved',
            '✅ Spot aprobado',
            'Tu spot "' . htmlspecialchars($spotData['title']) . '" ha sido aprobado y ahora es visible para todos.',
            ['spot_id' => $id, 'moderator_id' => ($user['id'] ?? $user['sub'] ?? null)]
        );

        ApiResponse::success($result, 'Spot approved');
    }

    public function rejectSpot(int $id): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();
        $role = Roles::getUserRole($user);
        if (!in_array($role, ['moderator','admin'])) ApiResponse::unauthorized('Moderator only');
        if ($id <= 0) ApiResponse::error('Invalid ID', 400);

        if (!DatabaseAdapter::useSupabase()) {
            $result = DatabaseAdapter::updateSpot($id, ['status' => 'rejected'], $token);
            if (isset($result['error'])) {
                ApiResponse::error($result['error'], 500);
            }
            ApiResponse::success($result, 'Spot rejected');
        }

        // Get spot details via REST API
        $supabaseUrl = $_ENV['SUPABASE_URL'] ?? '';
        $supabaseKey = $_ENV['SUPABASE_SERVICE_KEY'] ?? $_ENV['SUPABASE_ANON_KEY'] ?? '';
        
        $url = rtrim($supabaseUrl, '/') . '/rest/v1/spots?id=eq.' . $id . '&select=id,user_id,title';
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
            ApiResponse::error('Spot not found', 404);
        }

        $spots = json_decode($response, true);
        if (empty($spots)) {
            ApiResponse::error('Spot not found', 404);
        }

        $spotData = $spots[0];

        // Update spot status
        $result = DatabaseAdapter::updateSpot($id, ['status' => 'rejected'], $token);
        if (isset($result['error'])) {
            ApiResponse::error($result['error'], 500);
        }

        // Create notification for spot owner
        NotificationController::createNotification(
            $spotData['user_id'],
            'spot_rejected',
            '❌ Spot rechazado',
            'Tu spot "' . htmlspecialchars($spotData['title']) . '" no ha sido aprobado. Por favor, revisa las normas de la comunidad.',
            ['spot_id' => $id, 'moderator_id' => ($user['id'] ?? $user['sub'] ?? null)]
        );

        ApiResponse::success($result, 'Spot rejected');
    }
}
