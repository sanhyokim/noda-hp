<?php
/** @var array $case slug, title, location, biz[], work[], image, image_w, image_h */
$img = $case['image'] ?: 'media/cases/' . ($case['slug']) . '/thumb.jpg';
?>
<a class="card case" href="<?= e(url(rawurlencode($case['slug']) . '/')) ?>">
  <span class="case__img"><?= picture($img, (int)($case['image_w'] ?: 800), (int)($case['image_h'] ?: 533), '') ?></span>
  <span class="case__body">
    <span class="chips">
      <?php foreach (array_slice($case['biz'], 0, 1) as $c): ?><span class="chip"><?= e($c) ?></span><?php endforeach; ?>
      <?php foreach (array_slice($case['work'], 0, 1) as $c): ?><span class="chip"><?= e($c) ?></span><?php endforeach; ?>
    </span>
    <span class="case__title"><?= e($case['title']) ?></span>
    <?php if (!empty($case['location'])): ?>
      <span class="case__loc"><?= icon('pin', 14) ?><?= e($case['location']) ?></span>
    <?php endif; ?>
  </span>
</a>
