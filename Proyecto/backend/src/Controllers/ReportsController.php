<?php
namespace SpotMap\Controllers;

use SpotMap\Auth;
use SpotMap\Roles;
use SpotMap\ApiResponse;
use SpotMap\DatabaseAdapter;

class ReportsController
{
    public function report(int $spotId): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();
        if ($spotId <= 0) ApiResponse::error('Invalid spot', 400);
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $reason = trim($input['reason'] ?? '');
        if ($reason === '') ApiResponse::error('Empty reason', 400);
        $res = DatabaseAdapter::report($user['id'], $spotId, $reason, $token);
        if (isset($res['error'])) ApiResponse::error($res['error'], 400);
        ApiResponse::success($res, 'Report submitted');
    }
    public function list(): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();
        $role = Roles::getUserRole($user);
        if (!in_array($role, ['moderator','admin'])) ApiResponse::unauthorized('Moderator only');
        $status = $_GET['status'] ?? 'pending';
        $reports = DatabaseAdapter::listReports($status, $token);
        ApiResponse::success(['reports' => $reports, 'count' => count($reports)]);
    }
    public function moderate(int $reportId): void
    {
        $user = Auth::requireUser();
        $token = Auth::getBearerToken();
        $role = Roles::getUserRole($user);
        if (!in_array($role, ['moderator','admin'])) ApiResponse::unauthorized('Moderator only');
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $status = $input['status'] ?? '';
        $note = $input['note'] ?? null;
        if (!in_array($status, ['approved','hidden','rejected'])) ApiResponse::error('Invalid status', 400);
        $res = DatabaseAdapter::moderateReport($reportId, $status, $user['id'], $note, $token);
        if (isset($res['error'])) ApiResponse::error($res['error'], 400);
        ApiResponse::success($res, 'Report moderated');
    }
}
