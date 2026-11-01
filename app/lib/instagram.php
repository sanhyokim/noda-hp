<?php
declare(strict_types=1);

// Instagram の最新投稿の取得（指示書 6 章）。トークンは settings の instagram_token。
// 結果は storage/cache/instagram.json に保存し、公開ページは instagram_items() でこのファイルだけを読む。
// 写真は自分のサーバー（public/media/instagram/）に保存して、そこから出す。
// Instagram の写真のアドレスは数日で使えなくなるため、そのまま載せると画像が切れる（2026-11-01 Boss 指摘）。
// 読み込みは、ページが開かれた時に 6 時間に 1 回だけ、ページを返し終わってから行う（instagram_tick）。

if (!defined('IG_API')) {
    define('IG_API', 'https://graph.instagram.com'); // 試験では手元の偽物に向ける
}
const IG_INTERVAL = 6 * 3600;          // 取得は 6 時間に 1 回まで
const IG_TOKEN_DAYS = 60;              // 長期トークンの有効期間
const IG_REFRESH_BEFORE = 10 * 86400;  // 残り 10 日以下で更新
const IG_MEDIA_DIR = 'media/instagram'; // public からの道筋
const IG_SHOW = 6;                     // トップページに出す数
const IG_SIZE = 600;                   // 保存する正方形の一辺

function ig_get(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($body === false) {
        error_log('Instagram 接続失敗: ' . $err);
        return ['ok' => false, 'error' => 'Instagram につながりませんでした。少し時間をおいて、もう一度お試しください。'];
    }
    $json = json_decode((string)$body, true);
    if ($code !== 200 || !is_array($json) || isset($json['error'])) {
        error_log('Instagram エラー ' . $code . ': ' . (is_array($json) ? json_encode($json['error'] ?? $json, JSON_UNESCAPED_UNICODE) : substr((string)$body, 0, 300)));
        return ['ok' => false, 'error' => 'Instagram からトークンが受け付けられませんでした。貼り付けたトークンが正しいか、期限が切れていないかを確かめてください。'];
    }
    return ['ok' => true, 'data' => $json];
}

/**
 * 最新の投稿を取り直す。$force=false なら前回から 6 時間たっていない時は何もしない。
 * 戻り値：['ok' => bool, 'count' => 件数, 'error' => 文]。失敗しても前回のファイルはそのまま残す。
 */
function instagram_fetch(bool $force = false): array
{
    $token = (string)setting('instagram_token', '');
    if ($token === '') {
        return ['ok' => false, 'error' => 'トークンが入っていません。'];
    }
    $last = (int)setting('instagram_fetched_at', '0');
    if (!$force && time() - $last < IG_INTERVAL) {
        return ['ok' => true, 'count' => null, 'skipped' => true];
    }

    // 長期トークンは期限の 10 日前を過ぎたら延長する（発行から 24 時間以上たったものだけ延長できる）
    $expires = (int)setting('instagram_token_expires', '0');
    if ($expires > 0 && $expires - time() < IG_REFRESH_BEFORE) {
        $r = ig_get(IG_API . '/refresh_access_token?grant_type=ig_refresh_token&access_token=' . rawurlencode($token));
        if ($r['ok'] && !empty($r['data']['access_token'])) {
            $token = (string)$r['data']['access_token'];
            set_setting('instagram_token', $token);
            set_setting('instagram_token_expires', (string)(time() + (int)($r['data']['expires_in'] ?? IG_TOKEN_DAYS * 86400)));
        }
    }

    $r = ig_get(IG_API . '/me/media?fields=id,caption,media_type,media_url,thumbnail_url,permalink,timestamp&limit=12&access_token=' . rawurlencode($token));
    set_setting('instagram_fetched_at', (string)time());
    if (!$r['ok']) {
        set_setting('instagram_last_error', $r['error']);
        return $r;
    }
    $dir = STORAGE_DIR . '/cache';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $list = [];
    foreach (($r['data']['data'] ?? []) as $m) {
        if (!is_array($m) || count($list) >= IG_SHOW + 2) {
            continue;
        }
        $local = ig_save_image($m);
        if ($local !== null) {
            $list[] = $m + ['local' => $local];
        }
    }
    if (!$list && ($r['data']['data'] ?? [])) {
        // 写真を1枚も保存できなかった時は、前回の分をそのまま残す
        set_setting('instagram_last_error', 'Instagram の写真をサーバーに保存できませんでした。少し時間をおいて、もう一度お試しください。');
        return ['ok' => false, 'error' => 'Instagram の写真をサーバーに保存できませんでした。'];
    }
    ig_clean_images(array_column($list, 'local'));
    $data = ['fetched_at' => date('c'), 'data' => $list];
    file_put_contents($dir . '/instagram.json.tmp', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    rename($dir . '/instagram.json.tmp', $dir . '/instagram.json');
    set_setting('instagram_last_error', '');
    return ['ok' => true, 'count' => count($data['data'])];
}

/** 投稿の写真（動画は表紙）を正方形 600px の JPG と WebP にしてサーバーに保存する。戻り値は public からの道筋。失敗は null。 */
function ig_save_image(array $m): ?string
{
    $id = preg_replace('/[^0-9A-Za-z_]/', '', (string)($m['id'] ?? ''));
    $src = ($m['media_type'] ?? '') === 'VIDEO' ? (string)($m['thumbnail_url'] ?? '') : (string)($m['media_url'] ?? '');
    if ($id === '' || !str_starts_with($src, 'https://')) {
        return null;
    }
    $rel = IG_MEDIA_DIR . '/' . $id . '.jpg';
    $abs = PUBLIC_DIR . '/' . $rel;
    if (is_file($abs) && filesize($abs) > 0) {
        return $rel; // 同じ投稿は一度保存すれば十分
    }
    $ch = curl_init($src);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 3]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false || $code !== 200 || strlen((string)$body) < 1000 || strlen((string)$body) > 20 * 1024 * 1024) {
        error_log('Instagram 写真の取得失敗 ' . $code . ': ' . $id);
        return null;
    }
    $dir = dirname($abs);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        error_log('Instagram 写真の保存先を作れません: ' . $dir);
        return null;
    }
    $tmp = $dir . '/.tmp-' . $id;
    file_put_contents($tmp, $body);
    $im = load_image($tmp);
    @unlink($tmp);
    if (!$im) {
        error_log('Instagram 写真を読めません: ' . $id);
        return null;
    }
    // 真ん中を正方形に切り抜く（Instagram の一覧と同じ見え方）
    $w = imagesx($im);
    $h = imagesy($im);
    $side = min($w, $h);
    $out = imagecreatetruecolor(IG_SIZE, IG_SIZE);
    imagecopyresampled($out, $im, 0, 0, intdiv($w - $side, 2), intdiv($h - $side, 2), IG_SIZE, IG_SIZE, $side, $side);
    imagejpeg($out, $abs . '.tmp', 82);
    rename($abs . '.tmp', $abs);
    if (function_exists('imagewebp')) {
        imagewebp($out, preg_replace('/\.jpg$/', '.webp', $abs), 80);
    }
    return $rel;
}

/** 今の一覧に無くなった古い写真を消す（ずっと増え続けないように） */
function ig_clean_images(array $keep): void
{
    $dir = PUBLIC_DIR . '/' . IG_MEDIA_DIR;
    if (!$keep || !is_dir($dir)) {
        return;
    }
    $keepBase = array_map(fn($p) => preg_replace('/\.jpg$/', '', basename($p)), $keep);
    foreach (glob($dir . '/*.{jpg,webp}', GLOB_BRACE) ?: [] as $f) {
        if (!in_array(preg_replace('/\.(jpg|webp)$/', '', basename($f)), $keepBase, true)) {
            @unlink($f);
        }
    }
}

/**
 * ページが開かれた時に呼ぶ。トークンが入っていて、前回から 6 時間たっていたら、
 * ページを返し終わってから最新の投稿を読み込む（見ている人を待たせない。サーバーの特別な設定は要らない）。
 */
function instagram_tick(): void
{
    try {
        if ((string)setting_safe('instagram_token') === '') {
            return;
        }
        $last = (int)setting_safe('instagram_fetched_at');
        // 写真をまだ自分のサーバーに保存していない古い形のファイルなら、すぐ読み込み直す
        if (time() - $last < IG_INTERVAL && !instagram_cache_is_old_format()) {
            return;
        }
    } catch (Throwable $e) {
        return;
    }
    // 同時に開かれたページと重ならないよう、印のファイルで1回だけにする
    $flag = STORAGE_DIR . '/cache/instagram-tick';
    if (!is_dir(dirname($flag))) {
        @mkdir(dirname($flag), 0775, true);
    }
    if (is_file($flag) && filemtime($flag) > time() - 600) {
        return;
    }
    @touch($flag);
    register_shutdown_function(function (): void {
        if (function_exists('litespeed_finish_request')) {
            litespeed_finish_request();
        } elseif (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        try {
            instagram_fetch(true);
        } catch (Throwable $e) {
            error_log('Instagram 自動読み込み: ' . $e->getMessage());
        }
    });
}

/** 保存してある一覧が、写真をサーバーに置く前の古い形か */
function instagram_cache_is_old_format(): bool
{
    $file = STORAGE_DIR . '/cache/instagram.json';
    if (!is_file($file)) {
        return true;
    }
    $json = json_decode((string)file_get_contents($file), true);
    foreach (($json['data'] ?? []) as $m) {
        if (empty($m['local'])) {
            return true;
        }
    }
    return false;
}
