<?php
declare(strict_types=1);

// お問い合わせフォーム（指示書 4-6）：受け付け・確かめ・保存・メール送信。
// 画面は app/pages/contact.php。index.php から contact_handle() を呼び、戻り値を $form として渡す。

const CONTACT_PREFS = ['北海道','青森県','岩手県','宮城県','秋田県','山形県','福島県','茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県','新潟県','富山県','石川県','福井県','山梨県','長野県','岐阜県','静岡県','愛知県','三重県','滋賀県','京都府','大阪府','兵庫県','奈良県','和歌山県','鳥取県','島根県','岡山県','広島県','山口県','徳島県','香川県','愛媛県','高知県','福岡県','佐賀県','長崎県','熊本県','大分県','宮崎県','鹿児島県','沖縄県'];
const CONTACT_METHODS = ['phone' => '電話', 'email' => 'メール'];
const CONTACT_FILE_MAX = 10 * 1024 * 1024;
const CONTACT_FILE_COUNT = 3;
// 拡張子 => 中身として認める種類
const CONTACT_FILE_TYPES = [
    'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'],
    'heic' => ['image/heic', 'image/heif', 'image/heic-sequence', 'application/octet-stream'],
    'heif' => ['image/heic', 'image/heif', 'application/octet-stream'],
    'pdf' => ['application/pdf'],
];

/** 画面に出す値を作る。送信（POST）の時は確かめて、問題なければ保存・送信してから送信済みの画面へ移る */
function contact_handle(): array
{
    $token = contact_token();
    $form = ['csrf' => $token, 't' => contact_stamp($token, time()), 'from' => ($_GET['from'] ?? '') === 'chat' ? 'chat' : ''];
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $sent = $_GET['sent'] ?? null;
        $form['done'] = $sent === '1';
        $form['failed'] = $sent === '0';
        return $form;
    }

    $in = contact_input($_POST);
    $form['old'] = $in;
    $form['from'] = ($_POST['from'] ?? '') === 'chat' ? 'chat' : '';

    // 迷惑投稿対策：合言葉（CSRF）・見えない項目・表示から3秒未満・同じ所から1分に3回まで
    $reject = null;
    if (!hash_equals($token, (string)($_POST['csrf'] ?? '')) || !isset($_COOKIE['noda_form'])) {
        $reject = '画面を開いてから時間がたったため、送信できませんでした。お手数ですが、もう一度「送信する」を押してください。';
    } elseif ((string)($_POST['website'] ?? '') !== '') {
        $reject = '送信できませんでした。お電話でご相談ください。';
    } elseif (($shown = contact_stamp_time($token, (string)($_POST['t'] ?? ''))) === null || time() - $shown < 3) {
        $reject = '送信が早すぎたため、受け付けられませんでした。内容をご確認のうえ、もう一度「送信する」を押してください。';
    }
    if ($reject === null) {
        $errors = contact_validate($in);
        $files = contact_files($_FILES['attachments'] ?? null, $errors);
        if ($errors) {
            http_response_code(422);
            $form['errors'] = $errors;
            return $form;
        }
        // 回数に数えるのは、受け付けられる内容の送信だけ（入力の直しは何度でもできる）
        if (!contact_rate_ok((string)($_SERVER['REMOTE_ADDR'] ?? ''))) {
            $reject = '短い時間に何度も送信されたため、受け付けられませんでした。1分ほどおいてから、もう一度お試しください。';
        }
    }
    if ($reject !== null) {
        http_response_code(400);
        $form['reject'] = $reject;
        return $form;
    }

    $saved = contact_save_files($files);
    $id = null;
    if ($pdo = db()) {
        $pdo->prepare('INSERT INTO contact_logs (name, company, prefecture, phone, email, contact_method, message, attachment_path, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$in['name'], $in['company'], $in['prefecture'], $in['phone'], $in['email'], $in['contact_method'],
                ($form['from'] === 'chat' ? "【AI相談から】\n" : '') . $in['message'],
                $saved ? json_encode(array_column($saved, 'path'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45)]);
        $id = (int)$pdo->lastInsertId();
    }

    $ok = contact_send_staff($in, $saved, $form['from'] === 'chat');
    if ($ok) {
        contact_send_reply($in); // 自動返信は届かなくても受け付けは成功とする
    } else {
        error_log('contact: staff mail failed (contact_logs id ' . ($id ?? '-') . ')');
    }
    header('Location: ' . page_url('contact') . '?sent=' . ($ok ? '1' : '0') . '#form', true, 303);
    exit;
}

/** フォームの合言葉。ブラウザのクッキーと画面の隠し項目に同じ値を置き、一致した時だけ受け付ける */
function contact_token(): string
{
    $t = (string)($_COOKIE['noda_form'] ?? '');
    if (!preg_match('/^[a-f0-9]{32}$/', $t)) {
        $t = bin2hex(random_bytes(16));
        $_COOKIE['noda_form'] = $t;
        if (!headers_sent()) {
            setcookie('noda_form', $t, ['expires' => time() + 86400 * 7, 'path' => base_url(), 'secure' => !empty($_SERVER['HTTPS']), 'httponly' => true, 'samesite' => 'Lax']);
        }
    }
    return $t;
}

/** 画面を表示した時刻（書き換えられないよう、合言葉で署名する） */
function contact_stamp(string $token, int $time): string
{
    return $time . '.' . substr(hash_hmac('sha256', (string)$time, $token), 0, 20);
}

function contact_stamp_time(string $token, string $stamp): ?int
{
    if (!preg_match('/^(\d{9,11})\.([a-f0-9]{20})$/', $stamp, $m)) {
        return null;
    }
    return hash_equals(contact_stamp($token, (int)$m[1]), $stamp) ? (int)$m[1] : null;
}

/** 同じ所（IP）から1分に3回まで */
function contact_rate_ok(string $ip): bool
{
    $dir = STORAGE_DIR . '/contact';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $file = $dir . '/rate-' . substr(hash('sha256', $ip), 0, 16) . '.json';
    $now = time();
    $fp = @fopen($file, 'c+');
    if (!$fp) {
        return true;
    }
    flock($fp, LOCK_EX);
    $times = array_filter((array)json_decode((string)stream_get_contents($fp), true), fn($t) => is_int($t) && $t > $now - 60);
    $ok = count($times) < 3;
    if ($ok) {
        $times[] = $now;
    }
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode(array_values($times)));
    flock($fp, LOCK_UN);
    fclose($fp);
    return $ok;
}

/** 入力を整える（前後の空白、全角の英数字は半角に、改行の統一） */
function contact_input(array $p): array
{
    $line = fn(string $k, int $max) => mb_substr(trim(preg_replace('/[\r\n\t\x00-\x1F\x7F]+/u', ' ', (string)($p[$k] ?? ''))), 0, $max);
    $method = (string)($p['contact_method'] ?? '');
    return [
        'name' => $line('name', 100),
        'company' => $line('company', 200),
        'prefecture' => $line('prefecture', 10),
        'phone' => mb_convert_kana($line('phone', 30), 'as'),
        'email' => mb_convert_kana($line('email', 255), 'as'),
        'contact_method' => isset(CONTACT_METHODS[$method]) ? $method : 'phone',
        'message' => mb_substr(trim(str_replace(["\r\n", "\r"], "\n", (string)($p['message'] ?? ''))), 0, 5000),
        'agree' => !empty($p['agree']),
    ];
}

function contact_validate(array $in): array
{
    $e = [];
    if ($in['name'] === '') {
        $e['name'] = 'お名前を入力してください。';
    }
    if (!in_array($in['prefecture'], CONTACT_PREFS, true)) {
        $e['prefecture'] = '都道府県を選んでください。';
    }
    $digits = preg_replace('/\D/', '', $in['phone']);
    if ($in['phone'] === '') {
        $e['phone'] = '電話番号を入力してください。';
    } elseif (!preg_match('/^[0-9+\-() ]+$/', $in['phone']) || strlen($digits) < 10 || strlen($digits) > 13) {
        $e['phone'] = '電話番号は、市外局番から数字で入力してください（例：092-504-5502）。';
    }
    if ($in['email'] === '') {
        $e['email'] = 'メールアドレスを入力してください。';
    } elseif (!filter_var($in['email'], FILTER_VALIDATE_EMAIL)) {
        $e['email'] = 'メールアドレスの形が正しくありません。もう一度ご確認ください。';
    }
    if ($in['message'] === '') {
        $e['message'] = 'ご相談内容を入力してください。';
    }
    if (!$in['agree']) {
        $e['agree'] = 'プライバシーポリシーへの同意が必要です。';
    }
    return $e;
}

/** 添付ファイルを確かめる。戻り値は [['tmp' => 一時ファイル, 'name' => 元の名前, 'ext' => 拡張子], ...] */
function contact_files(mixed $f, array &$errors): array
{
    if (!is_array($f) || !isset($f['name']) || !is_array($f['name'])) {
        return [];
    }
    $list = [];
    foreach ($f['name'] as $i => $name) {
        $err = (int)($f['error'][$i] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $name = mb_substr(basename(str_replace('\\', '/', (string)$name)), 0, 100);
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE || (int)($f['size'][$i] ?? 0) > CONTACT_FILE_MAX) {
            $errors['attachments'] = '「' . $name . '」は10MBを超えています。10MB以下のファイルにしてください。';
            continue;
        }
        if ($err !== UPLOAD_ERR_OK || !is_uploaded_file((string)$f['tmp_name'][$i])) {
            $errors['attachments'] = '「' . $name . '」を受け取れませんでした。もう一度選び直してください。';
            continue;
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string)$f['tmp_name'][$i]) ?: '';
        if (!isset(CONTACT_FILE_TYPES[$ext]) || !in_array($mime, CONTACT_FILE_TYPES[$ext], true) || ($mime === 'application/octet-stream' && !contact_is_heic((string)$f['tmp_name'][$i]))) {
            $errors['attachments'] = '「' . $name . '」は添付できない種類のファイルです。写真（jpg・png・heic）か PDF を選んでください。';
            continue;
        }
        $list[] = ['tmp' => (string)$f['tmp_name'][$i], 'name' => $name, 'ext' => $ext === 'jpeg' ? 'jpg' : $ext];
    }
    if (count($list) > CONTACT_FILE_COUNT) {
        $errors['attachments'] = '添付できるのは' . CONTACT_FILE_COUNT . 'ファイルまでです。';
    }
    return $list;
}

/** iPhone の写真（HEIC）は種類の判定ができないサーバーがあるため、ファイルの先頭で確かめる */
function contact_is_heic(string $file): bool
{
    $head = (string)@file_get_contents($file, false, null, 0, 32);
    return substr($head, 4, 4) === 'ftyp' && in_array(substr($head, 8, 4), ['heic', 'heix', 'hevc', 'heim', 'heis', 'mif1', 'msf1'], true);
}

/** 添付を公開領域の外（storage/contact/年月/）に保存する */
function contact_save_files(array $files): array
{
    if (!$files) {
        return [];
    }
    $rel = 'contact/' . date('Ym');
    $dir = STORAGE_DIR . '/' . $rel;
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $saved = [];
    foreach ($files as $f) {
        $dest = $dir . '/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $f['ext'];
        if (@move_uploaded_file($f['tmp'], $dest)) {
            $saved[] = ['file' => $dest, 'path' => $rel . '/' . basename($dest), 'name' => $f['name']];
        }
    }
    return $saved;
}

/** 入力内容の控え（担当者あてと自動返信の両方で使う） */
function contact_summary(array $in): string
{
    $rows = [
        'お名前' => $in['name'],
        '法人名・店舗名' => $in['company'] !== '' ? $in['company'] : '（なし）',
        '都道府県' => $in['prefecture'],
        '電話番号' => $in['phone'],
        'メールアドレス' => $in['email'],
        'ご希望の連絡方法' => CONTACT_METHODS[$in['contact_method']] ?? '',
    ];
    $out = '';
    foreach ($rows as $k => $v) {
        $out .= '■' . $k . "\n" . $v . "\n\n";
    }
    return $out . "■ご相談内容\n" . $in['message'] . "\n";
}

function contact_send_staff(array $in, array $files, bool $fromChat): bool
{
    $body = "ホームページのお問い合わせフォームから、次の内容が届きました。\n"
        . ($fromChat ? "（AI相談のあとに送られたお問い合わせです）\n" : '')
        . "\n受信日時：" . date('Y年n月j日 H:i') . "\n\n"
        . contact_summary($in)
        . ($files ? "\n■添付ファイル\n" . implode("\n", array_column($files, 'name')) . "\n（このメールに添付しています）\n" : '')
        . "\n――\nこのメールに返信すると、お客様（" . $in['email'] . "）あてに届きます。\n"
        . "管理画面でも確認できます：" . abs_url('admin/settings/contacts/') . "\n";
    $subject = '【HPお問い合わせ】' . $in['name'] . '様（' . $in['prefecture'] . '）';
    return contact_mail((array)config('mail')['to'], $subject, $body, $in['email'], $in['name'], $files);
}

function contact_send_reply(array $in): bool
{
    $c = fn(string $k) => company($k);
    $body = $in['name'] . " 様\n\n"
        . "このたびは" . $c('name') . "にお問い合わせいただき、ありがとうございます。\n"
        . "下記の内容で受け付けました。営業日 " . $c('hours') . " に担当者よりご連絡します。\n"
        . "（土日・祝日にいただいた場合は、翌営業日以降のご連絡になります）\n\n"
        . "――――― 受け付けた内容 ―――――\n\n"
        . contact_summary($in)
        . "\n――――――――――――――――\n\n"
        . "お急ぎの場合は、お電話でもご相談いただけます。\n"
        . "フリーダイヤル " . $c('tel_free') . "（受付 " . $c('hours') . "・" . $c('closed') . "）\n\n"
        . "このメールに返信していただくと、担当者に届きます。\n"
        . "お心当たりのない場合は、お手数ですがこのメールを削除してください。\n\n"
        . $c('name') . "\n"
        . "〒" . $c('hq_zip') . " " . $c('hq_address') . "\n"
        . abs_url() . "\n";
    $to = (array)config('mail')['to'];
    return contact_mail([$in['email']], 'お問い合わせを受け付けました｜' . $c('name'), $body, (string)($to[0] ?? ''), $c('name'), []);
}

/**
 * メールを送る。config の mail.transport で送り方を選ぶ：
 * 'mail'（サーバーの送信機能。コアサーバーの基本）/ 'smtp'（届かない時の予備）/ 'log'（試験用。送らずに storage/logs/mail/ に保存）
 */
function contact_mail(array $to, string $subject, string $body, string $replyTo, string $replyName, array $files): bool
{
    $cfg = (array)config('mail') + ['transport' => 'mail', 'from' => 'noreply@duct-noda.com', 'from_name' => '株式会社野田', 'smtp' => []];
    $to = array_values(array_filter($to, fn($a) => filter_var($a, FILTER_VALIDATE_EMAIL)));
    if (!$to) {
        return false;
    }
    if ($cfg['transport'] === 'log') {
        $dir = STORAGE_DIR . '/logs/mail';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $data = ['to' => $to, 'from' => $cfg['from'], 'reply_to' => $replyTo, 'subject' => $subject, 'body' => $body, 'files' => array_column($files, 'name')];
        return (bool)file_put_contents($dir . '/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    if (is_file(ROOT_DIR . '/vendor/autoload.php')) {
        require_once ROOT_DIR . '/vendor/autoload.php';
    }
    if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
        // 部品が無い時の予備：本文だけ送る（添付はサーバーに保存済みで、管理画面から分かる）
        $headers = "From: " . mb_encode_mimeheader($cfg['from_name']) . " <{$cfg['from']}>\r\nReply-To: " . str_replace(["\r", "\n"], '', $replyTo);
        return mb_send_mail(implode(', ', $to), $subject, $body . ($files ? "\n（添付ファイルはサーバーに保存しています）\n" : ''), $headers, '-f' . $cfg['from']);
    }

    $m = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $m->CharSet = 'UTF-8';
        $m->Encoding = 'base64';
        if ($cfg['transport'] === 'smtp') {
            $s = (array)$cfg['smtp'] + ['host' => '', 'port' => 587, 'user' => '', 'pass' => '', 'secure' => 'tls'];
            $m->isSMTP();
            $m->Host = (string)$s['host'];
            $m->Port = (int)$s['port'];
            $m->SMTPAuth = $s['user'] !== '';
            $m->Username = (string)$s['user'];
            $m->Password = (string)$s['pass'];
            $m->SMTPSecure = (string)$s['secure'];
            $m->Timeout = 20;
        } else {
            $m->isMail();
        }
        $m->setFrom($cfg['from'], $cfg['from_name']);
        $m->Sender = $cfg['from'];
        foreach ($to as $a) {
            $m->addAddress($a);
        }
        if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $m->addReplyTo($replyTo, str_replace(["\r", "\n"], '', $replyName));
        }
        $m->Subject = str_replace(["\r", "\n"], '', $subject);
        $m->Body = $body;
        foreach ($files as $f) {
            $type = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'heic' => 'image/heic', 'heif' => 'image/heif', 'pdf' => 'application/pdf'][strtolower(pathinfo($f['file'], PATHINFO_EXTENSION))] ?? '';
            $m->addAttachment($f['file'], $f['name'], 'base64', $type);
        }
        return $m->send();
    } catch (\Throwable $e) {
        error_log('contact mail: ' . $e->getMessage());
        return false;
    }
}
