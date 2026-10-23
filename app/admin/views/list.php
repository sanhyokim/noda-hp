<?php
/** @var string $kind cases|news */
/** @var array $rows */
/** @var int $total */
/** @var string $q */
/** @var string $status '' | published | draft */
/** @var int $page */
/** @var int $pages */
$isCase = $kind === 'cases';
$label = $isCase ? '施工事例' : 'お知らせ';
$base = admin_url($kind . '/');
$qs = fn(array $over) => $base . (($p = http_build_query(array_filter($over + ['q' => $q, 'status' => $status, 'page' => null], fn($v) => $v !== '' && $v !== null && $v !== 1))) !== '' ? '?' . $p : '');
$tabs = ['' => 'すべて', 'published' => '公開中', 'draft' => '下書き'];
?>
<div class="bar">
  <h1 class="ttl"><?= e($label) ?></h1>
  <a class="abtn abtn--primary" href="<?= e($base . 'new/') ?>"><span class="abtn__plus" aria-hidden="true">＋</span>新しく書く</a>
</div>

<form class="search" method="get" action="<?= e($base) ?>" role="search">
<?php if ($status !== ''): ?>
  <input type="hidden" name="status" value="<?= e($status) ?>">
<?php endif; ?>
  <label class="u-sr" for="q">題名で探す</label>
  <input class="input" type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="題名で探す（例：焼肉）" enterkeyhint="search">
  <button class="abtn abtn--line" type="submit">探す</button>
</form>

<nav class="tabs" aria-label="表示の切り替え">
<?php foreach ($tabs as $k => $t): ?>
  <a class="tabs__item" href="<?= e($qs(['status' => $k])) ?>"<?= $status === $k ? ' aria-current="true"' : '' ?>><?= e($t) ?></a>
<?php endforeach; ?>
</nav>

<p class="count">
<?php if ($q !== ''): ?>「<?= e($q) ?>」を含む<?php endif; ?><?= e($tabs[$status] === 'すべて' ? '' : $tabs[$status] . 'の') ?><?= e($label) ?> <strong><?= number_format($total) ?></strong>件
<?php if ($q !== ''): ?><a class="count__clear" href="<?= e($qs(['q' => ''])) ?>">条件を消す</a><?php endif; ?>
</p>

<?php if (!$rows): ?>
<div class="empty">
  <p><?= $q !== '' ? '当てはまる' . e($label) . 'はありません。言葉を変えてお試しください。' : 'まだありません。「新しく書く」から追加できます。' ?></p>
</div>
<?php else: ?>
<ul class="rows">
<?php foreach ($rows as $r): ?>
  <li class="row">
    <a class="row__link" href="<?= e($base . $r['id'] . '/') ?>">
<?php if ($isCase): ?>
      <span class="row__img"><?php if (!empty($r['image'])): ?><img src="<?= e(url($r['image'])) ?>" alt="" loading="lazy" width="160" height="120"><?php else: ?><span class="row__noimg"><?= icon('image', 22) ?></span><?php endif; ?></span>
<?php endif; ?>
      <span class="row__body">
        <span class="row__meta">
<?php $scheduled = $r['status'] === 'published' && $r['published_at'] > today()->format('Y-m-d'); ?>
          <span class="badge badge--<?= $scheduled ? 'scheduled' : e($r['status']) ?>"><?= $scheduled ? '予約' : ($r['status'] === 'published' ? '公開中' : '下書き') ?></span>
<?php if (!empty($r['needs_review'])): ?>
          <span class="badge badge--review">要確認</span>
<?php endif; ?>
          <time datetime="<?= e($r['published_at']) ?>"><?= e(format_date($r['published_at'])) ?></time>
        </span>
        <span class="row__title"><?= e($r['title']) ?></span>
<?php if ($isCase): ?>
        <span class="row__cats"><?= e(implode('・', array_merge($r['biz'], $r['work'])) ?: 'カテゴリ未設定') ?></span>
<?php endif; ?>
      </span>
      <span class="row__go" aria-hidden="true"><?= icon('arrow', 18) ?></span>
    </a>
  </li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<?php if ($pages > 1):
    $nums = array_unique(array_filter([1, $page - 1, $page, $page + 1, $pages], fn($n) => $n >= 1 && $n <= $pages));
    sort($nums);
?>
<nav class="pager" aria-label="ページ送り">
<?php if ($page > 1): ?>
  <a class="pager__step" href="<?= e($qs(['page' => $page - 1])) ?>">前へ</a>
<?php endif; ?>
  <ol class="pager__nums">
<?php $prev = 0; foreach ($nums as $n): ?>
<?php if ($n - $prev > 1): ?>    <li class="pager__gap" aria-hidden="true">…</li>
<?php endif; ?>
    <li><?php if ($n === $page): ?><span class="pager__num" aria-current="page"><?= $n ?></span><?php else: ?><a class="pager__num" href="<?= e($qs(['page' => $n])) ?>" aria-label="<?= $n ?>ページ目"><?= $n ?></a><?php endif; ?></li>
<?php $prev = $n; endforeach; ?>
  </ol>
<?php if ($page < $pages): ?>
  <a class="pager__step" href="<?= e($qs(['page' => $page + 1])) ?>">次へ</a>
<?php endif; ?>
</nav>
<?php endif; ?>
