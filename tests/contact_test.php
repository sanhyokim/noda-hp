<?php
declare(strict_types=1);

// お問い合わせフォームの試験（指示書 10章）：必須の未入力・見えない項目・3秒規則・正常送信（4宛先＋自動返信、contact_logs に1行）・1分3回の上限。
// 実行：php tests/contact_test.php [サイトのアドレス]（既定 http://127.0.0.1:8080/）。
// app/config.php の mail.transport を 'log' にしておく（本当には送らず storage/logs/mail/ に保存される）。
// 試験で入れたお問い合わせは、終わったら消す。

require dirname(__DIR__) . '/app/bootstrap.php';

if ((config('mail')['transport'] ?? '') !== 'log') {
    exit("app/config.php の mail.transport を 'log' にしてから実行してください（本当のメールが送られるのを防ぐため）\n");
}
$url = rtrim($argv[1] ?? 'http://127.0.0.1:8080/', '/') . page_url('contact');
$pdo = db() ?? exit("データベースにつながりません\n");
$marker = 'test-' . bin2hex(random_bytes(4)) . '@example.com';
$mailDir = STORAGE_DIR . '/logs/mail';
@array_map('unlink', glob(STORAGE_DIR . '/contact/rate-*.json') ?: []);
$jar = tempnam(sys_get_temp_dir(), 'jar');

function req(string $url, ?array $post = null): array
{
    global $jar;
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_HEADER => true]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $res = (string)curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $size = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return [$code, substr($res, $size), substr($res, 0, $size)];
}
/** 画面を開いて、合言葉と表示時刻を受け取る */
function open_form(string $url): array
{
    [, $html] = req($url);
    preg_match('/name="csrf" value="([a-f0-9]*)"/', $html, $a);
    preg_match('/name="t" value="([0-9a-f.]*)"/', $html, $b);
    return ['csrf' => $a[1] ?? '', 't' => $b[1] ?? ''];
}
$valid = fn(array $tok) => $tok + [
    'name' => '試験 太郎', 'company' => '試験食堂', 'prefecture' => '福岡県', 'phone' => '０９２－５０４－５５０２',
    'email' => $GLOBALS['marker'], 'contact_method' => 'email', 'message' => "煙がこもります。\n試験の送信です。", 'agree' => '1', 'website' => '',
];
$rows = fn() => (int)$pdo->query('SELECT COUNT(*) FROM contact_logs WHERE email = ' . $pdo->quote($marker))->fetchColumn();
$mails = fn() => count(glob($mailDir . '/*.json') ?: []);

$fail = 0;
$check = function (string $name, bool $ok) use (&$fail): void {
    echo ($ok ? '✓ ' : '✗ ') . $name . "\n";
    $fail += $ok ? 0 : 1;
};

// 1. 必須の未入力
$tok = open_form($url);
sleep(3);
[$code, $html] = req($url, $tok + ['name' => '', 'prefecture' => '', 'phone' => '', 'email' => 'abc', 'message' => '', 'website' => '']);
$check('必須の未入力はエラー表示（422）', $code === 422 && str_contains($html, 'お名前を入力してください。') && str_contains($html, '都道府県を選んでください。')
    && str_contains($html, 'メールアドレスの形が正しくありません') && str_contains($html, 'プライバシーポリシーへの同意が必要です。') && str_contains($html, 'aria-invalid="true"'));
$check('エラーの時は入力が残る', str_contains($html, 'value="abc"'));

// 2. 見えない項目（ハニーポット）に入力がある
$tok = open_form($url);
sleep(3);
[$code] = req($url, ['website' => 'http://spam.example/'] + $valid($tok));
$check('見えない項目に入力があると拒否（400）', $code === 400 && $rows() === 0);

// 3. 表示から3秒未満
$tok = open_form($url);
[$code, $html] = req($url, $valid($tok));
$check('表示から3秒未満は拒否（400）', $code === 400 && str_contains($html, '送信が早すぎた') && $rows() === 0);

// 4. 表示時刻の書き換え
$tok = open_form($url);
sleep(3);
[$code] = req($url, ['t' => (string)(time() - 60) . '.00000000000000000000'] + $valid($tok));
$check('表示時刻を書き換えると拒否（400）', $code === 400 && $rows() === 0);

// 5. 合言葉（CSRF）が違う
$tok = open_form($url);
sleep(3);
[$code] = req($url, ['csrf' => str_repeat('0', 32)] + $valid($tok));
$check('合言葉が違うと拒否（400）', $code === 400 && $rows() === 0);

// 6. 正常送信（写真1枚を添付）
$jpg = tempnam(sys_get_temp_dir(), 'img');
$im = imagecreatetruecolor(40, 30);
imagejpeg($im, $jpg);
$before = $mails();
$tok = open_form($url);
sleep(3);
[$code, , $head] = req($url, $valid($tok) + ['attachments[0]' => new CURLFile($jpg, 'image/jpeg', '煙の写真.jpg')]);
$check('正常に送信すると受け付け画面へ（303）', $code === 303 && str_contains($head, '?sent=1'));
$check('contact_logs に1行', $rows() === 1);
$new = array_slice(glob($mailDir . '/*.json') ?: [], -2);
$m = array_map(fn($f) => json_decode((string)file_get_contents($f), true), $new);
$staff = array_values(array_filter($m, fn($x) => count($x['to']) === 4))[0] ?? null;
$reply = array_values(array_filter($m, fn($x) => $x['to'] === [$marker]))[0] ?? null;
$check('担当者4宛先あてのメール（件名・返信先・添付）', $mails() - $before === 2 && $staff
    && $staff['subject'] === '【HPお問い合わせ】試験 太郎様（福岡県）' && $staff['reply_to'] === $marker && $staff['files'] === ['煙の写真.jpg']
    && str_contains($staff['body'], '092-504-5502'));
$check('お客様への自動返信', $reply && $reply['subject'] === 'お問い合わせを受け付けました｜株式会社野田' && str_contains($reply['body'], '営業日 9:00〜17:00 に担当者よりご連絡します'));
$saved = json_decode((string)$pdo->query('SELECT attachment_path FROM contact_logs WHERE email = ' . $pdo->quote($marker))->fetchColumn(), true);
$check('添付は公開領域の外に保存', is_array($saved) && count($saved) === 1 && is_file(STORAGE_DIR . '/' . $saved[0]));

// 7. 写真に見せかけたファイル
$fake = tempnam(sys_get_temp_dir(), 'fake');
file_put_contents($fake, '<?php echo 1;');
$tok = open_form($url);
sleep(3);
[$code, $html] = req($url, $valid($tok) + ['attachments[0]' => new CURLFile($fake, 'image/jpeg', 'photo.jpg')]);
$check('写真に見せかけたファイルは拒否（422）', $code === 422 && str_contains($html, '添付できない種類') && $rows() === 1);

// 8. 同じ所から1分に3回まで（6. で1回、ここで2回は通り、4回目は拒否）
$codes = [];
for ($i = 0; $i < 3; $i++) {
    $tok = open_form($url);
    sleep(3);
    [$codes[]] = req($url, $valid($tok));
}
$check('1分に3回を超えると拒否', $codes === [303, 303, 400] && $rows() === 3);

// 後片付け
foreach ($pdo->query('SELECT attachment_path FROM contact_logs WHERE email = ' . $pdo->quote($marker))->fetchAll(PDO::FETCH_COLUMN) as $p) {
    foreach ((array)json_decode((string)$p, true) as $f) {
        @unlink(STORAGE_DIR . '/' . $f);
    }
}
$pdo->prepare('DELETE FROM contact_logs WHERE email = ?')->execute([$marker]);
@array_map('unlink', glob(STORAGE_DIR . '/contact/rate-*.json') ?: []);
foreach (glob($mailDir . '/*.json') ?: [] as $f) {
    if (str_contains((string)file_get_contents($f), $marker)) {
        unlink($f);
    }
}
@unlink($jpg);
@unlink($fake);
@unlink($jar);

echo $fail ? "失敗 {$fail} 件\n" : "すべて成功\n";
exit($fail ? 1 : 0);
