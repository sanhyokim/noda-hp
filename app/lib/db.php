<?php
declare(strict_types=1);

// データベース接続。設定が空なら null を返し、呼び出し側はダミーデータを使う。
function db(): ?PDO
{
    static $pdo = false;
    if ($pdo !== false) {
        return $pdo;
    }
    $c = config('db', []);
    if (empty($c['name'])) {
        return $pdo = null;
    }
    try {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $c['host'] ?? 'localhost', $c['name'], $c['charset'] ?? 'utf8mb4');
        $pdo = new PDO($dsn, $c['user'] ?? '', $c['pass'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        // 記録の日時（受信日時・会話の日時など）を、サーバーの設定によらず日本時間にそろえる（PHP 側の日時と比べるため）
        $pdo->exec("SET time_zone = '+09:00'");
    } catch (PDOException $ex) {
        error_log('DB接続失敗: ' . $ex->getMessage());
        $pdo = null;
    }
    return $pdo;
}

// settings 表の値（Instagram のトークンなど）
function setting(string $key, ?string $default = null): ?string
{
    $st = db()->prepare('SELECT value FROM settings WHERE `key` = ?');
    $st->execute([$key]);
    $v = $st->fetchColumn();
    return $v === false ? $default : $v;
}

function set_setting(string $key, ?string $value): void
{
    db()->prepare('INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)')->execute([$key, $value]);
}

/**
 * 置いた後のデータの手直し（migration/fixes/NNN-名前.sql）を、まだのものだけ1回ずつ流す。
 * 管理画面を開いた時に呼ぶ。済んだファイル名は settings の fixes_done に残す。
 */
function apply_data_fixes(): void
{
    $files = glob(ROOT_DIR . '/migration/fixes/*.sql') ?: [];
    if (!$files) {
        return;
    }
    $done = json_decode((string)setting('fixes_done', '[]'), true) ?: [];
    $pdo = db();
    foreach ($files as $f) {
        $name = basename($f);
        if (in_array($name, $done, true)) {
            continue;
        }
        $sql = preg_replace('/^--.*$/m', '', (string)file_get_contents($f));
        $pdo->beginTransaction();
        try {
            foreach (array_filter(array_map('trim', explode(";\n", $sql . "\n"))) as $q) {
                $pdo->exec(rtrim($q, ';'));
            }
            // 表の形を変える文（ALTER TABLE など）はその場で確定され、まとめの取り消しが効かなくなるため、残っている時だけ確定・取り消しする
            if ($pdo->inTransaction()) {
                $pdo->commit();
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('データの手直しに失敗: ' . $name . ' ' . $e->getMessage());
            return;
        }
        $done[] = $name;
        set_setting('fixes_done', json_encode($done));
    }
}
