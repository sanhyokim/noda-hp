<?php
declare(strict_types=1);

// 管理ページの共通部品：ログイン、ワンタイムの合言葉（CSRF）、画面の出力（指示書 5-1・7）。

const ADMIN_IDLE = 7200;      // 無操作 2 時間で自動ログアウト
const ADMIN_MAX_FAIL = 5;     // 5 回失敗で
const ADMIN_LOCK_MIN = 15;    // 15 分ロック

function admin_url(string $path = ''): string
{
    return url('admin/' . ltrim($path, '/'));
}

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    session_name('noda_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => base_url(), // 公開ページの「確認用の表示」でもログイン中か分かるように
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.gc_maxlifetime', (string)ADMIN_IDLE);
    $dir = STORAGE_DIR . '/sessions';
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    session_save_path($dir);
    session_start();
}

/** ログイン中の管理者（無ければ null）。無操作が 2 時間を超えていたらログアウトする。 */
function admin_user(): ?array
{
    admin_session_start();
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    if (time() - (int)($_SESSION['seen'] ?? 0) > ADMIN_IDLE) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['flash'] = ['info', '一定時間操作がなかったため、ログアウトしました。もう一度ログインしてください。'];
        return null;
    }
    $_SESSION['seen'] = time();
    $st = db()->prepare('SELECT id, login_id FROM admin_users WHERE id = ?');
    $st->execute([$_SESSION['admin_id']]);
    return $st->fetch() ?: null;
}

/** ID とパスワードを確かめる。戻り値：成功なら null、失敗なら画面に出す文。 */
function admin_login(string $loginId, string $password): ?string
{
    $pdo = db();
    $st = $pdo->prepare('SELECT * FROM admin_users WHERE login_id = ?');
    $st->execute([$loginId]);
    $u = $st->fetch();
    $wrong = 'ID またはパスワードが違います。';
    if (!$u) {
        password_verify($password, '$2y$10$H3K.demtXVneBPvdckU.Q.xnnUnuq7bIxa2nVTYL.PKWKWVXN.idO'); // 時間差で ID の有無が分からないように
        return $wrong;
    }
    if ($u['locked_until'] !== null && strtotime($u['locked_until']) > time()) {
        $min = (int)ceil((strtotime($u['locked_until']) - time()) / 60);
        return "続けて間違えたため、ロックしています。{$min}分ほど待ってから、もう一度お試しください。";
    }
    if (!password_verify($password, $u['password_hash'])) {
        $fail = (int)$u['failed_count'] + 1;
        if ($fail >= ADMIN_MAX_FAIL) {
            $pdo->prepare('UPDATE admin_users SET failed_count = 0, locked_until = ? WHERE id = ?')
                ->execute([date('Y-m-d H:i:s', time() + ADMIN_LOCK_MIN * 60), $u['id']]);
            return '5回続けて間違えたため、15分間ロックしました。15分ほど待ってから、もう一度お試しください。';
        }
        $pdo->prepare('UPDATE admin_users SET failed_count = ? WHERE id = ?')->execute([$fail, $u['id']]);
        return $wrong . '（あと' . (ADMIN_MAX_FAIL - $fail) . '回間違えると、15分間ロックします）';
    }
    if (password_needs_rehash($u['password_hash'], PASSWORD_BCRYPT)) {
        $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_BCRYPT), $u['id']]);
    }
    $pdo->prepare('UPDATE admin_users SET failed_count = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?')->execute([$u['id']]);
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$u['id'];
    $_SESSION['seen'] = time();
    return null;
}

function admin_logout(): void
{
    admin_session_start();
    $_SESSION = [];
    session_regenerate_id(true);
}

function csrf_token(): string
{
    admin_session_start();
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** POST はすべて合言葉を確かめる。合わなければ 400 で止める。 */
function csrf_check(): void
{
    $sent = (string)($_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            admin_json(['error' => '画面の有効期限が切れました。ページを読み込み直してから、もう一度お試しください。'], 400);
        }
        admin_render('error', ['title' => 'もう一度お試しください', 'message' => '画面の有効期限が切れたか、正しくない送信です。ページを読み込み直してから、もう一度お試しください。']);
        exit;
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = [$type, $message];
}

function take_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 303);
    exit;
}

function admin_json(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** 管理ページの画面を出す。$view は app/admin/views/ のファイル名。 */
function admin_render(string $view, array $vars = []): void
{
    $vars['user'] ??= null;
    extract($vars, EXTR_SKIP);
    ob_start();
    require APP_DIR . '/admin/views/' . $view . '.php';
    $content = (string)ob_get_clean();
    require APP_DIR . '/admin/views/layout.php';
}

/** 本文の HTML を許可した要素だけにし、空なら '' を返す */
function admin_clean_body(string $html): string
{
    $html = clean_body_html($html);
    return trim(strip_tags($html)) === '' ? '' : $html;
}

/** 重ならない URL 名を作る。$table は cases か news。$selfId は自分の id（編集時） */
function unique_slug(string $table, string $want, ?int $selfId): string
{
    $base = $want !== '' ? $want : date('Ymd-His');
    $slug = $base;
    for ($i = 2; ; $i++) {
        $st = db()->prepare("SELECT id FROM $table WHERE slug = ?" . ($selfId ? ' AND id <> ' . (int)$selfId : ''));
        $st->execute([$slug]);
        $taken = $st->fetchColumn() !== false;
        // 固定ページと同じ名前は使えない
        foreach (site('pages') as $p) {
            if ($table === 'cases' && $p['path'] === $slug . '/') {
                $taken = true;
            }
        }
        if (!$taken && !in_array($slug, ['admin', 'news', 'media', 'assets', 'wp-content', 'category', 'sitemap.xml'], true)) {
            return $slug;
        }
        $slug = $base . '-' . $i;
    }
}

/** ページ送り用の数字 */
function admin_pages(int $total, int $per): int
{
    return max(1, (int)ceil($total / $per));
}

/** 保存した時のお知らせ。投稿日が未来なら予約投稿として、その日から出ると伝える */
function admin_saved_message(string $status, string $date): string
{
    if ($status !== 'published') {
        return '下書きとして保存しました（公開ページにはまだ出ません）。';
    }
    if ($date > today()->format('Y-m-d')) {
        return '予約投稿として保存しました。投稿日の ' . date('Y年n月j日', strtotime($date)) . ' になると、公開ページに出ます。';
    }
    return '保存しました。公開ページに反映されています。';
}
