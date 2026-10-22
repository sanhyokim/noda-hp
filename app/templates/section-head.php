<?php
/** @var string $label 英字ラベル */
/** @var string $title H2（「|」の位置で改行できる） */
/** @var ?string $id H2 の id */
/** @var ?string $lead 説明文 */
/** @var ?array $link [文字, URL] 右端のリンク */
/** @var ?string $icon 英字ラベルの右に添える印（'instagram' のみ。ヘッダーと同じ墨色の形） */
// 大きな英字ラベルを薄く敷き、その上に日本語の見出しを重ねる（Boss「よくある型を改善」2026-10-22）。
?>
<div class="head">
  <p class="head__en" aria-hidden="true"><?= e($label) ?><?php if (($icon ?? '') === 'instagram'): ?><svg class="head__icon" viewBox="0 0 30 30" focusable="false"><rect width="30" height="30" rx="7" fill="currentColor"/><rect x="7.5" y="7.5" width="15" height="15" rx="4.2" fill="none" stroke="#fff" stroke-width="1.9"/><circle cx="15" cy="15" r="3.6" fill="none" stroke="#fff" stroke-width="1.9"/><circle cx="19.4" cy="10.6" r="1.1" fill="#fff"/></svg><?php endif; ?></p>
  <div class="head__bar">
    <h2 class="h2 kw"<?= !empty($id) ? ' id="' . e($id) . '"' : '' ?>><?= str_replace('|', '<wbr>', e($title)) ?></h2>
    <?php if (!empty($link)): ?>
      <a class="more head__link" href="<?= e($link[1]) ?>"<?= str_starts_with($link[1], 'http') ? ' target="_blank" rel="noopener"' : '' ?>><?= e($link[0]) ?><?= icon('arrow', 16) ?></a>
    <?php endif; ?>
  </div>
  <?php if (!empty($lead)): ?><p class="head__lead"><?= e($lead) ?></p><?php endif; ?>
</div>
