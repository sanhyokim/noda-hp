<?php
declare(strict_types=1);

// 最初の設定（指示書 5-1）：テーブルを作り、最初の管理者を登録する。
// 終わるとこのファイルの名前を setup.php.done に変えて、二度と使えないようにする。

admin_session_start();
$pdo = db();
if (!$pdo) {
    http_response_code(503);
    admin_render('error', ['title' => 'データベースにつながりません', 'message' => 'app/config.php のデータベースの設定（場所・名前・ユーザー・パスワード）を確認してから、このページを読み込み直してください。']);
    exit;
}

$finish = function (): void {
    @rename(__FILE__, __FILE__ . '.done');
};

// テーブル（何度流しても壊れない）
foreach (array_filter(array_map('trim', explode(';', preg_replace('/^--.*$/m', '', (string)file_get_contents(ROOT_DIR . '/migration/schema.sql'))))) as $sql) {
    $pdo->exec($sql);
}

// 管理者がもういるなら、ここでは何もしない
if ((int)$pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() > 0) {
    $finish();
    admin_render('error', ['title' => 'セットアップは完了しています', 'message' => '管理者はもう登録されています。ログイン画面からお入りください。', 'back' => [admin_url('login/'), 'ログイン画面へ']]);
    exit;
}

$errors = [];
$loginId = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $loginId = trim((string)($_POST['login_id'] ?? ''));
    $pw = (string)($_POST['password'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_.@-]{3,100}$/', $loginId)) {
        $errors['login_id'] = 'ID は半角の英数字（3文字以上）で決めてください。';
    }
    if (mb_strlen($pw) < 10) {
        $errors['password'] = 'パスワードは10文字以上にしてください。';
    } elseif ($pw !== (string)($_POST['again'] ?? '')) {
        $errors['again'] = '確認用のパスワードが一致しません。';
    }
    if (!$errors) {
        $pdo->prepare('INSERT INTO admin_users (login_id, password_hash) VALUES (?, ?)')->execute([$loginId, password_hash($pw, PASSWORD_BCRYPT)]);
        $finish();
        flash('ok', '管理者を登録しました。決めた ID とパスワードでログインしてください。');
        redirect(admin_url('login/'));
    }
}

admin_render('setup', ['title' => '最初の設定', 'errors' => $errors, 'loginId' => $loginId]);
