<?php
namespace SpotMap\Controllers;

use SpotMap\Auth;
use SpotMap\ApiResponse;
use SpotMap\DatabaseAdapter;
use SpotMap\Config;

class AccountController
{
    // Export user data summary
    public function export(): void
    {
        $user = Auth::requireUser();
        if (!DatabaseAdapter::useSupabase()) {
            ApiResponse::error('Export requires Supabase backend', 400);
        }
        $client = DatabaseAdapter::getClient();
        $uid = $user['id'];
        $data = [
            'user_id' => $uid,
            'email' => $user['email'] ?? null,
            'favorites' => $client->userFavorites($uid),
            'comments' => $client->userComments($uid),
            'ratings' => $client->userRatings($uid),
            'reports' => $client->userReports($uid),
            // Spots owned (if ownership enabled)
            'spots_owned' => [],
        ];
        if (Config::get('OWNERSHIP_ENABLED')) {
            // naive fetch of all spots then filter; for scale a dedicated endpoint/view needed
            $all = $client->getAllSpots(500,0,[]);
            $owned = array_values(array_filter($all, fn($s)=>($s['user_id']??null)===$uid));
            $data['spots_owned'] = $owned;
        }
        ApiResponse::success($data);
    }

    // Delete user-related data (logical delete; actual Supabase auth deletion handled externally)
    public function delete(): void
    {
        $user = Auth::requireUser();
        if (!DatabaseAdapter::useSupabase()) {
            ApiResponse::error('Delete requires Supabase backend', 400);
        }
        $client = DatabaseAdapter::getClient();
        $uid = $user['id'];
        $result = $client->bulkDeleteUserData($uid);
        ApiResponse::success(['user_id' => $uid, 'deleted' => $result], 'User data deleted');
    }
}
