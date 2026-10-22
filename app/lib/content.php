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
// トップのお知らせ。site.php の 'top_news'（現行サイトと同じ並び・日付）を施工事例のページにつなぐ。
function top_news(): array
{
    $pdo = db();
    $items = [];
    foreach (site('top_news') as $n) {
        $slug = null;
        if ($pdo) {
            $st = $pdo->prepare("SELECT slug FROM cases WHERE wp_id = ? AND status = 'published'");
            $st->execute([$n['wp_id']]);
            $slug = $st->fetchColumn() ?: null;
        }
        $items[] = [
            'type' => 'case',
            'title' => $n['title'],
            'url' => $slug !== null ? url(rawurlencode($slug) . '/') : page_url('construction_examples'),
            'published_at' => $n['date'],
        ];
    }
    return $items;
}

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

// 指定カテゴリ（名前で指定、どれか1つに当てはまれば可）の新しい施工事例
function cases_in_categories(array $names, int $limit = 3): array
{
    $pdo = db();
    if (!$pdo) {
        $rows = array_filter(dummy_data()['cases'], fn($c) => array_intersect($names, array_merge($c['biz'], $c['work'])));
        usort($rows, fn($a, $b) => strcmp($b['published_at'], $a['published_at']));
        return array_map(fn($r) => $r + ['image' => null, 'image_w' => 800, 'image_h' => 533], array_slice($rows, 0, $limit));
    }
    $in = implode(',', array_fill(0, count($names), '?'));
    $st = $pdo->prepare(
        "SELECT DISTINCT c.id, c.slug, c.title, c.published_at, c.location, i.path_thumb AS image, i.width AS image_w, i.height AS image_h
         FROM cases c
         JOIN case_categories cc ON cc.case_id = c.id
         JOIN categories cat ON cat.id = cc.category_id AND cat.name IN ($in)
         LEFT JOIN case_images i ON i.id = c.thumbnail_image_id
         WHERE c.status = 'published' ORDER BY c.published_at DESC, c.id DESC LIMIT " . (int)$limit
    );
    $st->execute(array_values($names));
    return attach_categories($st->fetchAll());
}

// ---------- 段階4：施工事例の一覧・詳細、お知らせ ----------

const CASES_PER_PAGE = 12;
const NEWS_PER_PAGE = 20;

// 絞り込みに使うカテゴリ。['biz' => [slug => name, ...], 'work' => [...]]（並び順は sort_order）
function case_categories(): array
{
    static $cats = null;
    if ($cats !== null) {
        return $cats;
    }
    $cats = ['biz' => [], 'work' => []];
    $pdo = db();
    if ($pdo) {
        foreach ($pdo->query('SELECT axis, slug, name FROM categories ORDER BY axis, sort_order') as $r) {
            $cats[$r['axis']][$r['slug']] = $r['name'];
        }
    }
    return $cats;
}

// 絞り込みの条件（業態1つ＋工事1つまで）を SQL にする。戻り値 [JOIN 句, 値]
function case_filter_sql(?string $biz, ?string $work): array
{
    $join = '';
    $args = [];
    foreach (['biz' => $biz, 'work' => $work] as $axis => $slug) {
        if ($slug === null) {
            continue;
        }
        $join .= " JOIN case_categories cc_$axis ON cc_$axis.case_id = c.id
                   JOIN categories cat_$axis ON cat_$axis.id = cc_$axis.category_id AND cat_$axis.axis = '$axis' AND cat_$axis.slug = ?";
        $args[] = $slug;
    }
    return [$join, $args];
}

// 一覧の1ページ分。戻り値 ['rows' => [...], 'total' => 件数]
function case_list(?string $biz, ?string $work, int $page, int $per = CASES_PER_PAGE): array
{
    $pdo = db();
    if (!$pdo) {
        return ['rows' => [], 'total' => 0];
    }
    [$join, $args] = case_filter_sql($biz, $work);
    $st = $pdo->prepare("SELECT COUNT(*) FROM cases c $join WHERE c.status = 'published'");
    $st->execute($args);
    $total = (int)$st->fetchColumn();
    $st = $pdo->prepare(
        "SELECT c.id, c.slug, c.title, c.published_at, c.location, i.path_thumb AS image, i.width AS image_w, i.height AS image_h
         FROM cases c $join LEFT JOIN case_images i ON i.id = c.thumbnail_image_id
         WHERE c.status = 'published' ORDER BY c.published_at DESC, c.id DESC
         LIMIT " . (int)$per . ' OFFSET ' . (int)(($page - 1) * $per)
    );
    $st->execute($args);
    return ['rows' => attach_categories($st->fetchAll()), 'total' => $total];
}

// チップに添える件数。もう一方の軸の絞り込みを効かせた数（選ぶと何件になるかが分かる）
function case_counts(string $axis, ?string $other): array
{
    $pdo = db();
    if (!$pdo) {
        return [];
    }
    [$join, $args] = $axis === 'biz' ? case_filter_sql(null, $other) : case_filter_sql($other, null);
    $st = $pdo->prepare(
        "SELECT cat.slug, COUNT(DISTINCT c.id) FROM cases c $join
         JOIN case_categories cc ON cc.case_id = c.id JOIN categories cat ON cat.id = cc.category_id AND cat.axis = ?
         WHERE c.status = 'published' GROUP BY cat.slug"
    );
    $st->execute(array_merge($args, [$axis]));
    return array_map('intval', $st->fetchAll(PDO::FETCH_KEY_PAIR));
}

function find_case(string $slug): ?array
{
    $pdo = db();
    if (!$pdo) {
        return null;
    }
    $st = $pdo->prepare("SELECT * FROM cases WHERE slug = ? AND status = 'published'");
    $st->execute([$slug]);
    $row = $st->fetch();
    if (!$row) {
        return null;
    }
    $st = $pdo->prepare('SELECT cc.case_id AS id, cat.axis, cat.slug, cat.name FROM case_categories cc JOIN categories cat ON cat.id = cc.category_id WHERE cc.case_id = ? ORDER BY cat.sort_order');
    $st->execute([$row['id']]);
    $row['cats'] = ['biz' => [], 'work' => []];
    foreach ($st->fetchAll() as $c) {
        $row['cats'][$c['axis']][$c['slug']] = $c['name'];
    }
    return $row;
}

// 古い slug（管理画面で題名を変えた時など）から今の slug を探す
function redirected_case_slug(string $old): ?string
{
    $pdo = db();
    if (!$pdo) {
        return null;
    }
    $st = $pdo->prepare("SELECT c.slug FROM slug_redirects r JOIN cases c ON c.slug = r.new_slug AND c.status = 'published' WHERE r.old_slug = ?");
    $st->execute([$old]);
    return $st->fetchColumn() ?: null;
}

function case_images(int $caseId): array
{
    $st = db()->prepare('SELECT path, path_thumb, width, height, alt FROM case_images WHERE case_id = ? ORDER BY sort_order, id');
    $st->execute([$caseId]);
    return $st->fetchAll();
}

// 同じカテゴリの事例（自分と $exclude を除く、新しい順）
function related_cases(string $axis, string $slug, array $exclude, int $limit = 3): array
{
    [$join, $args] = $axis === 'biz' ? case_filter_sql($slug, null) : case_filter_sql(null, $slug);
    $not = $exclude ? ' AND c.id NOT IN (' . implode(',', array_map('intval', $exclude)) . ')' : '';
    $st = db()->prepare(
        "SELECT c.id, c.slug, c.title, c.published_at, c.location, i.path_thumb AS image, i.width AS image_w, i.height AS image_h
         FROM cases c $join LEFT JOIN case_images i ON i.id = c.thumbnail_image_id
         WHERE c.status = 'published'$not ORDER BY c.published_at DESC, c.id DESC LIMIT " . (int)$limit
    );
    $st->execute($args);
    return attach_categories($st->fetchAll());
}

// 新しい順に並べた時の前後。['newer' => row|null, 'older' => row|null]
function adjacent_cases(array $case): array
{
    $sel = "SELECT c.slug, c.title, i.path_thumb AS image, i.width AS image_w, i.height AS image_h FROM cases c LEFT JOIN case_images i ON i.id = c.thumbnail_image_id WHERE c.status = 'published' AND ";
    $newer = db()->prepare($sel . '(c.published_at > :d OR (c.published_at = :d2 AND c.id > :id)) ORDER BY c.published_at ASC, c.id ASC LIMIT 1');
    $older = db()->prepare($sel . '(c.published_at < :d OR (c.published_at = :d2 AND c.id < :id)) ORDER BY c.published_at DESC, c.id DESC LIMIT 1');
    $out = [];
    foreach (['newer' => $newer, 'older' => $older] as $k => $st) {
        $st->execute([':d' => $case['published_at'], ':d2' => $case['published_at'], ':id' => $case['id']]);
        $out[$k] = $st->fetch() ?: null;
    }
    return $out;
}

// お知らせ一覧の1ページ分（施工事例とお知らせを混ぜて新しい順）。戻り値 ['rows' => [...], 'total' => 件数]
function feed_page(int $page, int $per = NEWS_PER_PAGE): array
{
    $pdo = db();
    if (!$pdo) {
        return ['rows' => array_slice(latest_feed(1000), ($page - 1) * $per, $per), 'total' => count(latest_feed(1000))];
    }
    $total = (int)$pdo->query("SELECT (SELECT COUNT(*) FROM cases WHERE status='published') + (SELECT COUNT(*) FROM news WHERE status='published')")->fetchColumn();
    $st = $pdo->query(
        "SELECT * FROM (
           SELECT 'case' AS type, slug, title, published_at, id FROM cases WHERE status='published'
           UNION ALL
           SELECT 'news' AS type, slug, title, published_at, id FROM news WHERE status='published'
         ) f ORDER BY published_at DESC, type ASC, id DESC LIMIT " . (int)$per . ' OFFSET ' . (int)(($page - 1) * $per)
    );
    $rows = array_map(fn($r) => [
        'type' => $r['type'],
        'title' => $r['title'],
        'url' => url(($r['type'] === 'news' ? 'news/' : '') . rawurlencode($r['slug']) . '/'),
        'published_at' => $r['published_at'],
    ], $st->fetchAll());
    return ['rows' => $rows, 'total' => $total];
}

function find_news(string $slug): ?array
{
    $pdo = db();
    if (!$pdo) {
        return null;
    }
    $st = $pdo->prepare("SELECT * FROM news WHERE slug = ? AND status = 'published'");
    $st->execute([$slug]);
    return $st->fetch() ?: null;
}

