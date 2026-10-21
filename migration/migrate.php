<?php
declare(strict_types=1);

// 現行サイトの施工事例を新サイトのデータベースへ移す（指示書 3-3）。何度実行しても同じ結果になる。
//
// 使い方：
//   1. php migration/fetch.php      … 現行サイトから記事データを取って migration/source/ に保存（最初に1回）
//   2. php migration/migrate.php    … テーブル作成・カテゴリ投入・記事と画像の移行・report.csv の出力
//
// 画像は元のURLと同じ場所 public/wp-content/uploads/ に保存し、
// そこから表示用（長辺1600px）と一覧用（幅800px）を public/media/cases/<事例ID>/ に作る。

if (PHP_SAPI !== 'cli') {
    exit('コマンドラインから実行してください。');
}
ini_set('memory_limit', '1024M');
set_time_limit(0);

define('PUBLIC_DIR', dirname(__DIR__) . '/public');
require dirname(__DIR__) . '/app/bootstrap.php';
require __DIR__ . '/lib.php';

const SOURCE_ORIGIN = 'https://duct-noda.com';
const MEDIA_LONG = 1600;
const MEDIA_THUMB_W = 800;

$pdo = db();
if (!$pdo) {
    fwrite(STDERR, "データベースに接続できません。app/config.php の db を確認してください。\n");
    exit(1);
}

$src = __DIR__ . '/source';
foreach (['posts.json', 'categories.json', 'featured_media.json'] as $f) {
    if (!is_file("$src/$f")) {
        fwrite(STDERR, "migration/source/$f がありません。先に php migration/fetch.php を実行してください。\n");
        exit(1);
    }
}
$posts = json_decode(file_get_contents("$src/posts.json"), true);
$oldCats = array_column(json_decode(file_get_contents("$src/categories.json"), true), 'name', 'id');
$featured = json_decode(file_get_contents("$src/featured_media.json"), true);
$overrides = require __DIR__ . '/category_overrides.php';

// 1. テーブル
foreach (array_filter(array_map('trim', explode(';', preg_replace('/^--.*$/m', '', file_get_contents(__DIR__ . '/schema.sql'))))) as $sql) {
    $pdo->exec($sql);
}

// 2. カテゴリ（付録B）
$catId = [];
$up = $pdo->prepare('INSERT INTO categories (axis, name, slug, sort_order) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), sort_order = VALUES(sort_order)');
foreach (MIG_CATEGORIES as $axis => $list) {
    foreach ($list as $i => [$name, $slug]) {
        $up->execute([$axis, $name, $slug, $i + 1]);
    }
}
foreach ($pdo->query('SELECT id, axis, slug, name FROM categories') as $r) {
    $catId[$r['axis']][$r['slug']] = (int)$r['id'];
    $catName[$r['axis']][$r['slug']] = $r['name'];
}

// 3. 対象の記事
$targets = array_values(array_filter($posts, fn($p) => $p['status'] === 'publish' && trim($p['title']) !== 'テンプレ'));
usort($targets, fn($a, $b) => strcmp($a['date'], $b['date']) ?: $a['id'] <=> $b['id']);
printf("対象の記事：%d 件\n", count($targets));

// 4. 画像を先にまとめて取得（すでにあるものは取らない）
$imageLists = [];
$allUrls = [];
foreach ($targets as $p) {
    $list = [];
    $fm = $featured[(string)$p['featured_media']] ?? null;
    if ($fm) {
        $list[] = ['url' => normalize_image_url($fm['url']), 'alt' => $fm['alt']];
    }
    foreach (extract_images($p['content']) as $img) {
        $list[] = $img;
    }
    $seen = [];
    $list = array_values(array_filter($list, function ($img) use (&$seen) {
        if (isset($seen[$img['url']])) {
            return false;
        }
        return $seen[$img['url']] = true;
    }));
    $imageLists[$p['id']] = $list;
    foreach ($list as $img) {
        $allUrls[$img['url']] = true;
    }
}
$failed = download_all(array_keys($allUrls));
printf("画像：%d 枚（取得失敗 %d 枚）\n", count($allUrls), count($failed));

// 5. 記事を入れる
$findCase = $pdo->prepare('SELECT id FROM cases WHERE wp_id = ?');
$insCase = $pdo->prepare('INSERT INTO cases (wp_id, slug, title, body_html, location, published_at, status, seo_title, seo_description, needs_review, created_at, updated_at)
    VALUES (:wp_id, :slug, :title, :body, :location, :published_at, "published", :seo_title, :seo_description, :needs_review, :created_at, :updated_at)');
$updCase = $pdo->prepare('UPDATE cases SET slug = :slug, title = :title, body_html = :body, location = :location, published_at = :published_at, status = "published",
    seo_title = :seo_title, seo_description = :seo_description, needs_review = :needs_review WHERE wp_id = :wp_id');
$delImages = $pdo->prepare('DELETE FROM case_images WHERE case_id = ?');
$insImage = $pdo->prepare('INSERT INTO case_images (case_id, path, path_thumb, width, height, alt, sort_order, source_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$setThumb = $pdo->prepare('UPDATE cases SET thumbnail_image_id = ? WHERE id = ?');
$delCats = $pdo->prepare('DELETE FROM case_categories WHERE case_id = ?');
$insCat = $pdo->prepare('INSERT INTO case_categories (case_id, category_id) VALUES (?, ?)');

$report = [];
$locationFromArea = 0;
foreach ($targets as $p) {
    $slug = rawurldecode($p['slug']);
    $title = trim($p['title']);
    $body = sanitize_body($p['content']);
    // 本文の「エリア」欄（現行の記事で地名を書く欄）を優先し、無ければ題名から取り出す（3-3-8）
    $location = location_from_area($p['content']);
    if ($location !== '') {
        $locationFromArea++;
    } else {
        $location = location_from_title($title);
    }
    // 末尾の「エリア」見出しと地名は、詳細ページで場所として別に出すので本文からは外す（地名が「ー」の記事も同じ）
    $body = preg_replace('/\s*<h3>エリア<\/h3>\s*<p>[^<]*<\/p>\s*$/u', '', $body);
    $oldNames = array_map(fn($id) => $oldCats[$id] ?? '', $p['categories']);
    $cats = map_categories($oldNames, $title);
    if (isset($overrides[$p['id']])) {
        $o = $overrides[$p['id']];
        $cats = ['biz' => $o['biz'], 'work' => $o['work'], 'needs_review' => !empty($o['review'])];
    }
    $seoTitle = trim($p['aioseo_title']) !== '' ? trim($p['aioseo_title']) : $title . '｜施工事例【株式会社野田】';
    $seoDesc = trim($p['aioseo_description']) !== '' ? trim($p['aioseo_description']) : auto_description($body);
    $vals = [
        ':wp_id' => $p['id'], ':slug' => $slug, ':title' => $title, ':body' => $body, ':location' => $location,
        ':published_at' => substr($p['date'], 0, 10), ':seo_title' => $seoTitle, ':seo_description' => $seoDesc,
        ':needs_review' => (int)$cats['needs_review'],
    ];

    $pdo->beginTransaction();
    $findCase->execute([$p['id']]);
    $caseId = $findCase->fetchColumn();
    if ($caseId === false) {
        $created = str_replace("T", " ", $p["date"]);
        $insCase->execute($vals + [":created_at" => $created, ":updated_at" => $created]);
        $caseId = (int)$pdo->lastInsertId();
    } else {
        $caseId = (int)$caseId;
        $updCase->execute($vals);
    }

    // 画像
    $delImages->execute([$caseId]);
    $okImages = 0;
    $failedHere = [];
    $thumbId = null;
    $usedFiles = [];
    foreach ($imageLists[$p['id']] as $img) {
        $orig = upload_path($img['url']);
        if (isset($failed[$img['url']]) || !is_file($orig)) {
            $failedHere[] = $img['url'];
            continue;
        }
        $made = make_case_media($orig, $caseId, $img['url']);
        if (!$made) {
            $failedHere[] = $img['url'];
            continue;
        }
        $okImages++;
        $usedFiles[] = basename($made['path']);
        $usedFiles[] = basename($made['path_thumb']);
        $usedFiles[] = preg_replace('/\.jpg$/', '.webp', basename($made['path']));
        $usedFiles[] = preg_replace('/\.jpg$/', '.webp', basename($made['path_thumb']));
        $alt = $img['alt'] !== '' ? $img['alt'] : $title . '（写真' . $okImages . '）';
        $insImage->execute([$caseId, $made['path'], $made['path_thumb'], $made['width'], $made['height'], $alt, $okImages, $img['url']]);
        $thumbId ??= (int)$pdo->lastInsertId();
    }
    $setThumb->execute([$thumbId, $caseId]);
    clean_media_dir($caseId, $usedFiles);

    // カテゴリ
    $delCats->execute([$caseId]);
    foreach (['biz', 'work'] as $axis) {
        foreach ($cats[$axis] as $s) {
            $insCat->execute([$caseId, $catId[$axis][$s]]);
        }
    }
    $pdo->commit();

    $reasons = [];
    if ($cats['needs_review']) {
        $reasons[] = 'カテゴリを仮で付けた';
    }
    $report[] = [
        $caseId,
        $title,
        SOURCE_ORIGIN . '/' . rawurlencode($slug) . '/',
        implode(' / ', array_filter($oldNames)),
        implode(' / ', array_map(fn($s) => $catName['biz'][$s], $cats['biz'])),
        implode(' / ', array_map(fn($s) => $catName['work'][$s], $cats['work'])),
        $location,
        $okImages,
        implode(' ', $failedHere),
        $cats['needs_review'] ? '要確認' : '',
    ];
}

// 移行元に無くなった記事は非公開にする（何度実行しても同じ結果にするため）
$wpIds = array_column($targets, 'id');
$in = implode(',', array_map('intval', $wpIds));
$pdo->exec("UPDATE cases SET status = 'deleted' WHERE wp_id IS NOT NULL AND wp_id NOT IN ($in)");

// 6. 報告（Excel で開けるよう BOM 付き UTF-8）
usort($report, fn($a, $b) => $a[0] <=> $b[0]);
$fh = fopen(__DIR__ . '/report.csv', 'w');
fwrite($fh, "\xEF\xBB\xBF");
fputcsv($fh, ['id', '題名', '公開URL', '旧カテゴリ', '新・業態', '新・工事の種類', '場所', '画像枚数', '取得失敗画像', '要確認フラグ'], ',', '"', '');
foreach ($report as $row) {
    fputcsv($fh, $row, ',', '"', '');
}
fclose($fh);

$review = count(array_filter($report, fn($r) => $r[9] !== ''));
$noLoc = count(array_filter($report, fn($r) => $r[6] === ''));
printf("移行：%d 件／要確認 %d 件／場所なし %d 件（うち本文の「エリア」欄から %d 件）／画像の取得失敗 %d 枚\n", count($report), $review, $noLoc, $locationFromArea, count($failed));
echo "報告：migration/report.csv\n";

// ---------------------------------------------------------------------------

function upload_path(string $url): string
{
    $rel = rawurldecode((string)parse_url($url, PHP_URL_PATH));
    return PUBLIC_DIR . $rel;
}

/** まだ手元に無い画像を並行して取得する。戻り値：失敗したURL => 理由 */
function download_all(array $urls): array
{
    $todo = array_values(array_filter($urls, fn($u) => !is_file(upload_path($u))));
    $failed = [];
    $mh = curl_multi_init();
    $active = [];
    $next = 0;
    $total = count($todo);
    $done = 0;
    $start = function (string $url) use ($mh, &$active): void {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 120, CURLOPT_FOLLOWLOCATION => true]);
        curl_multi_add_handle($mh, $ch);
        $active[(int)$ch] = [$ch, $url, 1];
    };
    while ($next < $total || $active) {
        while ($next < $total && count($active) < 8) {
            $start($todo[$next++]);
        }
        curl_multi_exec($mh, $running);
        curl_multi_select($mh, 1.0);
        while ($info = curl_multi_info_read($mh)) {
            $ch = $info['handle'];
            [, $url] = $active[(int)$ch];
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $body = curl_multi_getcontent($ch);
            $type = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            if ($info['result'] === CURLE_OK && $code === 200 && $body !== '' && str_starts_with($type, 'image/')) {
                $path = upload_path($url);
                if (!is_dir(dirname($path))) {
                    mkdir(dirname($path), 0775, true);
                }
                file_put_contents($path, $body);
            } else {
                $failed[$url] = $info['result'] !== CURLE_OK ? curl_strerror($info['result']) : "HTTP $code";
            }
            curl_multi_remove_handle($mh, $ch);
            unset($active[(int)$ch]);
            if (++$done % 100 === 0) {
                echo "  画像取得 $done / $total\n";
            }
        }
    }
    curl_multi_close($mh);
    return $failed;
}

/** 元画像から表示用・一覧用（JPG と WebP）を作る。作成済みならそのまま使う。 */
function make_case_media(string $orig, int $caseId, string $sourceUrl): ?array
{
    $dir = PUBLIC_DIR . '/media/cases/' . $caseId;
    $name = substr(md5($sourceUrl), 0, 12);
    $big = "$dir/$name.jpg";
    $thumb = "$dir/$name-800.jpg";
    $rel = 'media/cases/' . $caseId . '/';
    $files = [$big, $thumb, "$dir/$name.webp", "$dir/$name-800.webp"];
    $fresh = true;
    foreach ($files as $f) {
        if (!is_file($f) || filemtime($f) < filemtime($orig)) {
            $fresh = false;
        }
    }
    if ($fresh) {
        [$w, $h] = getimagesize($big);
        return ['path' => $rel . "$name.jpg", 'path_thumb' => $rel . "$name-800.jpg", 'width' => $w, 'height' => $h];
    }

    $im = load_image($orig);
    if (!$im) {
        return null;
    }
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $w = imagesx($im);
    $h = imagesy($im);
    $scale = min(1, MEDIA_LONG / max($w, $h));
    $bigIm = $scale < 1 ? imagescale($im, (int)round($w * $scale), (int)round($h * $scale), IMG_BICUBIC) : $im;
    $tw = min(MEDIA_THUMB_W, $w);
    $thumbIm = $tw < $w ? imagescale($im, $tw, (int)round($h * $tw / $w), IMG_BICUBIC) : $im;
    imagejpeg($bigIm, $big, 82);
    imagewebp($bigIm, "$dir/$name.webp", 80);
    imagejpeg($thumbIm, $thumb, 80);
    imagewebp($thumbIm, "$dir/$name-800.webp", 78);
    $out = ['path' => $rel . "$name.jpg", 'path_thumb' => $rel . "$name-800.jpg", 'width' => imagesx($bigIm), 'height' => imagesy($bigIm)];
    return $out;
}

/** 画像を読み込み、写真の向き（EXIF）を直し、透明部分は白で埋める。 */
function load_image(string $path): ?GdImage
{
    $info = @getimagesize($path);
    if (!$info) {
        return null;
    }
    $im = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
        IMAGETYPE_PNG => @imagecreatefrompng($path),
        IMAGETYPE_WEBP => @imagecreatefromwebp($path),
        IMAGETYPE_GIF => @imagecreatefromgif($path),
        default => false,
    };
    if (!$im) {
        return null;
    }
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $exif = @exif_read_data($path);
        $im = match ((int)($exif['Orientation'] ?? 1)) {
            3 => imagerotate($im, 180, 0),
            6 => imagerotate($im, -90, 0),
            8 => imagerotate($im, 90, 0),
            default => $im,
        };
    }
    if (!imageistruecolor($im) || $info[2] !== IMAGETYPE_JPEG) {
        $bg = imagecreatetruecolor(imagesx($im), imagesy($im));
        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
        $im = $bg;
    }
    return $im;
}

/** 使わなくなった生成画像を消す。 */
function clean_media_dir(int $caseId, array $keep): void
{
    $dir = PUBLIC_DIR . '/media/cases/' . $caseId;
    if (!is_dir($dir)) {
        return;
    }
    foreach (scandir($dir) as $f) {
        if ($f !== '.' && $f !== '..' && !in_array($f, $keep, true)) {
            unlink("$dir/$f");
        }
    }
}
