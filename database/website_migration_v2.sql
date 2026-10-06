-- ============================================================
-- Website Migration v2 — new settings + gallery video support
-- Run against sjassms database after website_tables.sql
-- ============================================================

-- Gallery: add video support columns (conditional, MySQL 8.0 compatible)
SET @dbname = DATABASE();

SET @sql_video = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'website_gallery' AND COLUMN_NAME = 'video_url') = 0,
  'ALTER TABLE website_gallery ADD COLUMN video_url VARCHAR(500) DEFAULT NULL',
  'SELECT 1 -- video_url already exists'
));
PREPARE _stmt FROM @sql_video; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;

SET @sql_mtype = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'website_gallery' AND COLUMN_NAME = 'media_type') = 0,
  "ALTER TABLE website_gallery ADD COLUMN media_type ENUM('photo','video') DEFAULT 'photo'",
  'SELECT 1 -- media_type already exists'
));
PREPARE _stmt FROM @sql_mtype; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;

-- New website_settings entries
INSERT IGNORE INTO website_settings (setting_key, setting_value) VALUES

-- Principal
('principal_name',           'Ato/Aadde [Principal Name]'),
('principal_title_en',       'School Principal'),
('principal_title_om',       'Hogganaa Mana Barumsaa'),
('principal_title_am',       'የትምህርት ቤቱ ዳይሬክተር'),
('principal_message_en',     'Dear students, parents, and community members, it is with great pride that I welcome you to Shalaka Jatan Ali Secondary School. Our dedicated teachers and staff work tirelessly every day to provide the highest quality education rooted in Ethiopian values and modern learning methods. Together we are shaping the leaders of tomorrow.'),
('principal_message_om',     'Barattoota, maatii fi miseensota hawaasaa kabajamoo, baga nagaan dhuftan mana barumsaa ol-aanaa Shalaka Jatan Ali. Barsiisotaa fi hojjattoota keenya amanamoon guyyaatti barnootaa gaarii Itoophiyaa gatii fi mala barumsa ammayyaa irratti hundaa''a kennan. Waliin hoggantootni boru hojjachaa jirra.'),
('principal_message_am',     'ውድ ተማሪዎቻቸን፣ ወላጆቻቸን እና የማህበረሰብ አባላቱ፣ ወደ ሻላካ ጃታን አሊ ሁለተኛ ደረጃ ትምህርት ቤት በደስታ ይቀበሉ። ቁርጠኛ መምህራኖቻችን እና ሰራተኞቻችን ኢትዮጵያዊ እሴቶቻችን ላይ የተመሰረተ ጥራት ያለው ትምህርት ለማቅረብ ሳይሰለቱ ይሰራሉ። ከሁሉ ጋር የነገ መሪዎችን እንቀርፃለን።'),
('vice_principal_name',      ''),
('vice_principal_title_en',  'Vice Principal'),
('vice_principal_title_om',  'Hogganaa Itti-aanaa'),
('vice_principal_title_am',  'ምክትል ዳይሬክተር'),

-- History
('school_founded',           '1999'),
('school_history_en',        'Shalaka Jatan Ali Secondary School was established in 1999 E.C. to meet the growing educational needs of the community. Starting with only four classrooms and a handful of dedicated teachers, the school quickly grew to become one of the leading secondary schools in the region. Today the school serves over 1,200 students and continues to uphold a tradition of academic excellence.'),
('school_history_om',        'Manni barumsaa ol-aanaa Shalaka Jatan Ali bara 1999 A.L.I keessatti guddina barnootaa hawaasaa guddataa deemuuf hundeeffame. Kutaalee afur fi barsiisota xiqqaa hordofuun, manni barumsaa kunis daddafaan naannoo keessatti mana barumsaa ol-aanoo beekamaa ta''e. Har''a barattootni 1,200 ol tajaajilama.'),
('school_history_am',        'ሻላካ ጃታን አሊ ሁለተኛ ደረጃ ትምህርት ቤት በ1999 ዓ.ም. ማህበረሰቡ እያደገ የሚመጣ የትምህርት ፍላጎቱን ለማሟላት ተቋቋመ። ከጥቂት ክፍሎች እና ቁርጠኛ መምህራን ጀምሮ ትምህርት ቤቱ ፈጥኖ ወደ ሁለተኛ ደረጃ ትምህርት ቤቶቹ አንዱ ሆኗል። ዛሬ ከ1,200 በላይ ተማሪዎችን ያስተምራል።'),

-- Vision & Mission
('school_vision_en',         'To be a center of academic excellence, producing graduates who are competitive nationally and internationally, equipped with the knowledge, skills, and values to contribute positively to their communities and the nation.'),
('school_vision_om',         'Iddoo guddina barnootaa ta''uuf, beekumsa, dandeettii fi gatii hawaasaafi biyyaaf bu''aa gaarii buusuuf danda''an qabatanii biyyaafi addunyaan waldorgomuu danda''an horachuu.'),
('school_vision_am',         'የአካዳሚክ ብቃት ማዕከል ሆኖ፣ ለማህበረሰቡ እና ለሀገሩ አዎንታዊ አስተዋፅኦ ለማበርከት እውቀት፣ ክህሎት እና እሴቶች የታጠቁ ተወዳዳሪ ተመራቂዎችን ማፍራት።'),

('school_mission_en',        'To provide quality secondary education in a safe, nurturing, and inclusive environment that empowers every student to achieve their full academic, moral, and social potential while celebrating the richness of our Ethiopian heritage.'),
('school_mission_om',        'Barattootni hundi dandeettii isaanii barnootaa, amala fi hawaasaa guutummaatti galmaan ga''uuf iddoo nagaa, jireenya horsiistuu fi walii galaa keessatti barnootaa ol-aanaa gaarii kennuu miraa Itoophiyaa kabajaaa.'),
('school_mission_am',        'እያንዳንዱ ተማሪ ዘርፈ ብዙ አቅሙን ሙሉ በሙሉ ለማሳካት የሚያስችለው ደህንነቱ የተጠበቀ፣ አሳዳጊ እና አካታች ከባቢ ውስጥ ጥራት ያለው ሁለተኛ ደረጃ ትምህርት ማቅረብ።'),

-- Google Map (iframe src)
('google_map_embed',         ''),

-- Departments (comma-separated per language)
('departments_en',           'Natural Science,Social Science,Mathematics,Languages & Literature,Physical Education,ICT & Technology'),
('departments_om',           'Saayinsii Uumaa,Saayinsii Hawaasummaa,Herrega,Afaanii fi Barreeffama,Barnootaa Qaamaa,TIM fi Teknooloojii'),
('departments_am',           'ተፈጥሯዊ ሳይንስ,ማህበራዊ ሳይንስ,ሒሳብ,ቋንቋ እና ስነ-ጽሁፍ,ሥነ-ሰውነት ትምህርት,አይሲቲ እና ቴክኖሎጂ');
