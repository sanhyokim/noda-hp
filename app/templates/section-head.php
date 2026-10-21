<?php
/** @var string $label 英字ラベル */
/** @var string $title H2（「|」の位置で改行できる） */
/** @var ?string $id H2 の id */
/** @var ?string $lead 説明文 */
/** @var ?array $link [文字, URL] 右上のリンク */
?>
<div class="head">
  <div class="head__main">
    <p class="label"><?= e($label) ?></p>
    <h2 class="h2 kw"<?= !empty($id) ? ' id="' . e($id) . '"' : '' ?>><?= str_replace('|', '<wbr>', e($title)) ?></h2>
    <?php if (!empty($lead)): ?><p class="head__lead"><?= e($lead) ?></p><?php endif; ?>
  </div>
  <?php if (!empty($link)): ?>
    <a class="btn btn--outline btn--sm" href="<?= e($link[1]) ?>"><?= e($link[0]) ?><?= icon('arrow', 16) ?></a>
  <?php endif; ?>
</div>
