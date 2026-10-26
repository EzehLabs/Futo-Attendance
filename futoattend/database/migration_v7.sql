USE futo_attendance;

-- Adds a semester-level planned class count without deleting existing data.
SET @has_planned_sessions := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'courses' AND COLUMN_NAME = 'planned_sessions'
);
SET @sql := IF(@has_planned_sessions = 0,
  'ALTER TABLE courses ADD COLUMN planned_sessions INT UNSIGNED NOT NULL DEFAULT 17 AFTER semester',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE courses SET planned_sessions = 17 WHERE planned_sessions IS NULL OR planned_sessions = 0;
