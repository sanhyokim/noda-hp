<?php
declare(strict_types=1);

// Instagram 欄の試験：写真をサーバーに保存して出すこと、保存できていない古い一覧では施工写真で埋めること。
// 実行：php tests/instagram_test.php [試しに保存する https の画像アドレス]（データベースは要らない）

$pub = sys_get_temp_dir() . '/ig-test-' . bin2hex(random_bytes(3));
mkdir($pub, 0775, true);
define('PUBLIC_DIR', $pub);
require dirname(__DIR__) . '/app/bootstrap.php';

$fail = 0;
$check = function (string $name, bool $ok) use (&$fail): void {
    echo ($ok ? '✓ ' : '✗ ') . $name . "\n";
    $fail += $ok ? 0 : 1;
};
$cache = STORAGE_DIR . '/cache/instagram.json';
$backup = is_file($cache) ? file_get_contents($cache) : null;
@mkdir(dirname($cache), 0775, true);

// 1. 写真のアドレスしか無い古い一覧（期限切れ）→ 施工写真6枚で埋める
file_put_contents($cache, json_encode(['data' => [['id' => '1', 'media_type' => 'IMAGE', 'media_url' => 'https://example.invalid/x.jpg', 'permalink' => 'https://www.instagram.com/p/x/']]]));
$items = instagram_items();
$check('古い一覧では6枚とも施工写真', count($items) === 6 && count(array_filter($items, fn($i) => $i['fallback'])) === 6);
$check('古い一覧は「読み込み直しが必要」と判定', instagram_cache_is_old_format());

// 2. 写真を保存する
$src = $argv[1] ?? 'https://duct-noda.com/assets/img/ig-fallback-1.jpg';
$rel = ig_save_image(['id' => '123_abc', 'media_type' => 'IMAGE', 'media_url' => $src]);
$check('写真をサーバーに保存できる', $rel === 'media/instagram/123_abc.jpg' && is_file("$pub/$rel"));
$size = $rel ? getimagesize("$pub/$rel") : [0, 0];
$check('正方形 600px', $size[0] === 600 && $size[1] === 600);
$check('WebP も作る', !function_exists('imagewebp') || is_file("$pub/media/instagram/123_abc.webp"));
$check('おかしな id は使わない', ig_save_image(['id' => '../../x', 'media_url' => 'http://a/b.jpg']) === null);

// 3. 保存した写真を使い、足りない分を施工写真で埋める
file_put_contents($cache, json_encode(['data' => [
    ['id' => '123_abc', 'local' => 'media/instagram/123_abc.jpg', 'permalink' => 'https://www.instagram.com/p/a/', 'caption' => 'テスト'],
    ['id' => '999', 'local' => 'media/instagram/999.jpg', 'permalink' => 'https://www.instagram.com/p/b/'], // ファイルが無い
]]));
$items = instagram_items();
$check('保存した写真を1枚目に使う', $items[0]['image'] === 'media/instagram/123_abc.jpg' && !$items[0]['fallback']);
$check('ファイルの無い分は飛ばし、6枚そろう', count($items) === 6 && $items[1]['fallback']);
$check('新しい形の一覧は読み込み直し不要と判定', !instagram_cache_is_old_format());
$check('表示に Instagram のアドレスを使わない', !str_contains(implode(' ', array_column($items, 'image')), 'cdninstagram'));

// 4. 古い写真の片付け
touch("$pub/media/instagram/old_1.jpg");
ig_clean_images(['media/instagram/123_abc.jpg']);
$check('一覧から外れた写真は消す', !is_file("$pub/media/instagram/old_1.jpg") && is_file("$pub/media/instagram/123_abc.jpg"));

// 後片付け
$backup === null ? @unlink($cache) : file_put_contents($cache, $backup);
array_map('unlink', glob("$pub/media/instagram/*") ?: []);
@rmdir("$pub/media/instagram");
@rmdir("$pub/media");
@rmdir($pub);
echo $fail ? "\n失敗 $fail 件\n" : "\nすべて通りました\n";
exit($fail ? 1 : 0);
