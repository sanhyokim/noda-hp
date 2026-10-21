<?php
/** @var string $label 英字ラベル */
/** @var string $title H1（「|」の位置で改行できる） */
/** @var ?string $lead 導入文 */
/** @var ?string $image 付録Cのファイル名 */
/** @var ?array $ratio [幅, 高さ] */
$ratio ??= [16, 9];
?>
<header class="page-head wrap">
  <div class="page-head__text">
    <p class="label"><?= e($label) ?></p>
    <h1 class="h1 kw"><?= str_replace('|', '<wbr>', e($title)) ?></h1>
    <?php if (!empty($lead)): ?><p class="lead"><?= e($lead) ?></p><?php endif; ?>
  </div>
  <?php if (!empty($image)): ?>
    <div class="page-head__img"><?= picture($image, $ratio[0] * 100, $ratio[1] * 100, '', ['priority' => true]) ?></div>
  <?php endif; ?>
</header>
