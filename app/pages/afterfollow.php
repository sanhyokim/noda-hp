<?php
// 文章は現行ページ https://duct-noda.com/afterfollow/ の全文（2026-10-21）。AI 生成の人物写真は使わず、実際の施工写真に置き換えた。
render('page-head', [
    'label' => 'AFTER FOLLOW',
    'title' => 'ダクト工事だけでは|終わらない、|徹底した|アフターフォロー',
    'lead' => '当社では、工事完了がゴールではなく、その後もトータルでサポートいたします。保守点検、メンテナンス（修理）、清掃まで、施工後のお困りごとにもワンストップで対応可能です。',
    'image' => 'after-rooftop.jpg', 'ratio' => [16, 9],
]);
$checks = [
    ['オープン以来、一度もダクトを清掃していない', '現状、特に問題がなくても、この機会に点検を受けることをおすすめします。特に、メンテナンス履歴が不明な居抜き物件の場合は、早めの点検が大変重要です。'],
    ['煙の吸い込みが以前より悪くなった気がする', 'ダクト内部には油やホコリが蓄積している場合があります。これらがたまると、煙の排出が滞るだけでなく、火災が拡大するリスクも高まるため、定期的な清掃が必要です。'],
    ['ファンから異音がする', 'ファンからガタガタという異音がする場合、ファンに大量の油汚れが付着している可能性があります。そのため、同様にダクト内にも油やホコリが蓄積している懸念がございます。'],
    ['排気ダクトから油が染み出している', '油汚れが酷い場合、接続部や配管の穴から大量の油が漏れ出す可能性があります。'],
    ['グリスフィルターを清掃していない', 'グリスフィルターが汚れていると、本来吸い込む空気中の油分を除去する機能が低下し、その結果、ダクト内に油汚れが蓄積される原因となります。'],
];
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'ONE STOP', 'title' => '同じ担当者が対応――|安心のアフターフォロー', 'id' => 's1']); ?>
  <div class="rows">
    <div class="row">
      <div class="row__img row__img--figure"><?php render('onestop-figure'); ?></div>
      <div class="row__body">
        <h3 class="row__head">安心の自社一貫対応</h3>
        <p>当社では、打ち合わせからダクト工事、フード取り付け、そしてアフターフォローに至るまで、一連の工程を同一の担当者が責任を持って対応いたします。</p>
        <p>各店舗の事情を十分に把握している担当者ならではの迅速かつ丁寧なサービスで、お客様のニーズに的確にお応えいたします。</p>
      </div>
    </div>
    <div class="row">
      <div class="row__img"><?= picture('after-grease.jpg', 900, 600, '厨房フードの内部') ?></div>
      <div class="row__body">
        <h3 class="row__head">メンテナンス・清掃</h3>
        <p>レンジフードだけ清掃している、または一度も清掃していない店舗もありますが、定期点検・清掃を行っているのはごく一部です。</p>
        <p>実際、7割以上の店舗が10年以上清掃されていないという調査結果もあります。「見た目は問題ない」「忙しい」という理由で清掃を怠ると、煙や臭いのトラブル、異音、騒音、さらには火災リスクが高まります。</p>
      </div>
    </div>
  </div>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'CHECK', 'title' => 'ダクトの油汚れで火災の危険性も！|あなたのお店は大丈夫？', 'id' => 's2', 'lead' => '下のチェックリストで、ダクト火災の危険度をチェックしてみましょう。']); ?>
    <ul class="checks">
      <?php foreach ($checks as [$t, $d]): ?>
        <li class="checks__item">
          <h3 class="checks__title"><?= e($t) ?></h3>
          <p><?= e($d) ?></p>
        </li>
      <?php endforeach; ?>
    </ul>
    <p class="checks__note">1つでも該当する項目があった場合は、専門家によるメンテナンス・清掃を受けることをおすすめします。</p>
  </div>
</section>

<section class="section wrap" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'DAMPER', 'title' => 'ダンパーの重要性', 'id' => 's3']); ?>
  <div class="row">
    <div class="row__img row__img--square"><?= picture('after-damper.jpg', 675, 675, '清掃前と清掃後のダンパー周り') ?></div>
    <div class="row__body">
      <p>ダクト火災が発生すると、ダンパーのヒューズが作動し、空気の流れを遮断することで、ダクト内からの延焼を防止します。</p>
      <p>しかし、油汚れなどが蓄積すると、ダンパーが正常に作動しなくなる可能性があります。定期的なメンテナンスと清掃でダンパーの機能を守り、火災発生や延焼のリスクを低減しましょう。</p>
    </div>
  </div>
</section>

<section class="section wrap" aria-labelledby="s4">
  <a class="feature" href="https://www.cleaning-duct.com/" target="_blank" rel="noopener">
    <span class="feature__img feature__img--banner"><?= picture('banner-cleaning.jpg', 1200, 600, '') ?></span>
    <span class="feature__body">
      <span class="label">CLEANING</span>
      <span class="feature__title" id="s4">定期清掃について</span>
      <span class="feature__text">焼肉店・焼き鳥店のダクト清掃は、専用サイトで清掃の流れ・料金の目安・実際の清掃写真をご案内しています。ダクトの点検は無料です。</span>
      <span class="more">ダクト清掃のサイトを見る<?= icon('external', 16) ?></span>
    </span>
  </a>
</section>
