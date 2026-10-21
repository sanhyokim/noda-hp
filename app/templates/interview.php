<?php
/** お客様インタビュー（現行サイトの各サービスページにある「お客様インタビュー」をそのまま載せる） */
/** @var string $name 店名 */
/** @var string $image 店の写真 */
/** @var array $imageSize [幅, 高さ] */
/** @var array $info [見出し, 内容] の配列（所在地など） */
/** @var ?array $link [文字, URL] */
/** @var string $title インタビューの題 */
/** @var array $qa [質問, 答え] の配列 */
/** @var string $work 施工内容 */
/** @var array $photos [ファイル, 幅, 高さ] の配列 */
$link ??= null;
$photos ??= [];
?>
<article class="interview">
  <header class="interview__head">
    <div class="interview__store"><?= picture($image, $imageSize[0], $imageSize[1], $name . 'の外観') ?></div>
    <div class="interview__who">
      <p class="interview__name"><?= e($name) ?> 様</p>
      <dl class="interview__info">
        <?php foreach ($info as [$k, $v]): ?><div><dt><?= e($k) ?></dt><dd><?= e($v) ?></dd></div><?php endforeach; ?>
      </dl>
      <?php if ($link): ?><a class="more" href="<?= e($link[1]) ?>" target="_blank" rel="noopener"><?= e($link[0]) ?><?= icon('external', 16) ?></a><?php endif; ?>
    </div>
  </header>
  <h3 class="interview__title">店主様インタビュー「<?= e($title) ?>」</h3>
  <dl class="interview__qa">
    <?php foreach ($qa as [$q, $a]): ?>
      <div>
        <dt><span aria-hidden="true">Q</span><?= e($q) ?></dt>
        <dd><span aria-hidden="true">A</span><div><?php foreach ((array) $a as $p): ?><p><?= e($p) ?></p><?php endforeach; ?></div></dd>
      </div>
    <?php endforeach; ?>
  </dl>
  <div class="interview__work">
    <h4>施工内容</h4>
    <?php foreach ((array) $work as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
    <?php if ($photos): ?>
      <ul class="interview__photos">
        <?php foreach ($photos as [$f, $w, $h]): ?><li style="aspect-ratio: <?= (int) $w ?> / <?= (int) $h ?>; max-height: <?= (int) $h ?>px"><?= picture($f, $w, $h, $name . 'の施工写真') ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</article>
