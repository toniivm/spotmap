<?php
namespace SpotMap\Controllers;

use SpotMap\Auth;
use SpotMap\ApiResponse;
use SpotMap\DatabaseAdapter;
use SpotMap\Cache;

class FavoritesController
{
    public function favorite(int $spotId): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();
        if ($spotId <= 0) ApiResponse::error('Invalid spot', 400);
        $res = DatabaseAdapter::favorite($user['id'], $spotId, $token);
        if (isset($res['error'])) ApiResponse::error($res['error'], 400);
        Cache::flushPattern('spots_*');
        ApiResponse::success($res, 'Favorited');
    }
    public function unfavorite(int $spotId): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();
        if ($spotId <= 0) ApiResponse::error('Invalid spot', 400);
        $res = DatabaseAdapter::unfavorite($user['id'], $spotId, $token);
        if (isset($res['error'])) ApiResponse::error($res['error'], 400);
        Cache::flushPattern('spots_*');
        ApiResponse::success($res, 'Unfavorited');
    }
    public function list(int $spotId): void
    {
        if ($spotId <= 0) ApiResponse::error('Invalid spot', 400);
        $list = DatabaseAdapter::favoritesOf($spotId);
        ApiResponse::success(['favorites' => $list, 'count' => count($list)]);
    }
}
