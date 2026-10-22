<?php
declare(strict_types=1);

// 管理ページの安全面の試験（指示書 10章）：ログイン失敗でのロック、合言葉（CSRF）のない送信の拒否、写真に見せかけたファイルの拒否。
// 実行：php tests/admin_test.php [サイトのアドレス]（既定 http://127.0.0.1:8080/）。サイトとデータベースが動いている状態で。
// 試験用の管理者を一時的に作り、終わったら消す。

require dirname(__DIR__) . '/app/bootstrap.php';

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8080/', '/') . base_url() . 'admin/';
$pdo = db() ?? exit("データベースにつながりません\n");
$login = 'test-' . bin2hex(random_bytes(4));
$pass = bin2hex(random_bytes(8));
$pdo->prepare('INSERT INTO admin_users (login_id, password_hash) VALUES (?, ?)')->execute([$login, password_hash($pass, PASSWORD_BCRYPT)]);
$jar = tempnam(sys_get_temp_dir(), 'jar');

function req(string $url, ?array $post = null, array $headers = []): array
{
    global $jar;
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_HTTPHEADER => $headers]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $body = (string)curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $body];
}
function csrf_of(string $html): string
{
    return preg_match('/name="csrf" value="([a-f0-9]+)"/', $html, $m) ? $m[1] : '';
}

$fail = 0;
$check = function (string $name, bool $ok) use (&$fail): void {
    echo ($ok ? '✓ ' : '✗ ') . $name . "\n";
    $fail += $ok ? 0 : 1;
};

try {
    // 1. 5回続けて間違えるとロックされ、正しいパスワードでも入れない
    for ($i = 1; $i <= 5; $i++) {
        [, $html] = req($base . 'login/');
        req($base . 'login/', ['csrf' => csrf_of($html), 'login_id' => $login, 'password' => 'wrong' . $i]);
    }
    [, $html] = req($base . 'login/');
    [$code, $html] = req($base . 'login/', ['csrf' => csrf_of($html), 'login_id' => $login, 'password' => $pass]);
    $check('5回の失敗でロックされ、正しいパスワードでも入れない', $code === 200 && str_contains($html, 'ロックしています'));
    $pdo->prepare('UPDATE admin_users SET locked_until = NULL, failed_count = 0 WHERE login_id = ?')->execute([$login]);

    [, $html] = req($base . 'login/');
    [$code] = req($base . 'login/', ['csrf' => csrf_of($html), 'login_id' => $login, 'password' => $pass]);
    $check('ロックを解くと正しいパスワードで入れる', $code === 303);

    // 2. 合言葉のない送信は拒否する
    [$code] = req($base . 'cases/new/', ['title' => '試験', 'published_at' => date('Y-m-d'), 'status' => 'draft']);
    $check('合言葉のない保存は拒否（400）', $code === 400);
    [$code] = req($base . 'cases/new/', ['csrf' => 'deadbeef', 'title' => '試験', 'published_at' => date('Y-m-d'), 'status' => 'draft']);
    $check('違う合言葉の保存も拒否（400）', $code === 400);

    // 3. 写真に見せかけたファイルは受け付けない
    [, $html] = req($base . 'cases/new/');
    preg_match('/data-token="([a-f0-9]+)"/', $html, $t);
    preg_match('/data-csrf="([a-f0-9]+)"/', $html, $c);
    $fake = tempnam(sys_get_temp_dir(), 'up');
    file_put_contents($fake, "\xFF\xD8\xFF\xE0<?php system(\$_GET['c']); ?>");
    [$code, $body] = req($base . 'upload/', ['token' => $t[1] ?? '', 'photo' => new CURLFile($fake, 'image/jpeg', 'photo.jpg')], ['X-CSRF-Token: ' . ($c[1] ?? ''), 'Accept: application/json']);
    $check('中身が写真でない .jpg は拒否（400）', $code === 400 && str_contains($body, 'error'));
    $php = tempnam(sys_get_temp_dir(), 'up');
    copy(PUBLIC_DIR . '/assets/img/logo.png', $php);
    [$code] = req($base . 'upload/', ['token' => $t[1] ?? '', 'photo' => new CURLFile($php, 'image/png', 'logo.php')], ['X-CSRF-Token: ' . ($c[1] ?? ''), 'Accept: application/json']);
    $check('拡張子が写真でないファイルは拒否（400）', $code === 400);
    @unlink($fake);
    @unlink($php);
} finally {
    $pdo->prepare('DELETE FROM admin_users WHERE login_id = ?')->execute([$login]);
    @unlink($jar);
}

echo $fail ? "失敗 $fail 件\n" : "すべて成功\n";
exit($fail ? 1 : 0);
