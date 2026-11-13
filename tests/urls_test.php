<?php
declare(strict_types=1);

// 公開側の全アドレスの試験（指示書 10章）：固定ページ・施工事例すべて・お知らせが 200、旧アドレスの転送が 301、
// 無いアドレスが 404、各ページの中のサイト内リンクが切れていないこと。
// 実行：php tests/urls_test.php [サイトのアドレス]（既定 http://127.0.0.1:8080/）。サイトとデータベースが動いている状態で。
// Apache で .htaccess まで確かめる時は、置いた場所の一番上のアドレスを渡す（例 http://127.0.0.1:8085/new/）。旧 WordPress の入口の 410 もそこで確かめる。

require dirname(__DIR__) . '/app/bootstrap.php';

$arg = $argv[1] ?? 'http://127.0.0.1:8080/';
$root = rtrim($arg, '/') . '/';
$origin = preg_replace('#^(https?://[^/]+).*$#', '$1', $root);
$apache = parse_url($root, PHP_URL_PORT) !== 8080;
$pdo = db() ?? exit("データベースにつながりません\n");

function fetch(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => ['X-Forwarded-Proto: https']]);
    $body = (string)curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $loc = (string)curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return [$code, $body, $loc];
}

$fail = 0;
$check = function (string $name, bool $ok, string $why = '') use (&$fail): void {
    if (!$ok) {
        echo '✗ ' . $name . ($why !== '' ? '（' . $why . '）' : '') . "\n";
        $fail++;
    }
};
$enc = fn(string $slug): string => rawurlencode($slug);

// 1. 固定ページ
$pages = array_map(fn($p) => $p['path'], site('pages'));
$pages = array_merge($pages, ['sitemap.xml', 'robots.txt']);
$html = [];
foreach ($pages as $p) {
    [$code, $body] = fetch($root . $p);
    $check('固定ページ /' . $p, $code === 200, (string)$code);
    $html['/' . $p] = $body;
}
echo '✓ 固定ページ ' . count($pages) . " 本\n";

// 2. 施工事例（公開中すべて。旧サイトと同じアドレス）
$slugs = $pdo->query('SELECT slug FROM cases WHERE ' . live_sql() . ' ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
$bad = 0;
foreach ($slugs as $s) {
    [$code, $body] = fetch($root . $enc($s) . '/');
    if ($code !== 200) {
        $check('施工事例 /' . $s . '/', false, (string)$code);
        $bad++;
    } elseif (count($html) < 40) {
        $html['/' . $s . '/'] = $body; // リンク切れ確認に何件か使う
    }
}
$check('施工事例が 168 件以上', count($slugs) >= 168, (string)count($slugs));
echo '✓ 施工事例 ' . (count($slugs) - $bad) . ' / ' . count($slugs) . " 本が 200\n";

// 3. お知らせ
$news = $pdo->query('SELECT slug FROM news WHERE ' . live_sql() . ' ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
foreach ($news as $s) {
    [$code] = fetch($root . 'news/' . $enc($s) . '/');
    $check('お知らせ /news/' . $s . '/', $code === 200, (string)$code);
}
echo '✓ お知らせ ' . count($news) . " 本\n";

// 4. 旧アドレスの転送・無いアドレス
foreach ([
    ['construction_examples/page/2/', 301, 'construction_examples/?page=2'],
    ['category/' . $enc('焼肉屋') . '/', 301, 'construction_examples/?biz=yakiniku'],
    ['category/' . $enc('消煙機') . '/page/2/', 301, 'construction_examples/?work=shoenki'],
    ['concept', 301, 'concept/'],
    ['zz-not-here/', 404, null],
] as [$path, $want, $to]) {
    [$code, , $loc] = fetch($root . $path);
    $check('転送 /' . $path, $code === $want && ($to === null || str_ends_with($loc, '/' . $to)), $code . ' ' . $loc);
}
if ($apache) {
    foreach (['wp-admin/', 'wp-login.php', 'wp-json/wp/v2/posts', 'feed/'] as $path) {
        [$code] = fetch($root . $path);
        $check('旧 WordPress の入口 /' . $path . ' は 410', $code === 410, (string)$code);
    }
    foreach (['app/config.php', 'storage/logs/', 'vendor/autoload.php', 'migration/seed.sql'] as $path) {
        [$code] = fetch($root . $path);
        $check('見せない場所 /' . $path . ' は 403', $code === 403, (string)$code);
    }
}
echo "✓ 転送・404" . ($apache ? '・410・403' : '') . "\n";

// 5. サイト内リンク切れ（取ってきたページの <a href> と画像）
$seen = [];
$links = 0;
foreach ($html as $from => $body) {
    preg_match_all('/(?:href|src)="([^"#]+)"/', $body, $m);
    foreach (array_unique($m[1]) as $u) {
        $u = html_entity_decode($u);
        if (str_starts_with($u, 'https://duct-noda.com/')) {
            continue; // 構造化データ・canonical などの本番の絶対アドレス
        }
        if (!str_starts_with($u, '/') || str_starts_with($u, '//')) {
            continue; // 外のサイト・tel: など
        }
        $abs = $origin . $u;
        if (isset($seen[$abs])) {
            continue;
        }
        [$code] = fetch($abs);
        $seen[$abs] = $code;
        $links++;
        $check('リンク ' . $u . '（' . $from . ' から）', $code === 200 || $code === 301 && str_contains($u, 'admin'), (string)$code);
    }
}
echo '✓ サイト内リンク・画像 ' . $links . " 本を確認\n";

echo $fail === 0 ? "すべて成功\n" : "失敗 {$fail} 件\n";
exit($fail === 0 ? 0 : 1);
