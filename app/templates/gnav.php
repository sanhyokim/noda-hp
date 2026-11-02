<?php
/** @var string $current */
// PC の上部メニュー。子項目はマウスを乗せるか、キーボードで親に移ると開く（JS なし）。
$isIn = fn(array $item): bool => $item['key'] === $current || in_array($current, $item['children'] ?? [], true);
?>
<nav class="gnav" aria-label="メインメニュー">
  <ul class="gnav__list">
    <?php foreach (site('gnav') as $item): ?>
      <?php $p = page($item['key']); $label = $item['label'] ?? ($p['nav'] ?? $p['name']); ?>
      <li class="gnav__item<?= !empty($item['children']) ? ' gnav__item--has' : '' ?>">
        <a class="gnav__link" href="<?= e(page_url($item['key'])) ?>"<?= $isIn($item) ? ' aria-current="' . ($item['key'] === $current ? 'page' : 'true') . '"' : '' ?>><?= e($label) ?><?php if (!empty($item['children'])): ?><?= icon('chev', 14, 'gnav__chev') ?><?php endif; ?></a>
        <?php if (!empty($item['children'])): ?>
          <ul class="gnav__sub">
            <?php foreach ($item['children'] as $child): ?>
              <?php $c = page($child); ?>
              <li><a href="<?= e(page_url($child)) ?>"<?= $child === $current ? ' aria-current="page"' : '' ?>><?= e($c['nav'] ?? $c['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>
