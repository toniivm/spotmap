-- ============================================
-- NOTIFICATIONS TABLE
-- ============================================
-- Stores system notifications for users (spot approvals, rejections, etc.)

CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) NOT NULL,
  type VARCHAR(50) NOT NULL COMMENT 'spot_approved, spot_rejected, spot_comment, etc.',
  title VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  data JSON DEFAULT NULL COMMENT 'Additional metadata (spot_id, moderator_id, etc.)',
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  read_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_is_read (is_read),
  INDEX idx_created_at (created_at),
  INDEX idx_user_unread (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
