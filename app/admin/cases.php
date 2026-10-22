<?php
declare(strict_types=1);

// 施工事例の管理（指示書 5-2）：一覧・新規・編集・削除・写真のアップロード。
/** @var string $action list|edit|delete|upload */
/** @var ?int $id */
/** @var array $user */

const ADMIN_PER_PAGE = 30;
const UPLOAD_MAX = 15 * 1024 * 1024;
const UPLOAD_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

$pdo = db();

// ---------- 写真のアップロード（1枚ずつ。保存するまでは仮の置き場 media/cases/new/<合言葉>/ に置く） ----------
if ($action === 'upload') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        admin_json(['error' => '送信の方法が正しくありません。'], 405);
    }
    $token = (string)($_POST['token'] ?? '');
    if (!preg_match('/^[a-f0-9]{32}$/', $token) || !in_array($token, $_SESSION['upload_tokens'] ?? [], true)) {
        admin_json(['error' => '画面の有効期限が切れました。ページを読み込み直してください。'], 400);
    }
    $f = $_FILES['photo'] ?? null;
    if (!$f || is_array($f['error']) || $f['error'] !== UPLOAD_ERR_OK) {
        $code = is_array($f['error'] ?? null) ? -1 : (int)($f['error'] ?? -1);
        admin_json(['error' => in_array($code, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? '写真が大きすぎます（1枚15MBまで）。' : '写真を受け取れませんでした。もう一度お試しください。'], 400);
    }
    if ($f['size'] > UPLOAD_MAX) {
        admin_json(['error' => '「' . $f['name'] . '」が15MBを超えています。'], 400);
    }
    $ext = strtolower(pathinfo((string)$f['name'], PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $info = @getimagesize($f['tmp_name']);
    // 拡張子・中身の種類・画像として読めるか、の3つがそろったものだけ受け付ける
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) || !isset(UPLOAD_TYPES[$mime]) || !$info || ($info['mime'] ?? '') !== $mime) {
        admin_json(['error' => '「' . $f['name'] . '」は写真として読み込めません。JPG・PNG・WebP の写真を選んでください。'], 400);
    }
    $im = load_image($f['tmp_name']);
    if (!$im) {
        admin_json(['error' => '「' . $f['name'] . '」を読み込めませんでした。'], 400);
    }
    // 2日より古い仮の置き場を片付ける
    foreach (glob(PUBLIC_DIR . '/media/cases/new/*', GLOB_ONLYDIR) ?: [] as $d) {
        if (filemtime($d) < time() - 172800) {
            array_map('unlink', glob("$d/*") ?: []);
            @rmdir($d);
        }
    }
    $name = bin2hex(random_bytes(6));
    $m = write_case_media($im, 'media/cases/new/' . $token, $name);
    admin_json(['tmp' => $token . '/' . $name, 'thumb' => url($m['path_thumb']), 'width' => $m['width'], 'height' => $m['height']]);
}

// ---------- 削除（論理削除：status=deleted） ----------
if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(admin_url('cases/'));
    }
    $st = $pdo->prepare("UPDATE cases SET status = 'deleted' WHERE id = ?");
    $st->execute([$id]);
    flash('ok', '施工事例を削除しました。');
    redirect(admin_url('cases/'));
}

$cats = ['biz' => [], 'work' => []];
foreach ($pdo->query('SELECT id, axis, name FROM categories ORDER BY axis, sort_order, id') as $r) {
    $cats[$r['axis']][(int)$r['id']] = $r['name'];
}

// ---------- 一覧 ----------
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
    $st = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE $where");
    $st->execute($args);
    $total = (int)$st->fetchColumn();
    $st = $pdo->prepare("SELECT c.id, c.slug, c.title, c.location, c.published_at, c.status, c.needs_review, i.path_thumb AS image
        FROM cases c LEFT JOIN case_images i ON i.id = c.thumbnail_image_id
        WHERE $where ORDER BY c.published_at DESC, c.id DESC LIMIT " . ADMIN_PER_PAGE . ' OFFSET ' . (($page - 1) * ADMIN_PER_PAGE));
    $st->execute($args);
    $rows = attach_categories($st->fetchAll());
    admin_render('list', compact('user', 'rows', 'total', 'q', 'status', 'page') + ['kind' => 'cases', 'title' => '施工事例', 'pages' => admin_pages($total, ADMIN_PER_PAGE), 'flash' => take_flash(), 'nav' => 'cases']);
    exit;
}

// ---------- 新規・編集 ----------
$case = null;
if ($id !== null) {
    $st = $pdo->prepare("SELECT * FROM cases WHERE id = ? AND status <> 'deleted'");
    $st->execute([$id]);
    $case = $st->fetch() ?: null;
    if (!$case) {
        http_response_code(404);
        admin_render('error', ['user' => $user, 'title' => '施工事例が見つかりません', 'message' => '削除されたか、アドレスが違います。', 'back' => [admin_url('cases/'), '施工事例の一覧へ']]);
        exit;
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = [
        'title' => trim((string)($_POST['title'] ?? '')),
        'body_html' => admin_clean_body((string)($_POST['body'] ?? '')),
        'location' => mb_substr(trim((string)($_POST['location'] ?? '')), 0, 100),
        'published_at' => (string)($_POST['published_at'] ?? ''),
        'status' => ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft',
        'seo_title' => mb_substr(trim((string)($_POST['seo_title'] ?? '')), 0, 255),
        'seo_description' => mb_substr(trim((string)($_POST['seo_description'] ?? '')), 0, 500),
        'slug' => make_slug(trim((string)($_POST['slug'] ?? ''))),
    ];
    $picked = [];
    foreach (['biz', 'work'] as $axis) {
        $picked[$axis] = array_values(array_intersect(array_map('intval', (array)($_POST[$axis] ?? [])), array_keys($cats[$axis])));
    }
    $photos = json_decode((string)($_POST['photos'] ?? '[]'), true);
    $photos = is_array($photos) ? $photos : [];

    if ($in['title'] === '') {
        $errors['title'] = '題名を入れてください。';
    }
    $d = DateTime::createFromFormat('!Y-m-d', $in['published_at']);
    if (!$d || $d->format('Y-m-d') !== $in['published_at']) {
        $errors['published_at'] = '投稿日を選んでください。';
    }
    if ($in['status'] === 'published' && !$picked['biz']) {
        $errors['biz'] = '公開する時は、業態を1つ以上選んでください。';
    }
    if ($in['status'] === 'published' && !$picked['work']) {
        $errors['work'] = '公開する時は、工事の種類を1つ以上選んでください。';
    }

    if (!$errors) {
        $pdo->beginTransaction();
        $slug = unique_slug('cases', $in['slug'] !== '' ? $in['slug'] : make_slug($in['title']), $case ? (int)$case['id'] : null);
        $vals = [$slug, $in['title'], $in['body_html'], $in['location'], $in['published_at'], $in['status'], $in['seo_title'], $in['seo_description']];
        if ($case) {
            $pdo->prepare('UPDATE cases SET slug = ?, title = ?, body_html = ?, location = ?, published_at = ?, status = ?, seo_title = ?, seo_description = ?, needs_review = 0 WHERE id = ?')
                ->execute(array_merge($vals, [$case['id']]));
            $caseId = (int)$case['id'];
            // URL 名を変えたら、古いアドレスから新しいアドレスへ案内する（301）
            if ($slug !== $case['slug']) {
                $pdo->prepare('INSERT INTO slug_redirects (old_slug, new_slug) VALUES (?, ?) ON DUPLICATE KEY UPDATE new_slug = VALUES(new_slug)')->execute([$case['slug'], $slug]);
                $pdo->prepare('UPDATE slug_redirects SET new_slug = ? WHERE new_slug = ?')->execute([$slug, $case['slug']]);
                $pdo->prepare('DELETE FROM slug_redirects WHERE old_slug = ?')->execute([$slug]);
            }
        } else {
            $pdo->prepare('INSERT INTO cases (slug, title, body_html, location, published_at, status, seo_title, seo_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')->execute($vals);
            $caseId = (int)$pdo->lastInsertId();
        }

        $pdo->prepare('DELETE FROM case_categories WHERE case_id = ?')->execute([$caseId]);
        $ins = $pdo->prepare('INSERT INTO case_categories (case_id, category_id) VALUES (?, ?)');
        foreach (array_merge($picked['biz'], $picked['work']) as $cid) {
            $ins->execute([$caseId, $cid]);
        }

        // 写真：並び順・代表写真・削除・新しく足した分
        $st = $pdo->prepare('SELECT id, path FROM case_images WHERE case_id = ?');
        $st->execute([$caseId]);
        $existing = array_column($st->fetchAll(), 'path', 'id');
        $keep = [];
        $mainId = null;
        $order = 0;
        $tokens = $_SESSION['upload_tokens'] ?? [];
        foreach ($photos as $p) {
            $imgId = null;
            if (!empty($p['id']) && isset($existing[(int)$p['id']])) {
                $imgId = (int)$p['id'];
                $pdo->prepare('UPDATE case_images SET sort_order = ? WHERE id = ?')->execute([$order, $imgId]);
            } elseif (!empty($p['tmp']) && preg_match('#^([a-f0-9]{32})/([a-f0-9]{12})$#', (string)$p['tmp'], $tm) && in_array($tm[1], $tokens, true)) {
                $from = PUBLIC_DIR . '/media/cases/new/' . $tm[1] . '/' . $tm[2];
                if (!is_file($from . '.jpg')) {
                    continue;
                }
                $dir = PUBLIC_DIR . '/media/cases/' . $caseId;
                if (!is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
                foreach (['.jpg', '.webp', '-800.jpg', '-800.webp'] as $ext) {
                    rename($from . $ext, $dir . '/' . $tm[2] . $ext);
                }
                [$w, $h] = getimagesize($dir . '/' . $tm[2] . '.jpg');
                $rel = 'media/cases/' . $caseId . '/' . $tm[2];
                $pdo->prepare('INSERT INTO case_images (case_id, path, path_thumb, width, height, alt, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)')
                    ->execute([$caseId, $rel . '.jpg', $rel . '-800.jpg', $w, $h, '', $order]);
                $imgId = (int)$pdo->lastInsertId();
            }
            if ($imgId === null) {
                continue;
            }
            $keep[] = $imgId;
            if (!empty($p['main']) || $mainId === null) {
                $mainId = !empty($p['main']) ? $imgId : ($mainId ?? $imgId);
            }
            $order++;
        }
        foreach ($tokens as $tk) {
            @rmdir(PUBLIC_DIR . '/media/cases/new/' . $tk); // 空になった仮の置き場を片付ける（中身が残っていれば消えない）
        }
        foreach ($existing as $imgId => $imgPath) {
            if (!in_array($imgId, $keep, true)) {
                $pdo->prepare('DELETE FROM case_images WHERE id = ?')->execute([$imgId]);
                if (str_starts_with($imgPath, 'media/cases/' . $caseId . '/')) {
                    delete_case_media($imgPath);
                }
            }
        }
        $pdo->prepare('UPDATE cases SET thumbnail_image_id = ? WHERE id = ?')->execute([$mainId, $caseId]);
        $pdo->commit();

        flash('ok', admin_saved_message($in['status'], $in['published_at']));
        if (($_POST['do'] ?? '') === 'preview') {
            // 「保存して確認」：公開ページの見た目で開く（上に「編集に戻る」の帯が出る）
            redirect(url(rawurlencode($slug) . '/') . '?preview=1');
        }
        redirect(admin_url('cases/' . $caseId . '/'));
    }
}

// 画面に出す値（送信して戻ってきた時は送った値）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $in + ['biz' => $picked['biz'], 'work' => $picked['work']];
    $photoList = [];
    foreach ($photos as $p) {
        if (!empty($p['thumb']) && is_string($p['thumb'])) {
            $photoList[] = ['id' => (int)($p['id'] ?? 0) ?: null, 'tmp' => $p['tmp'] ?? null, 'thumb' => $p['thumb'], 'main' => !empty($p['main'])];
        }
    }
} elseif ($case) {
    $st = $pdo->prepare('SELECT category_id FROM case_categories WHERE case_id = ?');
    $st->execute([$case['id']]);
    $mine = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
    $form = $case + [
        'biz' => array_values(array_intersect($mine, array_keys($cats['biz']))),
        'work' => array_values(array_intersect($mine, array_keys($cats['work']))),
    ];
    $st = $pdo->prepare('SELECT id, path_thumb FROM case_images WHERE case_id = ? ORDER BY sort_order, id');
    $st->execute([$case['id']]);
    $photoList = array_map(fn($r) => ['id' => (int)$r['id'], 'tmp' => null, 'thumb' => url($r['path_thumb']), 'main' => (int)$r['id'] === (int)$case['thumbnail_image_id']], $st->fetchAll());
} else {
    $form = ['title' => '', 'body_html' => '', 'location' => '', 'published_at' => today()->format('Y-m-d'), 'status' => 'published', 'seo_title' => '', 'seo_description' => '', 'slug' => '', 'biz' => [], 'work' => []];
    $photoList = [];
}

// 写真のアップロード用の合言葉（この画面だけで使う）
$token = bin2hex(random_bytes(16));
$_SESSION['upload_tokens'] = array_slice(array_merge($_SESSION['upload_tokens'] ?? [], [$token]), -20);

admin_render('form', [
    'user' => $user, 'nav' => 'cases', 'kind' => 'cases', 'title' => $case ? '施工事例を編集' : '施工事例を新しく書く',
    'item' => $case, 'form' => $form, 'cats' => $cats, 'errors' => $errors,
    'photoList' => $photoList, 'token' => $token, 'flash' => take_flash(),
]);
