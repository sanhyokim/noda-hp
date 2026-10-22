<?php
declare(strict_types=1);

// サーバーでファイル一式を広げる（指示書 段階7）。
// noda-site-1-of-N.zip …（全部）と一緒に、置きたいフォルダ（例：public_html/duct-noda.com/new/）へ FTP で上げ、ブラウザでこのファイルを開く。
//   1. PHP の版と必要な機能を確かめる（足りなければ何もしないで止まる）
//   2. zip がそろっているか確かめ、1つ目から少しずつ広げる（サーバーの時間制限にかからないよう、数秒ごとに画面を読み込み直して続ける）
//   3. 終わったら zip とこのファイルを消し、最初の設定画面（admin/setup.php）へ進む

const STEP_SECONDS = 8;

$dir = __DIR__;
$h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

function page(string $title, string $body, ?string $refresh = null): never
{
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Robots-Tag: noindex, nofollow');
    echo '<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">';
    if ($refresh !== null) {
        echo '<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($refresh, ENT_QUOTES, 'UTF-8') . '">';
    }
    echo '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title><style>'
        . 'body{font:16px/1.8 system-ui,sans-serif;color:#1B1F26;background:#fff;margin:0;padding:40px 16px}main{max-width:560px;margin:0 auto}'
        . 'h1{font-size:22px;margin:0 0 16px}.bar{height:10px;background:#E6E9EE;margin:24px 0 8px}.bar span{display:block;height:100%;background:#0A4EA2}'
        . 'ul{padding-left:1.2em}.ng{color:#B3261E;font-weight:700}.ok{color:#05893D;font-weight:700}a.btn{display:inline-block;margin-top:16px;padding:12px 24px;background:#0A4EA2;color:#fff;text-decoration:none}'
        . '</style></head><body><main><h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>' . $body . '</main></body></html>';
    exit;
}

// 1. PHP の版と機能
$checks = [
    'PHP 8.2 以上（いまは ' . PHP_VERSION . '）' => version_compare(PHP_VERSION, '8.2.0', '>='),
    'データベース（pdo_mysql）' => extension_loaded('pdo_mysql'),
    '日本語の文字（mbstring）' => extension_loaded('mbstring'),
    '写真の縮小・WebP（gd）' => function_exists('imagewebp'),
    'ファイルの種類の確認（fileinfo）' => class_exists('finfo'),
    '外部との通信（curl）' => function_exists('curl_init'),
    'zip を広げる（zip）' => class_exists('ZipArchive'),
];
if (in_array(false, $checks, true)) {
    $li = '';
    foreach ($checks as $label => $ok) {
        $li .= '<li>' . $h($label) . '：' . ($ok ? '<span class="ok">OK</span>' : '<span class="ng">足りません</span>') . '</li>';
    }
    page('サーバーの準備が足りません', '<p>このサーバーでは、新しいホームページがまだ動きません。「足りません」の所を直してから、このページを読み込み直してください。</p><ul>' . $li . '</ul>'
        . '<p>PHP の版は、コアサーバーの管理画面の「ドメイン設定」で、このドメインの PHP を 8.2 以上（あれば最新）に変えると直ります。</p>');
}

// 2. zip がそろっているか
$count = 0;
foreach (glob($dir . '/noda-site-*-of-*.zip') ?: [] as $f) {
    if (preg_match('/noda-site-\d+-of-(\d+)\.zip$/', $f, $m)) {
        $count = max($count, (int)$m[1]);
    }
}
if ($count === 0) {
    page('zip が見つかりません', '<p>このファイルと同じフォルダに、<b>noda-site-1-of-○.zip</b> などの zip を全部上げてから、このページを読み込み直してください。</p>'
        . '<p>もう広げ終わっている時は、<a href="admin/setup.php">最初の設定</a>へ進んでください。</p>');
}
$zipName = fn(int $n): string => 'noda-site-' . $n . '-of-' . $count . '.zip';
$lack = array_filter(range(1, $count), fn($n) => !is_file($dir . '/' . $zipName($n)));
if ($lack) {
    page('zip が足りません', '<p>次の zip がまだありません。FTP で上げてから、このページを読み込み直してください。</p><ul><li>'
        . implode('</li><li>', array_map(fn($n) => $h($zipName($n)), $lack)) . '</li></ul>');
}

// 3. 少しずつ広げる（?p=何個目&i=その中の何件目）
$part = min($count, max(1, (int)($_GET['p'] ?? 1)));
$zip = new ZipArchive();
if ($zip->open($dir . '/' . $zipName($part)) !== true) {
    page('zip を開けません', '<p>' . $h($zipName($part)) . ' が途中までしか上がっていない可能性があります。FTP でこの zip をもう一度上げ直して（上書き）から、このページを読み込み直してください。</p>');
}
$total = $zip->numFiles;
$i = max(0, (int)($_GET['i'] ?? 0));
$start = microtime(true);
@set_time_limit(60);
while ($i < $total && microtime(true) - $start < STEP_SECONDS) {
    $name = (string)$zip->getNameIndex($i);
    // 外のフォルダへ書き出す名前は受け付けない
    if ($name === '' || str_contains($name, '..') || str_starts_with($name, '/') || str_contains($name, '\\')) {
        page('zip の中身がおかしい', '<p>想定していないファイル名がありました（' . $h($name) . '）。制作側に連絡してください。</p>');
    }
    if (!$zip->extractTo($dir, $name)) {
        page('広げられませんでした', '<p>' . $h($name) . ' を書き込めませんでした。サーバーの空き容量か、フォルダの権限（755）を確かめてから、このページを読み込み直してください。</p>');
    }
    $i++;
}
$zip->close();

if ($i < $total || $part < $count) {
    $next = $i < $total ? '?p=' . $part . '&i=' . $i : '?p=' . ($part + 1) . '&i=0';
    $pct = (int)floor((($part - 1) + $i / max(1, $total)) / $count * 100);
    page('ファイルを広げています… ' . $pct . '%', '<div class="bar"><span style="width:' . $pct . '%"></span></div><p>zip ' . $part . ' / ' . $count . ' 個目。自動で進みます。このまま閉じずにお待ちください（数分かかります）。</p>'
        . '<p>止まってしまった時は、このページを読み込み直すと続きから進みます。</p>', $next);
}

// 4. 片付け
for ($n = 1; $n <= $count; $n++) {
    @unlink($dir . '/' . $zipName($n));
}
@unlink(__FILE__);
page('ファイルを広げ終わりました', '<p>ファイルを置き終わりました。zip とこのファイルは消しました。</p><p>次に、データベースの情報を入れて最初の設定をします。</p><a class="btn" href="admin/setup.php">最初の設定へ進む</a>');
