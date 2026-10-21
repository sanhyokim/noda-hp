<?php
/** @var array $items 文字列の配列 */
/** @var ?string $icon */
$icon ??= 'check';
?>
<ul class="bullets">
  <?php foreach ($items as $it): ?><li><?= icon($icon, 20) ?><span><?= e($it) ?></span></li><?php endforeach; ?>
</ul>
