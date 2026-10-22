<?php
declare(strict_types=1);

// AI チャットの受け口：POST /api/chat/（答える）・POST /api/chat/handoff/（「この内容で問い合わせる」を押した印）・POST /api/chat/callback/（担当者から電話をもらう）・POST /api/chat/rate/（役に立ったか）
/** @var string $path */

// chat/lib.php は bootstrap で読み込み済み

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

$send = function (array $data, int $code = 200): never {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $send(['error' => 'method'], 405);
}
// 他のサイトから勝手に使われないように：同じサイトからの送信だけ受ける（独自の見出しは他サイトからは付けられない）
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (($_SERVER['HTTP_X_NODA_CHAT'] ?? '') !== '1' || ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST))) {
    $send(['error' => 'forbidden'], 403);
}
if (!chat_enabled() || !db()) {
    $send(['error' => 'off', 'reply' => 'ただいま AI 相談はお休みしています。お電話か、お問い合わせフォーム・LINE からご相談ください。'], 503);
}

$in = json_decode((string)file_get_contents('php://input', false, null, 0, 200000), true);
$conv = is_array($in) && is_string($in['conv'] ?? null) && preg_match('/^[a-f0-9]{32}$/', $in['conv']) ? $in['conv'] : null;
if (!$conv) {
    $send(['error' => 'bad'], 400);
}

if ($path === 'api/chat/handoff/') {
    db()->prepare('UPDATE chat_logs SET handed_off = 1 WHERE conv_key = ?')->execute([$conv]);
    $send(['ok' => true]);
}

if ($path === 'api/chat/rate/') {
    // 「この相談は役に立ちましたか？」の答え（1：はい、0：いいえ）
    $rating = ($in['rating'] ?? null) === 1 ? 1 : (($in['rating'] ?? null) === 0 ? 0 : null);
    if ($rating === null) {
        $send(['error' => 'bad'], 400);
    }
    try {
        db()->prepare('UPDATE chat_logs SET rating = ? WHERE conv_key = ?')->execute([$rating, $conv]);
    } catch (Throwable $e) {
        error_log('AI チャット 評価の保存に失敗（管理画面を開くと直ります）: ' . $e->getMessage());
    }
    $send(['ok' => true]);
}

if ($path === 'api/chat/callback/') {
    $r = chat_callback($conv, (array)($in['callback'] ?? []));
    $send($r, $r['ok'] ? 200 : ($r['errors'] ? 422 : 400));
}

$messages = chat_clean_messages($in['messages'] ?? null);
if ($messages === null) {
    $send(['error' => 'bad', 'reply' => 'ご質問は800字以内でお願いします。長いご相談は、お問い合わせフォームからお送りください。'], 400);
}
if (($reason = chat_rate_check((string)($_SERVER['REMOTE_ADDR'] ?? ''))) !== null) {
    $send(['error' => 'limit', 'reply' => $reason], 429);
}

// 画面が「少しずつ受け取れる」と言ってきた時は、答えができた分から1行ずつ送る（1行に1つの JSON：{"d":"足す文字"}、最後に {"done":true,...}）
$onText = null;
if (($in['stream'] ?? false) === true) {
    @ini_set('zlib.output_compression', '0');
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    ignore_user_abort(true); // 途中で窓を閉じられても、記録と費用の集計は最後まで行う
    set_time_limit(90);
    header('Content-Type: application/x-ndjson; charset=UTF-8');
    header('Cache-Control: no-store, no-transform');
    header('X-Accel-Buffering: no');                 // 間のサーバーに溜め込まず、すぐ流してもらう
    header('X-LiteSpeed-Cache-Control: no-cache');
    // 少し溜まってから送る仕組みでも最初の文字がすぐ届くように、空白の行を先に送る（画面では読み飛ばす）
    echo str_repeat(' ', 4096), "\n";
    flush();
    $shown = '';
    $onText = function (string $raw) use (&$shown): void {
        $v = chat_visible_part($raw);
        if (mb_strlen($v) > mb_strlen($shown) && str_starts_with($v, $shown)) {
            echo json_encode(['d' => mb_substr($v, mb_strlen($shown))], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
            flush();
            $shown = $v;
        }
    };
}

$r = chat_ask($messages, $onText);
$page = is_string($in['page'] ?? null) ? $in['page'] : '';
$isNew = chat_log($conv, array_merge($messages, [['role' => 'assistant', 'content' => $r['reply']]]), $page);
if (!empty($r['unanswered'])) {
    chat_mark_unanswered($conv, end($messages)['content']);
}
if (isset($r['usage'])) {
    chat_add_usage($r['usage'] + ['conversations' => $isNew ? 1 : 0]);
}
if ($onText) {
    // 最後に仕上げた答え全体を送る（画面はこれで置き換える）
    echo json_encode(['done' => true, 'ok' => $r['ok'], 'reply' => $r['reply']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
    exit;
}
$send(['ok' => $r['ok'], 'reply' => $r['reply']], $r['ok'] ? 200 : 502);
