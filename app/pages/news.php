<?php
// お知らせ一覧：施工事例とお知らせを混ぜて新しい順、20件ずつ（4-5）
$pageNo = max(1, (int)($_GET['page'] ?? 1));
$feed = feed_page($pageNo);
$pages = max(1, (int)ceil($feed['total'] / NEWS_PER_PAGE));
if ($pageNo > $pages) {
    http_response_code(404);
    $meta = ['title' => 'ページが見つかりません｜株式会社野田', 'description' => 'お探しのページが見つかりません。', 'path' => 'news/', 'trail' => [['お知らせ', 'news/']]];
    require APP_DIR . '/pages/404.php';
    return;
}
$newsUrl = fn(int $n): string => page_url('news') . ($n > 1 ? '?page=' . $n : '');
if ($pageNo > 1) {
    $meta['title'] = preg_replace('/^お知らせ/u', 'お知らせ（' . $pageNo . 'ページ目）', $meta['title']);
    $meta['path'] = 'news/?page=' . $pageNo;
}
$meta['head_links'] = array_filter([
    'prev' => $pageNo > 1 ? $newsUrl($pageNo - 1) : null,
    'next' => $pageNo < $pages ? $newsUrl($pageNo + 1) : null,
]);
render('page-head', ['label' => 'NEWS', 'title' => 'お知らせ', 'lead' => '株式会社野田からのお知らせと最新の施工事例をお届けします。']);
?>
<section class="section wrap" aria-label="お知らせの一覧">
  <ul class="news__list">
    <?php foreach ($feed['rows'] as $f): ?>
      <li>
        <a class="news__row" href="<?= e($f['url']) ?>">
          <time datetime="<?= e($f['published_at']) ?>"><?= e(format_date($f['published_at'])) ?></time>
          <span class="chip <?= $f['type'] === 'case' ? 'chip--solid' : 'chip--outline' ?>"><?= $f['type'] === 'case' ? '施工事例' : 'お知らせ' ?></span>
          <span class="news__title"><?= e($f['title']) ?></span>
          <?= icon('arrow', 18, 'news__arrow') ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php render('pager', ['page' => $pageNo, 'pages' => $pages, 'url' => $newsUrl]); ?>
</section>
