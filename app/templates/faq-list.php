<?php
/** @var array $items [[質問, 答え], ...] */
?>
<div class="faq">
  <?php foreach ($items as [$q, $a]): ?>
    <details class="faq__item">
      <summary><span class="faq__q" aria-hidden="true">Q</span><span class="faq__qt"><?= e($q) ?></span><span class="faq__toggle" aria-hidden="true"></span></summary>
      <div class="faq__a"><span class="faq__al" aria-hidden="true">A</span><p><?= e($a) ?></p></div>
    </details>
  <?php endforeach; ?>
</div>
