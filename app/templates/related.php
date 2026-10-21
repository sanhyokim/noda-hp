<?php
/** @var array $names カテゴリ名 */
/** @var ?string $title */
/** @var ?string $label */
$rel = cases_in_categories($names, 3);
?>
<section class="section wrap" aria-labelledby="related-title">
  <?php render('section-head', ['label' => $label ?? 'WORKS', 'title' => $title ?? '関連する施工事例', 'id' => 'related-title', 'link' => ['施工事例をもっと見る', page_url('construction_examples')]]); ?>
  <?php if ($rel): ?>
    <ul class="grid grid--3 cases">
      <?php foreach ($rel as $case): ?><li><?php render('case-card', ['case' => $case]); ?></li><?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="empty">該当する事例は準備中です。</p>
  <?php endif; ?>
</section>
