<?php
/** @var array $items 文字列の配列 */
/** @var ?string $icon 'alert' はお困りごとの一覧（番号付き・墨の線）、それ以外は印付きの箇条書き */
$icon ??= 'check';
?>
<?php if ($icon === 'alert'): ?>
<ol class="troubles">
  <?php foreach ($items as $i => $it): ?><li><span class="troubles__no" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span><span class="troubles__text"><?= e($it) ?></span></li><?php endforeach; ?>
</ol>
<?php else: ?>
<ul class="bullets">
  <?php foreach ($items as $it): ?><li><?= icon($icon, 20) ?><span><?= e($it) ?></span></li><?php endforeach; ?>
</ul>
<?php endif; ?>
