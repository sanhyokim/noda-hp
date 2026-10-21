<?php
render('page-head', [
    'label' => 'AFTER FOLLOW',
    'title' => 'ダクト工事だけでは|終わらない、|徹底した|アフターフォロー',
    'lead' => '工事後も同じ担当者が保守点検・修理・清掃まで対応。油汚れを放置すると吸い込み低下・異音・ダクト火災の原因になります。',
    'image' => 'after-rooftop.jpg', 'ratio' => [16, 9],
]);
$checks = ['開店以来ダクトを清掃していない', '煙の吸い込みが以前より悪い', 'ファンから「ガタガタ」と異音がする', '排気ダクトから油が染み出している', 'グリスフィルターを清掃していない'];
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'ONE STOP', 'title' => '工事後も同じ担当者が対応します', 'id' => 's1']); ?>
  <ol class="chain">
    <?php foreach (['打ち合わせ', '工事', 'フード取付', 'その後まで'] as $i => $t): ?>
      <li><span><?= sprintf('%02d', $i + 1) ?></span><?= e($t) ?></li>
    <?php endforeach; ?>
  </ol>
  <p class="prose">保守点検・修理・清掃をワンストップで。</p>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'CHECK', 'title' => 'ダクト火災危険度チェック', 'id' => 's2', 'lead' => '1つでも当てはまれば点検を。']); ?>
    <div class="split">
      <div>
        <ul class="checklist">
          <?php foreach ($checks as $c): ?><li><?= e($c) ?></li><?php endforeach; ?>
        </ul>
        <p class="checklist__note">居抜き物件で履歴が分からない場合は早めの点検を。</p>
      </div>
      <div class="split__img"><?= picture('after-grease.jpg', 900, 600, '') ?></div>
    </div>
  </div>
</section>

<section class="section wrap" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'DAMPER', 'title' => 'ダンパーの話', 'id' => 's3']); ?>
  <p class="prose">ダクト火災のとき、ヒューズが作動して空気の流れを止め延焼を防ぐ部品です。油汚れがたまると作動しないことがあります。</p>
</section>

<section class="section wrap" aria-labelledby="s4">
  <a class="feature" href="https://www.cleaning-duct.com/" target="_blank" rel="noopener">
    <span class="feature__img feature__img--banner"><?= picture('banner-cleaning.jpg', 1200, 600, '') ?></span>
    <span class="feature__body">
      <span class="label">CLEANING</span>
      <span class="feature__title" id="s4">定期清掃について</span>
      <span class="feature__text">ダクト清掃の専用サイトでご案内しています。</span>
      <span class="more">ダクト清掃のサイトを見る<?= icon('external', 16) ?></span>
    </span>
  </a>
</section>
