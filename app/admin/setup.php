<?php
declare(strict_types=1);

// 最初の設定（指示書 5-1）：
//   1. 設定ファイル（app/config.php）がまだ無ければ、データベースの情報を聞いて作る
//   2. テーブルを作り、施工事例などのデータ（migration/seed.sql）があれば入れる
//   3. 最初の管理者を登録する
// 終わるとこのファイルの名前を setup.php.done に変えて、二度と使えないようにする。

admin_session_start();

if (!is_file(APP_DIR . '/config.php')) {
    $errors = [];
    $in = ['host' => 'localhost', 'name' => '', 'user' => '', 'pass' => '', 'trial' => base_url() !== '/'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        foreach (['host', 'name', 'user', 'pass'] as $k) {
            $in[$k] = trim((string)($_POST[$k] ?? ''));
        }
        $in['trial'] = !empty($_POST['trial']);
        // 他人が先にこの画面を開いて、自分のデータベースにつながせることのないよう、このサーバーの中のデータベースだけ受け付ける
        if (!preg_match('/^(localhost|127\.0\.0\.1|[a-z0-9.-]+\.coreserver\.jp)$/i', $in['host'])) {
            $errors['host'] = 'データベースの場所は、サーバーの管理画面に書かれているもの（ふつうは localhost）を入れてください。';
        }
        foreach (['name' => 'データベース名', 'user' => 'ユーザー名'] as $k => $label) {
            if ($in[$k] === '') {
                $errors[$k] = $label . 'を入れてください。';
            }
        }
        if (!$errors) {
            try {
                new PDO('mysql:host=' . $in['host'] . ';dbname=' . $in['name'] . ';charset=utf8mb4', $in['user'], $in['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
            } catch (PDOException $e) {
                error_log('setup: DB 接続失敗 ' . $e->getMessage());
                $errors['conn'] = 'データベースにつながりませんでした。データベース名・ユーザー名・パスワードを、サーバーの管理画面と見比べてください。';
            }
        }
        if (!$errors) {
            $ok = file_put_contents(APP_DIR . '/config.php', setup_config_text($in), LOCK_EX);
            if ($ok === false) {
                $errors['conn'] = '設定ファイルを書き込めませんでした。app フォルダに書き込めるか（権限 755）を確かめてください。';
            } else {
                redirect(admin_url('setup.php'));
            }
        }
    }
    admin_render('setup-config', ['title' => '最初の設定', 'errors' => $errors, 'in' => $in]);
    exit;
}

/** config.sample.php を元に、入力された値を入れた app/config.php の中身を作る */
function setup_config_text(array $in): string
{
    $t = (string)file_get_contents(APP_DIR . '/config.sample.php');
    $t = str_replace("// 設定ファイルの見本。これをコピーして app/config.php を作り、中身を書き換えてください。", '// 設定ファイル（' . date('Y-m-d') . ' に最初の設定画面で作成）。見本は config.sample.php。', $t);
    $t = preg_replace("/'noindex' => (true|false),/", "'noindex' => " . ($in['trial'] ? 'true' : 'false') . ',', $t, 1);
    // データベースの欄だけを書き換える
    $t = preg_replace_callback("/'db' => \[(.*?)\],/s", function ($m) use ($in) {
        $block = $m[1];
        foreach (['host', 'name', 'user', 'pass'] as $k) {
            $block = preg_replace("/'" . $k . "' => '[^']*',/", "'" . $k . "' => " . var_export($in[$k], true) . ',', $block, 1);
        }
        return "'db' => [" . $block . '],';
    }, $t, 1);
    return $t;
}

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

// 施工事例などのデータ（制作側が書き出した migration/seed.sql）を、まだ空なら入れる
$seed = ROOT_DIR . '/migration/seed.sql';
if (is_file($seed) && (int)$pdo->query('SELECT COUNT(*) FROM cases')->fetchColumn() === 0) {
    @set_time_limit(300);
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $pdo->beginTransaction();
    try {
        $fp = fopen($seed, 'r');
        while (($line = fgets($fp)) !== false) {
            if (str_starts_with($line, 'INSERT INTO')) {
                $pdo->exec(rtrim($line, "; \r\n"));
            }
        }
        fclose($fp);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('setup: seed.sql の取り込み失敗 ' . $e->getMessage());
        http_response_code(500);
        admin_render('error', ['title' => 'データを入れられませんでした', 'message' => '施工事例などのデータを入れる途中で止まりました。このページを読み込み直すと、もう一度試します。続く時は制作側に連絡してください。']);
        exit;
    } finally {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
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
