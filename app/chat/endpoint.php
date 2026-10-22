<?php
declare(strict_types=1);

// AI チャットの受け口：POST /api/chat/（答える）・POST /api/chat/handoff/（「この内容で問い合わせる」を押した印）
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

$messages = chat_clean_messages($in['messages'] ?? null);
if ($messages === null) {
    $send(['error' => 'bad', 'reply' => 'ご質問は800字以内でお願いします。長いご相談は、お問い合わせフォームからお送りください。'], 400);
}
if (($reason = chat_rate_check((string)($_SERVER['REMOTE_ADDR'] ?? ''))) !== null) {
    $send(['error' => 'limit', 'reply' => $reason], 429);
}

$r = chat_ask($messages);
$page = is_string($in['page'] ?? null) ? $in['page'] : '';
$isNew = chat_log($conv, array_merge($messages, [['role' => 'assistant', 'content' => $r['reply']]]), $page);
if (isset($r['usage'])) {
    chat_add_usage($r['usage'] + ['conversations' => $isNew ? 1 : 0]);
}
$send(['ok' => $r['ok'], 'reply' => $r['reply']], $r['ok'] ? 200 : 502);
