-- Migration: Add moderation_audit_log table
-- Version: 1.1.0
-- Date: 2025
-- Purpose: Implement comprehensive audit trail for all moderation actions

-- Create moderation_audit_log table (idempotent)
CREATE TABLE IF NOT EXISTS moderation_audit_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  moderator_id CHAR(36) NOT NULL COMMENT 'User performing moderation action',
  action VARCHAR(50) NOT NULL COMMENT 'Action: approve_spot, reject_spot, delete_comment, ban_user, etc.',
  target_type VARCHAR(50) NOT NULL COMMENT 'Resource type: spot, comment, user, report',
  target_id VARCHAR(100) NOT NULL COMMENT 'ID of the moderated resource',
  old_value TEXT COMMENT 'Previous state (JSON)',
  new_value TEXT COMMENT 'Updated state (JSON)',
  reason TEXT COMMENT 'Moderator justification for the action',
  metadata JSON COMMENT 'Additional context (IP, user_agent, referrer, etc.)',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (moderator_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_moderator_id (moderator_id),
  INDEX idx_action (action),
  INDEX idx_target (target_type, target_id),
  INDEX idx_created_at (created_at),
  INDEX idx_action_created (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Immutable audit trail for all moderation actions';

-- Verify table was created successfully
SELECT 
    CONCAT('✅ Migration successful: moderation_audit_log table ',
           IF(COUNT(*) > 0, 'exists', 'FAILED TO CREATE')) AS status
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
  AND table_name = 'moderation_audit_log';
