<?php
/**
 * AuditController - API endpoints for moderation audit log access
 * 
 * Provides read-only access to audit trail data for admin users.
 * Supports filtering, pagination, and statistics.
 * 
 * @package SpotMap\Controllers
 * @version 1.0.0
 */

namespace SpotMap\Controllers;

require_once __DIR__ . '/../AuditLogger.php';
require_once __DIR__ . '/../ApiResponse.php';
require_once __DIR__ . '/../Roles.php';

use SpotMap\ApiResponse;
use SpotMap\Roles;
use AuditLogger;

class AuditController {
    private $db;
    private $auditLogger;

    public function __construct($db) {
        $this->db = $db;
        $this->auditLogger = new AuditLogger($db);
    }

    /**
     * GET /api/audit/logs - Get audit log entries (admin-only)
     * 
     * Query Parameters:
     * - moderator_id: Filter by moderator
     * - action: Filter by action type
     * - target_type: Filter by resource type
     * - target_id: Filter by specific resource
     * - date_from: Filter by start date (YYYY-MM-DD)
     * - date_to: Filter by end date (YYYY-MM-DD)
     * - limit: Records per page (default 50, max 200)
     * - offset: Pagination offset (default 0)
     */
    public function getLogs($user) {
        // Require admin role
        if (!Roles::atLeast($user, 'admin')) {
            ApiResponse::error('Forbidden: Admin access required', 403);
            return;
        }

        // Build filters from query params
        $filters = [];
        if (isset($_GET['moderator_id'])) $filters['moderator_id'] = $_GET['moderator_id'];
        if (isset($_GET['action'])) $filters['action'] = $_GET['action'];
        if (isset($_GET['target_type'])) $filters['target_type'] = $_GET['target_type'];
        if (isset($_GET['target_id'])) $filters['target_id'] = $_GET['target_id'];
        if (isset($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (isset($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];

        // Pagination
        $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 200) : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        // Fetch logs and count
        $logs = $this->auditLogger->getLogs($filters, $limit, $offset);
        $total = $this->auditLogger->getCount($filters);

        ApiResponse::success([
            'logs' => $logs,
            'pagination' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total
            ]
        ]);
    }

    /**
     * GET /api/audit/stats - Get audit statistics (admin-only)
     * 
     * Query Parameters:
     * - date_from: Start date (YYYY-MM-DD)
     * - date_to: End date (YYYY-MM-DD)
     */
    public function getStats($user) {
        if (!Roles::atLeast($user, 'admin')) {
            ApiResponse::error('Forbidden: Admin access required', 403);
            return;
        }

        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;

        $actionStats = $this->auditLogger->getStatsByAction($dateFrom, $dateTo);
        $moderatorActivity = $this->auditLogger->getModeratorActivity(20, $dateFrom, $dateTo);

        ApiResponse::success([
            'actions' => $actionStats,
            'top_moderators' => $moderatorActivity
        ]);
    }

    /**
     * GET /api/audit/moderator/{moderatorId} - Get specific moderator's audit trail (admin-only)
     */
    public function getModeratorHistory($user, $moderatorId) {
        if (!Roles::atLeast($user, 'admin')) {
            ApiResponse::error('Forbidden: Admin access required', 403);
            return;
        }

        $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 200) : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        $filters = ['moderator_id' => $moderatorId];
        $logs = $this->auditLogger->getLogs($filters, $limit, $offset);
        $total = $this->auditLogger->getCount($filters);

        ApiResponse::success([
            'moderator_id' => $moderatorId,
            'logs' => $logs,
            'total' => $total
        ]);
    }

    /**
     * GET /api/audit/resource/{targetType}/{targetId} - Get audit trail for specific resource (moderator+)
     */
    public function getResourceHistory($user, $targetType, $targetId) {
        if (!Roles::atLeast($user, 'moderator')) {
            ApiResponse::error('Forbidden: Moderator access required', 403);
            return;
        }

        $filters = [
            'target_type' => $targetType,
            'target_id' => $targetId
        ];

        $logs = $this->auditLogger->getLogs($filters, 100, 0);

        ApiResponse::success([
            'target_type' => $targetType,
            'target_id' => $targetId,
            'audit_trail' => $logs,
            'total' => count($logs)
        ]);
    }
}
