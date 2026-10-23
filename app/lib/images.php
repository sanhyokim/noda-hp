<?php
declare(strict_types=1);

// 施工事例の写真を作る（移行スクリプトと管理ページのアップロードで共通）。
// 表示用は長辺 1600px、一覧用は幅 800px。どちらも JPG と WebP を保存する（指示書 3-3-5・5-2-3）。

const MEDIA_LONG = 1600;
const MEDIA_THUMB_W = 800;

/** 画像を読み込み、写真の向き（EXIF）を直し、透明部分は白で埋める。 */
function load_image(string $path): ?GdImage
{
    $info = @getimagesize($path);
    if (!$info) {
        return null;
    }
    $im = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
        IMAGETYPE_PNG => @imagecreatefrompng($path),
        IMAGETYPE_WEBP => @imagecreatefromwebp($path),
        IMAGETYPE_GIF => @imagecreatefromgif($path),
        default => false,
    };
    if (!$im) {
        return null;
    }
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $exif = @exif_read_data($path);
        $im = match ((int)($exif['Orientation'] ?? 1)) {
            3 => imagerotate($im, 180, 0),
            6 => imagerotate($im, -90, 0),
            8 => imagerotate($im, 90, 0),
            default => $im,
        };
    }
    if (!imageistruecolor($im) || $info[2] !== IMAGETYPE_JPEG) {
        $bg = imagecreatetruecolor(imagesx($im), imagesy($im));
        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
        $im = $bg;
    }
    return $im;
}

/**
 * 読み込んだ画像から表示用・一覧用の4ファイルを作る。$dir は public からの道筋（例 media/cases/12）、$name は拡張子なしの名前。
 * 戻り値：['path', 'path_thumb', 'width', 'height']（path は public からの道筋）
 */
function write_case_media(GdImage $im, string $dir, string $name): array
{
    $abs = PUBLIC_DIR . '/' . $dir;
    if (!is_dir($abs)) {
        mkdir($abs, 0775, true);
    }
    $w = imagesx($im);
    $h = imagesy($im);
    $scale = min(1, MEDIA_LONG / max($w, $h));
    $big = $scale < 1 ? imagescale($im, (int)round($w * $scale), (int)round($h * $scale), IMG_BICUBIC) : $im;
    $tw = min(MEDIA_THUMB_W, $w);
    $thumb = $tw < $w ? imagescale($im, $tw, (int)round($h * $tw / $w), IMG_BICUBIC) : $im;
    imagejpeg($big, "$abs/$name.jpg", 82);
    imagewebp($big, "$abs/$name.webp", 80);
    imagejpeg($thumb, "$abs/$name-800.jpg", 80);
    imagewebp($thumb, "$abs/$name-800.webp", 78);
    return ['path' => "$dir/$name.jpg", 'path_thumb' => "$dir/$name-800.jpg", 'width' => imagesx($big), 'height' => imagesy($big)];
}

/** 写真1枚分の4ファイル（JPG・WebP、表示用・一覧用）を消す。$path は path（表示用の JPG）。 */
function delete_case_media(string $path): void
{
    $base = PUBLIC_DIR . '/' . preg_replace('/\.jpg$/', '', $path);
    foreach (['.jpg', '.webp', '-800.jpg', '-800.webp'] as $ext) {
        if (is_file($base . $ext)) {
            unlink($base . $ext);
        }
    }
}
