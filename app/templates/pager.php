<?php
/** @var int $page 今のページ */
/** @var int $pages ページ数 */
/** @var callable $url fn(int $n): string */
// 前へ／番号／次へ。番号は最初・最後・今の前後だけを出し、間は「…」にする
if ($pages <= 1) {
    return;
}
$nums = array_unique(array_filter([1, $page - 1, $page, $page + 1, $pages], fn($n) => $n >= 1 && $n <= $pages));
sort($nums);
?>
<nav class="pager" aria-label="ページ送り">
  <?php if ($page > 1): ?>
    <a class="pager__step pager__step--prev" href="<?= e($url($page - 1)) ?>" rel="prev"><?= icon('arrow', 16) ?>前へ</a>
  <?php else: ?>
    <span class="pager__step pager__step--prev is-off" aria-hidden="true"><?= icon('arrow', 16) ?>前へ</span>
  <?php endif; ?>
  <ol class="pager__nums">
    <?php $prev = 0; foreach ($nums as $n): ?>
      <?php if ($n - $prev > 1): ?><li class="pager__gap" aria-hidden="true">…</li><?php endif; ?>
      <li><?php if ($n === $page): ?><span class="pager__num" aria-current="page"><?= $n ?></span><?php else: ?><a class="pager__num" href="<?= e($url($n)) ?>" aria-label="<?= $n ?>ページ目"><?= $n ?></a><?php endif; ?></li>
    <?php $prev = $n; endforeach; ?>
  </ol>
  <?php if ($page < $pages): ?>
    <a class="pager__step pager__step--next" href="<?= e($url($page + 1)) ?>" rel="next">次へ<?= icon('arrow', 16) ?></a>
  <?php else: ?>
    <span class="pager__step pager__step--next is-off" aria-hidden="true">次へ<?= icon('arrow', 16) ?></span>
  <?php endif; ?>
</nav>
