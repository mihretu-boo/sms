-- ============================================================
-- Ethiopian Secondary School Curriculum — Migration
-- Shalaka Jatan Ali Secondary School
-- Grade 9-12 correct subject structure
-- ============================================================
USE `sjassms`;

-- ===== STEP 1: Add stream column to subjects =====
ALTER TABLE `subjects`
  ADD COLUMN IF NOT EXISTS `stream` enum('all','natural','social') DEFAULT 'all' AFTER `grade`,
  ADD COLUMN IF NOT EXISTS `periods_week` int(11) DEFAULT 5 AFTER `credit_hours`;

-- ===== STEP 2: Add stream to students =====
ALTER TABLE `students`
  ADD COLUMN IF NOT EXISTS `stream` enum('general','natural','social') DEFAULT 'general' AFTER `class_id`;

-- ===== STEP 3: Update classes — ensure stream enum matches =====
-- (stream column already exists: 'natural','social','general')

-- ===== STEP 4: Update existing Grade 9 subjects (IDs 1-12) =====
UPDATE `subjects` SET code='AFO9',  name='Afaan Oromo',                       stream='all', credit_hours=4, periods_week=4 WHERE id=7;
UPDATE `subjects` SET code='AMH9',  name='Amharic',                           stream='all', credit_hours=4, periods_week=4 WHERE id=6;
UPDATE `subjects` SET code='ENG9',  name='English',                           stream='all', credit_hours=5, periods_week=5 WHERE id=5;
UPDATE `subjects` SET code='MATH9', name='Mathematics',                       stream='all', credit_hours=5, periods_week=5 WHERE id=1;
UPDATE `subjects` SET code='BIO9',  name='Biology',                           stream='all', credit_hours=4, periods_week=4 WHERE id=4;
UPDATE `subjects` SET code='CHEM9', name='Chemistry',                         stream='all', credit_hours=4, periods_week=4 WHERE id=3;
UPDATE `subjects` SET code='PHY9',  name='Physics',                           stream='all', credit_hours=4, periods_week=4 WHERE id=2;
UPDATE `subjects` SET code='GEO9',  name='Geography',                         stream='all', credit_hours=3, periods_week=3 WHERE id=9;
UPDATE `subjects` SET code='HIST9', name='History',                           stream='all', credit_hours=3, periods_week=3 WHERE id=8;
UPDATE `subjects` SET code='ECO9',  name='Economics',                         stream='all', credit_hours=3, periods_week=3 WHERE id=10;
UPDATE `subjects` SET code='IT9',   name='Information Technology (IT)',       stream='all', credit_hours=3, periods_week=3 WHERE id=11;
UPDATE `subjects` SET code='HPE9',  name='Health and Physical Education (HPE)',stream='all', credit_hours=2, periods_week=2 WHERE id=12;

-- Insert missing Grade 9 subject: Citizenship Education
INSERT IGNORE INTO `subjects` (code, name, department_id, grade, stream, credit_hours, periods_week, type)
VALUES ('CIT9', 'Citizenship Education', 2, '9', 'all', 3, 3, 'core');

-- ===== STEP 5: Update existing Grade 10 subjects (IDs 13-19) =====
UPDATE `subjects` SET code='MATH10', name='Mathematics',                        stream='all', credit_hours=5, periods_week=5 WHERE id=13;
UPDATE `subjects` SET code='PHY10',  name='Physics',                            stream='all', credit_hours=4, periods_week=4 WHERE id=14;
UPDATE `subjects` SET code='CHEM10', name='Chemistry',                          stream='all', credit_hours=4, periods_week=4 WHERE id=15;
UPDATE `subjects` SET code='BIO10',  name='Biology',                            stream='all', credit_hours=4, periods_week=4 WHERE id=16;
UPDATE `subjects` SET code='ENG10',  name='English',                            stream='all', credit_hours=5, periods_week=5 WHERE id=17;
UPDATE `subjects` SET code='AMH10',  name='Amharic',                            stream='all', credit_hours=4, periods_week=4 WHERE id=18;
UPDATE `subjects` SET code='AFO10',  name='Afaan Oromo',                        stream='all', credit_hours=4, periods_week=4 WHERE id=19;

-- Insert missing Grade 10 subjects
INSERT IGNORE INTO `subjects` (code, name, department_id, grade, stream, credit_hours, periods_week, type) VALUES
('GEO10',  'Geography',                          2, '10', 'all', 3, 3, 'core'),
('HIST10', 'History',                            2, '10', 'all', 3, 3, 'core'),
('ECO10',  'Economics',                          2, '10', 'all', 3, 3, 'core'),
('CIT10',  'Citizenship Education',              2, '10', 'all', 3, 3, 'core'),
('IT10',   'Information Technology (IT)',        1, '10', 'all', 3, 3, 'core'),
('HPE10',  'Health and Physical Education (HPE)',4, '10', 'all', 2, 2, 'core');

-- ===== STEP 6: Update existing Grade 11 subjects (IDs 20-24) =====
-- Set stream for existing ones
UPDATE `subjects` SET code='MATH11', name='Mathematics',    stream='all',     credit_hours=5, periods_week=5 WHERE id=20;
UPDATE `subjects` SET code='PHY11',  name='Physics',        stream='natural', credit_hours=5, periods_week=5 WHERE id=21;
UPDATE `subjects` SET code='CHEM11', name='Chemistry',      stream='natural', credit_hours=4, periods_week=4 WHERE id=22;
UPDATE `subjects` SET code='BIO11',  name='Biology',        stream='natural', credit_hours=4, periods_week=4 WHERE id=23;
UPDATE `subjects` SET code='ENG11',  name='English',        stream='all',     credit_hours=5, periods_week=5 WHERE id=24;

-- Insert missing Grade 11 subjects
INSERT IGNORE INTO `subjects` (code, name, department_id, grade, stream, credit_hours, periods_week, type) VALUES
-- Common (both streams)
('AFO11', 'Afaan Oromo',               2, '11', 'all',     4, 4, 'core'),
('IT11',  'Information Technology (IT)',1, '11', 'all',     3, 3, 'core'),
-- Natural Science only
('AGR11', 'Agriculture',               1, '11', 'natural', 4, 4, 'core'),
-- Social Science only
('GEO11', 'Geography',                 2, '11', 'social',  4, 4, 'core'),
('HIST11','History',                   2, '11', 'social',  4, 4, 'core'),
('ECO11', 'Economics',                 2, '11', 'social',  4, 4, 'core'),
('CIT11', 'Citizenship Education',     2, '11', 'social',  3, 3, 'core');

-- ===== STEP 7: Update existing Grade 12 subjects (IDs 25-29) =====
UPDATE `subjects` SET code='MATH12', name='Mathematics',    stream='all',     credit_hours=5, periods_week=5 WHERE id=25;
UPDATE `subjects` SET code='PHY12',  name='Physics',        stream='natural', credit_hours=5, periods_week=5 WHERE id=26;
UPDATE `subjects` SET code='CHEM12', name='Chemistry',      stream='natural', credit_hours=4, periods_week=4 WHERE id=27;
UPDATE `subjects` SET code='BIO12',  name='Biology',        stream='natural', credit_hours=4, periods_week=4 WHERE id=28;
UPDATE `subjects` SET code='ENG12',  name='English',        stream='all',     credit_hours=5, periods_week=5 WHERE id=29;

-- Insert missing Grade 12 subjects
INSERT IGNORE INTO `subjects` (code, name, department_id, grade, stream, credit_hours, periods_week, type) VALUES
-- Common (both streams)
('AFO12', 'Afaan Oromo',               2, '12', 'all',     4, 4, 'core'),
('IT12',  'Information Technology (IT)',1, '12', 'all',     3, 3, 'core'),
-- Natural Science only
('AGR12', 'Agriculture',               1, '12', 'natural', 4, 4, 'core'),
-- Social Science only
('GEO12', 'Geography',                 2, '12', 'social',  4, 4, 'core'),
('HIST12','History',                   2, '12', 'social',  4, 4, 'core'),
('ECO12', 'Economics',                 2, '12', 'social',  4, 4, 'core'),
('CIT12', 'Citizenship Education',     2, '12', 'social',  3, 3, 'core');

-- ===== STEP 8: Update departments for Agriculture =====
INSERT IGNORE INTO `departments` (name, code, description) VALUES
('Agriculture', 'AGR', 'Agriculture and natural sciences for Grade 11-12 Natural stream');

UPDATE `subjects` SET department_id = (SELECT id FROM departments WHERE code='AGR' LIMIT 1)
WHERE code IN ('AGR11','AGR12');

-- ===== STEP 9: Update students stream based on class stream =====
UPDATE `students` s
JOIN `classes` c ON s.class_id = c.id
SET s.stream = c.stream
WHERE s.stream = 'general' AND c.stream != 'general';

-- ===== STEP 10: Verify =====
SELECT grade, stream, COUNT(*) as subject_count,
       GROUP_CONCAT(name ORDER BY name SEPARATOR ', ') as subjects
FROM subjects
WHERE code NOT LIKE '%old%'
GROUP BY grade, stream
ORDER BY grade, stream;
