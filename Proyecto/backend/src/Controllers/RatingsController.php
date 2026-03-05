<?php
namespace SpotMap\Controllers;

use SpotMap\Auth;
use SpotMap\ApiResponse;
use SpotMap\DatabaseAdapter;

class RatingsController
{
    public function rate(int $spotId): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();
        if ($spotId <= 0) ApiResponse::error('Invalid spot', 400);
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $score = (int)($input['score'] ?? 0);
        if ($score < 1 || $score > 5) ApiResponse::error('Score must be 1-5', 400);
        $res = DatabaseAdapter::rate($user['id'], $spotId, $score, $token);
        if (isset($res['error'])) ApiResponse::error($res['error'], 400);
        ApiResponse::success($res, 'Rated');
    }
    public function aggregate(int $spotId): void
    {
        if ($spotId <= 0) ApiResponse::error('Invalid spot', 400);
        $agg = DatabaseAdapter::ratingAggregate($spotId);
        ApiResponse::success($agg);
    }
}
