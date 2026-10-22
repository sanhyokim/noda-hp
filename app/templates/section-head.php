<?php
/** @var string $label 英字ラベル */
/** @var string $title H2（「|」の位置で改行できる） */
/** @var ?string $id H2 の id */
/** @var ?string $lead 説明文 */
/** @var ?array $link [文字, URL] 右端のリンク */
// 大きな英字ラベルを薄く敷き、その上に日本語の見出しを重ねる（Boss「よくある型を改善」2026-10-22）。
?>
<div class="head">
  <p class="head__en" aria-hidden="true"><?= e($label) ?></p>
  <div class="head__bar">
    <h2 class="h2 kw"<?= !empty($id) ? ' id="' . e($id) . '"' : '' ?>><?= str_replace('|', '<wbr>', e($title)) ?></h2>
    <?php if (!empty($link)): ?>
      <a class="more head__link" href="<?= e($link[1]) ?>"<?= str_starts_with($link[1], 'http') ? ' target="_blank" rel="noopener"' : '' ?>><?= e($link[0]) ?><?= icon('arrow', 16) ?></a>
    <?php endif; ?>
  </div>
  <?php if (!empty($lead)): ?><p class="head__lead"><?= e($lead) ?></p><?php endif; ?>
</div>
