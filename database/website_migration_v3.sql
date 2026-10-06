-- ============================================================
-- Website Migration v3 — CTA, office hours, editable core values
-- Run against sjassms database after website_migration_v2.sql
-- ============================================================

INSERT IGNORE INTO website_settings (setting_key, setting_value) VALUES

-- CTA Section (Home + About pages)
('cta_title_en',    'Ready to Join Our School?'),
('cta_title_om',    'Mana Barumsaa Keenya Makamuu Qophooftuu?'),
('cta_title_am',    'ወደ ትምህርት ቤታችን ለመቀላቀል ዝግጁ ነዎት?'),
('cta_subtitle_en', 'Enroll your child today for a quality education that shapes their future.'),
('cta_subtitle_om', 'Daa\'ima keessan barnootaa gaarii gara fuula duraaf heeyyamaa.'),
('cta_subtitle_am', 'ልጅዎን ዛሬ ለቀጣይ ሕይወቱ ጥራት ያለው ትምህርት ያስመዝግቡ።'),

-- Office Hours (Contact page)
('office_hours_en', 'Monday – Friday: 8:00 AM – 5:00 PM'),
('office_hours_om', 'Wiixata – Jimaataa: 8:00 – 17:00'),
('office_hours_am', 'ሰኞ – አርብ፡ 8:00 – 17:00'),

-- Core Value 1
('cv1_icon',    'award'),
('cv1_name_en', 'Academic Excellence'),
('cv1_name_om', 'Guddina Barnootaa'),
('cv1_name_am', 'የአካዳሚክ ልህቀት'),
('cv1_desc_en', 'Commitment to the highest standards in teaching and learning.'),
('cv1_desc_om', 'Sadarkaa ol-aanaa barsiisuu fi barattuu irratti kutannoo.'),
('cv1_desc_am', 'በማስተማር እና በመማር ላይ ከፍተኛ ደረጃዎችን ለማሟላት ቁርጠኝነት።'),

-- Core Value 2
('cv2_icon',    'balance-scale'),
('cv2_name_en', 'Integrity'),
('cv2_name_om', 'Dhugummaa'),
('cv2_name_am', 'ታማኝነት'),
('cv2_desc_en', 'Upholding honesty, transparency and ethical conduct in all we do.'),
('cv2_desc_om', 'Hojii hunda keessatti dhugummaa, iftoominaa fi amala gaarii eeguuf.'),
('cv2_desc_am', 'ታማኝነት፣ ግልፅነት እና ሥነ-ምግባራዊ ድርጊት ለማስቀጠል።'),

-- Core Value 3
('cv3_icon',    'users'),
('cv3_name_en', 'Community'),
('cv3_name_om', 'Hawaasa'),
('cv3_name_am', 'ማህበረሰብ'),
('cv3_desc_en', 'Building strong partnerships between school, family and community.'),
('cv3_desc_om', 'Mana barumsaa, maatii fi hawaasa gidduutti hariiroo jabaa ijaaruu.'),
('cv3_desc_am', 'ትምህርት ቤቱ፣ ቤተሰብ እና ማህበረሰብ መካከል ጠንካራ አጋርነት መገንባት።'),

-- Core Value 4
('cv4_icon',    'lightbulb'),
('cv4_name_en', 'Innovation'),
('cv4_name_om', 'Haaroomsa'),
('cv4_name_am', 'ፈጠራ'),
('cv4_desc_en', 'Encouraging creative thinking and modern approaches to education.'),
('cv4_desc_om', 'Yaada uumamaa fi mala barumsa ammayyaa jajjabeessuu.'),
('cv4_desc_am', 'ፈጠራዊ አስተሳሰብ እና ዘመናዊ የትምህርት አቀራረቦችን ማበረታታት።'),

-- Core Value 5
('cv5_icon',    'heart'),
('cv5_name_en', 'Respect'),
('cv5_name_om', 'Kabajaa'),
('cv5_name_am', 'ክብር'),
('cv5_desc_en', 'Valuing every individual and fostering a culture of mutual respect.'),
('cv5_desc_om', 'Namni hundi gatii qabaachuu fi kabajaa walii galaa aadaa godhuu.'),
('cv5_desc_am', 'እያንዳንዱ ሰው ክብር ይሰጣል፣ የጋራ ክብር ባህል ማሳደግ።'),

-- Core Value 6
('cv6_icon',    'shield-alt'),
('cv6_name_en', 'Discipline'),
('cv6_name_om', 'Sirna'),
('cv6_name_am', 'ዲሲፕሊን'),
('cv6_desc_en', 'Instilling self-discipline and responsibility in our students.'),
('cv6_desc_om', 'Barattoota keenyaaf of-to\'annoo fi itti-gaafatamummaa dhaabuuf.'),
('cv6_desc_am', 'ለተማሪዎቻችን ራስን-ዲሲፕሊን እና ኃላፊነትን ማስዘሩ።');
