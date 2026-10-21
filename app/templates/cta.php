<?php
/** @var ?string $text ページごとに本文を差し替える時だけ渡す */
$text ??= '現地調査・お見積りは無料です。「煙の写真」を送っていただくだけでも、おおよその対策をお答えできます。';
?>
<section class="section wrap" aria-labelledby="cta-title">
  <div class="cta">
    <div class="cta__text">
      <p class="label label--light">CONTACT</p>
      <h2 id="cta-title" class="cta__title kw">まずは<wbr>お気軽に<wbr>ご相談ください</h2>
      <p class="cta__lead"><?= e($text) ?></p>
    </div>
    <div class="cta__actions">
      <a class="btn btn--white btn--lg" href="<?= e(tel_href(company('tel_free'))) ?>"><?= icon('phone', 22) ?><span><small>受付 <?= e(company('hours')) ?></small><?= e(company('tel_free')) ?></span></a>
      <a class="btn btn--line btn--lg" href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener"><?= line_icon(26) ?>LINEで相談する</a>
    </div>
  </div>
</section>
