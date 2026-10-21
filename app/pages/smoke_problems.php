<?php
render('page-head', [
    'label' => 'SMOKE & ODOR',
    'title' => '店舗の煙・臭い|トラブル解決工事',
    'lead' => '客席に煙がこもる、近隣から臭いの苦情が来た。原因調査のうえ、消煙機（電気集塵機）の導入、ダクトの延長・位置変更、排気口の移設で根本から解決します。',
    'image' => 'svc-smoke.jpg', 'ratio' => [3, 2],
]);
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'TROUBLES', 'title' => 'よくある2つのトラブル', 'id' => 's1']); ?>
  <?php render('cards', ['cols' => 2, 'numbered' => true, 'items' => [
      ['客席の煙で|複数のお客様からクレーム', ''], ['店から出る臭いで|近隣とトラブル', ''],
  ]]); ?>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'SOLUTIONS', 'title' => '信用を取り戻す3つの解決法', 'id' => 's2']); ?>
    <ol class="numlist">
      <li><h3>まず謝罪と、改善内容・期限の説明</h3><p>関係先への説明資料づくりをお手伝いします。</p></li>
      <li><h3>消煙機で煙と油を浄化する</h3><p>電気集塵機・油煙除去ウォーターフィルター。油の粒を取り除き、床の滑り・衣服の汚れ・機器への付着も防ぎます。</p></li>
      <li><h3>ダクトの交換・延長・位置変更</h3><p>住宅のない方向へ排気口を移す、建物の最上階まで延長する。</p></li>
    </ol>
  </div>
</section>

<section class="section wrap" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'VOICE', 'title' => 'お客様の声', 'id' => 's3']); ?>
  <?php render('voice', ['name' => '焼鳥八朔様', 'meta' => '福岡市中央区・焼き鳥店', 'text' => '煙で近隣から苦情があり、行政から改善指導。住民説明会で仕様を説明し、消煙機を導入、排気の立ち上げ工事を実施。行政の目視判定と臭気判定をクリアし、苦情はゼロに。']); ?>
</section>

<section class="section wrap" aria-labelledby="s4">
  <a class="feature" href="https://shoenki.duct-noda.com/" target="_blank" rel="noopener">
    <span class="feature__img"><?= picture('product-shoenki.jpg', 900, 600, '') ?></span>
    <span class="feature__body">
      <span class="label">SHOENKI</span>
      <span class="feature__title" id="s4">消煙機について詳しく</span>
      <span class="feature__text">別サイト「野田の消煙機」でご紹介しています。</span>
      <span class="more">野田の消煙機を見る<?= icon('external', 16) ?></span>
    </span>
  </a>
</section>

<?php render('related', ['names' => ['煙・臭い対策', '消煙機']]); ?>
