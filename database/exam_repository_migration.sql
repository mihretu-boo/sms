-- ============================================================
-- Digital Examination Repository Module — Migration
-- Run after the main sjassms.sql schema
-- ============================================================
USE `sjassms`;

-- Exam Repository: uploaded exam documents
CREATE TABLE IF NOT EXISTS `exam_repository` (
  `id`              int(11) NOT NULL AUTO_INCREMENT,
  `title`           varchar(200) NOT NULL,
  `subject_id`      int(11) DEFAULT NULL,
  `grade`           enum('9','10','11','12','all') DEFAULT 'all',
  `semester_id`     int(11) DEFAULT NULL,
  `academic_year_id` int(11) DEFAULT NULL,
  `department_id`   int(11) DEFAULT NULL,
  `exam_type`       enum('quiz','test','assignment','mid_semester','final','practical','regional','national','mock','entrance') NOT NULL,
  `category_type`   enum('internal','external') DEFAULT 'internal',
  `difficulty`      enum('easy','medium','hard') DEFAULT 'medium',
  `description`     text DEFAULT NULL,
  `instructions`    text DEFAULT NULL,
  `file_path`       varchar(255) NOT NULL,
  `file_original_name` varchar(255) NOT NULL,
  `file_size`       bigint(20) DEFAULT 0,
  `file_mime`       varchar(100) DEFAULT NULL,
  `version`         int(11) DEFAULT 1,
  `status`          enum('draft','submitted','under_review','approved','rejected','archived') NOT NULL DEFAULT 'draft',
  `is_public`       tinyint(1) DEFAULT 0,
  `watermark`       tinyint(1) DEFAULT 0,
  `tags`            varchar(500) DEFAULT NULL,
  `download_count`  int(11) DEFAULT 0,
  `uploaded_by`     int(11) NOT NULL,
  `approved_by`     int(11) DEFAULT NULL,
  `approved_at`     datetime DEFAULT NULL,
  `rejection_reason` varchar(500) DEFAULT NULL,
  `created_at`      datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status`        (`status`),
  KEY `idx_grade`         (`grade`),
  KEY `idx_exam_type`     (`exam_type`),
  KEY `idx_academic_year` (`academic_year_id`),
  KEY `uploaded_by`       (`uploaded_by`),
  CONSTRAINT `fk_er_sub`   FOREIGN KEY (`subject_id`)      REFERENCES `subjects` (`id`)       ON DELETE SET NULL,
  CONSTRAINT `fk_er_ay`    FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_er_sem`   FOREIGN KEY (`semester_id`)     REFERENCES `semesters` (`id`)      ON DELETE SET NULL,
  CONSTRAINT `fk_er_dept`  FOREIGN KEY (`department_id`)   REFERENCES `departments` (`id`)    ON DELETE SET NULL,
  CONSTRAINT `fk_er_user`  FOREIGN KEY (`uploaded_by`)     REFERENCES `users` (`id`)          ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exam Approval Workflow
CREATE TABLE IF NOT EXISTS `exam_approvals` (
  `id`            int(11) NOT NULL AUTO_INCREMENT,
  `exam_repo_id`  int(11) NOT NULL,
  `reviewer_id`   int(11) NOT NULL,
  `reviewer_role` enum('dept_head','vice_principal','principal','super_admin') NOT NULL,
  `action`        enum('submitted','approved','rejected','revision_requested') NOT NULL,
  `comments`      text DEFAULT NULL,
  `created_at`    datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `exam_repo_id` (`exam_repo_id`),
  CONSTRAINT `fk_ea_repo` FOREIGN KEY (`exam_repo_id`) REFERENCES `exam_repository` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ea_user` FOREIGN KEY (`reviewer_id`)  REFERENCES `users` (`id`)           ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exam File Version History
CREATE TABLE IF NOT EXISTS `exam_versions` (
  `id`           int(11) NOT NULL AUTO_INCREMENT,
  `exam_repo_id` int(11) NOT NULL,
  `version`      int(11) NOT NULL DEFAULT 1,
  `file_path`    varchar(255) NOT NULL,
  `file_original_name` varchar(255) NOT NULL,
  `file_size`    bigint(20) DEFAULT 0,
  `change_notes` text DEFAULT NULL,
  `uploaded_by`  int(11) DEFAULT NULL,
  `created_at`   datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `exam_repo_id` (`exam_repo_id`),
  CONSTRAINT `fk_ev_repo` FOREIGN KEY (`exam_repo_id`) REFERENCES `exam_repository` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Download Tracking
CREATE TABLE IF NOT EXISTS `exam_downloads` (
  `id`           int(11) NOT NULL AUTO_INCREMENT,
  `exam_repo_id` int(11) NOT NULL,
  `user_id`      int(11) NOT NULL,
  `ip_address`   varchar(45) DEFAULT NULL,
  `downloaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `exam_repo_id` (`exam_repo_id`),
  KEY `user_id`      (`user_id`),
  CONSTRAINT `fk_ed_repo` FOREIGN KEY (`exam_repo_id`) REFERENCES `exam_repository` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ed_user` FOREIGN KEY (`user_id`)      REFERENCES `users` (`id`)           ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Question Bank
CREATE TABLE IF NOT EXISTS `question_bank` (
  `id`               int(11) NOT NULL AUTO_INCREMENT,
  `subject_id`       int(11) DEFAULT NULL,
  `grade`            enum('9','10','11','12','all') DEFAULT 'all',
  `chapter`          varchar(100) DEFAULT NULL,
  `question_text`    text NOT NULL,
  `question_type`    enum('mcq','true_false','short_answer','essay','practical') DEFAULT 'mcq',
  `option_a`         varchar(500) DEFAULT NULL,
  `option_b`         varchar(500) DEFAULT NULL,
  `option_c`         varchar(500) DEFAULT NULL,
  `option_d`         varchar(500) DEFAULT NULL,
  `correct_answer`   varchar(500) DEFAULT NULL,
  `explanation`      text DEFAULT NULL,
  `difficulty`       enum('easy','medium','hard') DEFAULT 'medium',
  `marks`            int(11) DEFAULT 1,
  `learning_outcome` varchar(255) DEFAULT NULL,
  `tags`             varchar(255) DEFAULT NULL,
  `created_by`       int(11) DEFAULT NULL,
  `status`           enum('active','inactive') DEFAULT 'active',
  `created_at`       datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `subject_id`  (`subject_id`),
  KEY `created_by`  (`created_by`),
  CONSTRAINT `fk_qb_sub`  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qb_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed: example exam repository entries
INSERT IGNORE INTO `exam_repository`
  (`title`, `subject_id`, `grade`, `semester_id`, `academic_year_id`, `department_id`, `exam_type`, `category_type`, `difficulty`, `description`, `file_path`, `file_original_name`, `file_size`, `file_mime`, `status`, `is_public`, `uploaded_by`)
VALUES
  ('Mathematics Grade 9 Mid-Semester Exam 2024', 1, '9', 1, 1, 1, 'mid_semester', 'internal', 'medium',
   'Mid-semester mathematics examination for Grade 9 students covering chapters 1-6.',
   'uploads/exam-repository/placeholder.pdf', 'math_grade9_midsem_2024.pdf', 0, 'application/pdf', 'approved', 1, 1),
  ('Physics Grade 10 Final Exam 2024', 2, '10', 1, 1, 1, 'final', 'internal', 'hard',
   'Final examination for Physics Grade 10 covering all semester topics.',
   'uploads/exam-repository/placeholder.pdf', 'physics_grade10_final_2024.pdf', 0, 'application/pdf', 'approved', 1, 1),
  ('English Language Grade 9 Quiz 1', 5, '9', 1, 1, 2, 'quiz', 'internal', 'easy',
   'First quiz for English Language Grade 9.',
   'uploads/exam-repository/placeholder.pdf', 'english_grade9_quiz1.pdf', 0, 'application/pdf', 'submitted', 0, 6),
  ('National Mock Examination Grade 12 — 2024', NULL, '12', NULL, 1, NULL, 'mock', 'external', 'hard',
   'National mock examination for Grade 12 students in preparation for final national exams.',
   'uploads/exam-repository/placeholder.pdf', 'national_mock_grade12_2024.pdf', 0, 'application/pdf', 'approved', 1, 1);

-- Seed question bank
INSERT IGNORE INTO `question_bank`
  (`subject_id`, `grade`, `chapter`, `question_text`, `question_type`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `difficulty`, `marks`, `created_by`)
VALUES
  (1, '9', 'Chapter 1 - Algebra', 'What is the value of x if 2x + 6 = 14?', 'mcq', '2', '4', '6', '8', 'B', 'easy', 2, 3),
  (1, '9', 'Chapter 1 - Algebra', 'Simplify: 3(2x - 4) + 5x', 'mcq', '11x - 12', '11x - 4', '6x - 12', '11x + 12', 'A', 'medium', 3, 3),
  (2, '9', 'Chapter 2 - Motion', 'What is the SI unit of velocity?', 'mcq', 'm/s²', 'km/h', 'm/s', 'kg/m', 'C', 'easy', 1, 4),
  (1, '9', 'Chapter 3 - Geometry', 'Calculate the area of a circle with radius 7 cm (π=22/7)', 'short_answer', NULL, NULL, NULL, NULL, '154 cm²', 'medium', 4, 3),
  (5, '9', 'Chapter 1 - Grammar', 'Which of the following is a correct sentence?', 'mcq', 'She go to school.', 'She goes to school.', 'She going to school.', 'She gone to school.', 'B', 'easy', 2, 5);
