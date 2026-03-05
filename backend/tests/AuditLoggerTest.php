<?php
/**
 * AuditLoggerTest - Unit tests for moderation audit trail
 * 
 * Tests logging, retrieval, filtering, and statistics for audit system.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/AuditLogger.php';

class AuditLoggerTest extends TestCase {
    private $db;
    private $auditLogger;

    protected function setUp(): void {
        // Create in-memory SQLite database for testing
        $this->db = new SQLite3(':memory:');
        
        // Create moderation_audit_log table
        $this->db->exec("
            CREATE TABLE moderation_audit_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                moderator_id TEXT NOT NULL,
                action TEXT NOT NULL,
                target_type TEXT NOT NULL,
                target_id TEXT NOT NULL,
                old_value TEXT,
                new_value TEXT,
                reason TEXT,
                metadata TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create users table for joins
        $this->db->exec("
            CREATE TABLE users (
                id TEXT PRIMARY KEY,
                username TEXT,
                full_name TEXT,
                role TEXT
            )
        ");

        $this->auditLogger = new AuditLogger($this->db);
    }

    protected function tearDown(): void {
        $this->db->close();
    }

    public function testLogModerationSuccess() {
        $moderatorId = 'mod-123';
        $action = 'approve_spot';
        $targetType = 'spot';
        $targetId = '456';
        $oldValue = ['status' => 'pending'];
        $newValue = ['status' => 'approved'];
        $reason = 'Content meets quality standards';
        $metadata = ['ip' => '127.0.0.1', 'user_agent' => 'PHPUnit'];

        $logId = $this->auditLogger->logModeration(
            $moderatorId,
            $action,
            $targetType,
            $targetId,
            $oldValue,
            $newValue,
            $reason,
            $metadata
        );

        $this->assertIsInt($logId);
        $this->assertGreaterThan(0, $logId);

        // Verify record was inserted
        $result = $this->db->query("SELECT * FROM moderation_audit_log WHERE id = $logId");
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        $this->assertEquals($moderatorId, $row['moderator_id']);
        $this->assertEquals($action, $row['action']);
        $this->assertEquals($targetType, $row['target_type']);
        $this->assertEquals($targetId, $row['target_id']);
        $this->assertEquals(json_encode($oldValue, JSON_UNESCAPED_UNICODE), $row['old_value']);
        $this->assertEquals(json_encode($newValue, JSON_UNESCAPED_UNICODE), $row['new_value']);
        $this->assertEquals($reason, $row['reason']);
        $this->assertEquals(json_encode($metadata, JSON_UNESCAPED_UNICODE), $row['metadata']);
    }

    public function testLogModerationWithMinimalData() {
        $logId = $this->auditLogger->logModeration(
            'mod-789',
            'delete_comment',
            'comment',
            '999'
        );

        $this->assertIsInt($logId);
        $this->assertGreaterThan(0, $logId);

        $result = $this->db->query("SELECT * FROM moderation_audit_log WHERE id = $logId");
        $row = $result->fetchArray(SQLITE3_ASSOC);

        $this->assertNull($row['old_value']);
        $this->assertNull($row['new_value']);
        $this->assertNull($row['reason']);
        $this->assertNull($row['metadata']);
    }

    public function testLogModerationValidationFailsWithEmptyFields() {
        $result = $this->auditLogger->logModeration('', 'action', 'type', 'id');
        $this->assertFalse($result);

        $result = $this->auditLogger->logModeration('mod', '', 'type', 'id');
        $this->assertFalse($result);

        $result = $this->auditLogger->logModeration('mod', 'action', '', 'id');
        $this->assertFalse($result);

        $result = $this->auditLogger->logModeration('mod', 'action', 'type', '');
        $this->assertFalse($result);
    }

    public function testGetLogsReturnsAllRecords() {
        // Insert test data
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '1');
        $this->auditLogger->logModeration('mod-2', 'reject_spot', 'spot', '2');
        $this->auditLogger->logModeration('mod-1', 'delete_comment', 'comment', '3');

        $logs = $this->auditLogger->getLogs([], 100, 0);

        $this->assertCount(3, $logs);
        $this->assertEquals('delete_comment', $logs[0]['action']); // Most recent first
        $this->assertEquals('reject_spot', $logs[1]['action']);
        $this->assertEquals('approve_spot', $logs[2]['action']);
    }

    public function testGetLogsWithActionFilter() {
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '1');
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '2');
        $this->auditLogger->logModeration('mod-2', 'reject_spot', 'spot', '3');

        $logs = $this->auditLogger->getLogs(['action' => 'approve_spot'], 100, 0);

        $this->assertCount(2, $logs);
        $this->assertEquals('approve_spot', $logs[0]['action']);
        $this->assertEquals('approve_spot', $logs[1]['action']);
    }

    public function testGetLogsWithModeratorFilter() {
        $this->auditLogger->logModeration('mod-alpha', 'approve_spot', 'spot', '1');
        $this->auditLogger->logModeration('mod-beta', 'reject_spot', 'spot', '2');
        $this->auditLogger->logModeration('mod-alpha', 'delete_comment', 'comment', '3');

        $logs = $this->auditLogger->getLogs(['moderator_id' => 'mod-alpha'], 100, 0);

        $this->assertCount(2, $logs);
        $this->assertEquals('mod-alpha', $logs[0]['moderator_id']);
        $this->assertEquals('mod-alpha', $logs[1]['moderator_id']);
    }

    public function testGetLogsWithTargetFilter() {
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '100');
        $this->auditLogger->logModeration('mod-2', 'edit_spot', 'spot', '100');
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '200');

        $logs = $this->auditLogger->getLogs(
            ['target_type' => 'spot', 'target_id' => '100'],
            100,
            0
        );

        $this->assertCount(2, $logs);
        $this->assertEquals('100', $logs[0]['target_id']);
        $this->assertEquals('100', $logs[1]['target_id']);
    }

    public function testGetLogsPagination() {
        // Insert 10 records
        for ($i = 1; $i <= 10; $i++) {
            $this->auditLogger->logModeration("mod-$i", 'action', 'type', (string)$i);
        }

        // Get first page (5 records)
        $page1 = $this->auditLogger->getLogs([], 5, 0);
        $this->assertCount(5, $page1);

        // Get second page (5 records)
        $page2 = $this->auditLogger->getLogs([], 5, 5);
        $this->assertCount(5, $page2);

        // Ensure no overlap
        $this->assertNotEquals($page1[0]['id'], $page2[0]['id']);
    }

    public function testGetCountReturnsCorrectTotal() {
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '1');
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '2');
        $this->auditLogger->logModeration('mod-2', 'reject_spot', 'spot', '3');

        $total = $this->auditLogger->getCount([]);
        $this->assertEquals(3, $total);

        $filteredCount = $this->auditLogger->getCount(['action' => 'approve_spot']);
        $this->assertEquals(2, $filteredCount);
    }

    public function testGetStatsByActionGroupsCorrectly() {
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '1');
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '2');
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '3');
        $this->auditLogger->logModeration('mod-2', 'reject_spot', 'spot', '4');
        $this->auditLogger->logModeration('mod-2', 'delete_comment', 'comment', '5');

        $stats = $this->auditLogger->getStatsByAction();

        $this->assertArrayHasKey('approve_spot', $stats);
        $this->assertArrayHasKey('reject_spot', $stats);
        $this->assertArrayHasKey('delete_comment', $stats);
        
        $this->assertEquals(3, $stats['approve_spot']);
        $this->assertEquals(1, $stats['reject_spot']);
        $this->assertEquals(1, $stats['delete_comment']);
    }

    public function testGetModeratorActivityReturnsTopModerators() {
        // Insert users
        $this->db->exec("
            INSERT INTO users (id, username, full_name, role) VALUES
            ('mod-1', 'alice', 'Alice Moderator', 'moderator'),
            ('mod-2', 'bob', 'Bob Admin', 'admin')
        ");

        // Log activity
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '1');
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '2');
        $this->auditLogger->logModeration('mod-1', 'approve_spot', 'spot', '3');
        $this->auditLogger->logModeration('mod-2', 'reject_spot', 'spot', '4');

        $activity = $this->auditLogger->getModeratorActivity(10);

        $this->assertCount(2, $activity);
        
        // First moderator should be mod-1 (most active)
        $this->assertEquals('mod-1', $activity[0]['moderator_id']);
        $this->assertEquals('alice', $activity[0]['username']);
        $this->assertEquals(3, $activity[0]['total_actions']);
        
        // Second should be mod-2
        $this->assertEquals('mod-2', $activity[1]['moderator_id']);
        $this->assertEquals(1, $activity[1]['total_actions']);
    }

    public function testJsonEncodingPreservesUnicode() {
        $moderatorId = 'mod-unicode';
        $oldValue = ['title' => 'Café français'];
        $newValue = ['title' => 'Café español'];
        $reason = 'Corregir idioma: español ñ';

        $logId = $this->auditLogger->logModeration(
            $moderatorId,
            'edit_spot',
            'spot',
            '999',
            $oldValue,
            $newValue,
            $reason
        );

        $logs = $this->auditLogger->getLogs(['moderator_id' => $moderatorId], 1, 0);
        
        $this->assertEquals('Café français', $logs[0]['old_value']['title']);
        $this->assertEquals('Café español', $logs[0]['new_value']['title']);
        $this->assertEquals('Corregir idioma: español ñ', $logs[0]['reason']);
    }
}
