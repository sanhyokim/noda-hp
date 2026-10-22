<?php
/** @var array $news find_news() の結果（router が用意） */
// お知らせの詳しいページ /news/<slug>/（4-5）：H1、日付、本文
$path = 'news/' . rawurlencode($news['slug']) . '/';
$meta = [
    'title' => $news['title'] . '｜お知らせ【株式会社野田】',
    'description' => mb_strimwidth(trim(preg_replace('/\s+/u', '', html_entity_decode(strip_tags($news['body_html']), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), 0, 240, '…'),
    'path' => $path,
    'og_type' => 'article',
    'trail' => [[page('news')['name'], page('news')['path']], [$news['title'], $path]],
];
?>
<article class="cd">
  <header class="cd__head wrap">
    <p class="cd__en">NEWS</p>
    <h1 class="cd__title kw"><?= e($news['title']) ?></h1>
    <p class="cd__date"><time datetime="<?= e($news['published_at']) ?>"><?= e(format_date($news['published_at'])) ?></time></p>
  </header>
  <div class="section wrap">
    <div class="prose cd__body"><?= $news['body_html'] ?></div>
    <p class="cd__back"><a class="btn btn--outline" href="<?= e(page_url('news')) ?>">お知らせの一覧へ</a></p>
  </div>
</article>
