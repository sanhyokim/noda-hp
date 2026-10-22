<?php
declare(strict_types=1);

// AI 相談の答えを少しずつ見せる仕組みの試験：途中で見せた文字が後から消えたり変わったりしないこと、太字の印と「<要確認>」の書きかけを見せないこと、最後の答えと食い違わないこと。
// 実行：php tests/chat_test.php（データベースや AI にはつながない）

require dirname(__DIR__) . '/app/bootstrap.php';

$fail = 0;
$check = function (bool $ok, string $what) use (&$fail): void {
    echo ($ok ? 'OK  ' : 'NG  ') . $what . "\n";
    $fail += $ok ? 0 : 1;
};

$texts = [
    "ご相談ありがとうございます。\n・フード\n・給気口\nくわしくは /flow/ をご覧ください。",
    "その点は担当者に確認してからご案内します。\n<要確認>",
    "確認が必要です。＜要確認＞",
    "**ポイント**は2つです。\n## 給気\n・排気",
    "a<b は記号です。<br> も <要 も残ります。",
];
foreach ($texts as $n => $text) {
    // 1文字ずつ届いた場合
    $shown = '';
    $steady = true;
    $chars = mb_str_split($text);
    $raw = '';
    foreach ($chars as $c) {
        $raw .= $c;
        $v = chat_visible_part($raw);
        if (!str_starts_with($v, $shown)) {
            $steady = false;
        }
        $shown = $v;
        if (str_contains($v, '<要') && $n === 1 || str_contains($v, '**')) {
            $steady = false;
        }
    }
    $check($steady, "答え" . ($n + 1) . "：見せた文字が後から変わらない・印の書きかけを見せない");
    $fin = chat_finish($text, false, []);
    $check(str_starts_with(trim($shown), $fin['reply']) && trim($shown) === $fin['reply'], "答え" . ($n + 1) . "：最後に見せた文字と仕上げた答えが同じ");
}
$check(chat_finish("確認します。\n<要確認>", false, [])['unanswered'] === true, '「<要確認>」で答えられなかった質問として残す');
$check(chat_finish('', true, [])['ok'] === true && str_contains(chat_finish('', true, [])['reply'], 'お答えできません'), '断られた時の言葉');
$check(chat_finish('   ', false, [])['ok'] === false, '空の答えは失敗として扱う');

echo $fail ? "\n{$fail} 件の失敗\n" : "\nすべて OK\n";
exit($fail ? 1 : 0);
