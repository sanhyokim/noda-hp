<?php
// /llms.txt（と同じ中身の /ai.txt）：AI に会社とページを案内する Markdown（自動生成）。
// 旧 WordPress ではプラグインが作っていたもの。ページの題名・説明と、公開中の施工事例から毎回作る。
header('Content-Type: text/plain; charset=UTF-8');
header('X-Robots-Tag: noindex');

$home = page('home');
$c = fn(string $k) => company($k);
$out = [];
$out[] = '# ' . $c('name') . '｜飲食店の換気扇・排煙ダクト工事（福岡発・全国対応）';
$out[] = '';
$out[] = '> ' . $home['description'];
$out[] = '';
$out[] = '## 会社の基本情報';
$out[] = '';
$out[] = '- 会社名：' . $c('name') . '（' . $c('name_en') . '）';
$out[] = '- 設立：' . $c('founded_year') . '年11月';
$out[] = '- 本社：〒' . $c('hq_zip') . ' ' . $c('hq_address');
$out[] = '- ' . $c('branch_name') . '：〒' . $c('branch_zip') . ' ' . $c('branch_address');
$out[] = '- 電話：' . $c('tel_free') . '（フリーダイヤル）／' . $c('tel') . '（受付 平日' . $c('hours') . '、' . $c('closed') . '）';
$out[] = '- お問い合わせ：' . abs_url(page('contact')['path']) . '（現地調査・お見積りは無料）';
$out[] = '- LINE：' . $c('line_url');
$out[] = '- Instagram：' . $c('instagram_url');
$out[] = '- 対応エリア：福岡・九州・中国地方を中心に全国';
$out[] = '';
$out[] = '## ページ';
$out[] = '';
foreach (site('pages') as $key => $p) {
    if (in_array($key, ['home', 'news'], true)) {
        continue;
    }
    $out[] = '- [' . $p['name'] . '](' . abs_url($p['path']) . '): ' . $p['description'];
}

if ($pdo = db()) {
    $rows = $pdo->query("SELECT slug, title, location, published_at FROM cases WHERE " . live_sql() . " ORDER BY published_at DESC, id DESC LIMIT 50")->fetchAll();
    if ($rows) {
        $out[] = '';
        $out[] = '## 施工事例（新しい順。ほかは ' . abs_url(page('construction_examples')['path']) . ' から業態・工事で絞り込めます）';
        $out[] = '';
        foreach ($rows as $r) {
            $place = trim((string)$r['location']);
            $out[] = '- [' . $r['title'] . '](' . abs_url(rawurlencode($r['slug']) . '/') . '): ' . substr($r['published_at'], 0, 10) . ($place !== '' ? '、' . $place : '');
        }
    }
}

echo implode("\n", $out), "\n";
