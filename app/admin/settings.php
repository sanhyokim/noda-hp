<?php
declare(strict_types=1);

// 設定・保守（指示書 5-5）：Instagram のトークンと接続テスト、AI 相談の利用状況と会話の記録、パスワードの変更、
// お問い合わせの受信一覧、データの書き出し。
/** @var string $action index|contacts|chats|export */
/** @var array $user */

$pdo = db();

// ---------- データの書き出し（施工事例・お知らせ・カテゴリを JSON 1 ファイルで） ----------
if ($action === 'export') {
    $cats = $pdo->query('SELECT id, axis, name, slug, sort_order FROM categories ORDER BY axis, sort_order')->fetchAll();
    $catById = array_column($cats, null, 'id');
    $cases = $pdo->query("SELECT * FROM cases WHERE status <> 'deleted' ORDER BY published_at DESC, id DESC")->fetchAll();
    $imgs = [];
    foreach ($pdo->query('SELECT case_id, id, path, path_thumb, width, height, alt, sort_order, source_url FROM case_images ORDER BY case_id, sort_order, id') as $r) {
        $imgs[$r['case_id']][] = $r;
    }
    $cc = [];
    foreach ($pdo->query('SELECT case_id, category_id FROM case_categories') as $r) {
        if (isset($catById[$r['category_id']])) {
            $c = $catById[$r['category_id']];
            $cc[$r['case_id']][$c['axis']][] = $c['slug'];
        }
    }
    foreach ($cases as &$c) {
        $c['categories'] = ['biz' => $cc[$c['id']]['biz'] ?? [], 'work' => $cc[$c['id']]['work'] ?? []];
        $c['images'] = $imgs[$c['id']] ?? [];
    }
    unset($c);
    $out = [
        'exported_at' => date('c'),
        'site' => abs_url(),
        'categories' => $cats,
        'cases' => $cases,
        'news' => $pdo->query("SELECT * FROM news WHERE status <> 'deleted' ORDER BY published_at DESC, id DESC")->fetchAll(),
        'slug_redirects' => $pdo->query('SELECT * FROM slug_redirects')->fetchAll(),
    ];
    header('Content-Type: application/json; charset=UTF-8');
    header('Content-Disposition: attachment; filename="noda-site-' . date('Ymd-His') . '.json"');
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

// ---------- お問い合わせの受信一覧 ----------
if ($action === 'contacts') {
    $per = 20;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $total = (int)$pdo->query('SELECT COUNT(*) FROM contact_logs')->fetchColumn();
    $rows = $pdo->query('SELECT * FROM contact_logs ORDER BY created_at DESC, id DESC LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per))->fetchAll();
    admin_render('contacts', ['user' => $user, 'nav' => 'settings', 'title' => 'お問い合わせの受信一覧', 'rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => admin_pages($total, $per), 'flash' => take_flash()]);
    exit;
}

// ---------- AI 相談の会話の記録 ----------
if ($action === 'chats') {
    $per = 20;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $total = (int)$pdo->query('SELECT COUNT(*) FROM chat_logs')->fetchColumn();
    $rows = $pdo->query('SELECT * FROM chat_logs ORDER BY started_at DESC, id DESC LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per))->fetchAll();
    foreach ($rows as &$r) {
        $r['messages'] = json_decode($r['messages'], true) ?: [];
    }
    unset($r);
    $pageNames = [];
    foreach (site('pages') as $p) {
        $pageNames[url($p['path'])] = $p['name'];
    }
    admin_render('chats', ['user' => $user, 'nav' => 'settings', 'title' => 'AI 相談の会話の記録', 'rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => admin_pages($total, $per), 'pageNames' => $pageNames, 'flash' => take_flash()]);
    exit;
}

// ---------- 設定 ----------
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = (string)($_POST['op'] ?? '');
    if ($op === 'chat') {
        $off = ($_POST['chat'] ?? '') === 'off';
        set_setting('chat_off', $off ? '1' : '0');
        flash('ok', $off ? 'AI 相談を止めました。ホームページの右下の「AIに相談」ボタンが消えます。' : 'AI 相談を再開しました。ホームページの右下に「AIに相談」ボタンが出ます。');
        redirect(admin_url('settings/') . '#chat');
    }
    if ($op === 'instagram') {
        $tokenIn = trim((string)($_POST['instagram_token'] ?? ''));
        if (!empty($_POST['clear'])) {
            set_setting('instagram_token', '');
            set_setting('instagram_token_expires', '0');
            @unlink(STORAGE_DIR . '/cache/instagram.json');
            flash('ok', 'Instagram のトークンを消しました。トップページは代わりの写真6枚の表示に戻ります。');
            redirect(admin_url('settings/') . '#instagram');
        }
        if ($tokenIn !== '') {
            if (!preg_match('/^[A-Za-z0-9_\-|.]{20,1000}$/', $tokenIn)) {
                flash('error', 'トークンの形が正しくありません。前後に余計な文字が入っていないか確かめてください。');
                redirect(admin_url('settings/') . '#instagram');
            }
            set_setting('instagram_token', $tokenIn);
            set_setting('instagram_token_expires', (string)(time() + IG_TOKEN_DAYS * 86400));
        }
        // 保存と同時に接続テスト（＝最新の投稿を取得）
        $r = instagram_fetch(true);
        if ($r['ok']) {
            flash('ok', 'Instagram につながりました。最新の投稿を' . $r['count'] . '件読み込み、トップページに反映しました。');
        } else {
            flash('error', ($tokenIn !== '' ? 'トークンは保存しましたが、' : '') . '接続できませんでした。' . $r['error']);
        }
        redirect(admin_url('settings/') . '#instagram');
    }

    if ($op === 'password') {
        $cur = (string)($_POST['current'] ?? '');
        $new = (string)($_POST['new'] ?? '');
        $again = (string)($_POST['again'] ?? '');
        $st = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
        $st->execute([$user['id']]);
        if (!password_verify($cur, (string)$st->fetchColumn())) {
            $errors['current'] = '今のパスワードが違います。';
        }
        if (mb_strlen($new) < 10) {
            $errors['new'] = '新しいパスワードは10文字以上にしてください。';
        } elseif ($new === $cur) {
            $errors['new'] = '今と違うパスワードにしてください。';
        }
        if ($new !== $again) {
            $errors['again'] = '確認用のパスワードが一致しません。';
        }
        if (!$errors) {
            $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_BCRYPT), $user['id']]);
            session_regenerate_id(true);
            flash('ok', 'パスワードを変えました。次のログインから新しいパスワードを使ってください。');
            redirect(admin_url('settings/') . '#password');
        }
    }
}

$token = (string)setting('instagram_token', '');
$contactCount = (int)$pdo->query('SELECT COUNT(*) FROM contact_logs')->fetchColumn();
$latestContact = $pdo->query('SELECT created_at FROM contact_logs ORDER BY created_at DESC LIMIT 1')->fetchColumn() ?: null;
admin_render('settings', [
    'user' => $user, 'nav' => 'settings', 'title' => '設定', 'errors' => $errors, 'flash' => take_flash(),
    'ig' => [
        'has_token' => $token !== '',
        'token_tail' => $token !== '' ? substr($token, -6) : '',
        'expires' => (int)setting('instagram_token_expires', '0'),
        'fetched_at' => (int)setting('instagram_fetched_at', '0'),
        'error' => (string)setting('instagram_last_error', ''),
        'cache' => is_file(STORAGE_DIR . '/cache/instagram.json'),
    ],
    'chat' => [
        'configured' => chat_configured(),
        'off' => setting('chat_off') === '1',
        'month' => chat_usage(),
        'prev' => chat_usage(date('Ym', strtotime('first day of last month'))),
        'limit' => (int)chat_config('monthly_limit'),
        'logs' => (int)$pdo->query('SELECT COUNT(*) FROM chat_logs')->fetchColumn(),
        'handed' => (int)$pdo->query("SELECT COUNT(*) FROM chat_logs WHERE handed_off = 1 AND started_at >= '" . date('Y-m-01') . "'")->fetchColumn(),
    ],
    'contactCount' => $contactCount, 'latestContact' => $latestContact,
    'counts' => [
        'cases' => (int)$pdo->query("SELECT COUNT(*) FROM cases WHERE status <> 'deleted'")->fetchColumn(),
        'news' => (int)$pdo->query("SELECT COUNT(*) FROM news WHERE status <> 'deleted'")->fetchColumn(),
    ],
]);
