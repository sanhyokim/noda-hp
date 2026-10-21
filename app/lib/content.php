<?php
declare(strict_types=1);

function dummy_data(): array
{
    static $d = null;
    return $d ??= require APP_DIR . '/data/dummy.php';
}

// 施工事例を新しい順に。各要素：slug, title, published_at, location, biz[], work[], image(path|null), image_w, image_h
function latest_cases(int $limit): array
{
    $pdo = db();
    if (!$pdo) {
        $rows = dummy_data()['cases'];
        usort($rows, fn($a, $b) => strcmp($b['published_at'], $a['published_at']));
        return array_map(fn($r) => $r + ['image' => null, 'image_w' => 800, 'image_h' => 533], array_slice($rows, 0, $limit));
    }
    $st = $pdo->prepare(
        "SELECT c.id, c.slug, c.title, c.published_at, c.location, i.path_thumb AS image, i.width AS image_w, i.height AS image_h
         FROM cases c LEFT JOIN case_images i ON i.id = c.thumbnail_image_id
         WHERE c.status = 'published' ORDER BY c.published_at DESC, c.id DESC LIMIT :n"
    );
    $st->bindValue(':n', $limit, PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetchAll();
    return attach_categories($rows);
}

function attach_categories(array $rows): array
{
    $pdo = db();
    if (!$pdo || !$rows) {
        return $rows;
    }
    $ids = array_column($rows, 'id');
    $in = implode(',', array_fill(0, count($ids), '?'));
    $st = $pdo->prepare("SELECT cc.case_id, cat.axis, cat.name FROM case_categories cc JOIN categories cat ON cat.id = cc.category_id WHERE cc.case_id IN ($in) ORDER BY cat.sort_order");
    $st->execute($ids);
    $map = [];
    foreach ($st->fetchAll() as $r) {
        $map[$r['case_id']][$r['axis']][] = $r['name'];
    }
    foreach ($rows as &$r) {
        $r['biz'] = $map[$r['id']]['biz'] ?? [];
        $r['work'] = $map[$r['id']]['work'] ?? [];
    }
    return $rows;
}

// トップの「お知らせ」：施工事例とお知らせを混ぜて新しい順。各要素：type(case|news), title, url, published_at
function latest_feed(int $limit): array
{
    $pdo = db();
    if (!$pdo) {
        $items = [];
        foreach (dummy_data()['cases'] as $c) {
            $items[] = ['type' => 'case', 'title' => $c['title'], 'url' => url($c['slug'] . '/'), 'published_at' => $c['published_at']];
        }
        foreach (dummy_data()['news'] as $n) {
            $items[] = ['type' => 'news', 'title' => $n['title'], 'url' => url('news/' . $n['slug'] . '/'), 'published_at' => $n['published_at']];
        }
    } else {
        $st = $pdo->prepare(
            "(SELECT 'case' AS type, slug, title, published_at, id FROM cases WHERE status='published' ORDER BY published_at DESC, id DESC LIMIT :a)
             UNION ALL
             (SELECT 'news' AS type, slug, title, published_at, id FROM news WHERE status='published' ORDER BY published_at DESC, id DESC LIMIT :b)"
        );
        $st->bindValue(':a', $limit, PDO::PARAM_INT);
        $st->bindValue(':b', $limit, PDO::PARAM_INT);
        $st->execute();
        $items = array_map(fn($r) => [
            'type' => $r['type'],
            'title' => $r['title'],
            'url' => url(($r['type'] === 'news' ? 'news/' : '') . rawurlencode($r['slug']) . '/'),
            'published_at' => $r['published_at'],
        ], $st->fetchAll());
    }
    usort($items, fn($a, $b) => strcmp($b['published_at'], $a['published_at']));
    return array_slice($items, 0, $limit);
}

// Instagram：取得済みの storage/cache/instagram.json だけを読む。無ければ代替画像。
// 各要素：image(公開パス or 外部URL), url, alt, fallback(bool)
function instagram_items(): array
{
    $file = STORAGE_DIR . '/cache/instagram.json';
    if (is_file($file)) {
        $json = json_decode((string)file_get_contents($file), true);
        $items = [];
        foreach (($json['data'] ?? []) as $m) {
            $src = ($m['media_type'] ?? '') === 'VIDEO' ? ($m['thumbnail_url'] ?? '') : ($m['media_url'] ?? '');
            if ($src === '') {
                continue;
            }
            $items[] = ['image' => $src, 'url' => $m['permalink'] ?? company('instagram_url'), 'alt' => mb_strimwidth((string)($m['caption'] ?? 'Instagram の投稿'), 0, 60, '…'), 'fallback' => false];
            if (count($items) === 6) {
                break;
            }
        }
        if (count($items) === 6) {
            return $items;
        }
    }
    $items = [];
    for ($i = 1; $i <= 6; $i++) {
        $items[] = ['image' => 'ig-fallback-' . $i . '.jpg', 'url' => company('instagram_url'), 'alt' => '株式会社野田の施工の様子 ' . $i, 'fallback' => true];
    }
    return $items;
}
