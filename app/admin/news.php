<?php
declare(strict_types=1);

// お知らせの管理（指示書 5-3）：一覧・新規・編集・削除。施工事例と同じ作り（写真・カテゴリ・SEO 欄はなし）。
/** @var string $action list|edit|delete */
/** @var ?int $id */
/** @var array $user */

const NEWS_ADMIN_PER_PAGE = 30;

$pdo = db();

if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(admin_url('news/'));
    }
    $pdo->prepare("UPDATE news SET status = 'deleted' WHERE id = ?")->execute([$id]);
    flash('ok', 'お知らせを削除しました。');
    redirect(admin_url('news/'));
}

if ($action === 'list') {
    $q = trim((string)($_GET['q'] ?? ''));
    $status = in_array($_GET['status'] ?? '', ['published', 'draft'], true) ? $_GET['status'] : '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $where = "status <> 'deleted'";
    $args = [];
    if ($q !== '') {
        $where .= ' AND title LIKE ?';
        $args[] = '%' . addcslashes($q, '%_\\') . '%';
    }
    if ($status !== '') {
        $where .= ' AND status = ?';
        $args[] = $status;
    }
    $st = $pdo->prepare("SELECT COUNT(*) FROM news WHERE $where");
    $st->execute($args);
    $total = (int)$st->fetchColumn();
    $st = $pdo->prepare("SELECT id, slug, title, published_at, status FROM news WHERE $where ORDER BY published_at DESC, id DESC LIMIT " . NEWS_ADMIN_PER_PAGE . ' OFFSET ' . (($page - 1) * NEWS_ADMIN_PER_PAGE));
    $st->execute($args);
    $rows = $st->fetchAll();
    admin_render('list', compact('user', 'rows', 'total', 'q', 'status', 'page') + ['kind' => 'news', 'title' => 'お知らせ', 'pages' => admin_pages($total, NEWS_ADMIN_PER_PAGE), 'flash' => take_flash(), 'nav' => 'news']);
    exit;
}

$news = null;
if ($id !== null) {
    $st = $pdo->prepare("SELECT * FROM news WHERE id = ? AND status <> 'deleted'");
    $st->execute([$id]);
    $news = $st->fetch() ?: null;
    if (!$news) {
        http_response_code(404);
        admin_render('error', ['user' => $user, 'title' => 'お知らせが見つかりません', 'message' => '削除されたか、アドレスが違います。', 'back' => [admin_url('news/'), 'お知らせの一覧へ']]);
        exit;
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'title' => trim((string)($_POST['title'] ?? '')),
        'body_html' => admin_clean_body((string)($_POST['body'] ?? '')),
        'published_at' => (string)($_POST['published_at'] ?? ''),
        'status' => ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft',
        'slug' => make_slug(trim((string)($_POST['slug'] ?? ''))),
    ];
    if ($form['title'] === '') {
        $errors['title'] = '題名を入れてください。';
    }
    $d = DateTime::createFromFormat('!Y-m-d', $form['published_at']);
    if (!$d || $d->format('Y-m-d') !== $form['published_at']) {
        $errors['published_at'] = '投稿日を選んでください。';
    }
    if (!$errors) {
        $slug = unique_slug('news', $form['slug'] !== '' ? $form['slug'] : make_slug($form['title']), $news ? (int)$news['id'] : null);
        $vals = [$slug, $form['title'], $form['body_html'], $form['published_at'], $form['status']];
        if ($news) {
            $pdo->prepare('UPDATE news SET slug = ?, title = ?, body_html = ?, published_at = ?, status = ? WHERE id = ?')->execute(array_merge($vals, [$news['id']]));
            $newsId = (int)$news['id'];
        } else {
            $pdo->prepare('INSERT INTO news (slug, title, body_html, published_at, status) VALUES (?, ?, ?, ?, ?)')->execute($vals);
            $newsId = (int)$pdo->lastInsertId();
        }
        flash('ok', admin_saved_message($form['status'], $form['published_at']));
        if (($_POST['do'] ?? '') === 'preview') {
            redirect(url('news/' . rawurlencode($slug) . '/') . '?preview=1');
        }
        redirect(admin_url('news/' . $newsId . '/'));
    }
} elseif ($news) {
    $form = $news;
} else {
    $form = ['title' => '', 'body_html' => '', 'published_at' => today()->format('Y-m-d'), 'status' => 'published', 'slug' => ''];
}

admin_render('form', [
    'user' => $user, 'nav' => 'news', 'kind' => 'news', 'title' => $news ? 'お知らせを編集' : 'お知らせを新しく書く',
    'item' => $news, 'form' => $form, 'errors' => $errors, 'flash' => take_flash(),
]);
