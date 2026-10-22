<?php
declare(strict_types=1);

// 手元のデータベースから migration/seed.sql を書き出す（指示書 段階7）。
// 本番・/new/ の最初の設定画面（/admin/setup.php）が、空のデータベースにこれを流し込む。
// 1行に INSERT 1件。setup.php は「INSERT INTO」で始まる行だけを読むので、値の中に改行を残さない（PDO::quote が \n に直す）。
// 実行：php tools/export-seed.php

require dirname(__DIR__) . '/app/bootstrap.php';

$pdo = db() ?? exit("データベースにつながりません\n");
// 外部キーの順（親から）。管理者・設定・お問い合わせ・チャットの記録は持っていかない
$tables = ['categories', 'cases', 'case_categories', 'case_images', 'slug_redirects', 'news'];

$out = ROOT_DIR . '/migration/seed.sql';
$fp = fopen($out . '.tmp', 'w');
fwrite($fp, '-- 株式会社野田 公式サイトの初期データ（' . date('Y-m-d H:i') . " に tools/export-seed.php で書き出し）\n");
$missing = 0;
foreach ($tables as $t) {
    $rows = $pdo->query("SELECT * FROM `$t` ORDER BY 1, 2")->fetchAll(PDO::FETCH_ASSOC);
    fwrite($fp, "-- $t: " . count($rows) . " 件\n");
    foreach ($rows as $r) {
        if ($t === 'case_images') {
            foreach (['path', 'path_thumb'] as $k) {
                if (!is_file(PUBLIC_DIR . '/' . $r[$k])) {
                    fwrite(STDERR, "写真が見つかりません: {$r[$k]}\n");
                    $missing++;
                }
            }
        }
        $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string)$v), $r);
        $line = "INSERT INTO `$t` (`" . implode('`, `', array_keys($r)) . '`) VALUES (' . implode(', ', $vals) . ");\n";
        if (substr_count($line, "\n") !== 1) {
            exit("改行を含む値があり、1行にできません（$t）\n");
        }
        fwrite($fp, $line);
    }
    echo "$t: " . count($rows) . " 件\n";
}
fclose($fp);
rename($out . '.tmp', $out);
echo $missing ? "写真が {$missing} 枚見つかりません\n" : "書き出しました: migration/seed.sql\n";
exit($missing ? 1 : 0);
