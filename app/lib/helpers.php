<?php
declare(strict_types=1);

function config(string $key, mixed $default = null): mixed
{
    return $GLOBALS['config'][$key] ?? $default;
}

function site(string $key): mixed
{
    return $GLOBALS['site'][$key] ?? null;
}

function company(string $key): string
{
    return (string)($GLOBALS['site']['company'][$key] ?? '');
}

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** 見出し用：「|」を改行できる位置に、製品の型番（NC-02 など）は途中で折り返さない */
function heading_html(string $s): string
{
    $h = str_replace('|', '<wbr>', e($s));
    return preg_replace('/[A-Z]{2,}-\d+/', '<span class="nw">$0</span>', $h);
}

function base_url(): string
{
    static $base = null;
    if ($base === null) {
        $b = (string)config('base_url', 'auto');
        if ($b === 'auto') {
            // 置いた場所から決める（/new/index.php なら /new/）。設定ファイルを作る前の最初の設定画面でも正しく動くように
            $b = PHP_SAPI === 'cli-server' || PHP_SAPI === 'cli' ? '/' : str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/index.php')));
        }
        $base = '/' . trim($b, '/') . (trim($b, '/') === '' ? '' : '/');
    }
    return $base;
}

// サイト内のリンク。BASE_URL を付ける。url('concept/') → /new/concept/
function url(string $path = ''): string
{
    return base_url() . ltrim($path, '/');
}

// canonical・OGP 用の完全なアドレス
function abs_url(string $path = ''): string
{
    return rtrim((string)config('site_origin', ''), '/') . url($path);
}

function page(string $key): array
{
    return $GLOBALS['site']['pages'][$key] ?? [];
}

function page_url(string $key): string
{
    return url(page($key)['path'] ?? '');
}

// CSS・JS などの静的ファイル。更新日時を付けて、差し替え時に古いものが残らないようにする。
function asset(string $path): string
{
    $file = PUBLIC_DIR . '/assets/' . ltrim($path, '/');
    $v = is_file($file) ? '?v=' . filemtime($file) : '';
    return url('assets/' . ltrim($path, '/')) . $v;
}

function today(): DateTimeImmutable
{
    $tz = new DateTimeZone('Asia/Tokyo');
    $o = config('today_override');
    return $o ? new DateTimeImmutable((string)$o, $tz) : new DateTimeImmutable('now', $tz);
}

// 創業からの年数（西暦 − 2005）
function years_in_business(): int
{
    return (int)today()->format('Y') - (int)company('founded_year');
}

function tel_href(string $tel): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', $tel);
}

function format_date(string $date): string
{
    return date('Y.m.d', strtotime($date));
}

function render(string $template, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    require APP_DIR . '/templates/' . $template . '.php';
}

function icon(string $name, int $size = 20, string $class = ''): string
{
    static $paths = null;
    $paths ??= require APP_DIR . '/templates/icons.php';
    $d = $paths[$name] ?? '';
    $c = $class !== '' ? ' class="' . e($class) . '"' : '';
    return '<svg' . $c . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $d . '</svg>';
}

// LINE 公式ロゴ（ボス支給）。LINE のボタンだけに使う。
function line_icon(int $size = 20): string
{
    return '<img class="line-icon" src="' . e(asset('img/line-icon.png')) . '" width="' . $size . '" height="' . $size . '" alt="" decoding="async">';
}
