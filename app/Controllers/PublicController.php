<?php

class PublicController extends Controller {

    // -------------------------------------------------------
    // Language switch
    // -------------------------------------------------------
    public function switchLang(string $lang): void {
        Lang::set($lang);
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/site';
        // Only allow redirect to our own origin
        if (strpos($referer, BASE_URL) === 0) {
            header('Location: ' . $referer);
        } else {
            header('Location: ' . BASE_URL . '/site');
        }
        exit;
    }

    // -------------------------------------------------------
    // Home
    // -------------------------------------------------------
    public function home(): void {
        $db = getDB();

        $page = $db->prepare("SELECT * FROM website_pages WHERE slug = 'home' LIMIT 1");
        $page->execute();
        $homePage = $page->fetch() ?: [];

        $sliders = $db->query(
            "SELECT * FROM website_sliders WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 5"
        )->fetchAll();

        $news = $db->query(
            "SELECT * FROM website_news WHERE is_published = 1 ORDER BY published_at DESC, created_at DESC LIMIT 3"
        )->fetchAll();

        try {
            $gallery = $db->query(
                "SELECT * FROM website_gallery WHERE is_active = 1 AND (media_type IS NULL OR media_type = 'photo')
                 ORDER BY sort_order ASC, created_at DESC LIMIT 6"
            )->fetchAll();
        } catch (\PDOException $e) {
            // media_type column may not exist yet — fall back to unfiltered query
            $gallery = $db->query(
                "SELECT * FROM website_gallery WHERE is_active = 1
                 ORDER BY sort_order ASC, created_at DESC LIMIT 6"
            )->fetchAll();
        }

        $events = $db->query(
            "SELECT * FROM website_news WHERE is_published = 1 AND category = 'event'
             AND (published_at IS NULL OR published_at >= CURDATE())
             ORDER BY published_at ASC, created_at DESC LIMIT 4"
        )->fetchAll();

        $this->render('public.home', compact('homePage', 'sliders', 'news', 'gallery', 'events'), 'public');
    }

    // -------------------------------------------------------
    // About
    // -------------------------------------------------------
    public function about(): void {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM website_pages WHERE slug = 'about' LIMIT 1");
        $stmt->execute();
        $aboutPage = $stmt->fetch() ?: [];

        // Departments: parse comma-separated list from settings (language-aware)
        $lang     = Lang::current();
        $deptStr  = getWebsiteSetting('departments_' . $lang, getWebsiteSetting('departments_en', ''));
        $departments = $deptStr ? array_map('trim', explode(',', $deptStr)) : [];

        // Department icons mapped by index (wraps after 6)
        $deptIcons = ['flask','globe-africa','calculator','language','running','laptop-code'];

        $this->render('public.about', compact('aboutPage', 'departments', 'deptIcons'), 'public');
    }

    // -------------------------------------------------------
    // News list
    // -------------------------------------------------------
    public function news(): void {
        $db  = getDB();
        $cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 9;
        $offset  = ($page - 1) * $perPage;

        if ($cat) {
            $countStmt = $db->prepare("SELECT COUNT(*) FROM website_news WHERE is_published = 1 AND category = ?");
            $countStmt->execute([$cat]);
            $total = (int)$countStmt->fetchColumn();

            $stmt = $db->prepare(
                "SELECT * FROM website_news WHERE is_published = 1 AND category = ?
                 ORDER BY published_at DESC, created_at DESC LIMIT ? OFFSET ?"
            );
            $stmt->execute([$cat, $perPage, $offset]);
        } else {
            $total = (int)$db->query("SELECT COUNT(*) FROM website_news WHERE is_published = 1")->fetchColumn();
            $stmt  = $db->prepare(
                "SELECT * FROM website_news WHERE is_published = 1
                 ORDER BY published_at DESC, created_at DESC LIMIT ? OFFSET ?"
            );
            $stmt->execute([$perPage, $offset]);
        }

        $articles   = $stmt->fetchAll();
        $totalPages = (int)ceil($total / $perPage);

        $categories = $db->query(
            "SELECT DISTINCT category FROM website_news WHERE is_published = 1 ORDER BY category"
        )->fetchAll(PDO::FETCH_COLUMN);

        $this->render('public.news', compact('articles', 'categories', 'cat', 'page', 'totalPages'), 'public');
    }

    // -------------------------------------------------------
    // News detail
    // -------------------------------------------------------
    public function newsDetail(string $id): void {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM website_news WHERE id = ? AND is_published = 1 LIMIT 1");
        $stmt->execute([$id]);
        $article = $stmt->fetch();

        if (!$article) {
            http_response_code(404);
            require VIEWS_PATH . '/errors/404.php';
            return;
        }

        // Increment views
        $db->prepare("UPDATE website_news SET views = views + 1 WHERE id = ?")->execute([$id]);

        $related = $db->prepare(
            "SELECT * FROM website_news WHERE is_published = 1 AND id != ? AND category = ?
             ORDER BY published_at DESC LIMIT 3"
        );
        $related->execute([$id, $article['category']]);
        $relatedArticles = $related->fetchAll();

        $this->render('public.news-detail', compact('article', 'relatedArticles'), 'public');
    }

    // -------------------------------------------------------
    // Gallery
    // -------------------------------------------------------
    public function gallery(): void {
        $db  = getDB();
        $cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

        $categories = $db->query(
            "SELECT * FROM website_gallery_categories ORDER BY sort_order ASC"
        )->fetchAll();

        $mediaType = (isset($_GET['media']) && $_GET['media'] === 'video') ? 'video' : 'photo';

        try {
            if ($cat > 0) {
                $stmt = $db->prepare(
                    "SELECT * FROM website_gallery WHERE is_active = 1 AND category_id = ?
                     AND (media_type IS NULL OR media_type = ?)
                     ORDER BY sort_order ASC, created_at DESC"
                );
                $stmt->execute([$cat, $mediaType]);
            } else {
                $stmt = $db->prepare(
                    "SELECT * FROM website_gallery WHERE is_active = 1
                     AND (media_type IS NULL OR media_type = ?)
                     ORDER BY sort_order ASC, created_at DESC"
                );
                $stmt->execute([$mediaType]);
            }
            $images     = $stmt->fetchAll();
            $videoCount = (int)$db->query("SELECT COUNT(*) FROM website_gallery WHERE is_active = 1 AND media_type = 'video'")->fetchColumn();
        } catch (\PDOException $e) {
            // media_type column not yet created — run website_migration_v2.sql
            if ($cat > 0) {
                $stmt = $db->prepare("SELECT * FROM website_gallery WHERE is_active = 1 AND category_id = ? ORDER BY sort_order ASC, created_at DESC");
                $stmt->execute([$cat]);
            } else {
                $stmt = $db->query("SELECT * FROM website_gallery WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC");
            }
            $images     = $stmt->fetchAll();
            $videoCount = 0;
            $mediaType  = 'photo';
        }

        $this->render('public.gallery', compact('images', 'categories', 'cat', 'mediaType', 'videoCount'), 'public');
    }

    // -------------------------------------------------------
    // Contact
    // -------------------------------------------------------
    public function contact(): void {
        $this->render('public.contact', [], 'public');
    }

    public function submitContact(): void {
        $name    = trim($this->post('name', ''));
        $email   = trim($this->post('email', ''));
        $phone   = trim($this->post('phone', ''));
        $subject = trim($this->post('subject', ''));
        $message = trim($this->post('message', ''));

        if (!$name || !$email || !$subject || !$message) {
            $_SESSION['contact_error'] = Lang::get('contact_error');
            $this->redirect('site/contact');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['contact_error'] = Lang::get('val_email');
            $this->redirect('site/contact');
            return;
        }

        try {
            $db   = getDB();
            $stmt = $db->prepare(
                "INSERT INTO website_contact_messages (name, email, phone, subject, message, lang)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $email, $phone ?: null, $subject, $message, Lang::current()]);
            $_SESSION['contact_success'] = true;
        } catch (Exception $e) {
            $_SESSION['contact_error'] = Lang::get('contact_error');
        }

        $this->redirect('site/contact');
    }
}
