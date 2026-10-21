<?php
render('page-head', ['label' => 'NEWS', 'title' => 'お知らせ', 'lead' => '株式会社野田からのお知らせと最新の施工事例をお届けします。']);
$feed = latest_feed(20);
?>
<section class="section wrap" aria-label="お知らせの一覧">
  <ul class="news__list">
    <?php foreach ($feed as $f): ?>
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
</section>
