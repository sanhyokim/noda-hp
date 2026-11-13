<?php
/** @var string $label 英字ラベル */
/** @var string $title H1（「|」の位置で改行できる） */
/** @var ?string $lead 導入文 */
/** @var ?string $image 付録Cのファイル名 */
/** @var ?array $ratio [幅, 高さ] */
/** @var ?string $focus 写真のどこを見せるか（object-position。例 '50% 20%'）。文字や印が端で切れる写真に使う */
// 左に見出し（大きな英字＋日本語）、右に写真。写真にかぶせは敷かない（Boss「いけてない」「少し暗い」2026-10-22）。導入文はその下に続ける。
$ratio ??= [16, 9];
?>
<header class="page-head wrap">
  <div class="page-head__box<?= empty($image) ? ' page-head__box--plain' : '' ?>">
    <div class="page-head__text">
      <p class="page-head__en" aria-hidden="true"><?= e($label) ?></p>
      <h1 class="page-head__title kw"><?= heading_html($title) ?></h1>
    </div>
    <?php if (!empty($image)): ?>
      <div class="page-head__img"<?= !empty($focus) ? ' style="--focus: ' . e($focus) . '"' : '' ?>><?= picture($image, $ratio[0] * 100, $ratio[1] * 100, '', ['priority' => true]) ?></div>
    <?php endif; ?>
  </div>
  <?php if (!empty($lead)): ?><p class="page-head__lead"><?= e($lead) ?></p><?php endif; ?>
</header>
