<?php
declare(strict_types=1);

// 管理ページ /admin/ の入口（public/index.php から呼ばれる）。$path は 'admin/...' の形。
// 検索エンジンに載せない・計測タグを入れない・他サイトの枠に入れさせない。

require APP_DIR . '/admin/lib.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header("Content-Security-Policy: frame-ancestors 'self'");

$sub = substr($path, strlen('admin/'));
$method = $_SERVER['REQUEST_METHOD'];

// 初回セットアップ：/admin/setup.php（終わるとファイル名を setup.php.done に変えて無効になる）
if ($sub === 'setup.php') {
    if (!is_file(APP_DIR . '/admin/setup.php')) {
        http_response_code(404);
        admin_render('error', ['title' => 'セットアップは完了しています', 'message' => '最初の設定はもう済んでいます。ログイン画面からお入りください。', 'back' => [admin_url('login/'), 'ログイン画面へ']]);
        exit;
    }
    require APP_DIR . '/admin/setup.php';
    exit;
}

// 置いたばかりで設定ファイルが無い時は、最初の設定へ
if (!is_file(APP_DIR . '/config.php') && is_file(APP_DIR . '/admin/setup.php')) {
    redirect(admin_url('setup.php'));
}

if (!db()) {
    http_response_code(503);
    admin_render('error', ['title' => 'データベースにつながりません', 'message' => 'app/config.php のデータベースの設定を確認してください。']);
    exit;
}
apply_data_fixes();

if ($sub === 'login/') {
    admin_session_start();
    $error = null;
    $loginId = '';
    if ($method === 'POST') {
        csrf_check();
        $loginId = trim((string)($_POST['login_id'] ?? ''));
        $error = admin_login($loginId, (string)($_POST['password'] ?? ''));
        if ($error === null) {
            redirect(admin_url('cases/'));
        }
    } elseif (admin_user()) {
        redirect(admin_url('cases/'));
    }
    admin_render('login', ['title' => 'ログイン', 'error' => $error, 'loginId' => $loginId, 'flash' => take_flash()]);
    exit;
}

$user = admin_user();
if (!$user) {
    redirect(admin_url('login/'));
}

if ($method === 'POST') {
    csrf_check();
}

if ($sub === '' ) {
    redirect(admin_url('cases/'));
}
if ($sub === 'logout/' && $method === 'POST') {
    admin_logout();
    admin_session_start();
    flash('info', 'ログアウトしました。');
    redirect(admin_url('login/'));
}

$routes = [
    '#^cases/$#' => ['cases', 'list'],
    '#^cases/new/$#' => ['cases', 'edit'],
    '#^cases/(\d+)/$#' => ['cases', 'edit'],
    '#^cases/(\d+)/delete/$#' => ['cases', 'delete'],
    '#^upload/$#' => ['cases', 'upload'],
    '#^news/$#' => ['news', 'list'],
    '#^news/new/$#' => ['news', 'edit'],
    '#^news/(\d+)/$#' => ['news', 'edit'],
    '#^news/(\d+)/delete/$#' => ['news', 'delete'],
    '#^categories/$#' => ['categories', 'index'],
    '#^settings/$#' => ['settings', 'index'],
    '#^settings/contacts/$#' => ['settings', 'contacts'],
    '#^settings/chats/$#' => ['settings', 'chats'],
    '#^settings/answers/$#' => ['settings', 'answers'],
    '#^settings/export/$#' => ['settings', 'export'],
];
foreach ($routes as $re => [$file, $action]) {
    if (preg_match($re, $sub, $m)) {
        $id = isset($m[1]) ? (int)$m[1] : null;
        require APP_DIR . '/admin/' . $file . '.php';
        exit;
    }
}

http_response_code(404);
admin_render('error', ['user' => $user, 'title' => 'ページが見つかりません', 'message' => 'お探しの管理ページは見つかりませんでした。', 'back' => [admin_url('cases/'), '施工事例の一覧へ']]);
