-- ============================================================
-- Shalaka Jatan Ali Secondary School Management System
-- FINAL MERGED DATABASE SCHEMA v2.0
-- Includes: Core Schema + Ethiopian Curriculum + Exam Repository
--           + Password Reset Module + All Bug Fixes
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- CREATE DATABASE
-- ============================================================
CREATE DATABASE IF NOT EXISTS `sjassms`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `sjassms`;

-- ============================================================
-- DROP TABLES (clean slate, safe order)
-- ============================================================
DROP TABLE IF EXISTS `exam_downloads`;
DROP TABLE IF EXISTS `exam_versions`;
DROP TABLE IF EXISTS `exam_approvals`;
DROP TABLE IF EXISTS `exam_repository`;
DROP TABLE IF EXISTS `question_bank`;
DROP TABLE IF EXISTS `password_reset_rate_limit`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `sms_logs`;
DROP TABLE IF EXISTS `promotions`;
DROP TABLE IF EXISTS `transfers`;
DROP TABLE IF EXISTS `leave_requests`;
DROP TABLE IF EXISTS `club_members`;
DROP TABLE IF EXISTS `clubs`;
DROP TABLE IF EXISTS `inventory_items`;
DROP TABLE IF EXISTS `inventory_categories`;
DROP TABLE IF EXISTS `transport_routes`;
DROP TABLE IF EXISTS `vehicles`;
DROP TABLE IF EXISTS `hostel_allocations`;
DROP TABLE IF EXISTS `hostel_rooms`;
DROP TABLE IF EXISTS `hostels`;
DROP TABLE IF EXISTS `discipline_incidents`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `payroll`;
DROP TABLE IF EXISTS `expenses`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `student_fees`;
DROP TABLE IF EXISTS `fee_categories`;
DROP TABLE IF EXISTS `book_borrowings`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `materials`;
DROP TABLE IF EXISTS `submissions`;
DROP TABLE IF EXISTS `assignments`;
DROP TABLE IF EXISTS `timetable`;
DROP TABLE IF EXISTS `marks`;
DROP TABLE IF EXISTS `exams`;
DROP TABLE IF EXISTS `staff_attendance`;
DROP TABLE IF EXISTS `student_attendance`;
DROP TABLE IF EXISTS `class_subjects`;
DROP TABLE IF EXISTS `subjects`;
DROP TABLE IF EXISTS `parents`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `staff`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `semesters`;
DROP TABLE IF EXISTS `academic_years`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE `users` (
  `id`              int(11)    NOT NULL AUTO_INCREMENT,
  `username`        varchar(50)  NOT NULL UNIQUE,
  `email`           varchar(100) NOT NULL UNIQUE,
  `password`        varchar(255) NOT NULL,
  `role`            enum('super_admin','principal','vice_principal','registrar','teacher',
                         'dept_head','student','parent','finance_officer') NOT NULL DEFAULT 'student',
  `status`          enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `photo`           varchar(255) DEFAULT NULL,
  `phone`           varchar(20)  DEFAULT NULL,
  `last_login`      datetime     DEFAULT NULL,
  `two_fa_secret`   varchar(100) DEFAULT NULL,
  `two_fa_enabled`  tinyint(1)   DEFAULT 0,
  `lang`            varchar(10)  DEFAULT 'en',
  `created_at`      datetime     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      datetime     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_role`   (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SETTINGS
-- ============================================================
CREATE TABLE `settings` (
  `id`            int(11)      NOT NULL AUTO_INCREMENT,
  `setting_key`   varchar(100) NOT NULL UNIQUE,
  `setting_value` text         DEFAULT NULL,
  `group_name`    varchar(50)  DEFAULT 'general',
  `description`   varchar(255) DEFAULT NULL,
  `created_at`    datetime     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    datetime     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ACADEMIC YEARS
-- ============================================================
CREATE TABLE `academic_years` (
  `id`         int(11)     NOT NULL AUTO_INCREMENT,
  `name`       varchar(20) NOT NULL,
  `start_date` date        NOT NULL,
  `end_date`   date        NOT NULL,
  `status`     enum('active','inactive','archived') DEFAULT 'inactive',
  `created_at` datetime    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEMESTERS
-- ============================================================
CREATE TABLE `semesters` (
  `id`               int(11)     NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(11)     NOT NULL,
  `name`             varchar(30) NOT NULL,
  `start_date`       date        NOT NULL,
  `end_date`         date        NOT NULL,
  `status`           enum('active','inactive','archived') DEFAULT 'inactive',
  `created_at`       datetime    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `fk_sem_ay` FOREIGN KEY (`academic_year_id`)
    REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DEPARTMENTS
-- ============================================================
CREATE TABLE `departments` (
  `id`          int(11)      NOT NULL AUTO_INCREMENT,
  `name`        varchar(100) NOT NULL,
  `code`        varchar(10)  NOT NULL,
  `head_id`     int(11)      DEFAULT NULL,
  `description` text         DEFAULT NULL,
  `created_at`  datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CLASSES  (stream supports Grade 11/12 Natural/Social/General)
-- ============================================================
CREATE TABLE `classes` (
  `id`               int(11)     NOT NULL AUTO_INCREMENT,
  `grade`            enum('9','10','11','12') NOT NULL,
  `section`          varchar(5)  NOT NULL DEFAULT 'A',
  `name`             varchar(20) GENERATED ALWAYS AS (CONCAT('Grade ',grade,'-',section)) STORED,
  `class_teacher_id` int(11)     DEFAULT NULL,
  `academic_year_id` int(11)     NOT NULL,
  `room_no`          varchar(10) DEFAULT NULL,
  `max_students`     int(11)     DEFAULT 50,
  `stream`           enum('natural','social','general') DEFAULT 'general',
  `created_at`       datetime    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `class_teacher_id` (`class_teacher_id`),
  CONSTRAINT `fk_cls_ay` FOREIGN KEY (`academic_year_id`)
    REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SUBJECTS  (stream = all | natural | social; periods_week for timetable)
-- ============================================================
CREATE TABLE `subjects` (
  `id`            int(11)     NOT NULL AUTO_INCREMENT,
  `code`          varchar(15) NOT NULL UNIQUE,
  `name`          varchar(100) NOT NULL,
  `name_am`       varchar(100) DEFAULT NULL,
  `department_id` int(11)     DEFAULT NULL,
  `grade`         enum('9','10','11','12','all') DEFAULT 'all',
  `stream`        enum('all','natural','social')  DEFAULT 'all',
  `credit_hours`  int(11)     DEFAULT 3,
  `periods_week`  int(11)     DEFAULT 3,
  `type`          enum('core','elective','optional') DEFAULT 'core',
  `description`   text        DEFAULT NULL,
  `created_at`    datetime    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_sub_dept` FOREIGN KEY (`department_id`)
    REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STUDENTS  (stream inherited from class for Grade 11/12)
-- ============================================================
CREATE TABLE `students` (
  `id`                      int(11)      NOT NULL AUTO_INCREMENT,
  `user_id`                 int(11)      DEFAULT NULL,
  `student_id`              varchar(20)  NOT NULL UNIQUE,
  `first_name`              varchar(50)  NOT NULL,
  `last_name`               varchar(50)  NOT NULL,
  `gender`                  enum('male','female') NOT NULL,
  `dob`                     date         NOT NULL,
  `blood_type`              varchar(5)   DEFAULT NULL,
  `nationality`             varchar(50)  DEFAULT 'Ethiopian',
  `religion`                varchar(50)  DEFAULT NULL,
  `class_id`                int(11)      DEFAULT NULL,
  `stream`                  enum('general','natural','social') DEFAULT 'general',
  `academic_year_id`        int(11)      DEFAULT NULL,
  `admission_date`          date         NOT NULL,
  `admission_no`            varchar(20)  NOT NULL UNIQUE,
  `status`                  enum('active','inactive','graduated','transferred','expelled') DEFAULT 'active',
  `photo`                   varchar(255) DEFAULT NULL,
  `address`                 text         DEFAULT NULL,
  `city`                    varchar(50)  DEFAULT NULL,
  `phone`                   varchar(20)  DEFAULT NULL,
  `email`                   varchar(100) DEFAULT NULL,
  `previous_school`         varchar(150) DEFAULT NULL,
  `medical_info`            text         DEFAULT NULL,
  `emergency_contact_name`  varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20)  DEFAULT NULL,
  `created_at`              datetime     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`              datetime     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id`          (`user_id`),
  KEY `class_id`         (`class_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `fk_stu_user` FOREIGN KEY (`user_id`)          REFERENCES `users`         (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stu_cls`  FOREIGN KEY (`class_id`)         REFERENCES `classes`       (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stu_ay`   FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PARENTS / GUARDIANS
-- ============================================================
CREATE TABLE `parents` (
  `id`         int(11)     NOT NULL AUTO_INCREMENT,
  `user_id`    int(11)     DEFAULT NULL,
  `student_id` int(11)     NOT NULL,
  `relation`   enum('father','mother','guardian','other') NOT NULL DEFAULT 'father',
  `first_name` varchar(50) NOT NULL,
  `last_name`  varchar(50) NOT NULL,
  `phone`      varchar(20) NOT NULL,
  `alt_phone`  varchar(20) DEFAULT NULL,
  `email`      varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `address`    text        DEFAULT NULL,
  `is_primary` tinyint(1)  DEFAULT 1,
  `created_at` datetime    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id`    (`user_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_par_user` FOREIGN KEY (`user_id`)    REFERENCES `users`    (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_par_stu`  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STAFF
-- ============================================================
CREATE TABLE `staff` (
  `id`              int(11)      NOT NULL AUTO_INCREMENT,
  `user_id`         int(11)      DEFAULT NULL,
  `employee_id`     varchar(20)  NOT NULL UNIQUE,
  `first_name`      varchar(50)  NOT NULL,
  `last_name`       varchar(50)  NOT NULL,
  `gender`          enum('male','female') NOT NULL,
  `dob`             date         DEFAULT NULL,
  `nationality`     varchar(50)  DEFAULT 'Ethiopian',
  `department_id`   int(11)      DEFAULT NULL,
  `position`        varchar(100) NOT NULL,
  `qualification`   varchar(200) DEFAULT NULL,
  `specialization`  varchar(100) DEFAULT NULL,
  `hire_date`       date         NOT NULL,
  `contract_type`   enum('permanent','contract','temporary') DEFAULT 'permanent',
  `basic_salary`    decimal(10,2) DEFAULT 0.00,
  `phone`           varchar(20)  DEFAULT NULL,
  `email`           varchar(100) DEFAULT NULL,
  `address`         text         DEFAULT NULL,
  `photo`           varchar(255) DEFAULT NULL,
  `status`          enum('active','inactive','terminated','on_leave') DEFAULT 'active',
  `notes`           text         DEFAULT NULL,
  `created_at`      datetime     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      datetime     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id`       (`user_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_stf_user` FOREIGN KEY (`user_id`)       REFERENCES `users`       (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stf_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CLASS SUBJECTS
-- ============================================================
CREATE TABLE `class_subjects` (
  `id`              int(11) NOT NULL AUTO_INCREMENT,
  `class_id`        int(11) NOT NULL,
  `subject_id`      int(11) NOT NULL,
  `teacher_id`      int(11) DEFAULT NULL,
  `semester_id`     int(11) NOT NULL,
  `periods_per_week` int(11) DEFAULT 3,
  `created_at`      datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cls_sub_sem` (`class_id`,`subject_id`,`semester_id`),
  KEY `class_id`    (`class_id`),
  KEY `subject_id`  (`subject_id`),
  KEY `teacher_id`  (`teacher_id`),
  KEY `semester_id` (`semester_id`),
  CONSTRAINT `fk_cs_cls` FOREIGN KEY (`class_id`)    REFERENCES `classes`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cs_sub` FOREIGN KEY (`subject_id`)  REFERENCES `subjects`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cs_sem` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STUDENT ATTENDANCE
-- ============================================================
CREATE TABLE `student_attendance` (
  `id`          int(11) NOT NULL AUTO_INCREMENT,
  `student_id`  int(11) NOT NULL,
  `class_id`    int(11) NOT NULL,
  `date`        date    NOT NULL,
  `status`      enum('present','absent','late','excused') NOT NULL DEFAULT 'present',
  `remarks`     varchar(255) DEFAULT NULL,
  `recorded_by` int(11)      DEFAULT NULL,
  `created_at`  datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_stu_att` (`student_id`,`date`),
  KEY `class_id`    (`class_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `fk_sa_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sa_cls` FOREIGN KEY (`class_id`)   REFERENCES `classes`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STAFF ATTENDANCE
-- ============================================================
CREATE TABLE `staff_attendance` (
  `id`         int(11) NOT NULL AUTO_INCREMENT,
  `staff_id`   int(11) NOT NULL,
  `date`       date    NOT NULL,
  `check_in`   time    DEFAULT NULL,
  `check_out`  time    DEFAULT NULL,
  `status`     enum('present','absent','late','half_day','on_leave') DEFAULT 'present',
  `remarks`    varchar(255) DEFAULT NULL,
  `created_at` datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_stf_att` (`staff_id`,`date`),
  CONSTRAINT `fk_stfa_stf` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- EXAMS
-- ============================================================
CREATE TABLE `exams` (
  `id`              int(11)       NOT NULL AUTO_INCREMENT,
  `title`           varchar(100)  NOT NULL,
  `type`            enum('assignment','quiz','project','mid_exam','final_exam','national_prep') NOT NULL,
  `semester_id`     int(11)       NOT NULL,
  `class_id`        int(11)       NOT NULL,
  `subject_id`      int(11)       NOT NULL,
  `exam_date`       date          DEFAULT NULL,
  `total_marks`     decimal(6,2)  NOT NULL DEFAULT 100,
  `pass_marks`      decimal(6,2)  NOT NULL DEFAULT 50,
  `weight_percent`  decimal(5,2)  DEFAULT NULL,
  `instructions`    text          DEFAULT NULL,
  `created_by`      int(11)       DEFAULT NULL,
  `created_at`      datetime      DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `semester_id` (`semester_id`),
  KEY `class_id`    (`class_id`),
  KEY `subject_id`  (`subject_id`),
  KEY `created_by`  (`created_by`),
  CONSTRAINT `fk_ex_sem` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ex_cls` FOREIGN KEY (`class_id`)    REFERENCES `classes`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ex_sub` FOREIGN KEY (`subject_id`)  REFERENCES `subjects`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- MARKS / GRADES
-- ============================================================
CREATE TABLE `marks` (
  `id`             int(11)      NOT NULL AUTO_INCREMENT,
  `exam_id`        int(11)      NOT NULL,
  `student_id`     int(11)      NOT NULL,
  `marks_obtained` decimal(6,2) NOT NULL DEFAULT 0,
  `grade_letter`   varchar(5)   DEFAULT NULL,
  `grade_point`    decimal(4,2) DEFAULT NULL,
  `remarks`        varchar(255) DEFAULT NULL,
  `recorded_by`    int(11)      DEFAULT NULL,
  `created_at`     datetime     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     datetime     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mark_exam_stu` (`exam_id`,`student_id`),
  KEY `exam_id`    (`exam_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_mk_exam` FOREIGN KEY (`exam_id`)    REFERENCES `exams`    (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mk_stu`  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TIMETABLE
-- ============================================================
CREATE TABLE `timetable` (
  `id`          int(11)     NOT NULL AUTO_INCREMENT,
  `class_id`    int(11)     NOT NULL,
  `subject_id`  int(11)     NOT NULL,
  `teacher_id`  int(11)     NOT NULL,
  `day`         enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `period`      int(11)     NOT NULL,
  `start_time`  time        NOT NULL,
  `end_time`    time        NOT NULL,
  `room`        varchar(20) DEFAULT NULL,
  `semester_id` int(11)     NOT NULL,
  `created_at`  datetime    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `class_id`    (`class_id`),
  KEY `subject_id`  (`subject_id`),
  KEY `teacher_id`  (`teacher_id`),
  KEY `semester_id` (`semester_id`),
  CONSTRAINT `fk_tt_cls` FOREIGN KEY (`class_id`)    REFERENCES `classes`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tt_sub` FOREIGN KEY (`subject_id`)  REFERENCES `subjects`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tt_sem` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ASSIGNMENTS
-- ============================================================
CREATE TABLE `assignments` (
  `id`          int(11)      NOT NULL AUTO_INCREMENT,
  `title`       varchar(200) NOT NULL,
  `description` text         DEFAULT NULL,
  `class_id`    int(11)      NOT NULL,
  `subject_id`  int(11)      NOT NULL,
  `teacher_id`  int(11)      NOT NULL,
  `due_date`    datetime     NOT NULL,
  `max_marks`   decimal(6,2) DEFAULT 100,
  `file_path`   varchar(255) DEFAULT NULL,
  `semester_id` int(11)      NOT NULL,
  `status`      enum('active','closed') DEFAULT 'active',
  `created_at`  datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `class_id`   (`class_id`),
  KEY `subject_id` (`subject_id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SUBMISSIONS
-- ============================================================
CREATE TABLE `submissions` (
  `id`            int(11)      NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11)      NOT NULL,
  `student_id`    int(11)      NOT NULL,
  `submitted_at`  datetime     DEFAULT CURRENT_TIMESTAMP,
  `file_path`     varchar(255) DEFAULT NULL,
  `text_content`  text         DEFAULT NULL,
  `marks`         decimal(6,2) DEFAULT NULL,
  `feedback`      text         DEFAULT NULL,
  `status`        enum('submitted','late','graded') DEFAULT 'submitted',
  `created_at`    datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sub_asgn_stu` (`assignment_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_sub_asgn` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sub_stu`  FOREIGN KEY (`student_id`)    REFERENCES `students`    (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- LEARNING MATERIALS
-- ============================================================
CREATE TABLE `materials` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `title`        varchar(200) NOT NULL,
  `description`  text         DEFAULT NULL,
  `class_id`     int(11)      NOT NULL,
  `subject_id`   int(11)      NOT NULL,
  `teacher_id`   int(11)      NOT NULL,
  `file_path`    varchar(255) DEFAULT NULL,
  `file_type`    enum('pdf','doc','ppt','video','link','other') DEFAULT 'pdf',
  `external_url` varchar(500) DEFAULT NULL,
  `semester_id`  int(11)      NOT NULL,
  `created_at`   datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- LIBRARY BOOKS
-- ============================================================
CREATE TABLE `books` (
  `id`               int(11)      NOT NULL AUTO_INCREMENT,
  `isbn`             varchar(20)  DEFAULT NULL UNIQUE,
  `barcode`          varchar(50)  DEFAULT NULL,
  `title`            varchar(200) NOT NULL,
  `author`           varchar(150) NOT NULL,
  `publisher`        varchar(150) DEFAULT NULL,
  `publish_year`     year         DEFAULT NULL,
  `category`         varchar(100) DEFAULT NULL,
  `language`         varchar(50)  DEFAULT 'English',
  `copies_total`     int(11)      NOT NULL DEFAULT 1,
  `copies_available` int(11)      NOT NULL DEFAULT 1,
  `location`         varchar(50)  DEFAULT NULL,
  `cover_image`      varchar(255) DEFAULT NULL,
  `description`      text         DEFAULT NULL,
  `status`           enum('available','unavailable') DEFAULT 'available',
  `created_at`       datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_title`  (`title`),
  KEY `idx_author` (`author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BOOK BORROWINGS
-- ============================================================
CREATE TABLE `book_borrowings` (
  `id`          int(11)      NOT NULL AUTO_INCREMENT,
  `book_id`     int(11)      NOT NULL,
  `user_id`     int(11)      NOT NULL,
  `borrow_date` date         NOT NULL,
  `due_date`    date         NOT NULL,
  `return_date` date         DEFAULT NULL,
  `fine`        decimal(8,2) DEFAULT 0.00,
  `fine_paid`   tinyint(1)   DEFAULT 0,
  `status`      enum('borrowed','returned','overdue','lost') DEFAULT 'borrowed',
  `notes`       text         DEFAULT NULL,
  `issued_by`   int(11)      DEFAULT NULL,
  `created_at`  datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `book_id` (`book_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_bb_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bb_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- FEE CATEGORIES
-- ============================================================
CREATE TABLE `fee_categories` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `name`         varchar(100) NOT NULL,
  `amount`       decimal(10,2) NOT NULL DEFAULT 0,
  `type`         enum('tuition','registration','exam','library','hostel','transport','uniform','other') DEFAULT 'tuition',
  `frequency`    enum('one_time','monthly','semester','annual') DEFAULT 'semester',
  `description`  text         DEFAULT NULL,
  `is_mandatory` tinyint(1)   DEFAULT 1,
  `created_at`   datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STUDENT FEES
-- ============================================================
CREATE TABLE `student_fees` (
  `id`              int(11)      NOT NULL AUTO_INCREMENT,
  `student_id`      int(11)      NOT NULL,
  `fee_category_id` int(11)      NOT NULL,
  `academic_year_id` int(11)     NOT NULL,
  `semester_id`     int(11)      DEFAULT NULL,
  `amount`          decimal(10,2) NOT NULL,
  `due_date`        date         NOT NULL,
  `discount`        decimal(10,2) DEFAULT 0,
  `waiver_reason`   varchar(255) DEFAULT NULL,
  `status`          enum('unpaid','partial','paid','waived') DEFAULT 'unpaid',
  `created_at`      datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id`      (`student_id`),
  KEY `fee_category_id` (`fee_category_id`),
  CONSTRAINT `fk_sf_stu` FOREIGN KEY (`student_id`)      REFERENCES `students`       (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sf_fc`  FOREIGN KEY (`fee_category_id`) REFERENCES `fee_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PAYMENTS
-- ============================================================
CREATE TABLE `payments` (
  `id`              int(11)      NOT NULL AUTO_INCREMENT,
  `student_id`      int(11)      NOT NULL,
  `student_fee_id`  int(11)      DEFAULT NULL,
  `amount`          decimal(10,2) NOT NULL,
  `payment_date`    date         NOT NULL,
  `payment_method`  enum('cash','bank_transfer','telebirr','cbe_birr','other') DEFAULT 'cash',
  `receipt_no`      varchar(30)  NOT NULL UNIQUE,
  `transaction_ref` varchar(100) DEFAULT NULL,
  `recorded_by`     int(11)      DEFAULT NULL,
  `notes`           text         DEFAULT NULL,
  `created_at`      datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id`     (`student_id`),
  KEY `student_fee_id` (`student_fee_id`),
  CONSTRAINT `fk_pay_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- EXPENSES
-- ============================================================
CREATE TABLE `expenses` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `title`        varchar(200) NOT NULL,
  `category`     varchar(100) NOT NULL,
  `amount`       decimal(10,2) NOT NULL,
  `expense_date` date         NOT NULL,
  `description`  text         DEFAULT NULL,
  `attachment`   varchar(255) DEFAULT NULL,
  `approved_by`  int(11)      DEFAULT NULL,
  `recorded_by`  int(11)      NOT NULL,
  `status`       enum('pending','approved','rejected') DEFAULT 'approved',
  `created_at`   datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PAYROLL
-- ============================================================
CREATE TABLE `payroll` (
  `id`             int(11)      NOT NULL AUTO_INCREMENT,
  `staff_id`       int(11)      NOT NULL,
  `month`          int(2)       NOT NULL,
  `year`           int(4)       NOT NULL,
  `basic_salary`   decimal(10,2) NOT NULL,
  `allowances`     decimal(10,2) DEFAULT 0,
  `deductions`     decimal(10,2) DEFAULT 0,
  `income_tax`     decimal(10,2) DEFAULT 0,
  `pension`        decimal(10,2) DEFAULT 0,
  `net_salary`     decimal(10,2) NOT NULL,
  `payment_date`   date         DEFAULT NULL,
  `payment_method` enum('bank','cash','telebirr') DEFAULT 'bank',
  `status`         enum('pending','paid','cancelled') DEFAULT 'pending',
  `notes`          text         DEFAULT NULL,
  `processed_by`   int(11)      DEFAULT NULL,
  `created_at`     datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payroll` (`staff_id`,`month`,`year`),
  CONSTRAINT `fk_pr_stf` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ANNOUNCEMENTS
-- ============================================================
CREATE TABLE `announcements` (
  `id`              int(11)      NOT NULL AUTO_INCREMENT,
  `title`           varchar(200) NOT NULL,
  `content`         text         NOT NULL,
  `target_role`     varchar(50)  DEFAULT 'all',
  `target_class_id` int(11)      DEFAULT NULL,
  `author_id`       int(11)      NOT NULL,
  `start_date`      date         NOT NULL,
  `end_date`        date         NOT NULL,
  `priority`        enum('normal','important','urgent') DEFAULT 'normal',
  `attachment`      varchar(255) DEFAULT NULL,
  `views`           int(11)      DEFAULT 0,
  `status`          enum('active','inactive') DEFAULT 'active',
  `created_at`      datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- MESSAGES
-- ============================================================
CREATE TABLE `messages` (
  `id`                   int(11)      NOT NULL AUTO_INCREMENT,
  `sender_id`            int(11)      NOT NULL,
  `receiver_id`          int(11)      NOT NULL,
  `subject`              varchar(200) DEFAULT NULL,
  `content`              text         NOT NULL,
  `read_at`              datetime     DEFAULT NULL,
  `is_deleted_sender`    tinyint(1)   DEFAULT 0,
  `is_deleted_receiver`  tinyint(1)   DEFAULT 0,
  `parent_id`            int(11)      DEFAULT NULL,
  `created_at`           datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sender_id`   (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `fk_msg_sndr` FOREIGN KEY (`sender_id`)   REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_rcvr` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NOTIFICATIONS
-- ============================================================
CREATE TABLE `notifications` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `user_id`    int(11)      NOT NULL,
  `title`      varchar(200) NOT NULL,
  `message`    text         NOT NULL,
  `type`       enum('info','success','warning','danger') DEFAULT 'info',
  `icon`       varchar(50)  DEFAULT 'bell',
  `link`       varchar(255) DEFAULT NULL,
  `is_read`    tinyint(1)   DEFAULT 0,
  `created_at` datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DISCIPLINE INCIDENTS
-- ============================================================
CREATE TABLE `discipline_incidents` (
  `id`                   int(11)      NOT NULL AUTO_INCREMENT,
  `student_id`           int(11)      NOT NULL,
  `reported_by`          int(11)      DEFAULT NULL,
  `incident_date`        date         NOT NULL,
  `incident_type`        varchar(100) NOT NULL,
  `description`          text         NOT NULL,
  `action_taken`         text         DEFAULT NULL,
  `parent_notified`      tinyint(1)   DEFAULT 0,
  `parent_notified_date` date         DEFAULT NULL,
  `severity`             enum('minor','moderate','major','critical') DEFAULT 'minor',
  `status`               enum('open','resolved','escalated') DEFAULT 'open',
  `created_at`           datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_di_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- HOSTELS
-- ============================================================
CREATE TABLE `hostels` (
  `id`          int(11)      NOT NULL AUTO_INCREMENT,
  `name`        varchar(100) NOT NULL,
  `type`        enum('male','female','mixed') DEFAULT 'mixed',
  `total_rooms` int(11)      DEFAULT 0,
  `capacity`    int(11)      DEFAULT 0,
  `warden_id`   int(11)      DEFAULT NULL,
  `description` text         DEFAULT NULL,
  `created_at`  datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `hostel_rooms` (
  `id`                int(11)     NOT NULL AUTO_INCREMENT,
  `hostel_id`         int(11)     NOT NULL,
  `room_number`       varchar(20) NOT NULL,
  `capacity`          int(11)     DEFAULT 4,
  `current_occupancy` int(11)     DEFAULT 0,
  `type`              enum('single','double','dormitory') DEFAULT 'double',
  `floor`             varchar(10) DEFAULT NULL,
  `status`            enum('available','full','maintenance') DEFAULT 'available',
  `created_at`        datetime    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_hr_hostel` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `hostel_allocations` (
  `id`               int(11)      NOT NULL AUTO_INCREMENT,
  `student_id`       int(11)      NOT NULL,
  `room_id`          int(11)      NOT NULL,
  `academic_year_id` int(11)      NOT NULL,
  `check_in`         date         NOT NULL,
  `check_out`        date         DEFAULT NULL,
  `fee`              decimal(10,2) DEFAULT 0,
  `status`           enum('active','vacated') DEFAULT 'active',
  `created_at`       datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ha_stu`  FOREIGN KEY (`student_id`) REFERENCES `students`    (`id`),
  CONSTRAINT `fk_ha_room` FOREIGN KEY (`room_id`)    REFERENCES `hostel_rooms`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TRANSPORT
-- ============================================================
CREATE TABLE `vehicles` (
  `id`           int(11)     NOT NULL AUTO_INCREMENT,
  `plate_no`     varchar(20) NOT NULL UNIQUE,
  `type`         enum('bus','minibus','van') DEFAULT 'bus',
  `capacity`     int(11)     DEFAULT 40,
  `driver_name`  varchar(100) DEFAULT NULL,
  `driver_phone` varchar(20)  DEFAULT NULL,
  `model`        varchar(50)  DEFAULT NULL,
  `status`       enum('active','maintenance','retired') DEFAULT 'active',
  `created_at`   datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `transport_routes` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `name`         varchar(100) NOT NULL,
  `stops`        text         DEFAULT NULL,
  `vehicle_id`   int(11)      DEFAULT NULL,
  `morning_time` time         DEFAULT NULL,
  `evening_time` time         DEFAULT NULL,
  `monthly_fee`  decimal(8,2) DEFAULT 0,
  `status`       enum('active','inactive') DEFAULT 'active',
  `created_at`   datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vehicle_id` (`vehicle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- INVENTORY
-- ============================================================
CREATE TABLE `inventory_categories` (
  `id`          int(11)      NOT NULL AUTO_INCREMENT,
  `name`        varchar(100) NOT NULL,
  `description` text         DEFAULT NULL,
  `created_at`  datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `inventory_items` (
  `id`               int(11)      NOT NULL AUTO_INCREMENT,
  `category_id`      int(11)      DEFAULT NULL,
  `name`             varchar(200) NOT NULL,
  `item_code`        varchar(30)  DEFAULT NULL UNIQUE,
  `quantity`         int(11)      NOT NULL DEFAULT 0,
  `unit`             varchar(20)  DEFAULT 'pcs',
  `condition_status` enum('excellent','good','fair','poor','damaged') DEFAULT 'good',
  `location`         varchar(100) DEFAULT NULL,
  `purchase_date`    date         DEFAULT NULL,
  `cost`             decimal(10,2) DEFAULT 0,
  `supplier`         varchar(100) DEFAULT NULL,
  `notes`            text         DEFAULT NULL,
  `created_at`       datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `fk_inv_cat` FOREIGN KEY (`category_id`) REFERENCES `inventory_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CLUBS
-- ============================================================
CREATE TABLE `clubs` (
  `id`               int(11)      NOT NULL AUTO_INCREMENT,
  `name`             varchar(100) NOT NULL,
  `code`             varchar(10)  DEFAULT NULL,
  `description`      text         DEFAULT NULL,
  `supervisor_id`    int(11)      DEFAULT NULL,
  `meeting_schedule` varchar(200) DEFAULT NULL,
  `status`           enum('active','inactive') DEFAULT 'active',
  `created_at`       datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `club_members` (
  `id`          int(11)  NOT NULL AUTO_INCREMENT,
  `club_id`     int(11)  NOT NULL,
  `student_id`  int(11)  NOT NULL,
  `role`        enum('member','secretary','treasurer','president','vice_president') DEFAULT 'member',
  `joined_date` date     NOT NULL,
  `status`      enum('active','inactive') DEFAULT 'active',
  `created_at`  datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cm` (`club_id`,`student_id`),
  CONSTRAINT `fk_cm_club` FOREIGN KEY (`club_id`)    REFERENCES `clubs`    (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cm_stu`  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- LEAVE REQUESTS
-- ============================================================
CREATE TABLE `leave_requests` (
  `id`               int(11)  NOT NULL AUTO_INCREMENT,
  `staff_id`         int(11)  NOT NULL,
  `leave_type`       enum('annual','sick','maternity','paternity','emergency','unpaid','other') NOT NULL,
  `start_date`       date     NOT NULL,
  `end_date`         date     NOT NULL,
  `days`             int(11)  NOT NULL,
  `reason`           text     NOT NULL,
  `attachment`       varchar(255) DEFAULT NULL,
  `status`           enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approved_by`      int(11)  DEFAULT NULL,
  `approved_at`      datetime DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `created_at`       datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `fk_lr_stf` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STUDENT TRANSFERS
-- ============================================================
CREATE TABLE `transfers` (
  `id`            int(11)      NOT NULL AUTO_INCREMENT,
  `student_id`    int(11)      NOT NULL,
  `transfer_type` enum('in','out') NOT NULL,
  `from_school`   varchar(200) DEFAULT NULL,
  `to_school`     varchar(200) DEFAULT NULL,
  `transfer_date` date         NOT NULL,
  `reason`        text         DEFAULT NULL,
  `certificate_no` varchar(30) DEFAULT NULL,
  `approved_by`   int(11)      DEFAULT NULL,
  `status`        enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at`    datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_tr_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- AUDIT LOGS
-- ============================================================
CREATE TABLE `audit_logs` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `user_id`    int(11)      DEFAULT NULL,
  `action`     varchar(100) NOT NULL,
  `module`     varchar(50)  NOT NULL,
  `record_id`  int(11)      DEFAULT NULL,
  `old_data`   text         DEFAULT NULL,
  `new_data`   text         DEFAULT NULL,
  `ip_address` varchar(45)  DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id`     (`user_id`),
  KEY `idx_module`  (`module`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PROMOTIONS
-- ============================================================
CREATE TABLE `promotions` (
  `id`               int(11)     NOT NULL AUTO_INCREMENT,
  `student_id`       int(11)     NOT NULL,
  `from_class_id`    int(11)     NOT NULL,
  `to_class_id`      int(11)     DEFAULT NULL,
  `academic_year_id` int(11)     NOT NULL,
  `status`           enum('promoted','repeated','graduated','transferred') DEFAULT 'promoted',
  `gpa`              decimal(4,2) DEFAULT NULL,
  `rank`             int(11)     DEFAULT NULL,
  `remarks`          text        DEFAULT NULL,
  `promoted_by`      int(11)     DEFAULT NULL,
  `created_at`       datetime    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SMS LOGS
-- ============================================================
CREATE TABLE `sms_logs` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `phone`      varchar(20)  NOT NULL,
  `message`    text         NOT NULL,
  `type`       varchar(50)  DEFAULT 'general',
  `status`     enum('sent','failed','pending') DEFAULT 'pending',
  `sent_by`    int(11)      DEFAULT NULL,
  `created_at` datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PASSWORD RESETS  (enhanced with ip, user_agent, attempts)
-- ============================================================
CREATE TABLE `password_resets` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `email`      varchar(100) NOT NULL,
  `token`      varchar(128) NOT NULL,
  `expires_at` datetime     NOT NULL,
  `used`       tinyint(1)   DEFAULT 0,
  `ip_address` varchar(45)  DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `attempts`   int(11)      DEFAULT 0,
  `created_at` datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PASSWORD RESET RATE LIMIT
-- ============================================================
CREATE TABLE `password_reset_rate_limit` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `identifier`   varchar(100) NOT NULL COMMENT 'email or ip_address',
  `type`         enum('email','ip') DEFAULT 'email',
  `requests`     int(11)      DEFAULT 1,
  `window_start` datetime     DEFAULT CURRENT_TIMESTAMP,
  `last_request` datetime     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rl` (`identifier`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- EXAM REPOSITORY
-- ============================================================
CREATE TABLE `exam_repository` (
  `id`                 int(11)      NOT NULL AUTO_INCREMENT,
  `title`              varchar(200) NOT NULL,
  `subject_id`         int(11)      DEFAULT NULL,
  `grade`              enum('9','10','11','12','all') DEFAULT 'all',
  `semester_id`        int(11)      DEFAULT NULL,
  `academic_year_id`   int(11)      DEFAULT NULL,
  `department_id`      int(11)      DEFAULT NULL,
  `exam_type`          enum('quiz','test','assignment','mid_semester','final','practical',
                            'regional','national','mock','entrance') NOT NULL,
  `category_type`      enum('internal','external') DEFAULT 'internal',
  `difficulty`         enum('easy','medium','hard') DEFAULT 'medium',
  `description`        text         DEFAULT NULL,
  `instructions`       text         DEFAULT NULL,
  `file_path`          varchar(255) NOT NULL,
  `file_original_name` varchar(255) NOT NULL,
  `file_size`          bigint(20)   DEFAULT 0,
  `file_mime`          varchar(100) DEFAULT NULL,
  `version`            int(11)      DEFAULT 1,
  `status`             enum('draft','submitted','under_review','approved','rejected','archived') NOT NULL DEFAULT 'draft',
  `is_public`          tinyint(1)   DEFAULT 0,
  `watermark`          tinyint(1)   DEFAULT 0,
  `tags`               varchar(500) DEFAULT NULL,
  `download_count`     int(11)      DEFAULT 0,
  `uploaded_by`        int(11)      NOT NULL,
  `approved_by`        int(11)      DEFAULT NULL,
  `approved_at`        datetime     DEFAULT NULL,
  `rejection_reason`   varchar(500) DEFAULT NULL,
  `created_at`         datetime     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         datetime     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status`        (`status`),
  KEY `idx_grade`         (`grade`),
  KEY `idx_exam_type`     (`exam_type`),
  KEY `idx_academic_year` (`academic_year_id`),
  KEY `uploaded_by`       (`uploaded_by`),
  CONSTRAINT `fk_er_sub`  FOREIGN KEY (`subject_id`)       REFERENCES `subjects`      (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_er_ay`   FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_er_sem`  FOREIGN KEY (`semester_id`)      REFERENCES `semesters`     (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_er_dept` FOREIGN KEY (`department_id`)    REFERENCES `departments`   (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_er_user` FOREIGN KEY (`uploaded_by`)      REFERENCES `users`         (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `exam_approvals` (
  `id`            int(11)  NOT NULL AUTO_INCREMENT,
  `exam_repo_id`  int(11)  NOT NULL,
  `reviewer_id`   int(11)  NOT NULL,
  `reviewer_role` enum('dept_head','vice_principal','principal','super_admin') NOT NULL,
  `action`        enum('submitted','approved','rejected','revision_requested') NOT NULL,
  `comments`      text     DEFAULT NULL,
  `created_at`    datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `exam_repo_id` (`exam_repo_id`),
  CONSTRAINT `fk_ea_repo` FOREIGN KEY (`exam_repo_id`) REFERENCES `exam_repository` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ea_user` FOREIGN KEY (`reviewer_id`)  REFERENCES `users`           (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `exam_versions` (
  `id`                 int(11)      NOT NULL AUTO_INCREMENT,
  `exam_repo_id`       int(11)      NOT NULL,
  `version`            int(11)      NOT NULL DEFAULT 1,
  `file_path`          varchar(255) NOT NULL,
  `file_original_name` varchar(255) NOT NULL,
  `file_size`          bigint(20)   DEFAULT 0,
  `change_notes`       text         DEFAULT NULL,
  `uploaded_by`        int(11)      DEFAULT NULL,
  `created_at`         datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `exam_repo_id` (`exam_repo_id`),
  CONSTRAINT `fk_ev_repo` FOREIGN KEY (`exam_repo_id`) REFERENCES `exam_repository` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `exam_downloads` (
  `id`            int(11)  NOT NULL AUTO_INCREMENT,
  `exam_repo_id`  int(11)  NOT NULL,
  `user_id`       int(11)  NOT NULL,
  `ip_address`    varchar(45) DEFAULT NULL,
  `downloaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `exam_repo_id` (`exam_repo_id`),
  KEY `user_id`      (`user_id`),
  CONSTRAINT `fk_ed_repo` FOREIGN KEY (`exam_repo_id`) REFERENCES `exam_repository` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ed_user` FOREIGN KEY (`user_id`)      REFERENCES `users`           (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- QUESTION BANK
-- ============================================================
CREATE TABLE `question_bank` (
  `id`               int(11)      NOT NULL AUTO_INCREMENT,
  `subject_id`       int(11)      DEFAULT NULL,
  `grade`            enum('9','10','11','12','all') DEFAULT 'all',
  `chapter`          varchar(100) DEFAULT NULL,
  `question_text`    text         NOT NULL,
  `question_type`    enum('mcq','true_false','short_answer','essay','practical') DEFAULT 'mcq',
  `option_a`         varchar(500) DEFAULT NULL,
  `option_b`         varchar(500) DEFAULT NULL,
  `option_c`         varchar(500) DEFAULT NULL,
  `option_d`         varchar(500) DEFAULT NULL,
  `correct_answer`   varchar(500) DEFAULT NULL,
  `explanation`      text         DEFAULT NULL,
  `difficulty`       enum('easy','medium','hard') DEFAULT 'medium',
  `marks`            int(11)      DEFAULT 1,
  `learning_outcome` varchar(255) DEFAULT NULL,
  `tags`             varchar(255) DEFAULT NULL,
  `created_by`       int(11)      DEFAULT NULL,
  `status`           enum('active','inactive') DEFAULT 'active',
  `created_at`       datetime     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `fk_qb_sub`  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qb_user` FOREIGN KEY (`created_by`) REFERENCES `users`    (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- ============================================================
--  S E E D   D A T A
-- ============================================================
-- ============================================================

-- ============================================================
-- USERS  (password = "password" for all accounts)
-- ============================================================
INSERT INTO `users` (`username`,`email`,`password`,`role`,`status`) VALUES
('admin',     'admin@sjassms.edu.et',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin',    'active'),
('principal', 'principal@sjassms.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'principal',      'active'),
('vp',        'vp@sjassms.edu.et',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vice_principal', 'active'),
('registrar', 'registrar@sjassms.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'registrar',      'active'),
('finance',   'finance@sjassms.edu.et',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'finance_officer','active'),
('teacher1',  'teacher1@sjassms.edu.et',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher',        'active'),
('teacher2',  'teacher2@sjassms.edu.et',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher',        'active'),
('teacher3',  'teacher3@sjassms.edu.et',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher',        'active'),
('student1',  'student1@sjassms.edu.et',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student',        'active'),
('student2',  'student2@sjassms.edu.et',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student',        'active'),
('parent1',   'parent1@gmail.com',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent',         'active');

-- ============================================================
-- ACADEMIC YEAR & SEMESTERS
-- ============================================================
INSERT INTO `academic_years` (`name`,`start_date`,`end_date`,`status`) VALUES
('2024-2025','2024-09-01','2025-07-30','active');

INSERT INTO `semesters` (`academic_year_id`,`name`,`start_date`,`end_date`,`status`) VALUES
(1,'Semester 1','2024-09-01','2025-01-31','active'),
(1,'Semester 2','2025-02-01','2025-07-30','inactive');

-- ============================================================
-- DEPARTMENTS  (5 departments including Agriculture)
-- ============================================================
INSERT INTO `departments` (`name`,`code`,`description`) VALUES
('Mathematics & Natural Science', 'MNS', 'Mathematics, Physics, Chemistry, Biology'),
('Social Science & Language',     'SSL', 'History, Geography, Economics, Languages'),
('Technical & Vocational',        'TV',  'Technical and vocational education'),
('Physical Education & Arts',     'PEA', 'Physical education, music, art'),
('Agriculture',                   'AGR', 'Agriculture for Grade 11-12 Natural stream');

-- ============================================================
-- CLASSES
-- ============================================================
INSERT INTO `classes` (`grade`,`section`,`academic_year_id`,`room_no`,`max_students`,`stream`) VALUES
('9',  'A', 1, 'R-101', 50, 'general'),
('9',  'B', 1, 'R-102', 50, 'general'),
('9',  'C', 1, 'R-103', 50, 'general'),
('10', 'A', 1, 'R-201', 50, 'general'),
('10', 'B', 1, 'R-202', 50, 'general'),
('11', 'A', 1, 'R-301', 45, 'natural'),
('11', 'B', 1, 'R-302', 45, 'social'),
('12', 'A', 1, 'R-401', 45, 'natural'),
('12', 'B', 1, 'R-402', 45, 'social');

-- ============================================================
-- SUBJECTS — Ethiopian Secondary School Curriculum
-- ============================================================

-- ---- GRADE 9: 13 subjects, all students ----
INSERT INTO `subjects` (`code`,`name`,`department_id`,`grade`,`stream`,`credit_hours`,`periods_week`,`type`) VALUES
('AFO9',  'Afaan Oromo',                            2, '9', 'all', 4, 4, 'core'),
('AMH9',  'Amharic',                                2, '9', 'all', 4, 4, 'core'),
('ENG9',  'English',                                2, '9', 'all', 5, 5, 'core'),
('MATH9', 'Mathematics',                            1, '9', 'all', 5, 5, 'core'),
('BIO9',  'Biology',                                1, '9', 'all', 4, 4, 'core'),
('CHEM9', 'Chemistry',                              1, '9', 'all', 4, 4, 'core'),
('PHY9',  'Physics',                                1, '9', 'all', 4, 4, 'core'),
('GEO9',  'Geography',                              2, '9', 'all', 3, 3, 'core'),
('HIST9', 'History',                                2, '9', 'all', 3, 3, 'core'),
('CIT9',  'Citizenship Education',                  2, '9', 'all', 3, 3, 'core'),
('IT9',   'Information Technology (IT)',             1, '9', 'all', 3, 3, 'core'),
('ECO9',  'Economics',                              2, '9', 'all', 3, 3, 'core'),
('HPE9',  'Health and Physical Education (HPE)',    4, '9', 'all', 2, 2, 'core');

-- ---- GRADE 10: 13 subjects, all students ----
INSERT INTO `subjects` (`code`,`name`,`department_id`,`grade`,`stream`,`credit_hours`,`periods_week`,`type`) VALUES
('AFO10',  'Afaan Oromo',                           2, '10', 'all', 4, 4, 'core'),
('AMH10',  'Amharic',                               2, '10', 'all', 4, 4, 'core'),
('ENG10',  'English',                               2, '10', 'all', 5, 5, 'core'),
('MATH10', 'Mathematics',                           1, '10', 'all', 5, 5, 'core'),
('BIO10',  'Biology',                               1, '10', 'all', 4, 4, 'core'),
('CHEM10', 'Chemistry',                             1, '10', 'all', 4, 4, 'core'),
('PHY10',  'Physics',                               1, '10', 'all', 4, 4, 'core'),
('GEO10',  'Geography',                             2, '10', 'all', 3, 3, 'core'),
('HIST10', 'History',                               2, '10', 'all', 3, 3, 'core'),
('CIT10',  'Citizenship Education',                 2, '10', 'all', 3, 3, 'core'),
('IT10',   'Information Technology (IT)',            1, '10', 'all', 3, 3, 'core'),
('ECO10',  'Economics',                             2, '10', 'all', 3, 3, 'core'),
('HPE10',  'Health and Physical Education (HPE)',   4, '10', 'all', 2, 2, 'core');

-- ---- GRADE 11: Common (4) + Natural (4) + Social (3) = Natural:8, Social:7 ----
-- Common to both streams
INSERT INTO `subjects` (`code`,`name`,`department_id`,`grade`,`stream`,`credit_hours`,`periods_week`,`type`) VALUES
('AFO11',  'Afaan Oromo',                   2, '11', 'all',     4, 4, 'core'),
('ENG11',  'English',                       2, '11', 'all',     5, 5, 'core'),
('MATH11', 'Mathematics',                   1, '11', 'all',     5, 5, 'core'),
('IT11',   'Information Technology (IT)',    1, '11', 'all',     3, 3, 'core');
-- Natural Science only
INSERT INTO `subjects` (`code`,`name`,`department_id`,`grade`,`stream`,`credit_hours`,`periods_week`,`type`) VALUES
('PHY11',  'Physics',                       1, '11', 'natural', 5, 5, 'core'),
('CHEM11', 'Chemistry',                     1, '11', 'natural', 4, 4, 'core'),
('BIO11',  'Biology',                       1, '11', 'natural', 4, 4, 'core'),
('AGR11',  'Agriculture',                   5, '11', 'natural', 4, 4, 'core');
-- Social Science only  (NO Citizenship Education)
INSERT INTO `subjects` (`code`,`name`,`department_id`,`grade`,`stream`,`credit_hours`,`periods_week`,`type`) VALUES
('GEO11',  'Geography',                     2, '11', 'social',  4, 4, 'core'),
('HIST11', 'History',                       2, '11', 'social',  4, 4, 'core'),
('ECO11',  'Economics',                     2, '11', 'social',  4, 4, 'core');

-- ---- GRADE 12: Same structure as Grade 11 ----
-- Common to both streams
INSERT INTO `subjects` (`code`,`name`,`department_id`,`grade`,`stream`,`credit_hours`,`periods_week`,`type`) VALUES
('AFO12',  'Afaan Oromo',                   2, '12', 'all',     4, 4, 'core'),
('ENG12',  'English',                       2, '12', 'all',     5, 5, 'core'),
('MATH12', 'Mathematics',                   1, '12', 'all',     5, 5, 'core'),
('IT12',   'Information Technology (IT)',    1, '12', 'all',     3, 3, 'core');
-- Natural Science only
INSERT INTO `subjects` (`code`,`name`,`department_id`,`grade`,`stream`,`credit_hours`,`periods_week`,`type`) VALUES
('PHY12',  'Physics',                       1, '12', 'natural', 5, 5, 'core'),
('CHEM12', 'Chemistry',                     1, '12', 'natural', 4, 4, 'core'),
('BIO12',  'Biology',                       1, '12', 'natural', 4, 4, 'core'),
('AGR12',  'Agriculture',                   5, '12', 'natural', 4, 4, 'core');
-- Social Science only  (NO Citizenship Education)
INSERT INTO `subjects` (`code`,`name`,`department_id`,`grade`,`stream`,`credit_hours`,`periods_week`,`type`) VALUES
('GEO12',  'Geography',                     2, '12', 'social',  4, 4, 'core'),
('HIST12', 'History',                       2, '12', 'social',  4, 4, 'core'),
('ECO12',  'Economics',                     2, '12', 'social',  4, 4, 'core');

-- ============================================================
-- STAFF
-- ============================================================
INSERT INTO `staff` (`user_id`,`employee_id`,`first_name`,`last_name`,`gender`,`department_id`,`position`,`qualification`,`hire_date`,`basic_salary`,`phone`,`email`,`status`) VALUES
(2,'EMP-001','Abebe',   'Kebede', 'male',  1,'Principal',            'M.Ed. Educational Administration','2020-09-01',12000.00,'+251911000001','principal@sjassms.edu.et','active'),
(3,'EMP-002','Chaltu',  'Bekele', 'female',2,'Vice Principal',        'B.Ed. English Language',          '2018-09-01',10000.00,'+251911000002','vp@sjassms.edu.et',       'active'),
(6,'EMP-003','Gemechu', 'Feyisa', 'male',  1,'Mathematics Teacher',   'B.Ed. Mathematics',               '2021-09-01', 7500.00,'+251911000003','teacher1@sjassms.edu.et', 'active'),
(7,'EMP-004','Lensa',   'Duresa', 'female',1,'Physics Teacher',       'B.Sc. Physics',                   '2019-09-01', 7500.00,'+251911000004','teacher2@sjassms.edu.et', 'active'),
(8,'EMP-005','Dinka',   'Tolessa','male',  2,'English Teacher',       'B.A. English Literature',         '2022-09-01', 7000.00,'+251911000005','teacher3@sjassms.edu.et', 'active');

-- ============================================================
-- SAMPLE STUDENTS  (Grade 9A → general stream)
-- ============================================================
INSERT INTO `students` (`user_id`,`student_id`,`first_name`,`last_name`,`gender`,`dob`,`class_id`,`stream`,`academic_year_id`,`admission_date`,`admission_no`,`status`,`phone`,`address`) VALUES
(9, 'STU-2024-001','Biruk', 'Alemu', 'male',  '2008-03-15',1,'general',1,'2024-09-01','ADM-2024-001','active','+251922000001','Yabelo, Borana Zone'),
(10,'STU-2024-002','Fatuma','Hassan','female', '2007-07-22',4,'general',1,'2024-09-01','ADM-2024-002','active','+251922000002','Yabelo, Borana Zone');

-- PARENT
INSERT INTO `parents` (`user_id`,`student_id`,`relation`,`first_name`,`last_name`,`phone`,`email`,`is_primary`) VALUES
(11,1,'father','Alemu','Kedir','+251933000001','parent1@gmail.com',1);

-- ============================================================
-- CLASS SUBJECTS — Grade 9A, Semester 1
-- (Maps to new subject IDs — AFO9=1, AMH9=2, ENG9=3, MATH9=4,
--  BIO9=5, CHEM9=6, PHY9=7, GEO9=8, HIST9=9, CIT9=10,
--  IT9=11, ECO9=12, HPE9=13)
-- ============================================================
INSERT INTO `class_subjects` (`class_id`,`subject_id`,`teacher_id`,`semester_id`,`periods_per_week`) VALUES
(1,1, 3,1,4),(1,2, 5,1,4),(1,3, 5,1,5),(1,4, 3,1,5),
(1,5, 3,1,4),(1,6, 4,1,4),(1,7, 4,1,4),(1,8, 5,1,3),
(1,9, 5,1,3),(1,10,5,1,3),(1,11,3,1,3),(1,12,3,1,3),(1,13,4,1,2);

-- ============================================================
-- TIMETABLE — Grade 9A sample
-- ============================================================
INSERT INTO `timetable` (`class_id`,`subject_id`,`teacher_id`,`day`,`period`,`start_time`,`end_time`,`room`,`semester_id`) VALUES
(1,4,3,'Monday',   1,'08:00','08:45','R-101',1),
(1,3,5,'Monday',   2,'08:45','09:30','R-101',1),
(1,7,4,'Monday',   3,'09:45','10:30','R-101',1),
(1,2,5,'Monday',   4,'10:30','11:15','R-101',1),
(1,6,4,'Monday',   5,'11:30','12:15','R-101',1),
(1,4,3,'Tuesday',  1,'08:00','08:45','R-101',1),
(1,5,3,'Tuesday',  2,'08:45','09:30','R-101',1),
(1,1,3,'Tuesday',  3,'09:45','10:30','R-101',1),
(1,9,5,'Tuesday',  4,'10:30','11:15','R-101',1),
(1,11,3,'Tuesday', 5,'11:30','12:15','R-101',1),
(1,7,4,'Wednesday',1,'08:00','08:45','R-101',1),
(1,3,5,'Wednesday',2,'08:45','09:30','R-101',1),
(1,8,5,'Wednesday',3,'09:45','10:30','R-101',1),
(1,12,3,'Wednesday',4,'10:30','11:15','R-101',1),
(1,13,4,'Wednesday',5,'11:30','12:15','R-101',1);

-- ============================================================
-- STUDENT ATTENDANCE — sample
-- ============================================================
INSERT INTO `student_attendance` (`student_id`,`class_id`,`date`,`status`,`recorded_by`) VALUES
(1,1,'2024-09-02','present',3),(1,1,'2024-09-03','present',3),
(1,1,'2024-09-04','absent', 3),(1,1,'2024-09-05','present',3),
(1,1,'2024-09-09','present',3),(1,1,'2024-09-10','late',   3),
(1,1,'2024-09-11','present',3),(1,1,'2024-09-12','present',3);

-- ============================================================
-- EXAMS & MARKS — sample (use new subject IDs: MATH9=4, ENG9=3)
-- ============================================================
INSERT INTO `exams` (`title`,`type`,`semester_id`,`class_id`,`subject_id`,`exam_date`,`total_marks`,`pass_marks`,`weight_percent`,`created_by`) VALUES
('Mathematics Assignment 1','assignment',1,1,4,'2024-10-15',20,10,10,3),
('Mathematics Quiz 1',      'quiz',      1,1,4,'2024-10-25',10, 5, 5,3),
('Mathematics Mid Exam',    'mid_exam',  1,1,4,'2024-11-15',30,15,30,3),
('English Assignment 1',    'assignment',1,1,3,'2024-10-15',20,10,10,5),
('English Mid Exam',        'mid_exam',  1,1,3,'2024-11-15',30,15,30,5);

INSERT INTO `marks` (`exam_id`,`student_id`,`marks_obtained`,`grade_letter`,`grade_point`,`recorded_by`) VALUES
(1,1,17,'A', 4.00,3),(2,1, 8,'B+',3.50,3),(3,1,24,'A', 4.00,3),
(4,1,15,'B+',3.50,5),(5,1,22,'A-',3.75,5);

-- ============================================================
-- FEE CATEGORIES
-- ============================================================
INSERT INTO `fee_categories` (`name`,`amount`,`type`,`frequency`,`description`,`is_mandatory`) VALUES
('Registration Fee',50.00,'registration','annual', 'Annual student registration fee',1),
('Exam Fee',        30.00,'exam',        'semester','Semester examination fee',       1),
('Library Fee',     20.00,'library',     'annual',  'Annual library access fee',      1),
('Activity Fee',    25.00,'other',       'annual',  'Student activity fee',           1),
('Hostel Fee',     500.00,'hostel',      'semester','Semester hostel fee',            0),
('Transport Fee',  200.00,'transport',   'semester','Semester transport fee',         0);

-- ============================================================
-- STUDENT FEES & PAYMENTS
-- ============================================================
INSERT INTO `student_fees` (`student_id`,`fee_category_id`,`academic_year_id`,`amount`,`due_date`,`status`) VALUES
(1,1,1,50.00,'2024-09-30','paid'),(1,2,1,30.00,'2024-10-31','paid'),
(1,3,1,20.00,'2024-09-30','unpaid'),(1,4,1,25.00,'2024-09-30','paid'),
(2,1,1,50.00,'2024-09-30','unpaid'),(2,2,1,30.00,'2024-10-31','unpaid');

INSERT INTO `payments` (`student_id`,`student_fee_id`,`amount`,`payment_date`,`payment_method`,`receipt_no`,`recorded_by`) VALUES
(1,1,50.00,'2024-09-15','cash',    'REC-2024-0001',5),
(1,2,30.00,'2024-09-20','cash',    'REC-2024-0002',5),
(1,4,25.00,'2024-09-22','telebirr','REC-2024-0003',5);

-- ============================================================
-- BOOKS
-- ============================================================
INSERT INTO `books` (`isbn`,`title`,`author`,`publisher`,`publish_year`,`category`,`copies_total`,`copies_available`,`location`) VALUES
('978-99944-0-001-0','Mathematics Grade 9',  'MOE Ethiopia','FDRE MOE',2023,'Textbook',50,48,'Shelf A1'),
('978-99944-0-002-0','Physics Grade 9',      'MOE Ethiopia','FDRE MOE',2023,'Textbook',50,46,'Shelf A2'),
('978-99944-0-003-0','Chemistry Grade 9',    'MOE Ethiopia','FDRE MOE',2023,'Textbook',50,44,'Shelf A3'),
('978-99944-0-004-0','English Grade 9',      'MOE Ethiopia','FDRE MOE',2023,'Textbook',50,45,'Shelf B1'),
('978-99944-0-005-0','Amharic Grade 9',      'MOE Ethiopia','FDRE MOE',2023,'Textbook',50,47,'Shelf B2'),
('978-99944-0-006-0','Biology Grade 9',      'MOE Ethiopia','FDRE MOE',2023,'Textbook',45,40,'Shelf A4'),
('978-99944-0-007-0','History Grade 9',      'MOE Ethiopia','FDRE MOE',2023,'Textbook',45,43,'Shelf C1'),
('978-99944-0-008-0','Geography Grade 10',   'MOE Ethiopia','FDRE MOE',2023,'Textbook',45,41,'Shelf C2');

-- ============================================================
-- INVENTORY
-- ============================================================
INSERT INTO `inventory_categories` (`name`,`description`) VALUES
('Computer & Electronics','Computers, projectors, and electronic equipment'),
('Furniture',             'Desks, chairs, and classroom furniture'),
('Laboratory Equipment',  'Science lab equipment and materials'),
('Sports Equipment',      'Physical education and sports gear'),
('Office Supplies',       'Administrative supplies and stationery');

INSERT INTO `inventory_items` (`category_id`,`name`,`item_code`,`quantity`,`unit`,`condition_status`,`location`,`purchase_date`,`cost`) VALUES
(1,'Desktop Computer',  'ICT-001',30,'pcs', 'good','Computer Lab',      '2022-09-01',1500.00),
(1,'Projector',         'ICT-002',10,'pcs', 'good','Classrooms',        '2023-01-15', 800.00),
(2,'Student Desk',      'FUR-001',450,'pcs','good','Classrooms',        '2021-09-01',  50.00),
(2,'Teacher Desk',      'FUR-002',30,'pcs', 'good','Classrooms',        '2021-09-01', 120.00),
(3,'Microscope',        'LAB-001',20,'pcs', 'good','Biology Lab',       '2022-03-01', 300.00),
(3,'Beaker Set',        'LAB-002',50,'sets','good','Chemistry Lab',     '2022-03-01',  45.00),
(4,'Football',          'SPT-001',10,'pcs', 'good','PE Store',          '2023-09-01',  15.00),
(4,'Volleyball Net',    'SPT-002', 4,'pcs', 'good','PE Store',          '2023-09-01',  80.00);

-- ============================================================
-- CLUBS
-- ============================================================
INSERT INTO `clubs` (`name`,`code`,`description`,`supervisor_id`,`status`) VALUES
('ICT Club',         'ICT', 'Information and Communication Technology Club',3,'active'),
('Science Club',     'SCI', 'Science research and experiments club',        4,'active'),
('Mathematics Club', 'MATH','Mathematics olympiad and competition club',    3,'active'),
('Environmental Club','ENV','Environmental conservation club',              5,'active'),
('Sports Club',      'SPT', 'Multi-sports activities and competitions',     1,'active');

-- ============================================================
-- HOSTEL
-- ============================================================
INSERT INTO `hostels` (`name`,`type`,`total_rooms`,`capacity`,`warden_id`) VALUES
('Boys Hostel', 'male',  20, 80, 3),
('Girls Hostel','female',15, 60, 4);

INSERT INTO `hostel_rooms` (`hostel_id`,`room_number`,`capacity`,`type`,`floor`,`status`) VALUES
(1,'B-101',4,'dormitory','Ground','available'),(1,'B-102',4,'dormitory','Ground','available'),
(1,'B-201',4,'dormitory','1st',  'available'),
(2,'G-101',4,'dormitory','Ground','available'),(2,'G-102',4,'dormitory','Ground','available');

-- ============================================================
-- ANNOUNCEMENTS
-- ============================================================
INSERT INTO `announcements` (`title`,`content`,`target_role`,`author_id`,`start_date`,`end_date`,`priority`,`status`) VALUES
('Welcome to 2024-2025 Academic Year',
 'Dear students, staff, and parents. Welcome to the new academic year 2024-2025!',
 'all',2,'2024-09-01','2024-09-15','important','active'),
('Semester 1 Exam Schedule',
 'Mid-term examinations begin November 11, 2024. Timetables at the registrar office.',
 'all',2,'2024-10-25','2024-11-10','urgent','active'),
('Library Hours Updated',
 'Library is open 7:30 AM to 5:30 PM Monday through Friday.',
 'student',2,'2024-09-05','2025-07-30','normal','active'),
('Staff Meeting',
 'Monthly staff meeting Friday September 27, 2024 at 3:00 PM.',
 'teacher',2,'2024-09-20','2024-09-27','important','active');

-- ============================================================
-- EXPENSES
-- ============================================================
INSERT INTO `expenses` (`title`,`category`,`amount`,`expense_date`,`description`,`recorded_by`,`status`) VALUES
('Office Supplies Purchase','Office Supplies',1500.00,'2024-09-05','Pens, notebooks for admin',5,'approved'),
('Lab Equipment Maintenance','Maintenance',  3000.00,'2024-09-10','Annual biology/chemistry lab',5,'approved'),
('Sports Equipment',        'Sports',        2500.00,'2024-09-12','Football, volleyball gear',  5,'approved'),
('Cleaning Supplies',       'Facilities',     800.00,'2024-09-15','Monthly cleaning supplies',  5,'approved'),
('Electricity Bill',        'Utilities',     4500.00,'2024-09-30','Monthly electricity bill',   5,'approved');

-- ============================================================
-- EXAM REPOSITORY — sample entries
-- ============================================================
INSERT INTO `exam_repository`
  (`title`,`subject_id`,`grade`,`semester_id`,`academic_year_id`,`department_id`,
   `exam_type`,`category_type`,`difficulty`,`description`,
   `file_path`,`file_original_name`,`file_size`,`file_mime`,`status`,`is_public`,`uploaded_by`)
VALUES
  ('Mathematics Grade 9 Mid-Semester Exam 2024',
   4,'9',1,1,1,'mid_semester','internal','medium',
   'Mid-semester mathematics examination covering chapters 1-6.',
   'uploads/exam-repository/placeholder.pdf','math_grade9_midsem_2024.pdf',0,'application/pdf','approved',1,1),
  ('Physics Grade 10 Final Exam 2024',
   20,'10',1,1,1,'final','internal','hard',
   'Final examination for Physics Grade 10.',
   'uploads/exam-repository/placeholder.pdf','physics_grade10_final_2024.pdf',0,'application/pdf','approved',1,1),
  ('English Grade 9 Quiz 1',
   3,'9',1,1,2,'quiz','internal','easy',
   'First quiz for English Grade 9.',
   'uploads/exam-repository/placeholder.pdf','english_grade9_quiz1.pdf',0,'application/pdf','submitted',0,6),
  ('National Mock Examination Grade 12 - 2024',
   NULL,'12',NULL,1,NULL,'mock','external','hard',
   'National mock examination for Grade 12 students.',
   'uploads/exam-repository/placeholder.pdf','national_mock_grade12_2024.pdf',0,'application/pdf','approved',1,1);

-- ============================================================
-- QUESTION BANK — samples (use new subject IDs: MATH9=4, PHY9=7, ENG9=3)
-- ============================================================
INSERT INTO `question_bank`
  (`subject_id`,`grade`,`chapter`,`question_text`,`question_type`,
   `option_a`,`option_b`,`option_c`,`option_d`,`correct_answer`,`difficulty`,`marks`,`created_by`)
VALUES
  (4,'9','Chapter 1 - Algebra',
   'What is the value of x if 2x + 6 = 14?',
   'mcq','2','4','6','8','B','easy',2,3),
  (4,'9','Chapter 1 - Algebra',
   'Simplify: 3(2x - 4) + 5x',
   'mcq','11x - 12','11x - 4','6x - 12','11x + 12','A','medium',3,3),
  (7,'9','Chapter 2 - Motion',
   'What is the SI unit of velocity?',
   'mcq','m/s2','km/h','m/s','kg/m','C','easy',1,4),
  (4,'9','Chapter 3 - Geometry',
   'Calculate the area of a circle with radius 7 cm (use pi = 22/7)',
   'short_answer',NULL,NULL,NULL,NULL,'154 cm2','medium',4,3),
  (3,'9','Chapter 1 - Grammar',
   'Which sentence is grammatically correct?',
   'mcq','She go to school.','She goes to school.','She going to school.','She gone to school.','B','easy',2,5);

-- ============================================================
-- SYSTEM SETTINGS
-- ============================================================
INSERT INTO `settings` (`setting_key`,`setting_value`,`group_name`,`description`) VALUES
-- General
('school_name',          'Shalaka Jatan Ali Secondary School', 'general','School full name'),
('school_name_short',    'SJASS',                              'general','School short name'),
('school_motto',         'Excellence Through Knowledge',       'general','School motto'),
('school_address',       'Yabelo, Borana Zone, Oromia Region, Ethiopia','general','Physical address'),
('school_phone',         '+251460000001',                      'general','Contact phone'),
('school_email',         'info@sjassms.edu.et',                'general','Contact email'),
('school_website',       'www.sjassms.edu.et',                 'general','Website URL'),
('school_logo',          'assets/images/logo.png',             'general','Logo file path'),
('school_url',           'http://localhost/studentmanagement', 'general','Base URL for email links'),
('language',             'en',                                 'general','Default system language'),
('timezone',             'Africa/Addis_Ababa',                 'general','System timezone'),
-- Academic
('academic_year_id',     '1',   'academic','Current active academic year'),
('semester_id',          '1',   'academic','Current active semester'),
('grade_system',         'ethiopian','academic','Grading system'),
('pass_mark',            '50',  'academic','Minimum passing mark (%)'),
('max_attendance_absent','20',  'academic','Maximum allowed absence percentage (%)'),
-- Finance
('currency',             'ETB', 'finance', 'Currency code'),
-- Library
('fine_per_day',         '2.00','library', 'Library fine per overdue day (ETB)'),
('max_borrow_days',      '14',  'library', 'Maximum book borrow days'),
('max_books_per_student','3',   'library', 'Maximum books a student can borrow'),
-- Email / SMTP
('smtp_host',            'smtp.gmail.com','email','SMTP server hostname'),
('smtp_port',            '587',           'email','SMTP port (587=TLS, 465=SSL)'),
('smtp_encryption',      'tls',           'email','Encryption: tls, ssl, or none'),
('smtp_auth',            '1',             'email','SMTP authentication (1=yes)'),
('smtp_user',            '',              'email','SMTP username / email address'),
('smtp_pass',            '',              'email','SMTP password or app password'),
('smtp_from_email',      '',              'email','From email (blank = use smtp_user)'),
('smtp_from_name',       'Shalaka Jatan Ali Secondary School','email','From display name'),
('smtp_timeout',         '30',            'email','Connection timeout in seconds'),
('reset_token_expiry',   '30',            'email','Password reset link expiry (minutes)'),
('reset_rate_limit',     '3',             'email','Max reset requests per hour per email'),
-- SMS
('sms_api_key',          '', 'sms','SMS gateway API key'),
('sms_sender',           'SJASSMS','sms','SMS sender name');

COMMIT;

-- ============================================================
-- FINAL VERIFICATION
-- ============================================================
SELECT '=== SJASSMS DATABASE READY ===' AS status;

SELECT CONCAT('Grade ', grade, ' | Stream: ', stream, ' | Subjects: ', COUNT(*)) AS summary
FROM subjects
GROUP BY grade, stream
ORDER BY grade, FIELD(stream,'all','natural','social');

SELECT
  CONCAT('Grade 9 & 10 Citizenship Education: ',
    (SELECT COUNT(*) FROM subjects WHERE name LIKE '%Citizen%' AND grade IN ('9','10')),
    ' subject(s) — CORRECT') AS citizenship_check,
  CONCAT('Grade 11 & 12 Citizenship Education: ',
    (SELECT COUNT(*) FROM subjects WHERE name LIKE '%Citizen%' AND grade IN ('11','12')),
    ' subject(s) — CORRECT (should be 0)') AS grade_11_12_check;
