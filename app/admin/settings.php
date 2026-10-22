<?php
declare(strict_types=1);

// 設定・保守（指示書 5-5）：Instagram のトークンと接続テスト、AI 相談の利用状況と会話の記録、パスワードの変更、
// お問い合わせの受信一覧、データの書き出し。
/** @var string $action index|contacts|chats|answers|export */
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
    // 相談番号（LINE・折り返し依頼に書かれる6文字）か、言葉で探す
    $q = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 50);
    $where = '';
    $args = [];
    if (preg_match('/^[A-Fa-f0-9]{4,32}$/', $q)) {
        $where = ' WHERE conv_key LIKE ?';
        $args[] = strtolower($q) . '%';
    } elseif ($q !== '') {
        $where = ' WHERE messages LIKE ?';
        $args[] = '%' . addcslashes(trim(json_encode($q, JSON_UNESCAPED_UNICODE), '"'), '%_\\') . '%';
    }
    $st = $pdo->prepare('SELECT COUNT(*) FROM chat_logs' . $where);
    $st->execute($args);
    $total = (int)$st->fetchColumn();
    $st = $pdo->prepare('SELECT * FROM chat_logs' . $where . ' ORDER BY started_at DESC, id DESC LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per));
    $st->execute($args);
    $rows = $st->fetchAll();
    foreach ($rows as &$r) {
        $r['messages'] = json_decode($r['messages'], true) ?: [];
    }
    unset($r);
    $pageNames = [];
    foreach (site('pages') as $p) {
        $pageNames[url($p['path'])] = $p['name'];
    }
    admin_render('chats', ['user' => $user, 'nav' => 'settings', 'title' => 'AI 相談の会話の記録', 'rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => admin_pages($total, $per), 'pageNames' => $pageNames, 'q' => $q, 'flash' => take_flash()]);
    exit;
}

// ---------- AI に教える答え（答えられなかった質問に、担当者が後から答えを足す） ----------
if ($action === 'answers') {
    $back = admin_url('settings/answers/');
    // 答えられなかった質問の一覧から1つ外す（$logId の記録の $idx 番目）
    $drop = function (int $logId, int $idx) use ($pdo): void {
        $st = $pdo->prepare('SELECT unanswered FROM chat_logs WHERE id = ?');
        $st->execute([$logId]);
        $list = json_decode((string)$st->fetchColumn(), true) ?: [];
        unset($list[$idx]);
        $pdo->prepare('UPDATE chat_logs SET unanswered = ? WHERE id = ?')
            ->execute([$list ? json_encode(array_values($list), JSON_UNESCAPED_UNICODE) : null, $logId]);
    };
    $errors = [];
    $form = ['id' => 0, 'question' => '', 'answer' => '', 'log' => 0, 'idx' => 0];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $op = (string)($_POST['op'] ?? '');
        if ($op === 'dismiss') {
            $drop((int)($_POST['log'] ?? 0), (int)($_POST['idx'] ?? 0));
            flash('ok', '一覧から外しました。');
            redirect($back . '#todo');
        }
        if ($op === 'delete') {
            $pdo->prepare('DELETE FROM chat_answers WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
            flash('ok', '答えを消しました。次の質問から AI は使いません。');
            redirect($back . '#list');
        }
        if ($op === 'save') {
            $form = [
                'id' => (int)($_POST['id'] ?? 0),
                'question' => trim((string)($_POST['question'] ?? '')),
                'answer' => trim((string)($_POST['answer'] ?? '')),
                'log' => (int)($_POST['log'] ?? 0),
                'idx' => (int)($_POST['idx'] ?? 0),
            ];
            if ($form['question'] === '') {
                $errors['question'] = 'お客様の質問を入れてください。';
            } elseif (mb_strlen($form['question']) > 500) {
                $errors['question'] = '質問は500字以内にしてください。';
            }
            if ($form['answer'] === '') {
                $errors['answer'] = '答えを入れてください。';
            } elseif (mb_strlen($form['answer']) > 2000) {
                $errors['answer'] = '答えは2,000字以内にしてください。';
            }
            if (!$errors) {
                if ($form['id'] > 0) {
                    $pdo->prepare('UPDATE chat_answers SET question = ?, answer = ? WHERE id = ?')->execute([$form['question'], $form['answer'], $form['id']]);
                } else {
                    $pdo->prepare('INSERT INTO chat_answers (question, answer) VALUES (?, ?)')->execute([$form['question'], $form['answer']]);
                }
                if ($form['log'] > 0) {
                    $drop($form['log'], $form['idx']);
                }
                flash('ok', '答えを保存しました。次の質問から AI がこの答えを使います。');
                redirect($back . '#list');
            }
        }
    } elseif (isset($_GET['edit'])) {
        $st = $pdo->prepare('SELECT id, question, answer FROM chat_answers WHERE id = ?');
        $st->execute([(int)$_GET['edit']]);
        if ($row = $st->fetch()) {
            $form = ['id' => (int)$row['id'], 'question' => $row['question'], 'answer' => $row['answer'], 'log' => 0, 'idx' => 0];
        }
    } elseif (isset($_GET['log'])) {
        // 「答えられなかった質問」の「答えを書く」から来た時は、質問を入れておく
        $st = $pdo->prepare('SELECT unanswered FROM chat_logs WHERE id = ?');
        $st->execute([(int)$_GET['log']]);
        $list = json_decode((string)$st->fetchColumn(), true) ?: [];
        $idx = (int)($_GET['idx'] ?? 0);
        if (isset($list[$idx])) {
            $form = ['id' => 0, 'question' => $list[$idx], 'answer' => '', 'log' => (int)$_GET['log'], 'idx' => $idx];
        }
    }
    $todo = [];
    foreach ($pdo->query('SELECT id, conv_key, started_at, unanswered FROM chat_logs WHERE unanswered IS NOT NULL ORDER BY started_at DESC, id DESC LIMIT 100') as $r) {
        foreach (json_decode((string)$r['unanswered'], true) ?: [] as $i => $q) {
            $todo[] = ['log' => (int)$r['id'], 'idx' => $i, 'q' => $q, 'code' => chat_code($r['conv_key']), 'at' => $r['started_at']];
        }
    }
    admin_render('answers', ['user' => $user, 'nav' => 'settings', 'title' => 'AI に教える答え', 'todo' => $todo, 'answers' => chat_answers(), 'form' => $form, 'errors' => $errors, 'flash' => take_flash()]);
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
    if ($op === 'chat_report') {
        $mode = (string)($_POST['chat_report'] ?? '');
        if (isset(CHAT_REPORT_MODES[$mode])) {
            set_setting('chat_report', $mode);
            flash('ok', '担当者への知らせを「' . CHAT_REPORT_MODES[$mode] . '」にしました。');
        }
        redirect(admin_url('settings/') . '#chat-report');
    }
    if ($op === 'chat_key') {
        if (!empty($_POST['clear'])) {
            set_setting('chat_api_key', '');
            flash('ok', 'API キーを消しました。ホームページの右下の「AIに相談」ボタンが消えます。');
            redirect(admin_url('settings/') . '#chat');
        }
        $keyIn = trim((string)($_POST['chat_api_key'] ?? ''));
        $key = $keyIn !== '' ? $keyIn : (string)setting('chat_api_key');
        $model = trim((string)($_POST['chat_model'] ?? ''));
        if (!preg_match('/^sk-(ant|or)-[A-Za-z0-9_\-]{20,300}$/', $key)) {
            flash('error', $keyIn === '' ? 'API キーを貼ってください。' : 'キーの形が正しくありません。「sk-or-」（OpenRouter）か「sk-ant-」（Anthropic）で始まる長い文字を、前後に空白を入れずに貼り付けてください。');
            redirect(admin_url('settings/') . '#chat');
        }
        if (!preg_match('/^[A-Za-z0-9._:\/\-]{0,100}$/', $model)) {
            flash('error', 'モデルの名前の形が正しくありません。例：anthropic/claude-sonnet-5.5');
            redirect(admin_url('settings/') . '#chat');
        }
        $r = chat_test_key($key, $model);
        if (!$r['ok']) {
            flash('error', '保存していません。' . $r['error']);
            redirect(admin_url('settings/') . '#chat');
        }
        set_setting('chat_api_key', $key);
        set_setting('chat_model', $model);
        flash('ok', 'AI とつながることを確かめて保存しました。使うモデル：' . chat_config('model') . '。ホームページの右下に「AIに相談」ボタンが出ます。');
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
        'saved_key' => (string)setting('chat_api_key'),
        'model' => (string)setting('chat_model'),
        'model_now' => chat_configured() ? (string)chat_config('model') : '',
        'provider' => chat_provider(),
        'off' => setting('chat_off') === '1',
        'month' => chat_usage(),
        'prev' => chat_usage(date('Ym', strtotime('first day of last month'))),
        'limit' => (int)chat_config('monthly_limit'),
        'logs' => (int)$pdo->query('SELECT COUNT(*) FROM chat_logs')->fetchColumn(),
        'answers' => count(chat_answers()),
        'todo' => (function () use ($pdo): int {
            try {
                return (int)$pdo->query('SELECT COUNT(*) FROM chat_logs WHERE unanswered IS NOT NULL')->fetchColumn();
            } catch (Throwable $e) {
                return 0;
            }
        })(),
        'report' => chat_report_mode(),
        'handed' => (int)$pdo->query("SELECT COUNT(*) FROM chat_logs WHERE handed_off = 1 AND started_at >= '" . date('Y-m-01') . "'")->fetchColumn(),
        'rated' => (function () use ($pdo): array {
            try {
                $r = $pdo->query("SELECT SUM(rating = 1), SUM(rating = 0) FROM chat_logs WHERE started_at >= '" . date('Y-m-01') . "'")->fetch(PDO::FETCH_NUM);
                return [(int)$r[0], (int)$r[1]];
            } catch (Throwable $e) {
                return [0, 0];
            }
        })(),
    ],
    'contactCount' => $contactCount, 'latestContact' => $latestContact,
    'counts' => [
        'cases' => (int)$pdo->query("SELECT COUNT(*) FROM cases WHERE status <> 'deleted'")->fetchColumn(),
        'news' => (int)$pdo->query("SELECT COUNT(*) FROM news WHERE status <> 'deleted'")->fetchColumn(),
    ],
]);
