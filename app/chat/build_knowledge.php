<?php
declare(strict_types=1);

// AI チャットが答えに使う「サイトの中身」を作り直す（ページの文章を変えた時に実行）。
// 実行：php app/chat/build_knowledge.php [サイトのアドレス]（既定 http://127.0.0.1:8080/）→ app/data/chat-knowledge.md
// 公開ページの本文だけを取り出す。サイトにない事実を AI が作らないよう、答えはこの文章と会社情報だけに基づかせる。

if (PHP_SAPI !== 'cli') {
    exit;
}
require dirname(__DIR__) . '/bootstrap.php';

$origin = rtrim($argv[1] ?? 'http://127.0.0.1:8080/', '/');
$skip = ['contact', 'news', 'privacypolicy', 'construction_examples'];

function page_text(string $html): string
{
    $doc = new DOMDocument();
    @$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
    $x = new DOMXPath($doc);
    $main = $x->query('//main')->item(0);
    if (!$main) {
        return '';
    }
    foreach (iterator_to_array($x->query('.//nav|.//form|.//script|.//style|.//svg|.//button|.//*[contains(@class,"u-sr")]|.//*[contains(@class,"crumbs")]|.//*[@aria-hidden="true"]', $main)) as $n) {
        $n->parentNode?->removeChild($n);
    }
    $out = [];
    foreach ($x->query('.//h1|.//h2|.//h3|.//h4|.//summary|.//p|.//li|.//dt|.//dd|.//th|.//td|.//figcaption', $main) as $n) {
        // 入れ子（li の中の p など）は内側だけを使う
        if ($x->query('.//p|.//li|.//dd|.//td', $n)->length && !in_array($n->nodeName, ['h1', 'h2', 'h3', 'h4'], true)) {
            continue;
        }
        $t = trim(preg_replace('/\s+/u', ' ', $n->textContent));
        if ($t === '' || mb_strlen($t) < 2) {
            continue;
        }
        $prefix = match ($n->nodeName) { 'h1' => '## ', 'h2' => '### ', 'h3', 'h4' => '#### ', 'summary' => 'Q. ', 'li', 'dd' => '- ', default => '' };
        $line = $prefix . $t;
        if (end($out) !== $line) {
            $out[] = $line;
        }
    }
    // 施工事例の新着・お知らせ・Instagram・毎ページ同じ相談の帯は除く（答えに不要で、長くなるだけ）
    $drop = ['お知らせ', '最新の施工事例', '私たちの日々の仕事', '関連する施工事例', 'まずはお気軽にご相談ください', 'まずはご相談ください。'];
    $keep = [];
    $skipping = false;
    foreach ($out as $line) {
        if (preg_match('/^(#+) (.*)$/u', $line, $m) && strlen($m[1]) <= 3) {
            $skipping = in_array($m[2], $drop, true);
        }
        if (!$skipping && !preg_match('/^[A-Z][A-Z &]+$/', $line)) { // 英字だけの飾り見出し（FAQ・CONTACT など）
            $keep[] = $line;
        }
    }
    return implode("\n", array_unique($keep));
}

$parts = [];
foreach (site('pages') as $key => $p) {
    if (in_array($key, $skip, true)) {
        continue;
    }
    $html = @file_get_contents($origin . base_url() . $p['path']);
    if ($html === false) {
        fwrite(STDERR, "読めません: {$p['path']}\n");
        exit(1);
    }
    $parts[] = "# ページ「{$p['name']}」 " . base_url() . $p['path'] . "\n" . page_text($html);
}

// 施工事例は件数と絞り込みのアドレスだけ（全件を入れると長くなりすぎるため）
$lines = [];
foreach (case_categories() as $axis => $cats) {
    $counts = case_counts($axis, null);
    foreach ($cats as $slug => $name) {
        $lines[] = '- ' . $name . '：' . ($counts[$slug] ?? 0) . '件 ' . page_url('construction_examples') . '?' . $axis . '=' . rawurlencode($slug);
    }
}
$total = case_list(null, null, 1)['total'];
$parts[] = "# 施工事例（全{$total}件） " . page_url('construction_examples') . "\n業態別・工事の種類別の件数と一覧のアドレス：\n" . implode("\n", $lines);

$file = APP_DIR . '/data/chat-knowledge.md';
file_put_contents($file, "<!-- app/chat/build_knowledge.php が " . date('Y-m-d') . " に作成。手で直さない -->\n" . implode("\n\n", $parts) . "\n");
echo $file . '：' . mb_strlen(file_get_contents($file)) . "文字\n";
