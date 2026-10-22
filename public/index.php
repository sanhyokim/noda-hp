<?php
declare(strict_types=1);

// 入口。すべての公開ページはここを通る（.htaccess と php -S の両方に対応）。

// php -S で実在するファイル（CSS・画像など）はそのまま返す
if (PHP_SAPI === 'cli-server') {
    $f = __DIR__ . urldecode((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if ($f !== __DIR__ . '/' && is_file($f) && !str_ends_with($f, '.php')) {
        return false;
    }
}

define('PUBLIC_DIR', __DIR__);
// app/ は公開フォルダの外が基本。置けない時は public/app/ に移す（.htaccess で保護済み）。
require is_file(__DIR__ . '/../app/bootstrap.php') ? __DIR__ . '/../app/bootstrap.php' : __DIR__ . '/app/bootstrap.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
if (site_noindex()) {
    header('X-Robots-Tag: noindex, nofollow'); // 試験用の場所（/new/）を検索エンジンに載せない
}
chat_report_tick(); // AI 相談の知らせのメール（5分に1回だけ確かめ、ページを返し終わってから送る）

$path = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$base = base_url();
if (str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
} elseif ($path . '/' === $base) {
    $path = '';
}

// 末尾スラッシュに寄せる（拡張子つきは除く）
if ($path !== '' && !str_ends_with($path, '/') && !preg_match('/\.[a-z0-9]{2,5}$/i', $path)) {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: ' . url(implode('/', array_map('rawurlencode', explode('/', $path)))) . '/' . ($qs !== '' ? '?' . $qs : ''), true, 301);
    exit;
}

// AI チャットの受け口
if ($path === 'api/chat/' || $path === 'api/chat/handoff/' || $path === 'api/chat/callback/' || $path === 'api/chat/rate/') {
    require APP_DIR . '/chat/endpoint.php';
    exit;
}

// 管理ページ（指示書 5）
if ($path === 'admin/' || str_starts_with($path, 'admin/')) {
    require APP_DIR . '/admin/index.php';
    exit;
}

// 旧サイトのアドレス（カテゴリ一覧・一覧の2ページ目以降）からの転送
if (($to = legacy_redirect($path)) !== null) {
    header('Location: ' . $to, true, 301);
    exit;
}

if ($path === 'sitemap.xml') {
    require APP_DIR . '/pages/sitemap.php';
    exit;
}

// 管理ページの「保存して確認」：ログイン中の人だけ、下書きも表示し、上に「編集に戻る」を出す
$preview = null;
if (isset($_GET['preview']) && isset($_COOKIE['noda_admin'])) {
    require_once APP_DIR . '/admin/lib.php';
    if (db() && admin_user()) {
        $preview = true;
        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: no-store');
    }
}

$current = null;
foreach (site('pages') as $key => $p) {
    if ($p['path'] === $path) {
        $current = $key;
        break;
    }
}

$tpl = null;
if ($current !== null) {
    $p = page($current);
    $meta = ['title' => $p['title'], 'description' => $p['description'], 'path' => $p['path']];
    if ($current === 'home') {
        $meta['jsonld'] = [local_business_ld()];
    } else {
        $meta['trail'] = [[$p['name'], $p['path']]];
    }
    $tpl = is_file(APP_DIR . '/pages/' . $current . '.php') ? $current : 'stub';
} elseif (preg_match('#^news/([^/]+)/$#u', $path, $m) && ($news = find_news($m[1], (bool)$preview))) {
    // お知らせの詳しいページ /news/<slug>/
    $current = 'news';
    $tpl = 'news-detail';
} elseif (preg_match('#^([^/]+)/$#u', $path, $m)) {
    // 施工事例の詳しいページ /<slug>/（旧サイトと同じアドレス）
    if ($case = find_case($m[1], (bool)$preview)) {
        $current = 'construction_examples';
        $tpl = 'case';
    } elseif (($to = redirected_case_slug($m[1])) !== null) {
        header('Location: ' . url(rawurlencode($to) . '/'), true, 301);
        exit;
    }
}

if ($tpl === null) {
    http_response_code(404);
    $current = '404';
    $meta = ['title' => 'ページが見つかりません｜株式会社野田', 'description' => 'お探しのページが見つかりません。', 'path' => $path, 'trail' => [['ページが見つかりません', $path]]];
    $tpl = '404';
}

// お問い合わせフォームの受け付け（送信できたら送信済みの画面へ移る）
if ($tpl === 'contact') {
    $form = contact_handle();
}

ob_start();
require APP_DIR . '/pages/' . $tpl . '.php';
$content = (string)ob_get_clean();
// ページ側が $meta を作り直すので、その後で足す
if ($preview && in_array($tpl, ['case', 'news-detail'], true)) {
    $item = $tpl === 'case' ? $case : $news;
    $meta['admin_bar'] = [
        $item['status'] === 'published' ? '公開中のページです（確認用の表示）' : '下書きの確認用の表示です（まだ公開されていません）',
        admin_url(($tpl === 'case' ? 'cases/' : 'news/') . $item['id'] . '/'),
    ];
}

render('layout', ['meta' => $meta, 'content' => $content, 'current' => $current]);
