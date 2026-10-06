<?php

class WebsiteAdminController extends Controller {

    private function guard(): void {
        $this->requireAuth(['super_admin', 'principal', 'vice_principal']);
    }

    // -------------------------------------------------------
    // CMS Dashboard
    // -------------------------------------------------------
    public function dashboard(): void {
        $this->guard();
        $db = getDB();

        $stats = [
            'news'     => (int)$db->query("SELECT COUNT(*) FROM website_news")->fetchColumn(),
            'gallery'  => (int)$db->query("SELECT COUNT(*) FROM website_gallery")->fetchColumn(),
            'messages' => (int)$db->query("SELECT COUNT(*) FROM website_contact_messages")->fetchColumn(),
            'unread'   => (int)$db->query("SELECT COUNT(*) FROM website_contact_messages WHERE is_read = 0")->fetchColumn(),
            'sliders'  => (int)$db->query("SELECT COUNT(*) FROM website_sliders WHERE is_active = 1")->fetchColumn(),
        ];

        $recent = $db->query(
            "SELECT * FROM website_contact_messages ORDER BY created_at DESC LIMIT 5"
        )->fetchAll();

        $this->render('website-admin.dashboard', compact('stats', 'recent'));
    }

    // -------------------------------------------------------
    // Pages (Home / About)
    // -------------------------------------------------------
    public function pages(): void {
        $this->guard();
        $db    = getDB();
        $pages = $db->query("SELECT * FROM website_pages ORDER BY slug")->fetchAll();
        $this->render('website-admin.pages', compact('pages'));
    }

    public function editPage(string $slug): void {
        $this->guard();
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM website_pages WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $page = $stmt->fetch();
        if (!$page) {
            Flash::set('error', 'Page not found.');
            $this->redirect('website/pages');
            return;
        }
        $this->render('website-admin.page-edit', compact('page'));
    }

    public function savePage(string $slug): void {
        $this->guard();
        $this->validateCsrf();
        $db = getDB();

        $fields = [
            'title_en'   => $this->post('title_en'),
            'title_om'   => $this->post('title_om'),
            'title_am'   => $this->post('title_am'),
            'content_en' => $_POST['content_en'] ?? '',
            'content_om' => $_POST['content_om'] ?? '',
            'content_am' => $_POST['content_am'] ?? '',
        ];

        // Strip dangerous tags but allow basic HTML for content
        foreach (['content_en','content_om','content_am'] as $k) {
            $fields[$k] = strip_tags($fields[$k], '<p><br><b><strong><i><em><ul><ol><li><h2><h3><h4><a><span>');
        }

        $stmt = $db->prepare(
            "UPDATE website_pages SET title_en=?, title_om=?, title_am=?, content_en=?, content_om=?, content_am=?
             WHERE slug=?"
        );
        $stmt->execute([
            $fields['title_en'], $fields['title_om'], $fields['title_am'],
            $fields['content_en'], $fields['content_om'], $fields['content_am'],
            $slug
        ]);

        Auth::audit('update', 'website_pages', null, "slug=$slug");
        Flash::set('success', 'Page updated successfully.');
        $this->redirect('website/pages');
    }

    // -------------------------------------------------------
    // News / Articles
    // -------------------------------------------------------
    public function news(): void {
        $this->guard();
        $db      = getDB();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset  = ($page - 1) * $perPage;

        $total   = (int)$db->query("SELECT COUNT(*) FROM website_news")->fetchColumn();
        $articles = $db->prepare(
            "SELECT n.*, u.username AS author_name
             FROM website_news n
             LEFT JOIN users u ON n.author_id = u.id
             ORDER BY n.created_at DESC LIMIT ? OFFSET ?"
        );
        $articles->execute([$perPage, $offset]);
        $articles    = $articles->fetchAll();
        $totalPages  = (int)ceil($total / $perPage);

        $this->render('website-admin.news.index', compact('articles', 'page', 'totalPages', 'total'));
    }

    public function createNews(): void {
        $this->guard();
        $this->render('website-admin.news.form', ['article' => null, 'formAction' => url('website/news/create')]);
    }

    public function storeNews(): void {
        $this->guard();
        $this->validateCsrf();

        $data = $this->collectNewsData();
        if (!$data['title_en'] && !$data['title_om'] && !$data['title_am']) {
            Flash::set('error', 'At least one title is required.');
            $this->redirect('website/news/create');
            return;
        }

        $image = $this->uploadFile('image', 'website/news', ALLOWED_IMAGE_TYPES);

        $db   = getDB();
        $stmt = $db->prepare(
            "INSERT INTO website_news
             (title_en,title_om,title_am,excerpt_en,excerpt_om,excerpt_am,
              content_en,content_om,content_am,image,category,is_published,published_at,author_id)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $data['title_en'], $data['title_om'], $data['title_am'],
            $data['excerpt_en'], $data['excerpt_om'], $data['excerpt_am'],
            $data['content_en'], $data['content_om'], $data['content_am'],
            $image ?: null,
            $data['category'],
            $data['is_published'],
            $data['published_at'] ?: null,
            Auth::id(),
        ]);

        Auth::audit('create', 'website_news');
        Flash::set('success', 'Article created successfully.');
        $this->redirect('website/news');
    }

    public function editNews(string $id): void {
        $this->guard();
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM website_news WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $article = $stmt->fetch();

        if (!$article) {
            Flash::set('error', 'Article not found.');
            $this->redirect('website/news');
            return;
        }
        $this->render('website-admin.news.form', [
            'article'    => $article,
            'formAction' => url('website/news/edit/' . $id),
        ]);
    }

    public function updateNews(string $id): void {
        $this->guard();
        $this->validateCsrf();

        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM website_news WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) {
            Flash::set('error', 'Article not found.');
            $this->redirect('website/news');
            return;
        }

        $data  = $this->collectNewsData();
        $image = $this->uploadFile('image', 'website/news', ALLOWED_IMAGE_TYPES);
        $imageVal = $image ?: $existing['image'];

        $db->prepare(
            "UPDATE website_news SET
             title_en=?,title_om=?,title_am=?,excerpt_en=?,excerpt_om=?,excerpt_am=?,
             content_en=?,content_om=?,content_am=?,image=?,category=?,is_published=?,published_at=?
             WHERE id=?"
        )->execute([
            $data['title_en'], $data['title_om'], $data['title_am'],
            $data['excerpt_en'], $data['excerpt_om'], $data['excerpt_am'],
            $data['content_en'], $data['content_om'], $data['content_am'],
            $imageVal, $data['category'], $data['is_published'],
            $data['published_at'] ?: null,
            $id,
        ]);

        Auth::audit('update', 'website_news', (int)$id);
        Flash::set('success', 'Article updated successfully.');
        $this->redirect('website/news');
    }

    public function deleteNews(string $id): void {
        $this->guard();
        $this->validateCsrf();
        $db = getDB();
        $db->prepare("DELETE FROM website_news WHERE id = ?")->execute([$id]);
        Auth::audit('delete', 'website_news', (int)$id);
        Flash::set('success', 'Article deleted.');
        $this->redirect('website/news');
    }

    private function collectNewsData(): array {
        return [
            'title_en'     => $this->post('title_en', ''),
            'title_om'     => $this->post('title_om', ''),
            'title_am'     => $this->post('title_am', ''),
            'excerpt_en'   => $this->post('excerpt_en', ''),
            'excerpt_om'   => $this->post('excerpt_om', ''),
            'excerpt_am'   => $this->post('excerpt_am', ''),
            'content_en'   => strip_tags($_POST['content_en'] ?? '', '<p><br><b><strong><i><em><ul><ol><li><h2><h3><h4><a><span><img>'),
            'content_om'   => strip_tags($_POST['content_om'] ?? '', '<p><br><b><strong><i><em><ul><ol><li><h2><h3><h4><a><span>'),
            'content_am'   => strip_tags($_POST['content_am'] ?? '', '<p><br><b><strong><i><em><ul><ol><li><h2><h3><h4><a><span>'),
            'category'     => $this->post('category', 'news'),
            'is_published' => (int)(bool)$this->post('is_published'),
            'published_at' => $this->post('published_at', date('Y-m-d')),
        ];
    }

    // -------------------------------------------------------
    // Gallery
    // -------------------------------------------------------
    public function gallery(): void {
        $this->guard();
        $db = getDB();

        $categories = $db->query("SELECT * FROM website_gallery_categories ORDER BY sort_order")->fetchAll();
        $images     = $db->query(
            "SELECT g.*, c.name_en AS cat_name
             FROM website_gallery g
             LEFT JOIN website_gallery_categories c ON g.category_id = c.id
             ORDER BY g.sort_order ASC, g.created_at DESC"
        )->fetchAll();

        $this->render('website-admin.gallery', compact('images', 'categories'));
    }

    public function uploadGallery(): void {
        $this->guard();
        $this->validateCsrf();

        $image = $this->uploadFile('image', 'website/gallery', ALLOWED_IMAGE_TYPES);
        if (!$image) {
            Flash::set('error', 'Image upload failed. Check file type and size.');
            $this->redirect('website/gallery');
            return;
        }

        $db = getDB();
        $db->prepare(
            "INSERT INTO website_gallery (image, caption_en, caption_om, caption_am, category_id, sort_order)
             VALUES (?, ?, ?, ?, ?, ?)"
        )->execute([
            $image,
            $this->post('caption_en', ''),
            $this->post('caption_om', ''),
            $this->post('caption_am', ''),
            $this->post('category_id') ?: null,
            (int)$this->post('sort_order', 0),
        ]);

        Flash::set('success', 'Image uploaded successfully.');
        $this->redirect('website/gallery');
    }

    public function deleteGallery(string $id): void {
        $this->guard();
        $this->validateCsrf();
        $db = getDB();
        $db->prepare("DELETE FROM website_gallery WHERE id = ?")->execute([$id]);
        Flash::set('success', 'Image deleted.');
        $this->redirect('website/gallery');
    }

    public function galleryCategories(): void {
        $this->guard();
        $this->validateCsrf();
        $db = getDB();
        $db->prepare(
            "INSERT INTO website_gallery_categories (name_en, name_om, name_am, sort_order) VALUES (?,?,?,?)"
        )->execute([
            $this->post('name_en', ''),
            $this->post('name_om', ''),
            $this->post('name_am', ''),
            (int)$this->post('sort_order', 0),
        ]);
        Flash::set('success', 'Category added.');
        $this->redirect('website/gallery');
    }

    // -------------------------------------------------------
    // Sliders
    // -------------------------------------------------------
    public function sliders(): void {
        $this->guard();
        $db      = getDB();
        $sliders = $db->query("SELECT * FROM website_sliders ORDER BY sort_order ASC")->fetchAll();
        $this->render('website-admin.sliders', compact('sliders'));
    }

    public function uploadSlider(): void {
        $this->guard();
        $this->validateCsrf();

        $image = $this->uploadFile('image', 'website/sliders', ALLOWED_IMAGE_TYPES);
        if (!$image) {
            Flash::set('error', 'Image upload failed.');
            $this->redirect('website/sliders');
            return;
        }

        $db = getDB();
        $db->prepare(
            "INSERT INTO website_sliders (image,title_en,title_om,title_am,subtitle_en,subtitle_om,subtitle_am,sort_order)
             VALUES (?,?,?,?,?,?,?,?)"
        )->execute([
            $image,
            $this->post('title_en', ''),
            $this->post('title_om', ''),
            $this->post('title_am', ''),
            $this->post('subtitle_en', ''),
            $this->post('subtitle_om', ''),
            $this->post('subtitle_am', ''),
            (int)$this->post('sort_order', 0),
        ]);

        Flash::set('success', 'Slider added.');
        $this->redirect('website/sliders');
    }

    public function editSlider(string $id): void {
        $this->guard();
        $slider = $this->findSlider($id);
        if (!$slider) {
            Flash::set('error', 'Slider not found.');
            $this->redirect('website/sliders');
            return;
        }

        $this->render('website-admin.slider-edit', compact('slider'));
    }

    public function updateSlider(string $id): void {
        $this->guard();
        $this->validateCsrf();

        $existing = $this->findSlider($id);
        if (!$existing) {
            Flash::set('error', 'Slider not found.');
            $this->redirect('website/sliders');
            return;
        }

        $hasNewImage = isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;
        $image = $this->uploadFile('image', 'website/sliders', ALLOWED_IMAGE_TYPES);
        if ($hasNewImage && !$image) {
            Flash::set('error', 'Image upload failed. Please choose a valid image and try again.');
            $this->redirect('website/sliders/edit/' . $id);
            return;
        }
        $image = $image ?: $existing['image'];

        getDB()->prepare(
            "UPDATE website_sliders SET image=?, title_en=?, title_om=?, title_am=?,
             subtitle_en=?, subtitle_om=?, subtitle_am=?, sort_order=? WHERE id=?"
        )->execute([
            $image,
            $this->post('title_en', ''),
            $this->post('title_om', ''),
            $this->post('title_am', ''),
            $this->post('subtitle_en', ''),
            $this->post('subtitle_om', ''),
            $this->post('subtitle_am', ''),
            (int)$this->post('sort_order', 0),
            $id,
        ]);

        Auth::audit('update', 'website_sliders', (int)$id);
        Flash::set('success', 'Slider updated.');
        $this->redirect('website/sliders');
    }

    private function findSlider(string $id): ?array {
        $stmt = getDB()->prepare("SELECT * FROM website_sliders WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function toggleSlider(string $id): void {
        $this->guard();
        $this->validateCsrf();
        $db = getDB();
        $db->prepare("UPDATE website_sliders SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
        Flash::set('success', 'Slider updated.');
        $this->redirect('website/sliders');
    }

    public function deleteSlider(string $id): void {
        $this->guard();
        $this->validateCsrf();
        $db = getDB();
        $db->prepare("DELETE FROM website_sliders WHERE id = ?")->execute([$id]);
        Flash::set('success', 'Slider deleted.');
        $this->redirect('website/sliders');
    }

    // -------------------------------------------------------
    // Contact Messages
    // -------------------------------------------------------
    public function contactMessages(): void {
        $this->guard();
        $db   = getDB();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per  = 20;

        $total    = (int)$db->query("SELECT COUNT(*) FROM website_contact_messages")->fetchColumn();
        $stmt     = $db->prepare(
            "SELECT * FROM website_contact_messages ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$per, ($page - 1) * $per]);
        $messages   = $stmt->fetchAll();
        $totalPages = (int)ceil($total / $per);

        // Mark all as read
        $db->exec("UPDATE website_contact_messages SET is_read = 1 WHERE is_read = 0");

        $this->render('website-admin.contact-messages', compact('messages', 'page', 'totalPages', 'total'));
    }

    public function deleteMessage(string $id): void {
        $this->guard();
        $this->validateCsrf();
        $db = getDB();
        $db->prepare("DELETE FROM website_contact_messages WHERE id = ?")->execute([$id]);
        Flash::set('success', 'Message deleted.');
        $this->redirect('website/contact-messages');
    }

    // -------------------------------------------------------
    // Principal Message — dedicated page
    // -------------------------------------------------------
    public function principalPage(): void {
        $this->guard();
        $db       = getDB();
        $settings = $db->query("SELECT setting_key, setting_value FROM website_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        $this->render('website-admin.principal', compact('settings'));
    }

    public function savePrincipal(): void {
        $this->guard();
        $this->validateCsrf();
        $db   = getDB();
        $keys = [
            'principal_name',
            'principal_title_en','principal_title_om','principal_title_am',
            'principal_message_en','principal_message_om','principal_message_am',
            'vice_principal_name',
            'vice_principal_title_en','vice_principal_title_om','vice_principal_title_am',
        ];
        foreach ($keys as $key) {
            $db->prepare(
                "INSERT INTO website_settings (setting_key, setting_value) VALUES (?,?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
            )->execute([$key, $this->post($key, '')]);
        }

        // Handle photo upload
        if ($this->post('remove_photo') === '1') {
            $db->prepare(
                "INSERT INTO website_settings (setting_key, setting_value) VALUES ('principal_photo','')
                 ON DUPLICATE KEY UPDATE setting_value = ''"
            )->execute();
        } else {
            $photo = $this->uploadFile('principal_photo', 'website/principal', ALLOWED_IMAGE_TYPES);
            if ($photo) {
                $db->prepare(
                    "INSERT INTO website_settings (setting_key, setting_value) VALUES ('principal_photo',?)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
                )->execute([$photo]);
            }
        }

        Auth::audit('update', 'website_settings');
        Flash::set('success', 'Principal information saved.');
        $this->redirect('website/principal');
    }

    // -------------------------------------------------------
    // Home Page Content — dedicated page
    // -------------------------------------------------------
    public function homeContent(): void {
        $this->guard();
        $db       = getDB();
        $settings = $db->query("SELECT setting_key, setting_value FROM website_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        $this->render('website-admin.home-content', compact('settings'));
    }

    public function saveHomeContent(): void {
        $this->guard();
        $this->validateCsrf();
        $db   = getDB();
        $keys = [
            'tagline_en','tagline_om','tagline_am',
            'stat_students','stat_teachers','stat_years','stat_classes',
            'cta_title_en','cta_title_om','cta_title_am',
            'cta_subtitle_en','cta_subtitle_om','cta_subtitle_am',
        ];
        foreach ($keys as $key) {
            $db->prepare(
                "INSERT INTO website_settings (setting_key, setting_value) VALUES (?,?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
            )->execute([$key, $this->post($key, '')]);
        }
        Auth::audit('update', 'website_settings');
        Flash::set('success', 'Home page content saved.');
        $this->redirect('website/home-content');
    }

    // -------------------------------------------------------
    // About Page Content — dedicated page
    // -------------------------------------------------------
    public function aboutContent(): void {
        $this->guard();
        $db       = getDB();
        $settings = $db->query("SELECT setting_key, setting_value FROM website_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        $this->render('website-admin.about-content', compact('settings'));
    }

    public function saveAboutContent(): void {
        $this->guard();
        $this->validateCsrf();
        $db   = getDB();
        $keys = [
            'school_founded',
            'school_history_en','school_history_om','school_history_am',
            'school_vision_en','school_vision_om','school_vision_am',
            'school_mission_en','school_mission_om','school_mission_am',
            'departments_en','departments_om','departments_am',
        ];
        // Core values 1–6
        for ($i = 1; $i <= 6; $i++) {
            $keys[] = "cv{$i}_icon";
            foreach (['en','om','am'] as $l) {
                $keys[] = "cv{$i}_name_{$l}";
                $keys[] = "cv{$i}_desc_{$l}";
            }
        }
        foreach ($keys as $key) {
            $db->prepare(
                "INSERT INTO website_settings (setting_key, setting_value) VALUES (?,?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
            )->execute([$key, $this->post($key, '')]);
        }
        Auth::audit('update', 'website_settings');
        Flash::set('success', 'About page content saved.');
        $this->redirect('website/about-content');
    }

    // -------------------------------------------------------
    // Website Settings
    // -------------------------------------------------------
    public function siteSettings(): void {
        $this->guard();
        $db       = getDB();
        $settings = $db->query("SELECT setting_key, setting_value FROM website_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        $this->render('website-admin.settings', compact('settings'));
    }

    public function saveSiteSettings(): void {
        $this->guard();
        $this->validateCsrf();
        $db = getDB();

        $keys = [
            'tagline_en','tagline_om','tagline_am',
            'stat_students','stat_teachers','stat_years','stat_classes',
            'contact_address','contact_email','contact_phone',
            'facebook_url','twitter_url','youtube_url','telegram_url',
            // Principal
            'principal_name','principal_title_en','principal_title_om','principal_title_am',
            'principal_message_en','principal_message_om','principal_message_am',
            'vice_principal_name','vice_principal_title_en','vice_principal_title_om','vice_principal_title_am',
            // History & founding
            'school_founded',
            'school_history_en','school_history_om','school_history_am',
            // Vision & Mission
            'school_vision_en','school_vision_om','school_vision_am',
            'school_mission_en','school_mission_om','school_mission_am',
            // Departments (comma-separated per lang)
            'departments_en','departments_om','departments_am',
            // Map
            'google_map_embed',
            // Office hours
            'office_hours_en','office_hours_om','office_hours_am',
            // CTA section
            'cta_title_en','cta_title_om','cta_title_am',
            'cta_subtitle_en','cta_subtitle_om','cta_subtitle_am',
        ];

        foreach ($keys as $key) {
            $val  = $this->post($key, '');
            $stmt = $db->prepare(
                "INSERT INTO website_settings (setting_key, setting_value)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
            );
            $stmt->execute([$key, $val]);
        }

        Auth::audit('update', 'website_settings');
        Flash::set('success', 'Website settings saved.');
        $this->redirect('website/settings');
    }
}
