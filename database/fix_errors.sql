-- ============================================================
-- SJASSMS — Database Fix Script
-- 1. Remove Citizenship Education from Grade 11 & 12 Social Science
-- 2. Data integrity cleanup
-- ============================================================
USE `sjassms`;

-- ===== FIX 1: Remove Citizenship Education from Grade 11 & 12 Social Science =====
-- Remove from class_subjects assignments first (if any were auto-assigned)
DELETE cs FROM class_subjects cs
  JOIN subjects s ON cs.subject_id = s.id
  WHERE s.code IN ('CIT11','CIT12')
    AND s.grade IN ('11','12')
    AND s.stream = 'social';

-- Remove any exams linked to these subjects (cascade safety)
DELETE e FROM exams e
  JOIN subjects s ON e.subject_id = s.id
  WHERE s.code IN ('CIT11','CIT12')
    AND s.grade IN ('11','12')
    AND s.stream = 'social';

-- Now delete the subjects
DELETE FROM subjects WHERE code IN ('CIT11','CIT12') AND grade IN ('11','12') AND stream = 'social';

-- ===== FIX 2: Verify correct Grade 11/12 Social Science subjects =====
-- Should be: Afaan Oromo, English, Math, IT (common) + Geography, History, Economics (social)
SELECT 'Grade 11/12 Social Science subjects after fix:' as info;
SELECT code, name, grade, stream
FROM subjects
WHERE grade IN ('11','12') AND (stream = 'all' OR stream = 'social')
ORDER BY grade, stream, name;

-- ===== FIX 3: Ensure no orphaned class_subjects =====
DELETE cs FROM class_subjects cs
  LEFT JOIN subjects s ON cs.subject_id = s.id
  WHERE s.id IS NULL;

DELETE cs FROM class_subjects cs
  LEFT JOIN classes c ON cs.class_id = c.id
  WHERE c.id IS NULL;

DELETE cs FROM class_subjects cs
  LEFT JOIN semesters sem ON cs.semester_id = sem.id
  WHERE sem.id IS NULL;

-- ===== FIX 4: Ensure no orphaned marks =====
DELETE m FROM marks m
  LEFT JOIN exams e ON m.exam_id = e.id
  WHERE e.id IS NULL;

-- ===== FIX 5: Fix NULL created_by on any existing exams (set to admin user) =====
UPDATE exams SET created_by = 1 WHERE created_by IS NULL;

-- ===== FIX 6: Fix NULL reported_by on discipline incidents =====
UPDATE discipline_incidents SET reported_by = 1 WHERE reported_by IS NULL OR NOT EXISTS (
  SELECT 1 FROM users u WHERE u.id = discipline_incidents.reported_by
);

-- ===== FIX 7: Ensure admin user (id=1) exists and is active =====
UPDATE users SET status = 'active' WHERE id = 1 AND status != 'active';

-- ===== FIX 8: Fix exam_repository uploaded_by NULL references =====
UPDATE exam_repository SET uploaded_by = 1 WHERE NOT EXISTS (
  SELECT 1 FROM users u WHERE u.id = exam_repository.uploaded_by
);

-- ===== FIX 9: Ensure school_url setting exists =====
INSERT IGNORE INTO settings (setting_key, setting_value, group_name, description)
VALUES ('school_url', 'http://localhost/studentmanagement', 'general', 'Base URL for email links');

-- ===== FIX 10: Ensure stream column defaults are correct =====
UPDATE students SET stream = 'general' WHERE stream IS NULL;
UPDATE subjects SET stream = 'all' WHERE stream IS NULL;

-- ===== FIX 11: Fix any Grade 9/10 students accidentally assigned a non-general stream =====
UPDATE students s
  JOIN classes c ON s.class_id = c.id
  SET s.stream = 'general'
  WHERE c.grade IN ('9','10') AND s.stream != 'general';

-- ===== FIX 12: Sync student stream with class stream for Grade 11/12 =====
UPDATE students s
  JOIN classes c ON s.class_id = c.id
  SET s.stream = c.stream
  WHERE c.grade IN ('11','12')
    AND c.stream != 'general'
    AND s.stream = 'general';

-- ===== VERIFICATION =====
SELECT 'Citizenship Education check (should be Grade 9 & 10 only):' as check_name;
SELECT id, code, name, grade, stream FROM subjects WHERE name LIKE '%Citizen%' ORDER BY grade;

SELECT 'Grade 11/12 subject count by stream:' as check_name;
SELECT grade,
  CASE stream WHEN 'all' THEN 'Common (Both Streams)' WHEN 'natural' THEN 'Natural Science' WHEN 'social' THEN 'Social Science' END as stream_label,
  COUNT(*) as subject_count
FROM subjects
WHERE grade IN ('11','12')
GROUP BY grade, stream
ORDER BY grade, stream;

SELECT 'Grade 11/12 Natural Science (8 subjects):' as check_name;
SELECT name FROM subjects WHERE grade='11' AND (stream='all' OR stream='natural') ORDER BY name;

SELECT 'Grade 11/12 Social Science (7 subjects):' as check_name;
SELECT name FROM subjects WHERE grade='11' AND (stream='all' OR stream='social') ORDER BY name;
