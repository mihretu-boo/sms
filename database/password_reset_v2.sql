-- ============================================================
-- Forgot Password & Email Recovery Module — Migration v2
-- ============================================================
USE `sjassms`;

-- Enhance password_resets table
ALTER TABLE `password_resets`
  ADD COLUMN IF NOT EXISTS `ip_address`    varchar(45)  DEFAULT NULL AFTER `used`,
  ADD COLUMN IF NOT EXISTS `user_agent`    varchar(255) DEFAULT NULL AFTER `ip_address`,
  ADD COLUMN IF NOT EXISTS `attempts`      int(11)      DEFAULT 0   AFTER `user_agent`,
  MODIFY COLUMN `token` varchar(128) NOT NULL,
  MODIFY COLUMN `expires_at` datetime NOT NULL DEFAULT (NOW() + INTERVAL 30 MINUTE);

-- Rate-limiting table: tracks reset requests per email/IP
CREATE TABLE IF NOT EXISTS `password_reset_rate_limit` (
  `id`          int(11) NOT NULL AUTO_INCREMENT,
  `identifier`  varchar(100) NOT NULL COMMENT 'email or ip_address',
  `type`        enum('email','ip') DEFAULT 'email',
  `requests`    int(11) DEFAULT 1,
  `window_start` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_request` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rl` (`identifier`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add SMTP settings if not present
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `group_name`, `description`) VALUES
('smtp_host',          'smtp.gmail.com',       'email', 'SMTP server hostname'),
('smtp_port',          '587',                  'email', 'SMTP port (587=TLS, 465=SSL, 25=plain)'),
('smtp_encryption',    'tls',                  'email', 'Encryption: tls, ssl, or none'),
('smtp_user',          '',                     'email', 'SMTP username / email address'),
('smtp_pass',          '',                     'email', 'SMTP password or app password'),
('smtp_from_email',    '',                     'email', 'From email address (leave blank to use smtp_user)'),
('smtp_from_name',     'Shalaka Jatan Ali Secondary School', 'email', 'From display name'),
('smtp_auth',          '1',                    'email', 'SMTP authentication required (1=yes, 0=no)'),
('smtp_timeout',       '30',                   'email', 'Connection timeout in seconds'),
('reset_token_expiry', '30',                   'email', 'Password reset link expiry in minutes'),
('reset_rate_limit',   '3',                    'email', 'Max reset requests per hour per email'),
('school_url',         'http://localhost/studentmanagement', 'general', 'School system base URL for emails');
