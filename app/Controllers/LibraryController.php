<?php

require_once ROOT . '/app/Core/Controller.php';

class LibraryController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $db = getDB();

        $totalBooks    = $db->query("SELECT SUM(copies_total) FROM books")->fetchColumn();
        $available     = $db->query("SELECT SUM(copies_available) FROM books")->fetchColumn();
        $borrowed      = $db->query("SELECT COUNT(*) FROM book_borrowings WHERE status='borrowed'")->fetchColumn();
        $overdue       = $db->query("SELECT COUNT(*) FROM book_borrowings WHERE status='borrowed' AND due_date < CURDATE()")->fetchColumn();
        $recentBorrows = $db->query("SELECT bb.*, b.title, u.username FROM book_borrowings bb LEFT JOIN books b ON bb.book_id=b.id LEFT JOIN users u ON bb.user_id=u.id ORDER BY bb.created_at DESC LIMIT 10")->fetchAll();

        $this->render('library/index', [
            'title'         => 'Library',
            'total_books'   => (int)$totalBooks,
            'available'     => (int)$available,
            'borrowed'      => (int)$borrowed,
            'overdue'       => (int)$overdue,
            'recent_borrows'=> $recentBorrows,
        ]);
    }

    public function books(): void {
        $this->requireAuth();
        $db     = getDB();
        $search = $this->get('search', '');
        $cat    = $this->get('category', '');
        $page   = max(1, (int)$this->get('page', 1));
        $limit  = PER_PAGE;
        $offset = ($page - 1) * $limit;

        $where  = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
            $like = "%$search%";
            array_push($params, $like, $like, $like);
        }
        if ($cat) { $where[] = "category = ?"; $params[] = $cat; }

        $whereStr = implode(' AND ', $where);
        $countStmt = $db->prepare("SELECT COUNT(*) FROM books WHERE $whereStr");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $params[] = $limit; $params[] = $offset;
        $stmt = $db->prepare("SELECT * FROM books WHERE $whereStr ORDER BY title LIMIT ? OFFSET ?");
        $stmt->execute($params);

        $cats = $db->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

        $this->render('library/books', [
            'title'    => 'Books',
            'books'    => $stmt->fetchAll(),
            'total'    => $total,
            'page'     => $page,
            'pages'    => ceil($total / $limit),
            'search'   => $search,
            'category' => $cat,
            'cats'     => $cats,
        ]);
    }

    public function createBook(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->render('library/create-book', ['title' => 'Add Book']);
    }

    public function storeBook(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db   = getDB();
        $data = [
            'isbn'             => $this->post('isbn', '') ?: null,
            'title'            => $this->post('title', ''),
            'author'           => $this->post('author', ''),
            'publisher'        => $this->post('publisher', ''),
            'publish_year'     => $this->post('publish_year', '') ?: null,
            'category'         => $this->post('category', ''),
            'language'         => $this->post('language', 'English'),
            'copies_total'     => (int)$this->post('copies_total', 1),
            'copies_available' => (int)$this->post('copies_total', 1),
            'location'         => $this->post('location', ''),
            'description'      => $this->post('description', ''),
        ];

        if (empty($data['title']) || empty($data['author'])) {
            Flash::set('error', 'Title and author are required.');
            $this->redirect('library/books/create');
            return;
        }

        try {
            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO books ($cols) VALUES ($ph)")->execute(array_values($data));
            Flash::set('success', 'Book added successfully.');
            $this->redirect('library/books');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
            $this->redirect('library/books/create');
        }
    }

    public function editBook(string $id): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM books WHERE id=?");
        $stmt->execute([$id]);
        $book = $stmt->fetch();
        if (!$book) { Flash::set('error', 'Book not found.'); $this->redirect('library/books'); return; }
        $this->render('library/edit-book', ['title' => 'Edit Book', 'book' => $book]);
    }

    public function updateBook(string $id): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db   = getDB();
        $data = [
            'title'        => $this->post('title', ''),
            'author'       => $this->post('author', ''),
            'publisher'    => $this->post('publisher', ''),
            'category'     => $this->post('category', ''),
            'copies_total' => (int)$this->post('copies_total', 1),
            'location'     => $this->post('location', ''),
        ];

        try {
            $sets = implode('=?,', array_keys($data)) . '=?';
            $vals = array_values($data); $vals[] = $id;
            $db->prepare("UPDATE books SET $sets WHERE id=?")->execute($vals);
            Flash::set('success', 'Book updated.');
            $this->redirect('library/books');
        } catch (Exception $e) {
            Flash::set('error', 'Failed.');
            $this->redirect('library/books/edit/' . $id);
        }
    }

    public function borrowings(): void {
        $this->requireAuth();
        $db     = getDB();
        $status = $this->get('status', '');
        $search = $this->get('search', '');

        $where  = ['1=1'];
        $params = [];
        if ($status) { $where[] = "bb.status=?"; $params[] = $status; }
        if ($search) {
            $where[] = "(b.title LIKE ? OR u.username LIKE ?)";
            array_push($params, "%$search%", "%$search%");
        }

        // Students/parents can only see their own
        if (Auth::role() === 'student') {
            $where[] = "bb.user_id=?"; $params[] = Auth::id();
        }

        $whereStr = implode(' AND ', $where);
        $stmt = $db->prepare("SELECT bb.*, b.title, b.author, u.username, DATEDIFF(CURDATE(), bb.due_date) as days_overdue FROM book_borrowings bb LEFT JOIN books b ON bb.book_id=b.id LEFT JOIN users u ON bb.user_id=u.id WHERE $whereStr ORDER BY bb.created_at DESC");
        $stmt->execute($params);

        $books = $db->query("SELECT id, title, author, copies_available FROM books WHERE copies_available > 0 ORDER BY title")->fetchAll();
        $users = $db->query("SELECT id, username FROM users WHERE status='active' ORDER BY username")->fetchAll();

        $this->render('library/borrowings', [
            'title'      => 'Borrowings',
            'borrowings' => $stmt->fetchAll(),
            'books'      => $books,
            'users'      => $users,
            'status'     => $status,
        ]);
    }

    public function borrow(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db     = getDB();
        $bookId = $this->post('book_id', '');
        $userId = $this->post('user_id', '');
        $dueDate = date('Y-m-d', strtotime('+' . getSetting('max_borrow_days', 14) . ' days'));

        $book = $db->prepare("SELECT * FROM books WHERE id=? AND copies_available > 0");
        $book->execute([$bookId]);
        if (!$book->fetch()) { Flash::set('error', 'Book not available.'); $this->redirect('library/borrowings'); return; }

        // Check max books
        $maxBooks = (int)getSetting('max_books_per_student', 3);
        $currentCount = $db->prepare("SELECT COUNT(*) FROM book_borrowings WHERE user_id=? AND status='borrowed'");
        $currentCount->execute([$userId]);
        if ((int)$currentCount->fetchColumn() >= $maxBooks) {
            Flash::set('error', "User has reached maximum borrowing limit ($maxBooks books).");
            $this->redirect('library/borrowings');
            return;
        }

        try {
            $db->beginTransaction();
            $db->prepare("INSERT INTO book_borrowings (book_id, user_id, borrow_date, due_date, status, issued_by) VALUES (?,?,CURDATE(),?,?,?)")->execute([$bookId, $userId, $dueDate, 'borrowed', Auth::id()]);
            $db->prepare("UPDATE books SET copies_available = copies_available - 1 WHERE id=?")->execute([$bookId]);
            $db->commit();
            Flash::set('success', 'Book issued. Due date: ' . formatDate($dueDate));
        } catch (Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('library/borrowings');
    }

    public function returnBook(string $id): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM book_borrowings WHERE id=?");
        $stmt->execute([$id]);
        $borrow = $stmt->fetch();
        if (!$borrow || $borrow['status'] !== 'borrowed') {
            Flash::set('error', 'Invalid borrowing record.');
            $this->redirect('library/borrowings');
            return;
        }

        $finePerDay = (float)getSetting('fine_per_day', 2);
        $daysOverdue = max(0, (int)((strtotime('today') - strtotime($borrow['due_date'])) / 86400));
        $fine = $daysOverdue * $finePerDay;

        try {
            $db->beginTransaction();
            $db->prepare("UPDATE book_borrowings SET status='returned', return_date=CURDATE(), fine=? WHERE id=?")->execute([$fine, $id]);
            $db->prepare("UPDATE books SET copies_available = copies_available + 1 WHERE id=?")->execute([$borrow['book_id']]);
            $db->commit();
            $msg = 'Book returned successfully.';
            if ($fine > 0) $msg .= " Fine: ETB $fine";
            Flash::set('success', $msg);
        } catch (Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Failed.');
        }
        $this->redirect('library/borrowings');
    }
}
