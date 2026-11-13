<?php
declare(strict_types=1);

// 現行サイト（WordPress）の公開記事・カテゴリ・アイキャッチ画像の情報を取得し、
// migration/source/ に JSON で保存する。migrate.php はこの保存済みファイルだけを読む。
//
// 指示書 3-3 は書き出しファイル（WordPress_2026-10-21.xml）を読む前提だが、手元に届いていないため、
// 同じ内容を現行サイトの公開API（/wp-json/wp/v2/）から取る。XML の項目との対応：
//   post_name → slug / post_date → date / category → categories / _thumbnail_id → featured_media
//   _aioseo_title・_aioseo_description → aioseo_meta_data.title・description
//
// 使い方：php migration/fetch.php

const WP_API = 'https://duct-noda.com/wp-json/wp/v2/';
$outDir = __DIR__ . '/source';
if (!is_dir($outDir)) {
    mkdir($outDir, 0775, true);
}

function api_get(string $path): array
{
    $ch = curl_init(WP_API . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 120, CURLOPT_FOLLOWLOCATION => true]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($body === false || $code !== 200) {
        fwrite(STDERR, "取得失敗 ($code): $path " . curl_error($ch) . "\n");
        exit(1);
    }
    return json_decode((string)$body, true, 512, JSON_THROW_ON_ERROR);
}

// 記事（公開のみ。API は公開記事しか返さない）
$posts = [];
for ($page = 1; ; $page++) {
    $rows = api_get("posts?per_page=100&page=$page&orderby=id&order=asc");
    foreach ($rows as $p) {
        $meta = $p['aioseo_meta_data'] ?? [];
        $posts[] = [
            'id' => $p['id'],
            'date' => $p['date'],
            'slug' => $p['slug'],
            'status' => $p['status'],
            'title' => html_entity_decode($p['title']['rendered'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'content' => $p['content']['rendered'],
            'categories' => $p['categories'],
            'featured_media' => $p['featured_media'],
            'aioseo_title' => (string)($meta['title'] ?? ''),
            'aioseo_description' => (string)($meta['description'] ?? ''),
        ];
    }
    if (count($rows) < 100) {
        break;
    }
}

$cats = array_map(fn($c) => ['id' => $c['id'], 'name' => html_entity_decode($c['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'slug' => $c['slug']], api_get('categories?per_page=100'));

// アイキャッチ画像の元ファイルURL
$ids = array_values(array_unique(array_filter(array_column($posts, 'featured_media'))));
$media = [];
foreach (array_chunk($ids, 100) as $chunk) {
    foreach (api_get('media?per_page=100&include=' . implode(',', $chunk)) as $m) {
        $media[(string)$m['id']] = ['url' => $m['source_url'], 'alt' => (string)($m['alt_text'] ?? '')];
    }
}

$flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
file_put_contents("$outDir/posts.json", json_encode($posts, $flags));
file_put_contents("$outDir/categories.json", json_encode($cats, $flags));
file_put_contents("$outDir/featured_media.json", json_encode($media, $flags));
printf("記事 %d 件、カテゴリ %d 件、アイキャッチ %d 件を保存しました。\n", count($posts), count($cats), count($media));
