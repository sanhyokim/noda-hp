<?php
// 文章は現行ページ https://duct-noda.com/new_stores/ の全文（2026-10-21）。写真も現行ページのもの。
render('page-head', [
    'label' => 'NEW STORES',
    'title' => '飲食店の換気扇・ダクト工事は|専門業者に|任せましょう',
    'lead' => '新規開業の換気・ダクトは、図面の段階でご相談ください。給排気のバランスを事前に計算し、開業後の煙・臭い・吸い込み不良を防ぎます。',
    'image' => 'svc-newstore.jpg', 'ratio' => [3, 2],
]);
$troubles = [
    ['油煙が原因のトラブル', 'new-case1.jpg', '揚げ物を多く調理する飲食店では、油を多く含んだ油煙が近隣住宅の外壁を汚して苦情につながるケースが後を絶ちません。必要な数のダクトフードや換気扇が設置されていても、メンテナンス不足により油煙処理が不十分なことが原因です。設備は設置するだけでなく、長期間にわたって正常に機能させるため、定期的なメンテナンスと清掃が不可欠です。'],
    ['煙が原因のトラブル', 'new-case2.jpg', '焼鳥屋から発生する煙と臭いが原因で、近隣住民が窓を開けられない状況となり、クレームが発生しました。原因は、排気口が住民の自宅に近かったことです。排気口の位置変更工事により問題を解決しました。給排気系ダクトの設置時は、排気口が近隣に迷惑をかけないよう、事前に十分な調査・検討と設計配慮が重要です。'],
    ['臭いが原因のトラブル', 'new-case3.jpg', '焼肉屋から発生する臭いが、近隣住民のベランダで干す洗濯物に染み付いたという苦情がありました。調査の結果、使用中の機器では臭い除去が不十分であることが判明し、より高性能な機器への入れ替えで解決しました。換気扇やダクトは、設置場所に合った換気・給排気能力が必要ですが、その判断は難しいため、専門業者にご相談ください。'],
];
?>
<section class="section wrap" aria-labelledby="s0">
  <?php render('section-head', ['label' => 'WHY SPECIALISTS', 'title' => '換気扇とダクトは、|事前の計算が重要です', 'id' => 's0']); ?>
  <div class="row row--thumb">
    <div class="row__img"><?= picture('new-grill.jpg', 228, 171, '焼き鳥の煙') ?></div>
    <div class="row__body">
      <p>飲食店の厨房に欠かせない設備として、「換気扇」と「ダクト」があります。これらは、焼き物や揚げ物などの調理で発生する煙や臭いがホールに流れ込むのを防ぐためのものです。設置の際には、必要な吸引力や給気・排気のバランスを事前に計算することが重要です。吸引力不足やバランス不良は、トラブルの原因となる可能性があります。</p>
    </div>
  </div>
  <ul class="pairs">
    <li><span>吸引力が弱いと</span><?= icon('arrow', 18) ?><b>ホールに煙や臭いが充満してしまう</b></li>
    <li><span>給排気のバランスが悪いと</span><?= icon('arrow', 18) ?><b>入口のドアが開きにくくなる</b></li>
  </ul>
  <div class="prose u-mt">
    <p>このようなトラブルは、専門業者に設計・工事を依頼することで回避できます。新たに飲食店をオープンする場合には、飲食店の換気扇・ダクトの工事を専門とする、実績豊富な業者に任せることをおすすめします。</p>
  </div>
</section>

<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'BEFORE OPENING', 'title' => '開業前に決めておくべき|3つのこと', 'id' => 's1']); ?>
  <?php render('cards', ['cols' => 3, 'numbered' => true, 'items' => [
      ['吸引力の計算', '厨房機器の熱量や調理の内容から、必要な吸引力を計算します。足りないとホールに煙や臭いが充満します。'],
      ['給排気のバランス', '排気だけ強くしても、同じ量の空気を取り込まなければドアが重くなり、煙を吸わなくなります。'],
      ['排気口の位置と|近隣への配慮', '排気口が近隣に迷惑をかけないよう、事前の調査・検討と設計の配慮が欠かせません。'],
  ]]); ?>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'TROUBLES', 'title' => '放っておくと|こんなトラブルに発展します', 'id' => 's2']); ?>
    <ol class="rows">
      <?php foreach ($troubles as $i => [$t, $img, $d]): ?>
        <li class="row row--thumb">
          <div class="row__img"><?= picture($img, $img === 'new-case2.jpg' ? 870 : 228, $img === 'new-case2.jpg' ? 580 : 171, '') ?></div>
          <div class="row__body">
            <h3 class="row__head"><span class="row__case">事例<?= $i + 1 ?></span><?= e($t) ?></h3>
            <p><?= e($d) ?></p>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="section wrap" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'LEAVE IT TO US', 'title' => '私たちにお任せください', 'id' => 's3']); ?>
  <p class="prose">厨房換気扇・排煙ダクト工事に特化した専門業者「株式会社野田」は、飲食店新規開業をサポートいたします。各店舗の規模やレイアウトに合わせた機器のご提案・設置に加え、ダクトの入れ替え、メンテナンス、清掃まで一貫して対応。専門業者として培ったノウハウで、さまざまなトラブルやお悩みを解消しますので、お気軽にお問い合わせください。</p>
</section>

<section class="section wrap" aria-labelledby="s4">
  <div class="partner">
    <span class="partner__icon"><?= icon('blueprint', 28) ?></span>
    <div class="partner__text">
      <p class="label">FOR PARTNERS</p>
      <h2 class="partner__title" id="s4">設計事務所・内装業者様との連携も可能</h2>
    </div>
    <a class="btn btn--blue" href="<?= e(page_url('customer')) ?>">業者様向けページへ<?= icon('arrow', 18) ?></a>
  </div>
</section>
