<?php
/** @var string $label 英字ラベル */
/** @var string $title H2（「|」の位置で改行できる） */
/** @var ?string $id H2 の id */
/** @var ?string $lead 説明文 */
/** @var ?array $link [文字, URL] 右端のリンク */
// 現行サイトと同じ「チェック印＋見出し＋青い下線」。英字ラベルは下線の右端に小さく置く。
?>
<div class="head">
  <div class="head__bar">
    <h2 class="h2 kw"<?= !empty($id) ? ' id="' . e($id) . '"' : '' ?>><?= icon('checkbox', 24, 'h2__mark') ?><span><?= str_replace('|', '<wbr>', e($title)) ?></span></h2>
    <?php if (!empty($link)): ?>
      <a class="more head__link" href="<?= e($link[1]) ?>"<?= str_starts_with($link[1], 'http') ? ' target="_blank" rel="noopener"' : '' ?>><?= e($link[0]) ?><?= icon('arrow', 16) ?></a>
    <?php else: ?>
      <span class="head__en" aria-hidden="true"><?= e($label) ?></span>
    <?php endif; ?>
  </div>
  <?php if (!empty($lead)): ?><p class="head__lead"><?= e($lead) ?></p><?php endif; ?>
</div>
