<?php
declare(strict_types=1);

// 画像の出力はすべてこの関数を通す。
// ファイルがあれば <picture>（WebP があれば優先）、無ければ同じ比率の灰色の枠にファイル名を出す。
// $src は 'logo.png' のような名前（assets/img/ を探す）か、'media/cases/1/a.jpg' のような公開フォルダからの道筋。
function picture(string $src, int $w, int $h, string $alt = '', array $opt = []): string
{
    $rel = str_contains($src, '/') ? ltrim($src, '/') : 'assets/img/' . $src;
    $file = PUBLIC_DIR . '/' . $rel;
    $class = trim('media ' . ($opt['class'] ?? ''));

    if (!is_file($file)) {
        $label = basename($src);
        $aria = $alt !== '' ? ' role="img" aria-label="' . e($alt) . '"' : ' aria-hidden="true"';
        return '<span class="' . e($class) . ' ph" style="aspect-ratio:' . $w . '/' . $h . '"' . $aria . '>'
            . '<span class="ph__name">' . e($label) . '</span></span>';
    }

    $v = '?v=' . filemtime($file);
    $img = '<img src="' . e(url($rel) . $v) . '" width="' . $w . '" height="' . $h . '" alt="' . e($alt) . '"';
    if (!empty($opt['priority'])) {
        $img .= ' fetchpriority="high" decoding="async"';
    } else {
        $img .= ' loading="lazy" decoding="async"';
    }
    $img .= '>';

    $webpRel = preg_replace('/\.(jpe?g|png)$/i', '.webp', $rel);
    $source = '';
    if ($webpRel !== $rel && is_file(PUBLIC_DIR . '/' . $webpRel)) {
        $source = '<source type="image/webp" srcset="' . e(url($webpRel) . '?v=' . filemtime(PUBLIC_DIR . '/' . $webpRel)) . '">';
    }
    return '<picture class="' . e($class) . '">' . $source . $img . '</picture>';
}

function image_exists(string $name): bool
{
    return is_file(PUBLIC_DIR . '/assets/img/' . $name);
}
