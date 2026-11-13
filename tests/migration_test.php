<?php
declare(strict_types=1);

// 移行の変換規則の試験（指示書 10章：カテゴリ変換と場所抽出、題名 20 件の期待値表）。
// 実行：php tests/migration_test.php

require dirname(__DIR__) . '/migration/lib.php';

// [題名, 旧カテゴリ, 期待する業態, 期待する工事の種類, 要確認, 題名から取れる場所]
$cases = [
    ['福岡市中央区【焼き鳥店】排煙ダクト工事', ['厨房フード', '焼き鳥屋'], ['yakitori'], ['kitchen-hood'], false, '福岡市中央区'],
    ['見蘭牛！お洒落な雰囲気で、美味しい焼肉を安全に。', ['無煙ロースター', '焼肉屋'], ['yakiniku'], ['roaster'], false, ''],
    ['角ダクトこだわり編', ['その他・換気工事他'], ['other'], ['ventilation'], true, ''],
    ['牛丼の吉野家　ダクト改修工事', ['厨房フード'], ['chain'], ['kitchen-hood', 'renewal'], false, ''],
    ['しゃぶ葉 熊本合志店様の新店ダクト工事', ['その他・換気工事他', '厨房フード', '大型店'], ['chain'], ['kitchen-hood', 'ventilation'], false, ''],
    ['飯塚市のホルモンセンター2階の改装工事', ['焼肉 上引きフード', '焼肉屋'], ['yakiniku'], ['upper-hood', 'renewal'], false, '飯塚市'],
    ['コインランドリー施工', ['その他・換気工事他'], ['other'], ['ventilation'], false, ''],
    ['福岡県-ホルモン・焼肉 まんぷく様（甘木駅前） ダクト工事', ['焼肉 上引きフード', '焼肉屋'], ['yakiniku'], ['upper-hood'], false, '福岡県'],
    ['ファン交換による換気能力改善工事', ['排煙・煙対策工事', '焼肉屋'], ['yakiniku'], ['smoke', 'maintenance', 'ventilation'], false, ''],
    ['北九州市八幡西区の人気焼肉店-クリアフード工事', ['焼肉 上引きフード', '焼肉屋'], ['yakiniku'], ['upper-hood'], false, '北九州市八幡西区'],
    ['広島市西区のスタイリッシュな焼肉店ー排煙ダクト工事', ['焼肉 上引きフード', '焼肉屋'], ['yakiniku'], ['upper-hood'], false, '広島市西区'],
    ['福岡市博多区春吉-博多水炊き「鳥千代様」厨房フード工事', ['厨房フード', '居酒屋'], ['izakaya'], ['kitchen-hood'], false, '福岡市博多区'],
    ['火災リスクを徹底排除！究極の防火装置「グリスセパレーター」設置工事', ['その他・換気工事他', '焼き鳥屋'], ['yakitori'], ['fire-safety'], false, ''],
    ['行橋市テイクアウト唐揚げ店！プレハブ改修で快適厨房が完成', ['テイクアウト店', '厨房フード'], ['takeout'], ['kitchen-hood', 'renewal'], false, '行橋市'],
    ['落語カフェの換気工事！新鮮な外気を取り入れ、快適な空間を創造しました。', ['その他・換気工事他', '喫茶店'], ['cafe'], ['ventilation'], false, ''],
    ['志免町に現れた話題店！『手作りハンバーグ きりん』様 施工', ['厨房フード', '洋食屋'], ['restaurant'], ['kitchen-hood'], false, '志免町'],
    ['焼肉店の命を守る！床下ダクト点検口と二重の防火対策', ['無煙ロースター', '焼肉屋'], ['yakiniku'], ['roaster', 'fire-safety'], false, ''],
    ['大手回転寿司チェーン様のダクト工事を担当しました', ['大型店', '居酒屋'], ['chain', 'izakaya'], ['ventilation'], true, ''],
    ['キッチンに大型アイランドフード設置', ['レストラン', '厨房フード'], ['restaurant'], ['kitchen-hood'], false, ''],
    ['福岡県糟屋郡の焼き鳥店様の排煙ダクト工事', ['厨房フード', '焼き鳥屋'], ['yakitori'], ['kitchen-hood'], false, '福岡県'],
];

$fail = 0;
foreach ($cases as $i => [$title, $old, $biz, $work, $review, $loc]) {
    $got = map_categories($old, $title);
    $gotLoc = location_from_title($title);
    $errs = [];
    if (array_diff($biz, $got['biz']) || array_diff($got['biz'], $biz)) {
        $errs[] = '業態 ' . implode(',', $got['biz']) . ' ≠ ' . implode(',', $biz);
    }
    if (array_diff($work, $got['work']) || array_diff($got['work'], $work)) {
        $errs[] = '工事 ' . implode(',', $got['work']) . ' ≠ ' . implode(',', $work);
    }
    if ($got['needs_review'] !== $review) {
        $errs[] = '要確認 ' . var_export($got['needs_review'], true);
    }
    if ($gotLoc !== $loc) {
        $errs[] = "場所「{$gotLoc}」≠「{$loc}」";
    }
    if ($errs) {
        $fail++;
        echo '✗ ' . ($i + 1) . " $title\n    " . implode("\n    ", $errs) . "\n";
    }
}

// 本文の整え方
$html = '<!-- wp:heading --><h5 class="x" style="color:red">施工内容</h5><!-- /wp:heading -->'
    . '<div class="g"><a href="https://duct-noda.com/wp-content/uploads/2026/08/a.jpg"><img src="https://duct-noda.com/wp-content/uploads/2026/08/a-300x225.jpg" data-full="https://duct-noda.com/wp-content/uploads/2026/08/a.jpg"></a></div>'
    . '<p class="p"><span style="x">本文</span><b>強調</b></p><p> </p><p>&nbsp;</p><h1>大見出し</h1><script>var a=1;</script>'
    . '<img src="https://duct-noda.com/wp-content/uploads/2026/08/a-1024x768.jpg">';
$expected = "<h3>施工内容</h3>\n<p>本文<strong>強調</strong></p>\n<h2>大見出し</h2>";
$got = sanitize_body($html);
if ($got !== $expected) {
    $fail++;
    echo "✗ 本文の整え方\n    得た値：" . str_replace("\n", '⏎', $got) . "\n";
}
$imgs = extract_images($html);
if (count($imgs) !== 1 || $imgs[0]['url'] !== 'https://duct-noda.com/wp-content/uploads/2026/08/a.jpg') {
    $fail++;
    echo "✗ 画像の取り出し（重複を1枚にまとめる）\n";
}

$total = count($cases) + 2;
echo $fail ? "失敗 $fail / $total\n" : "すべて成功（$total 件）\n";
exit($fail ? 1 : 0);
