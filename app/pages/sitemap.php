<?php
// /sitemap.xml：固定ページ＋公開中の施工事例＋お知らせ（自動生成）
header('Content-Type: application/xml; charset=UTF-8');
$urls = [];
foreach (site('pages') as $p) {
    $urls[] = [abs_url($p['path']), null];
}
if ($pdo = db()) {
    foreach (['cases' => '', 'news' => 'news/'] as $table => $prefix) {
        foreach ($pdo->query("SELECT slug, updated_at FROM $table WHERE status = 'published' ORDER BY published_at DESC, id DESC") as $r) {
            $urls[] = [abs_url($prefix . rawurlencode($r['slug']) . '/'), substr($r['updated_at'], 0, 10)];
        }
    }
}
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($urls as [$loc, $mod]) {
    echo '  <url><loc>', e($loc), '</loc>', $mod ? '<lastmod>' . e($mod) . '</lastmod>' : '', "</url>\n";
}
echo "</urlset>\n";
