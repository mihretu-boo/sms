-- ============================================================
-- Shalaka Jatan Ali Secondary School Management System (SJASSMS)
-- Database Schema v1.0
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ============================================================
-- Create Database
-- ============================================================
CREATE DATABASE IF NOT EXISTS `sjassms` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sjassms`;

-- ============================================================
-- USERS TABLE (all system users)
-- ============================================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','principal','vice_principal','registrar','teacher','dept_head','student','parent','finance_officer') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `photo` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `two_fa_secret` varchar(100) DEFAULT NULL,
  `two_fa_enabled` tinyint(1) DEFAULT 0,
  `lang` varchar(10) DEFAULT 'en',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ACADEMIC YEARS
-- ============================================================
CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive','archived') DEFAULT 'inactive',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEMESTERS
-- ============================================================
CREATE TABLE `semesters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive','archived') DEFAULT 'inactive',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `fk_sem_ay` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DEPARTMENTS
-- ============================================================
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `head_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CLASSES (Grade 9-12 with sections)
-- ============================================================
CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `grade` enum('9','10','11','12') NOT NULL,
  `section` varchar(5) NOT NULL DEFAULT 'A',
  `name` varchar(20) GENERATED ALWAYS AS (CONCAT('Grade ', grade, '-', section)) STORED,
  `class_teacher_id` int(11) DEFAULT NULL,
  `academic_year_id` int(11) NOT NULL,
  `room_no` varchar(10) DEFAULT NULL,
  `max_students` int(11) DEFAULT 50,
  `stream` enum('natural','social','general') DEFAULT 'general',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `class_teacher_id` (`class_teacher_id`),
  CONSTRAINT `fk_cls_ay` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STUDENTS
-- ============================================================
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `student_id` varchar(20) NOT NULL UNIQUE,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `dob` date NOT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT 'Ethiopian',
  `religion` varchar(50) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `academic_year_id` int(11) DEFAULT NULL,
  `admission_date` date NOT NULL,
  `admission_no` varchar(20) NOT NULL UNIQUE,
  `status` enum('active','inactive','graduated','transferred','expelled') DEFAULT 'active',
  `photo` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `previous_school` varchar(150) DEFAULT NULL,
  `medical_info` text DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `class_id` (`class_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `fk_stu_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stu_cls` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stu_ay` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PARENTS / GUARDIANS
-- ============================================================
CREATE TABLE `parents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `relation` enum('father','mother','guardian','other') NOT NULL DEFAULT 'father',
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `alt_phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_par_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_par_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STAFF
-- ============================================================
CREATE TABLE `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `employee_id` varchar(20) NOT NULL UNIQUE,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `dob` date DEFAULT NULL,
  `nationality` varchar(50) DEFAULT 'Ethiopian',
  `department_id` int(11) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `qualification` varchar(200) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `contract_type` enum('permanent','contract','temporary') DEFAULT 'permanent',
  `basic_salary` decimal(10,2) DEFAULT 0.00,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','terminated','on_leave') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_stf_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stf_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SUBJECTS
-- ============================================================
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(15) NOT NULL UNIQUE,
  `name` varchar(100) NOT NULL,
  `name_am` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `grade` enum('9','10','11','12','all') DEFAULT 'all',
  `credit_hours` int(11) DEFAULT 3,
  `type` enum('core','elective','optional') DEFAULT 'core',
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_sub_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CLASS SUBJECTS (Subjects assigned to a class in a semester)
-- ============================================================
CREATE TABLE `class_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `semester_id` int(11) NOT NULL,
  `periods_per_week` int(11) DEFAULT 5,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cls_sub_sem` (`class_id`,`subject_id`,`semester_id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `semester_id` (`semester_id`),
  CONSTRAINT `fk_cs_cls` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cs_sub` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cs_sem` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STUDENT ATTENDANCE
-- ============================================================
CREATE TABLE `student_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL DEFAULT 'present',
  `remarks` varchar(255) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_stu_att` (`student_id`,`date`),
  KEY `class_id` (`class_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `fk_sa_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sa_cls` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STAFF ATTENDANCE
-- ============================================================
CREATE TABLE `staff_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('present','absent','late','half_day','on_leave') DEFAULT 'present',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_stf_att` (`staff_id`,`date`),
  CONSTRAINT `fk_stfa_stf` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- EXAMS
-- ============================================================
CREATE TABLE `exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `type` enum('assignment','quiz','project','mid_exam','final_exam','national_prep') NOT NULL,
  `semester_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_date` date DEFAULT NULL,
  `total_marks` decimal(6,2) NOT NULL DEFAULT 100,
  `pass_marks` decimal(6,2) NOT NULL DEFAULT 50,
  `weight_percent` decimal(5,2) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `semester_id` (`semester_id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `fk_ex_sem` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ex_cls` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ex_sub` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- MARKS / GRADES
-- ============================================================
CREATE TABLE `marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `marks_obtained` decimal(6,2) NOT NULL DEFAULT 0,
  `grade_letter` varchar(5) DEFAULT NULL,
  `grade_point` decimal(4,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mark_exam_stu` (`exam_id`,`student_id`),
  KEY `exam_id` (`exam_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_mk_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mk_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TIMETABLE
-- ============================================================
CREATE TABLE `timetable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `period` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room` varchar(20) DEFAULT NULL,
  `semester_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `semester_id` (`semester_id`),
  CONSTRAINT `fk_tt_cls` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tt_sub` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tt_sem` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ASSIGNMENTS
-- ============================================================
CREATE TABLE `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `due_date` datetime NOT NULL,
  `max_marks` decimal(6,2) DEFAULT 100,
  `file_path` varchar(255) DEFAULT NULL,
  `semester_id` int(11) NOT NULL,
  `status` enum('active','closed') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ASSIGNMENT SUBMISSIONS
-- ============================================================
CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `submitted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `file_path` varchar(255) DEFAULT NULL,
  `text_content` text DEFAULT NULL,
  `marks` decimal(6,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `status` enum('submitted','late','graded') DEFAULT 'submitted',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sub_asgn_stu` (`assignment_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_sub_asgn` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sub_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- LEARNING MATERIALS
-- ============================================================
CREATE TABLE `materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` enum('pdf','doc','ppt','video','link','other') DEFAULT 'pdf',
  `external_url` varchar(500) DEFAULT NULL,
  `semester_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- LIBRARY BOOKS
-- ============================================================
CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isbn` varchar(20) DEFAULT NULL UNIQUE,
  `barcode` varchar(50) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `author` varchar(150) NOT NULL,
  `publisher` varchar(150) DEFAULT NULL,
  `publish_year` year DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `language` varchar(50) DEFAULT 'English',
  `copies_total` int(11) NOT NULL DEFAULT 1,
  `copies_available` int(11) NOT NULL DEFAULT 1,
  `location` varchar(50) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`),
  KEY `idx_author` (`author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BOOK BORROWINGS
-- ============================================================
CREATE TABLE `book_borrowings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `fine` decimal(8,2) DEFAULT 0.00,
  `fine_paid` tinyint(1) DEFAULT 0,
  `status` enum('borrowed','returned','overdue','lost') DEFAULT 'borrowed',
  `notes` text DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
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
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0,
  `type` enum('tuition','registration','exam','library','hostel','transport','uniform','other') DEFAULT 'tuition',
  `frequency` enum('one_time','monthly','semester','annual') DEFAULT 'semester',
  `description` text DEFAULT NULL,
  `is_mandatory` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STUDENT FEES (Assigned fees per student per period)
-- ============================================================
CREATE TABLE `student_fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `fee_category_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `semester_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `discount` decimal(10,2) DEFAULT 0,
  `waiver_reason` varchar(255) DEFAULT NULL,
  `status` enum('unpaid','partial','paid','waived') DEFAULT 'unpaid',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `fee_category_id` (`fee_category_id`),
  CONSTRAINT `fk_sf_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sf_fc` FOREIGN KEY (`fee_category_id`) REFERENCES `fee_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PAYMENTS
-- ============================================================
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `student_fee_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','bank_transfer','telebirr','cbe_birr','other') DEFAULT 'cash',
  `receipt_no` varchar(30) NOT NULL UNIQUE,
  `transaction_ref` varchar(100) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `student_fee_id` (`student_fee_id`),
  CONSTRAINT `fk_pay_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- EXPENSES
-- ============================================================
CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PAYROLL
-- ============================================================
CREATE TABLE `payroll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `month` int(2) NOT NULL,
  `year` int(4) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `allowances` decimal(10,2) DEFAULT 0,
  `deductions` decimal(10,2) DEFAULT 0,
  `income_tax` decimal(10,2) DEFAULT 0,
  `pension` decimal(10,2) DEFAULT 0,
  `net_salary` decimal(10,2) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` enum('bank','cash','telebirr') DEFAULT 'bank',
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payroll` (`staff_id`,`month`,`year`),
  CONSTRAINT `fk_pr_stf` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ANNOUNCEMENTS
-- ============================================================
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `target_role` varchar(50) DEFAULT 'all',
  `target_class_id` int(11) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `priority` enum('normal','important','urgent') DEFAULT 'normal',
  `attachment` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- MESSAGES (Internal Messaging)
-- ============================================================
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `content` text NOT NULL,
  `read_at` datetime DEFAULT NULL,
  `is_deleted_sender` tinyint(1) DEFAULT 0,
  `is_deleted_receiver` tinyint(1) DEFAULT 0,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `fk_msg_sndr` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_rcvr` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NOTIFICATIONS
-- ============================================================
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger') DEFAULT 'info',
  `icon` varchar(50) DEFAULT 'bell',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DISCIPLINE INCIDENTS
-- ============================================================
CREATE TABLE `discipline_incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `incident_date` date NOT NULL,
  `incident_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `action_taken` text DEFAULT NULL,
  `parent_notified` tinyint(1) DEFAULT 0,
  `parent_notified_date` date DEFAULT NULL,
  `severity` enum('minor','moderate','major','critical') DEFAULT 'minor',
  `status` enum('open','resolved','escalated') DEFAULT 'open',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_di_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- HOSTELS
-- ============================================================
CREATE TABLE `hostels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('male','female','mixed') DEFAULT 'mixed',
  `total_rooms` int(11) DEFAULT 0,
  `capacity` int(11) DEFAULT 0,
  `warden_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- HOSTEL ROOMS
-- ============================================================
CREATE TABLE `hostel_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostel_id` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `capacity` int(11) DEFAULT 4,
  `current_occupancy` int(11) DEFAULT 0,
  `type` enum('single','double','dormitory') DEFAULT 'double',
  `floor` varchar(10) DEFAULT NULL,
  `status` enum('available','full','maintenance') DEFAULT 'available',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_hr_hostel` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- HOSTEL ALLOCATIONS
-- ============================================================
CREATE TABLE `hostel_allocations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT 0,
  `status` enum('active','vacated') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ha_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `fk_ha_room` FOREIGN KEY (`room_id`) REFERENCES `hostel_rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- VEHICLES (Transport)
-- ============================================================
CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plate_no` varchar(20) NOT NULL UNIQUE,
  `type` enum('bus','minibus','van') DEFAULT 'bus',
  `capacity` int(11) DEFAULT 40,
  `driver_name` varchar(100) DEFAULT NULL,
  `driver_phone` varchar(20) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `status` enum('active','maintenance','retired') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TRANSPORT ROUTES
-- ============================================================
CREATE TABLE `transport_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `stops` text DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `morning_time` time DEFAULT NULL,
  `evening_time` time DEFAULT NULL,
  `monthly_fee` decimal(8,2) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vehicle_id` (`vehicle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- INVENTORY CATEGORIES
-- ============================================================
CREATE TABLE `inventory_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- INVENTORY ITEMS
-- ============================================================
CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `item_code` varchar(30) DEFAULT NULL UNIQUE,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(20) DEFAULT 'pcs',
  `condition_status` enum('excellent','good','fair','poor','damaged') DEFAULT 'good',
  `location` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT 0,
  `supplier` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `fk_inv_cat` FOREIGN KEY (`category_id`) REFERENCES `inventory_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CLUBS
-- ============================================================
CREATE TABLE `clubs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `meeting_schedule` varchar(200) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CLUB MEMBERS
-- ============================================================
CREATE TABLE `club_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `club_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `role` enum('member','secretary','treasurer','president','vice_president') DEFAULT 'member',
  `joined_date` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cm` (`club_id`,`student_id`),
  CONSTRAINT `fk_cm_club` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cm_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- LEAVE REQUESTS
-- ============================================================
CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `leave_type` enum('annual','sick','maternity','paternity','emergency','unpaid','other') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days` int(11) NOT NULL,
  `reason` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `fk_lr_stf` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STUDENT TRANSFERS
-- ============================================================
CREATE TABLE `transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `transfer_type` enum('in','out') NOT NULL,
  `from_school` varchar(200) DEFAULT NULL,
  `to_school` varchar(200) DEFAULT NULL,
  `transfer_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `certificate_no` varchar(30) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_tr_stu` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- AUDIT LOGS
-- ============================================================
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_data` text DEFAULT NULL,
  `new_data` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_module` (`module`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SYSTEM SETTINGS
-- ============================================================
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `group_name` varchar(50) DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PROMOTIONS (Year-end student promotion)
-- ============================================================
CREATE TABLE `promotions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `from_class_id` int(11) NOT NULL,
  `to_class_id` int(11) DEFAULT NULL,
  `academic_year_id` int(11) NOT NULL,
  `status` enum('promoted','repeated','graduated','transferred') DEFAULT 'promoted',
  `gpa` decimal(4,2) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `promoted_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SMS LOGS
-- ============================================================
CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'general',
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `sent_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PASSWORD RESETS
-- ============================================================
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ============================================================
-- SEED DATA
-- ============================================================
-- ============================================================

-- Default Admin User (password: Admin@123)
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES
('admin', 'admin@sjassms.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active'),
('principal', 'principal@sjassms.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'principal', 'active'),
('vp', 'vp@sjassms.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vice_principal', 'active'),
('registrar', 'registrar@sjassms.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'registrar', 'active'),
('finance', 'finance@sjassms.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'finance_officer', 'active'),
('teacher1', 'teacher1@sjassms.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active'),
('teacher2', 'teacher2@sjassms.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active'),
('teacher3', 'teacher3@sjassms.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active'),
('student1', 'student1@sjassms.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
('student2', 'student2@sjassms.edu.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
('parent1', 'parent1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 'active');

-- Academic Year
INSERT INTO `academic_years` (`name`, `start_date`, `end_date`, `status`) VALUES
('2024-2025', '2024-09-01', '2025-07-30', 'active');

-- Semesters
INSERT INTO `semesters` (`academic_year_id`, `name`, `start_date`, `end_date`, `status`) VALUES
(1, 'Semester 1', '2024-09-01', '2025-01-31', 'active'),
(1, 'Semester 2', '2025-02-01', '2025-07-30', 'inactive');

-- Departments
INSERT INTO `departments` (`name`, `code`, `description`) VALUES
('Mathematics & Natural Science', 'MNS', 'Mathematics, Physics, Chemistry, Biology'),
('Social Science & Language', 'SSL', 'History, Geography, Economics, Languages'),
('Technical & Vocational', 'TV', 'Technical and vocational education'),
('Physical Education & Arts', 'PEA', 'Physical education, music, art');

-- Classes
INSERT INTO `classes` (`grade`, `section`, `academic_year_id`, `room_no`, `max_students`) VALUES
('9', 'A', 1, 'R-101', 50), ('9', 'B', 1, 'R-102', 50), ('9', 'C', 1, 'R-103', 50),
('10', 'A', 1, 'R-201', 50), ('10', 'B', 1, 'R-202', 50),
('11', 'A', 1, 'R-301', 45), ('11', 'B', 1, 'R-302', 45),
('12', 'A', 1, 'R-401', 45), ('12', 'B', 1, 'R-402', 45);

-- Subjects (Ethiopian curriculum)
INSERT INTO `subjects` (`code`, `name`, `department_id`, `grade`, `credit_hours`, `type`) VALUES
('MATH9', 'Mathematics', 1, '9', 5, 'core'),
('PHY9', 'Physics', 1, '9', 4, 'core'),
('CHEM9', 'Chemistry', 1, '9', 4, 'core'),
('BIO9', 'Biology', 1, '9', 4, 'core'),
('ENG9', 'English Language', 2, '9', 5, 'core'),
('AMH9', 'Amharic Language', 2, '9', 4, 'core'),
('ORM9', 'Afaan Oromoo', 2, '9', 4, 'core'),
('HIST9', 'History', 2, '9', 3, 'core'),
('GEO9', 'Geography', 2, '9', 3, 'core'),
('ECO9', 'Economics', 2, '9', 3, 'core'),
('ICT9', 'Information Technology', 1, '9', 3, 'core'),
('PE9', 'Physical Education', 4, '9', 2, 'core'),
('MATH10', 'Mathematics', 1, '10', 5, 'core'),
('PHY10', 'Physics', 1, '10', 4, 'core'),
('CHEM10', 'Chemistry', 1, '10', 4, 'core'),
('BIO10', 'Biology', 1, '10', 4, 'core'),
('ENG10', 'English Language', 2, '10', 5, 'core'),
('AMH10', 'Amharic Language', 2, '10', 4, 'core'),
('ORM10', 'Afaan Oromoo', 2, '10', 4, 'core'),
('MATH11', 'Mathematics', 1, '11', 5, 'core'),
('PHY11', 'Physics', 1, '11', 5, 'core'),
('CHEM11', 'Chemistry', 1, '11', 4, 'core'),
('BIO11', 'Biology', 1, '11', 4, 'core'),
('ENG11', 'English Language', 2, '11', 5, 'core'),
('MATH12', 'Mathematics', 1, '12', 5, 'core'),
('PHY12', 'Physics', 1, '12', 5, 'core'),
('CHEM12', 'Chemistry', 1, '12', 4, 'core'),
('BIO12', 'Biology', 1, '12', 4, 'core'),
('ENG12', 'English Language', 2, '12', 5, 'core');

-- Staff
INSERT INTO `staff` (`user_id`, `employee_id`, `first_name`, `last_name`, `gender`, `department_id`, `position`, `qualification`, `hire_date`, `basic_salary`, `phone`, `email`, `status`) VALUES
(2, 'EMP-001', 'Abebe', 'Kebede', 'male', 1, 'Principal', 'M.Ed. Educational Administration', '2020-09-01', 12000.00, '+251911000001', 'principal@sjassms.edu.et', 'active'),
(3, 'EMP-002', 'Chaltu', 'Bekele', 'female', 2, 'Vice Principal', 'B.Ed. English Language', '2018-09-01', 10000.00, '+251911000002', 'vp@sjassms.edu.et', 'active'),
(6, 'EMP-003', 'Gemechu', 'Feyisa', 'male', 1, 'Mathematics Teacher', 'B.Ed. Mathematics', '2021-09-01', 7500.00, '+251911000003', 'teacher1@sjassms.edu.et', 'active'),
(7, 'EMP-004', 'Lensa', 'Duresa', 'female', 1, 'Physics Teacher', 'B.Sc. Physics', '2019-09-01', 7500.00, '+251911000004', 'teacher2@sjassms.edu.et', 'active'),
(8, 'EMP-005', 'Dinka', 'Tolessa', 'male', 2, 'English Teacher', 'B.A. English Literature', '2022-09-01', 7000.00, '+251911000005', 'teacher3@sjassms.edu.et', 'active');

-- Sample Students
INSERT INTO `students` (`user_id`, `student_id`, `first_name`, `last_name`, `gender`, `dob`, `class_id`, `academic_year_id`, `admission_date`, `admission_no`, `status`, `phone`, `address`) VALUES
(9, 'STU-2024-001', 'Biruk', 'Alemu', 'male', '2008-03-15', 1, 1, '2024-09-01', 'ADM-2024-001', 'active', '+251922000001', 'Yabelo, Borana Zone'),
(10, 'STU-2024-002', 'Fatuma', 'Hassan', 'female', '2007-07-22', 4, 1, '2024-09-01', 'ADM-2024-002', 'active', '+251922000002', 'Yabelo, Borana Zone');

-- Parents
INSERT INTO `parents` (`user_id`, `student_id`, `relation`, `first_name`, `last_name`, `phone`, `email`, `is_primary`) VALUES
(11, 1, 'father', 'Alemu', 'Kedir', '+251933000001', 'parent1@gmail.com', 1);

-- Fee Categories
INSERT INTO `fee_categories` (`name`, `amount`, `type`, `frequency`, `description`, `is_mandatory`) VALUES
('Tuition Fee', 0.00, 'tuition', 'semester', 'Government school - no tuition fee', 0),
('Registration Fee', 50.00, 'registration', 'annual', 'Annual student registration fee', 1),
('Exam Fee', 30.00, 'exam', 'semester', 'Semester examination fee', 1),
('Library Fee', 20.00, 'library', 'annual', 'Annual library access fee', 1),
('Activity Fee', 25.00, 'other', 'annual', 'Student activity and development fee', 1),
('Hostel Fee', 500.00, 'hostel', 'semester', 'Semester hostel accommodation', 0),
('Transport Fee', 200.00, 'transport', 'semester', 'Semester transport fee', 0);

-- Books
INSERT INTO `books` (`isbn`, `title`, `author`, `publisher`, `publish_year`, `category`, `copies_total`, `copies_available`, `location`) VALUES
('978-99944-0-001-0', 'Mathematics Grade 9', 'MOE Ethiopia', 'FDRE MOE', 2023, 'Textbook', 50, 48, 'Shelf A1'),
('978-99944-0-002-0', 'Physics Grade 9', 'MOE Ethiopia', 'FDRE MOE', 2023, 'Textbook', 50, 46, 'Shelf A2'),
('978-99944-0-003-0', 'Chemistry Grade 9', 'MOE Ethiopia', 'FDRE MOE', 2023, 'Textbook', 50, 44, 'Shelf A3'),
('978-99944-0-004-0', 'English Grade 9', 'MOE Ethiopia', 'FDRE MOE', 2023, 'Textbook', 50, 45, 'Shelf B1'),
('978-99944-0-005-0', 'Amharic Grade 9', 'MOE Ethiopia', 'FDRE MOE', 2023, 'Textbook', 50, 47, 'Shelf B2'),
('978-99944-0-006-0', 'Biology Grade 9', 'MOE Ethiopia', 'FDRE MOE', 2023, 'Textbook', 45, 40, 'Shelf A4'),
('978-99944-0-007-0', 'History Grade 9', 'MOE Ethiopia', 'FDRE MOE', 2023, 'Textbook', 45, 43, 'Shelf C1'),
('978-99944-0-008-0', 'Geography Grade 10', 'MOE Ethiopia', 'FDRE MOE', 2023, 'Textbook', 45, 41, 'Shelf C2');

-- Inventory Categories
INSERT INTO `inventory_categories` (`name`, `description`) VALUES
('Computer & Electronics', 'Computers, projectors, and electronic equipment'),
('Furniture', 'Desks, chairs, and classroom furniture'),
('Laboratory Equipment', 'Science lab equipment and materials'),
('Sports Equipment', 'Physical education and sports gear'),
('Office Supplies', 'Administrative supplies and stationery');

-- Inventory Items
INSERT INTO `inventory_items` (`category_id`, `name`, `item_code`, `quantity`, `unit`, `condition_status`, `location`, `purchase_date`, `cost`) VALUES
(1, 'Desktop Computer', 'ICT-001', 30, 'pcs', 'good', 'Computer Lab', '2022-09-01', 1500.00),
(1, 'Projector', 'ICT-002', 10, 'pcs', 'good', 'Various Classrooms', '2023-01-15', 800.00),
(2, 'Student Desk', 'FUR-001', 450, 'pcs', 'good', 'Classrooms', '2021-09-01', 50.00),
(2, 'Teacher Desk', 'FUR-002', 30, 'pcs', 'good', 'Classrooms', '2021-09-01', 120.00),
(3, 'Microscope', 'LAB-001', 20, 'pcs', 'good', 'Biology Lab', '2022-03-01', 300.00),
(3, 'Beaker Set', 'LAB-002', 50, 'sets', 'good', 'Chemistry Lab', '2022-03-01', 45.00),
(4, 'Football', 'SPT-001', 10, 'pcs', 'good', 'PE Store', '2023-09-01', 15.00),
(4, 'Volleyball Net', 'SPT-002', 4, 'pcs', 'good', 'PE Store', '2023-09-01', 80.00);

-- Clubs
INSERT INTO `clubs` (`name`, `code`, `description`, `supervisor_id`, `status`) VALUES
('ICT Club', 'ICT', 'Information and Communication Technology Club', 3, 'active'),
('Science Club', 'SCI', 'Science research and experiments club', 4, 'active'),
('Mathematics Club', 'MATH', 'Mathematics olympiad and competition club', 3, 'active'),
('Environmental Club', 'ENV', 'Environmental conservation and awareness club', 5, 'active'),
('Sports Club', 'SPT', 'Multi-sports activities and competitions', 1, 'active');

-- System Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `group_name`, `description`) VALUES
('school_name', 'Shalaka Jatan Ali Secondary School', 'general', 'School full name'),
('school_name_short', 'SJASS', 'general', 'School short name'),
('school_motto', 'Excellence Through Knowledge', 'general', 'School motto'),
('school_address', 'Yabelo, Borana Zone, Oromia Region, Ethiopia', 'general', 'Physical address'),
('school_phone', '+251460000001', 'general', 'Contact phone'),
('school_email', 'info@sjassms.edu.et', 'general', 'Contact email'),
('school_website', 'www.sjassms.edu.et', 'general', 'Website URL'),
('school_logo', 'assets/images/logo.png', 'general', 'Logo file path'),
('academic_year_id', '1', 'academic', 'Current active academic year'),
('semester_id', '1', 'academic', 'Current active semester'),
('grade_system', 'ethiopian', 'academic', 'Grading system'),
('pass_mark', '50', 'academic', 'Minimum passing mark (%)'),
('max_attendance_absent', '20', 'academic', 'Maximum allowed absence percentage'),
('currency', 'ETB', 'finance', 'Currency code'),
('fine_per_day', '2.00', 'library', 'Library fine per overdue day'),
('max_borrow_days', '14', 'library', 'Maximum book borrow days'),
('max_books_per_student', '3', 'library', 'Maximum books a student can borrow'),
('smtp_host', 'smtp.gmail.com', 'email', 'SMTP server'),
('smtp_port', '587', 'email', 'SMTP port'),
('smtp_user', '', 'email', 'SMTP username'),
('smtp_pass', '', 'email', 'SMTP password'),
('sms_api_key', '', 'sms', 'SMS gateway API key'),
('sms_sender', 'SJASSMS', 'sms', 'SMS sender name'),
('language', 'en', 'general', 'Default system language'),
('timezone', 'Africa/Addis_Ababa', 'general', 'System timezone');

-- Class Subjects mapping for Grade 9A, Semester 1
INSERT INTO `class_subjects` (`class_id`, `subject_id`, `teacher_id`, `semester_id`, `periods_per_week`) VALUES
(1, 1, 3, 1, 5), (1, 2, 4, 1, 4), (1, 3, 4, 1, 4), (1, 4, 3, 1, 4),
(1, 5, 5, 1, 5), (1, 6, 5, 1, 4), (1, 7, 5, 1, 4), (1, 8, 5, 1, 3),
(1, 9, 5, 1, 3), (1, 10, 3, 1, 3), (1, 11, 3, 1, 3), (1, 12, 4, 1, 2);

-- Sample Timetable for Grade 9A
INSERT INTO `timetable` (`class_id`, `subject_id`, `teacher_id`, `day`, `period`, `start_time`, `end_time`, `room`, `semester_id`) VALUES
(1, 1, 3, 'Monday', 1, '08:00:00', '08:45:00', 'R-101', 1),
(1, 5, 5, 'Monday', 2, '08:45:00', '09:30:00', 'R-101', 1),
(1, 2, 4, 'Monday', 3, '09:45:00', '10:30:00', 'R-101', 1),
(1, 6, 5, 'Monday', 4, '10:30:00', '11:15:00', 'R-101', 1),
(1, 3, 4, 'Monday', 5, '11:30:00', '12:15:00', 'R-101', 1),
(1, 1, 3, 'Tuesday', 1, '08:00:00', '08:45:00', 'R-101', 1),
(1, 4, 3, 'Tuesday', 2, '08:45:00', '09:30:00', 'R-101', 1),
(1, 7, 5, 'Tuesday', 3, '09:45:00', '10:30:00', 'R-101', 1),
(1, 8, 5, 'Tuesday', 4, '10:30:00', '11:15:00', 'R-101', 1),
(1, 11, 3, 'Tuesday', 5, '11:30:00', '12:15:00', 'R-101', 1),
(1, 2, 4, 'Wednesday', 1, '08:00:00', '08:45:00', 'R-101', 1),
(1, 5, 5, 'Wednesday', 2, '08:45:00', '09:30:00', 'R-101', 1),
(1, 9, 5, 'Wednesday', 3, '09:45:00', '10:30:00', 'R-101', 1),
(1, 10, 3, 'Wednesday', 4, '10:30:00', '11:15:00', 'R-101', 1),
(1, 12, 4, 'Wednesday', 5, '11:30:00', '12:15:00', 'R-101', 1);

-- Sample Attendance for Student 1
INSERT INTO `student_attendance` (`student_id`, `class_id`, `date`, `status`, `recorded_by`) VALUES
(1, 1, '2024-09-02', 'present', 3), (1, 1, '2024-09-03', 'present', 3),
(1, 1, '2024-09-04', 'absent', 3), (1, 1, '2024-09-05', 'present', 3),
(1, 1, '2024-09-09', 'present', 3), (1, 1, '2024-09-10', 'late', 3),
(1, 1, '2024-09-11', 'present', 3), (1, 1, '2024-09-12', 'present', 3);

-- Sample Announcements
INSERT INTO `announcements` (`title`, `content`, `target_role`, `author_id`, `start_date`, `end_date`, `priority`, `status`) VALUES
('Welcome to 2024-2025 Academic Year', 'Dear students, staff, and parents. We are delighted to welcome you to the new academic year 2024-2025. Let us work together to achieve excellence!', 'all', 2, '2024-09-01', '2024-09-15', 'important', 'active'),
('Semester 1 Exam Schedule', 'The Semester 1 mid-term examinations will begin on November 11, 2024. Students are advised to prepare adequately. Timetables are available at the registrar office.', 'all', 2, '2024-10-25', '2024-11-10', 'urgent', 'active'),
('Library Hours Updated', 'The school library is now open from 7:30 AM to 5:30 PM Monday through Friday. Students are encouraged to make use of library resources.', 'student', 2, '2024-09-05', '2025-07-30', 'normal', 'active'),
('Staff Meeting', 'All teaching staff are required to attend the monthly staff meeting on Friday, September 27, 2024 at 3:00 PM in the conference room.', 'teacher', 2, '2024-09-20', '2024-09-27', 'important', 'active');

-- Sample Exams
INSERT INTO `exams` (`title`, `type`, `semester_id`, `class_id`, `subject_id`, `exam_date`, `total_marks`, `pass_marks`, `weight_percent`, `created_by`) VALUES
('Mathematics Assignment 1', 'assignment', 1, 1, 1, '2024-10-15', 20, 10, 10, 3),
('Mathematics Quiz 1', 'quiz', 1, 1, 1, '2024-10-25', 10, 5, 5, 3),
('Mathematics Mid Exam', 'mid_exam', 1, 1, 1, '2024-11-15', 30, 15, 30, 3),
('English Assignment 1', 'assignment', 1, 1, 5, '2024-10-15', 20, 10, 10, 5),
('English Mid Exam', 'mid_exam', 1, 1, 5, '2024-11-15', 30, 15, 30, 5);

-- Sample Marks
INSERT INTO `marks` (`exam_id`, `student_id`, `marks_obtained`, `grade_letter`, `grade_point`, `recorded_by`) VALUES
(1, 1, 17, 'A', 4.0, 3), (2, 1, 8, 'B+', 3.5, 3), (3, 1, 24, 'A', 4.0, 3),
(4, 1, 15, 'B+', 3.5, 5), (5, 1, 22, 'A-', 3.75, 5);

-- Hostel data
INSERT INTO `hostels` (`name`, `type`, `total_rooms`, `capacity`, `warden_id`) VALUES
('Boys Hostel', 'male', 20, 80, 3),
('Girls Hostel', 'female', 15, 60, 4);

INSERT INTO `hostel_rooms` (`hostel_id`, `room_number`, `capacity`, `type`, `floor`, `status`) VALUES
(1, 'B-101', 4, 'dormitory', 'Ground', 'available'),
(1, 'B-102', 4, 'dormitory', 'Ground', 'available'),
(1, 'B-201', 4, 'dormitory', '1st', 'available'),
(2, 'G-101', 4, 'dormitory', 'Ground', 'available'),
(2, 'G-102', 4, 'dormitory', 'Ground', 'available');

-- Student Fees
INSERT INTO `student_fees` (`student_id`, `fee_category_id`, `academic_year_id`, `amount`, `due_date`, `status`) VALUES
(1, 2, 1, 50.00, '2024-09-30', 'paid'),
(1, 3, 1, 30.00, '2024-10-31', 'paid'),
(1, 4, 1, 20.00, '2024-09-30', 'unpaid'),
(1, 5, 1, 25.00, '2024-09-30', 'paid'),
(2, 2, 1, 50.00, '2024-09-30', 'unpaid'),
(2, 3, 1, 30.00, '2024-10-31', 'unpaid');

-- Payments
INSERT INTO `payments` (`student_id`, `student_fee_id`, `amount`, `payment_date`, `payment_method`, `receipt_no`, `recorded_by`) VALUES
(1, 1, 50.00, '2024-09-15', 'cash', 'REC-2024-0001', 5),
(1, 2, 30.00, '2024-09-20', 'cash', 'REC-2024-0002', 5),
(1, 4, 25.00, '2024-09-22', 'telebirr', 'REC-2024-0003', 5);

-- Expenses
INSERT INTO `expenses` (`title`, `category`, `amount`, `expense_date`, `description`, `recorded_by`, `status`) VALUES
('Office Supplies Purchase', 'Office Supplies', 1500.00, '2024-09-05', 'Pens, notebooks, paper for admin office', 5, 'approved'),
('Lab Equipment Maintenance', 'Maintenance', 3000.00, '2024-09-10', 'Annual maintenance of biology and chemistry lab', 5, 'approved'),
('Sports Equipment', 'Sports', 2500.00, '2024-09-12', 'Football, volleyball, athletics equipment', 5, 'approved'),
('Cleaning Supplies', 'Facilities', 800.00, '2024-09-15', 'Monthly cleaning supplies for the school', 5, 'approved'),
('Electricity Bill', 'Utilities', 4500.00, '2024-09-30', 'Monthly electricity bill', 5, 'approved');

COMMIT;
