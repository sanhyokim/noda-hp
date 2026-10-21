<?php
/** @var array $items [[名前, パス], ...] */
$last = count($items) - 1;
?>
<nav class="crumbs wrap" aria-label="パンくずリスト">
  <ol>
    <?php foreach ($items as $i => [$name, $path]): ?>
      <li><?php if ($i < $last): ?><a href="<?= e(url($path)) ?>"><?= e($name) ?></a><?php else: ?><span aria-current="page"><?= e($name) ?></span><?php endif; ?></li>
    <?php endforeach; ?>
  </ol>
</nav>
