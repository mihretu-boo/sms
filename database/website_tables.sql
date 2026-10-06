-- ============================================================
-- Public Website Tables for SJASSMS
-- Run after main schema (sjassms_final.sql)
-- ============================================================

-- Homepage slider images
CREATE TABLE IF NOT EXISTS website_sliders (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  image       VARCHAR(500)  NOT NULL,
  title_en    VARCHAR(255)  DEFAULT NULL,
  title_om    VARCHAR(255)  DEFAULT NULL,
  title_am    VARCHAR(255)  DEFAULT NULL,
  subtitle_en VARCHAR(500)  DEFAULT NULL,
  subtitle_om VARCHAR(500)  DEFAULT NULL,
  subtitle_am VARCHAR(500)  DEFAULT NULL,
  sort_order  INT           DEFAULT 0,
  is_active   TINYINT(1)    DEFAULT 1,
  created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Static page content (home, about)
CREATE TABLE IF NOT EXISTS website_pages (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(50)   NOT NULL UNIQUE,
  title_en    VARCHAR(300)  DEFAULT NULL,
  title_om    VARCHAR(300)  DEFAULT NULL,
  title_am    VARCHAR(300)  DEFAULT NULL,
  content_en  LONGTEXT      DEFAULT NULL,
  content_om  LONGTEXT      DEFAULT NULL,
  content_am  LONGTEXT      DEFAULT NULL,
  extra_json  JSON          DEFAULT NULL,
  updated_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- News / articles
CREATE TABLE IF NOT EXISTS website_news (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  title_en     VARCHAR(500)  DEFAULT NULL,
  title_om     VARCHAR(500)  DEFAULT NULL,
  title_am     VARCHAR(500)  DEFAULT NULL,
  excerpt_en   TEXT          DEFAULT NULL,
  excerpt_om   TEXT          DEFAULT NULL,
  excerpt_am   TEXT          DEFAULT NULL,
  content_en   LONGTEXT      DEFAULT NULL,
  content_om   LONGTEXT      DEFAULT NULL,
  content_am   LONGTEXT      DEFAULT NULL,
  image        VARCHAR(500)  DEFAULT NULL,
  category     VARCHAR(100)  DEFAULT 'news',
  is_published TINYINT(1)    DEFAULT 0,
  published_at DATE          DEFAULT NULL,
  author_id    INT           DEFAULT NULL,
  views        INT           DEFAULT 0,
  created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gallery categories
CREATE TABLE IF NOT EXISTS website_gallery_categories (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name_en    VARCHAR(100) DEFAULT NULL,
  name_om    VARCHAR(100) DEFAULT NULL,
  name_am    VARCHAR(100) DEFAULT NULL,
  sort_order INT          DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gallery images
CREATE TABLE IF NOT EXISTS website_gallery (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  image       VARCHAR(500) NOT NULL,
  caption_en  VARCHAR(300) DEFAULT NULL,
  caption_om  VARCHAR(300) DEFAULT NULL,
  caption_am  VARCHAR(300) DEFAULT NULL,
  category_id INT          DEFAULT NULL,
  sort_order  INT          DEFAULT 0,
  is_active   TINYINT(1)   DEFAULT 1,
  created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES website_gallery_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact form submissions
CREATE TABLE IF NOT EXISTS website_contact_messages (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(200) NOT NULL,
  email      VARCHAR(200) NOT NULL,
  phone      VARCHAR(30)  DEFAULT NULL,
  subject    VARCHAR(300) NOT NULL,
  message    TEXT         NOT NULL,
  lang       VARCHAR(5)   DEFAULT 'en',
  is_read    TINYINT(1)   DEFAULT 0,
  created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Website-specific settings
CREATE TABLE IF NOT EXISTS website_settings (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  setting_key   VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT         DEFAULT NULL,
  updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Default seed data
-- ============================================================

INSERT IGNORE INTO website_pages (slug, title_en, title_om, title_am, content_en, content_om, content_am) VALUES
('home',
  'Welcome to Shalaka Jatan Ali Secondary School',
  'Mana Barumsaa Ol-aanaa Shalaka Jatan Ali Baga Nagaan Dhuftan',
  'ወደ ሻላካ ጃታን አሊ ሁለተኛ ደረጃ ትምህርት ቤት እንኳን ደህና መጡ',
  'We are committed to nurturing bright minds and building strong character in every student. Our school provides quality education grounded in Ethiopian values and modern learning methods.',
  'Barattootni keenyaa akka cimaniif beekumsa fi amala gaarii horachuu irratti hojjenna. Manni barumsaa keenya barnootaa gaarii ogummaa Itoophiyaa fi mala barumsaa ammayyaa irratti hundaa''a.',
  'እያንዳንዱ ተማሪ ብሩህ አዕምሮ እንዲኖረው እና ጠንካራ ባህሪ እንዲገነባ ቁርጠኛ ነን። ትምህርት ቤታችን በኢትዮጵያዊ እሴቶች እና ዘመናዊ የትምህርት ዘዴዎች ላይ የተመሰረተ ጥራት ያለው ትምህርት ይሰጣል።'),
('about',
  'About Our School',
  'Mana Barumsaa Keenya Waa''ee',
  'ስለ ትምህርት ቤታችን',
  '<h4>Our Mission</h4><p>To provide quality secondary education that empowers students academically, morally, and socially to become responsible citizens and future leaders.</p><h4>Our Vision</h4><p>To be a center of excellence in secondary education, producing graduates who are competitive at national and international levels.</p><h4>Our Values</h4><ul><li>Academic Excellence</li><li>Integrity and Honesty</li><li>Respect and Discipline</li><li>Community Service</li><li>Innovation and Creativity</li></ul>',
  '<h4>Kaayyoo Keenya</h4><p>Barattoota beekumsa, amala gaarii fi hawaasa keessatti jabaa ta''uuf qophaa''an horuuf barnootaa ol-aanaa gaarii kennuu.</p><h4>Mul''ata Keenya</h4><p>Iddoo guddina barnootaa ol-aanaa keessatti waldorgommii biyyaa fi addunyaa irra dhaabbachuu danda''an horachuu.</p><h4>Gatii Keenya</h4><ul><li>Guddina Barnootaa</li><li>Dhugummaa fi Amantummaa</li><li>Kabajaa fi Sirna</li><li>Hawaasaaf Tajaajiluu</li><li>Haaroomsa fi Uumamummaa</li></ul>',
  '<h4>ተልዕኳችን</h4><p>ተማሪዎችን አካዳሚያዊ፣ ሥነ-ምግባራዊ እና ማህበራዊ ሆነው ኃላፊነት ያለባቸው ዜጎች እና የወደፊት መሪዎች እንዲሆኑ ለማብቃት ጥራት ያለው ሁለተኛ ደረጃ ትምህርት ማቅረብ።</p><h4>ራዕያችን</h4><p>በሁለተኛ ደረጃ ትምህርት ውስጥ የብቃት ማዕከል ሆኖ፣ በብሔራዊ እና ዓለም አቀፍ ደረጃ ተወዳዳሪ ተመራቂዎችን ማፍራት።</p><h4>እሴቶቻችን</h4><ul><li>የአካዳሚክ ልህቀት</li><li>ታማኝነት እና ቅንዓት</li><li>ክብር እና ዲሲፕሊን</li><li>ማህበረሰብ አገልግሎት</li><li>ፈጠራ እና ፈጠራ</li></ul>');

INSERT IGNORE INTO website_settings (setting_key, setting_value) VALUES
('tagline_en',       'Excellence in Education'),
('tagline_om',       'Barumsa Keessatti Guddina'),
('tagline_am',       'በትምህርት ውስጥ ብቃት'),
('stat_students',    '1200'),
('stat_teachers',    '65'),
('stat_years',       '25'),
('stat_classes',     '32'),
('contact_address',  'Shalaka, Oromia, Ethiopia'),
('contact_email',    'info@sjassms.edu.et'),
('contact_phone',    '+251 000 000 000'),
('facebook_url',     '#'),
('twitter_url',      '#'),
('youtube_url',      '#'),
('telegram_url',     '#');

INSERT IGNORE INTO website_gallery_categories (name_en, name_om, name_am, sort_order) VALUES
('All',            'Hundumaa',    'ሁሉ',           0),
('School Life',    'Jireenya Mana Barumsaa', 'የትምህርት ቤት ሕይወት', 1),
('Sports',         'Ispoortii',   'ስፖርት',         2),
('Events',         'Taateewwan',  'ዝግጅቶች',        3),
('Graduation',     'Eebbifamuu',  'ምረቃ',          4);

INSERT IGNORE INTO website_sliders (image, title_en, title_om, title_am, subtitle_en, subtitle_om, subtitle_am, sort_order) VALUES
('assets/images/slider1.jpg',
  'Excellence in Education',
  'Barumsa Keessatti Guddina',
  'በትምህርት ውስጥ ብቃት',
  'Shaping the leaders of tomorrow through quality education today',
  'Hoggantootni boru barnootaa gaarii har''aa ta''een hojjatamaa jiru',
  'ዛሬ ጥራት ያለው ትምህርት በኩል የነገ መሪዎችን እናቀርፃለን',
  1),
('assets/images/slider2.jpg',
  'Nurturing Young Minds',
  'Sammuu Dargaggoota Horuu',
  'ወጣት አዕምሮዎችን ማሳደግ',
  'Building character, knowledge, and skills for a better future',
  'Amala, beekumsa fi dandeettii jireenya gaariitiif ijaaruu',
  'ለተሻለ ወደፊት ባህሪ፣ እውቀት እና ክህሎት መገንባት',
  2),
('assets/images/slider3.jpg',
  'Join Our Community',
  'Hawaasa Keenya Keessatti Makamaa',
  'ማህበረሰባችን ይቀላቀሉ',
  'A place where every student can grow and achieve their potential',
  'Barataan hundi guddachuu fi dandeettii isaa mul''isuu danda''u iddoo',
  'እያንዳንዱ ተማሪ ማደግ እና አቅሙን ማሳየት የሚችልበት ቦታ',
  3);
