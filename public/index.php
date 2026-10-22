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

// 旧サイトのアドレス（カテゴリ一覧・一覧の2ページ目以降）からの転送
if (($to = legacy_redirect($path)) !== null) {
    header('Location: ' . $to, true, 301);
    exit;
}

if ($path === 'sitemap.xml') {
    require APP_DIR . '/pages/sitemap.php';
    exit;
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
} elseif (preg_match('#^news/([^/]+)/$#u', $path, $m) && ($news = find_news($m[1]))) {
    // お知らせの詳しいページ /news/<slug>/
    $current = 'news';
    $tpl = 'news-detail';
} elseif (preg_match('#^([^/]+)/$#u', $path, $m)) {
    // 施工事例の詳しいページ /<slug>/（旧サイトと同じアドレス）
    if ($case = find_case($m[1])) {
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

ob_start();
require APP_DIR . '/pages/' . $tpl . '.php';
$content = (string)ob_get_clean();

render('layout', ['meta' => $meta, 'content' => $content, 'current' => $current]);
