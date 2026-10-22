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

    [$jpgUrl, $webpUrl] = img_urls($src);
    // $opt['small'] = 同じ写真の幅800pxの版（施工事例の -800.jpg）。あればスマホではそちらを読む
    $srcset = $sizes = '';
    if (!empty($opt['small']) && is_file(PUBLIC_DIR . '/' . ltrim($opt['small'], '/'))) {
        [$sJpg, $sWebp] = img_urls($opt['small']);
        $sizes = ' sizes="' . e($opt['sizes'] ?? '100vw') . '"';
        $srcset = ' srcset="' . e($sJpg . ' 800w, ' . $jpgUrl . ' ' . $w . 'w') . '"';
        if ($webpUrl !== null && $sWebp !== null) {
            $webpUrl = $sWebp . ' 800w, ' . $webpUrl . ' ' . $w . 'w';
        }
    }
    $img = '<img src="' . e($jpgUrl) . '"' . $srcset . $sizes . ' width="' . $w . '" height="' . $h . '" alt="' . e($alt) . '"';
    if (!empty($opt['priority'])) {
        $img .= ' fetchpriority="high" decoding="async"';
    } else {
        $img .= ' loading="lazy" decoding="async"';
    }
    $img .= '>';

    $source = $webpUrl !== null ? '<source type="image/webp" srcset="' . e($webpUrl) . '"' . $sizes . '>' : '';
    return '<picture class="' . e($class) . '">' . $source . $img . '</picture>';
}

/** picture() と同じアドレス [元の写真, WebP（無ければ null）]。CSS の背景に同じ写真を使う時、二重に読み込まないように */
function img_urls(string $src): array
{
    $rel = str_contains($src, '/') ? ltrim($src, '/') : 'assets/img/' . $src;
    $file = PUBLIC_DIR . '/' . $rel;
    $jpg = url($rel) . (is_file($file) ? '?v=' . filemtime($file) : '');
    $webpRel = preg_replace('/\.(jpe?g|png)$/i', '.webp', $rel);
    $webp = $webpRel !== $rel && is_file(PUBLIC_DIR . '/' . $webpRel) ? url($webpRel) . '?v=' . filemtime(PUBLIC_DIR . '/' . $webpRel) : null;
    return [$jpg, $webp];
}

function image_exists(string $name): bool
{
    return is_file(PUBLIC_DIR . '/assets/img/' . $name);
}

// 写真が数秒ごとにふわっと切り替わる枠（Boss「写真が切り替わる動き」2026-10-22）。
// 1枚目だけ最初から見せ、2枚目以降は site.js が順に重ねる。動きを減らす設定の人には1枚目だけ。
function slides(array $images, int $w, int $h, string $alt = ''): string
{
    $out = '';
    foreach ($images as $i => $src) {
        $out .= '<span class="slides__item' . ($i === 0 ? ' is-on' : '') . '"' . ($i ? ' aria-hidden="true"' : '') . '>'
            . picture($src, $w, $h, $i === 0 ? $alt : '') . '</span>';
    }
    return '<span class="slides" data-slides>' . $out . '</span>';
}
