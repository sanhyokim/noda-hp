<?php
declare(strict_types=1);

// Instagram の最新投稿の取得（指示書 6 章）。トークンは settings の instagram_token。
// 結果は storage/cache/instagram.json に保存し、公開ページは instagram_items() でこのファイルだけを読む。

const IG_API = 'https://graph.instagram.com';
const IG_INTERVAL = 6 * 3600;          // 取得は 6 時間に 1 回まで
const IG_TOKEN_DAYS = 60;              // 長期トークンの有効期間
const IG_REFRESH_BEFORE = 10 * 86400;  // 残り 10 日以下で更新

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
    $data = ['fetched_at' => date('c'), 'data' => $r['data']['data'] ?? []];
    file_put_contents($dir . '/instagram.json.tmp', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    rename($dir . '/instagram.json.tmp', $dir . '/instagram.json');
    set_setting('instagram_last_error', '');
    return ['ok' => true, 'count' => count($data['data'])];
}
