<?php
/**
 * AuditLogger - Moderation Audit Trail Service
 * 
 * Immutable logging system for all moderation actions.
 * Provides accountability, compliance, and forensic tracking.
 * 
 * @package SpotMap\Core
 * @version 1.0.0
 */

class AuditLogger {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Log a moderation action with full context
     * 
     * @param string $moderatorId UUID of user performing action
     * @param string $action Action type (approve_spot, reject_spot, delete_comment, ban_user, etc.)
     * @param string $targetType Resource type (spot, comment, user, report)
     * @param string $targetId ID of the moderated resource
     * @param array|null $oldValue Previous state (will be JSON encoded)
     * @param array|null $newValue Updated state (will be JSON encoded)
     * @param string|null $reason Moderator justification
     * @param array|null $metadata Additional context (IP, user_agent, referrer, etc.)
     * @return int|false Log entry ID on success, false on failure
     */
    public function logModeration(
        string $moderatorId,
        string $action,
        string $targetType,
        string $targetId,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?string $reason = null,
        ?array $metadata = null
    ) {
        // Validate inputs
        if (empty($moderatorId) || empty($action) || empty($targetType) || empty($targetId)) {
            error_log("AuditLogger: Missing required fields");
            return false;
        }

        // Encode arrays to JSON
        $oldValueJson = $oldValue ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : null;
        $newValueJson = $newValue ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : null;
        $metadataJson = $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null;

        $sql = "INSERT INTO moderation_audit_log 
                (moderator_id, action, target_type, target_id, old_value, new_value, reason, metadata)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("AuditLogger: Failed to prepare statement - " . $this->db->error);
            return false;
        }

        $stmt->bind_param(
            'ssssssss',
            $moderatorId,
            $action,
            $targetType,
            $targetId,
            $oldValueJson,
            $newValueJson,
            $reason,
            $metadataJson
        );

        if ($stmt->execute()) {
            $insertId = $stmt->insert_id;
            $stmt->close();
            return $insertId;
        } else {
            error_log("AuditLogger: Failed to execute - " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    /**
     * Get audit log entries with filtering
     * 
     * @param array $filters Optional filters (moderator_id, action, target_type, date_from, date_to)
     * @param int $limit Maximum records to return
     * @param int $offset Pagination offset
     * @return array List of audit log entries
     */
    public function getLogs(array $filters = [], int $limit = 50, int $offset = 0): array {
        $whereConditions = [];
        $params = [];
        $types = '';

        // Build WHERE clause based on filters
        if (!empty($filters['moderator_id'])) {
            $whereConditions[] = "moderator_id = ?";
            $params[] = $filters['moderator_id'];
            $types .= 's';
        }

        if (!empty($filters['action'])) {
            $whereConditions[] = "action = ?";
            $params[] = $filters['action'];
            $types .= 's';
        }

        if (!empty($filters['target_type'])) {
            $whereConditions[] = "target_type = ?";
            $params[] = $filters['target_type'];
            $types .= 's';
        }

        if (!empty($filters['target_id'])) {
            $whereConditions[] = "target_id = ?";
            $params[] = $filters['target_id'];
            $types .= 's';
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "created_at >= ?";
            $params[] = $filters['date_from'];
            $types .= 's';
        }

        if (!empty($filters['date_to'])) {
            $whereConditions[] = "created_at <= ?";
            $params[] = $filters['date_to'];
            $types .= 's';
        }

        $whereClause = count($whereConditions) > 0 
            ? 'WHERE ' . implode(' AND ', $whereConditions)
            : '';

        $sql = "SELECT 
                    id,
                    moderator_id,
                    action,
                    target_type,
                    target_id,
                    old_value,
                    new_value,
                    reason,
                    metadata,
                    created_at
                FROM moderation_audit_log
                $whereClause
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("AuditLogger::getLogs - prepare failed: " . $this->db->error);
            return [];
        }

        // Add limit and offset to params
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        // Bind parameters dynamically
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            // Decode JSON fields
            $row['old_value'] = $row['old_value'] ? json_decode($row['old_value'], true) : null;
            $row['new_value'] = $row['new_value'] ? json_decode($row['new_value'], true) : null;
            $row['metadata'] = $row['metadata'] ? json_decode($row['metadata'], true) : null;
            $logs[] = $row;
        }

        $stmt->close();
        return $logs;
    }

    /**
     * Get total count of audit logs matching filters
     * 
     * @param array $filters Same filters as getLogs()
     * @return int Total record count
     */
    public function getCount(array $filters = []): int {
        $whereConditions = [];
        $params = [];
        $types = '';

        if (!empty($filters['moderator_id'])) {
            $whereConditions[] = "moderator_id = ?";
            $params[] = $filters['moderator_id'];
            $types .= 's';
        }

        if (!empty($filters['action'])) {
            $whereConditions[] = "action = ?";
            $params[] = $filters['action'];
            $types .= 's';
        }

        if (!empty($filters['target_type'])) {
            $whereConditions[] = "target_type = ?";
            $params[] = $filters['target_type'];
            $types .= 's';
        }

        if (!empty($filters['target_id'])) {
            $whereConditions[] = "target_id = ?";
            $params[] = $filters['target_id'];
            $types .= 's';
        }

        if (!empty($filters['date_from'])) {
            $whereConditions[] = "created_at >= ?";
            $params[] = $filters['date_from'];
            $types .= 's';
        }

        if (!empty($filters['date_to'])) {
            $whereConditions[] = "created_at <= ?";
            $params[] = $filters['date_to'];
            $types .= 's';
        }

        $whereClause = count($whereConditions) > 0 
            ? 'WHERE ' . implode(' AND ', $whereConditions)
            : '';

        $sql = "SELECT COUNT(*) as total FROM moderation_audit_log $whereClause";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("AuditLogger::getCount - prepare failed: " . $this->db->error);
            return 0;
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int)($row['total'] ?? 0);
    }

    /**
     * Get aggregated statistics by action type
     * 
     * @param string|null $dateFrom Optional start date filter
     * @param string|null $dateTo Optional end date filter
     * @return array Action counts grouped by type
     */
    public function getStatsByAction(?string $dateFrom = null, ?string $dateTo = null): array {
        $whereConditions = [];
        $params = [];
        $types = '';

        if ($dateFrom) {
            $whereConditions[] = "created_at >= ?";
            $params[] = $dateFrom;
            $types .= 's';
        }

        if ($dateTo) {
            $whereConditions[] = "created_at <= ?";
            $params[] = $dateTo;
            $types .= 's';
        }

        $whereClause = count($whereConditions) > 0 
            ? 'WHERE ' . implode(' AND ', $whereConditions)
            : '';

        $sql = "SELECT action, COUNT(*) as count
                FROM moderation_audit_log
                $whereClause
                GROUP BY action
                ORDER BY count DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("AuditLogger::getStatsByAction - prepare failed: " . $this->db->error);
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[$row['action']] = (int)$row['count'];
        }

        $stmt->close();
        return $stats;
    }

    /**
     * Get moderator activity leaderboard
     * 
     * @param int $limit Number of top moderators to return
     * @param string|null $dateFrom Optional start date filter
     * @param string|null $dateTo Optional end date filter
     * @return array List of moderators with action counts
     */
    public function getModeratorActivity(int $limit = 10, ?string $dateFrom = null, ?string $dateTo = null): array {
        $whereConditions = [];
        $params = [];
        $types = '';

        if ($dateFrom) {
            $whereConditions[] = "mal.created_at >= ?";
            $params[] = $dateFrom;
            $types .= 's';
        }

        if ($dateTo) {
            $whereConditions[] = "mal.created_at <= ?";
            $params[] = $dateTo;
            $types .= 's';
        }

        $whereClause = count($whereConditions) > 0 
            ? 'WHERE ' . implode(' AND ', $whereConditions)
            : '';

        $sql = "SELECT 
                    mal.moderator_id,
                    u.username,
                    u.full_name,
                    u.role,
                    COUNT(*) as total_actions,
                    MIN(mal.created_at) as first_action,
                    MAX(mal.created_at) as last_action
                FROM moderation_audit_log mal
                LEFT JOIN users u ON mal.moderator_id = u.id
                $whereClause
                GROUP BY mal.moderator_id, u.username, u.full_name, u.role
                ORDER BY total_actions DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("AuditLogger::getModeratorActivity - prepare failed: " . $this->db->error);
            return [];
        }

        $params[] = $limit;
        $types .= 'i';

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $activity = [];
        while ($row = $result->fetch_assoc()) {
            $activity[] = $row;
        }

        $stmt->close();
        return $activity;
    }
}
