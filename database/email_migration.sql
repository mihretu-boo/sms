-- SJASSMS Email System Migration
-- Run: docker exec -i studentmanagement_db mysql -uroot -proot sjassms < database/email_migration.sql

-- Email template configuration table
CREATE TABLE IF NOT EXISTS email_templates (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  template_key     VARCHAR(50)  UNIQUE NOT NULL,
  name             VARCHAR(100) NOT NULL,
  subject_template VARCHAR(255) NOT NULL,
  enabled          TINYINT(1)   DEFAULT 1,
  description      TEXT,
  created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email send log
CREATE TABLE IF NOT EXISTS email_logs (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  to_email      VARCHAR(255)                      NOT NULL,
  to_name       VARCHAR(100),
  subject       VARCHAR(255)                      NOT NULL,
  template_key  VARCHAR(50),
  body_html     MEDIUMTEXT,
  status        ENUM('sent','failed','queued')    DEFAULT 'sent',
  error_message TEXT,
  sent_by       INT DEFAULT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status  (status),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed all email types
INSERT INTO email_templates (template_key, name, subject_template, description) VALUES
('welcome_student',    'Student Welcome',            'Welcome to {school_name}, {name}!',                'Sent when a new student account is created'),
('welcome_staff',      'Staff Welcome',              'Welcome to {school_name} — Your Account Details',   'Sent when a new staff member is added'),
('parent_credentials', 'Parent Login Credentials',   '{school_name} — Parent Portal Access',              'Sent to parent when their login credentials are set'),
('fee_reminder',       'Fee Payment Reminder',        'Fee Reminder — Payment Due Soon ({school_name})',   'Sent before a fee payment due date'),
('attendance_alert',   'Low Attendance Alert',        'Attendance Alert for {student_name} — {school_name}','Sent to parent when attendance falls below threshold'),
('exam_results',       'Exam Results Published',      '{exam_name} Results — {school_name}',               'Sent when exam results are published'),
('announcement',       'School Announcement',         '[Notice] {title} — {school_name}',                  'Sent when a school-wide announcement is published'),
('password_reset',     'Password Reset',              'Reset Your Password — {school_name}',               'Sent when a user requests a password reset'),
('password_changed',   'Password Changed',            'Your Password Has Been Changed — {school_name}',    'Sent after a successful password change')
ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description);
