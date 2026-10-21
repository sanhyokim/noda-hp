<?php
/** @var array $items [[題, 説明], ...] */
/** @var ?int $cols 2〜4 */
/** @var ?bool $numbered 番号を付ける */
$cols ??= 3;
?>
<ul class="grid grid--<?= (int)$cols ?> points">
  <?php foreach ($items as $i => $it): ?>
    <li>
      <div class="point">
        <?php if (!empty($numbered)): ?><span class="point__no"><?= sprintf('%02d', $i + 1) ?></span><?php endif; ?>
        <h3 class="point__title kw"><?= str_replace('|', '<wbr>', e($it[0])) ?></h3>
        <?php if (!empty($it[1])): ?><p class="point__text"><?= e($it[1]) ?></p><?php endif; ?>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
