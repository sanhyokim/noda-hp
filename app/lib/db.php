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
