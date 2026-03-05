<?php
namespace SpotMap\Controllers;

use SpotMap\Auth;
use SpotMap\ApiResponse;
use SpotMap\DatabaseAdapter;

class CommentsController
{
    public function list(int $spotId): void
    {
        try {
            if ($spotId <= 0) ApiResponse::error('Invalid spot', 400);
            $comments = DatabaseAdapter::commentsOf($spotId);
            ApiResponse::success(['comments' => $comments, 'count' => count($comments)]);
        } catch (\Throwable $e) {
            ApiResponse::serverError($e->getMessage());
        }
    }
    public function add(int $spotId): void
    {
        try {
            $user = Auth::requireUser();
            $token = Auth::getBearerToken();
            if ($spotId <= 0) ApiResponse::error('Invalid spot', 400);
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $body = trim($input['body'] ?? '');
            if ($body === '') ApiResponse::error('Empty body', 400);
            $res = DatabaseAdapter::addComment($user['id'], $spotId, $body, $token);
            if (isset($res['error'])) ApiResponse::error($res['error'], 400);
            ApiResponse::success($res, 'Comment added');
        } catch (\Throwable $e) {
            ApiResponse::serverError($e->getMessage());
        }
    }
    public function delete(int $commentId): void
    {
        try {
            $user = Auth::requireUser();
            $token = Auth::getBearerToken();
            if ($commentId <= 0) ApiResponse::error('Invalid comment', 400);
            $res = DatabaseAdapter::deleteComment($commentId, $token);
            if (isset($res['error'])) ApiResponse::error($res['error'], 400);
            ApiResponse::success($res, 'Comment deleted');
        } catch (\Throwable $e) {
            ApiResponse::serverError($e->getMessage());
        }
    }
}
