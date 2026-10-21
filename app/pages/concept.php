<?php
// 文章は現行ページ https://duct-noda.com/concept/ の内容をすべて移したもの（2026-10-21、Boss「情報量が足りない」を受けて戻した）。
// 写真は実際の施工写真を使う（現行の AI 生成の人物画像は使わない）。
render('page-head', [
    'label' => 'COMMITMENT',
    'title' => '私たちのこだわり',
    'lead' => '当社は創業以来、全国の飲食店オーナー様をサポートしてきました。新規開業店舗には企画提案から施工、アフターメンテナンスまで一貫したサービスを提供しています。',
    'image' => 'concept-workshop.jpg', 'ratio' => [16, 9],
]);
$commits = [
    ['自社一貫対応', 'flow-consult.jpg', '天井内に通した断熱材付きのダクト', [
        '当社は換気扇やダクトのご提案から設計、施工までをすべて自社で行っております。店舗オーナー様のご希望・ご要望をしっかりうかがうこと、それを設計に反映させること、そしてその設計を施工により忠実に形にすること。',
        '自社一貫で対応することにより、ミスなく、作業の無駄なく、責任を持って進行できます。',
    ]],
    ['フルオーダーメイド設計', 'svc-duct-general.jpg', '現場に合わせて製作した排気フード', [
        '当社は、これまでの豊富な実績に裏打ちされた給排気ダクト設計力に自信を持っております。店舗オーナー様のご希望を踏まえ、最適な種類、サイズ、形状のダクトをご提案いたします。',
        '完全オーダーメイドのため、1mm単位で寸法を柔軟に調整可能です。さらに、施工中に生じる設計変更にも迅速に対応いたします。',
    ]],
    ['飲食店のダクト工事に特化', 'concept-drawing.jpg', '店の内装に合わせて木で仕上げたフード', [
        '厨房周辺の給排気ダクトはトラブルが多発しがちですが、特に厨房の排煙・油煙対策に特化したダクト工事の専門業者は少ない状況です。煙や臭いの問題はお客様や近隣住民に大きな迷惑を及ぼすだけでなく、火災につながるケースもあるため、迅速かつ的確な対策が求められています。',
        '当社は給排気ダクトの設計から施工まで豊富な実績を誇り、各種トラブルへの対応や改善策のご提案に自信があります。',
    ]],
    ['施工後のアフターフォローもまとめてお任せ', 'top-afterfollow.jpg', '点検しやすく仕上げたフード下のダクト', [
        '当社では、換気扇・ダクト工事の完了をもって終わりとせず、保守点検から修理・清掃に至るまで、施工後のすべてを一括してお引き受けいたします。',
        '各部の汚れやすさやパターン、さらには最適な洗剤の選定に精通しているため、換気扇やダクトの長期にわたる機能維持を実現いたします。',
    ]],
];
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'OUR COMMITMENT', 'title' => '私たちの4つのこだわり', 'id' => 's1']); ?>
  <ol class="rows">
    <?php foreach ($commits as $i => [$t, $img, $alt, $ps]): ?>
      <li class="row">
        <div class="row__img"><?= picture($img, 900, 600, $alt) ?></div>
        <div class="row__body">
          <h3 class="row__head"><span class="row__no"><?= sprintf('%02d', $i + 1) ?></span><?= e($t) ?></h3>
          <?php foreach ($ps as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'AREA', 'title' => '対応エリア', 'id' => 's2']); ?>
    <div class="area">
      <div class="area__lead">
        <p>福岡本社と広島出張所を拠点に、東は広島県から南は鹿児島県まで幅広いエリアの既存店舗のダクト入れ替え工事に対応してきました。現在は日本全国どこでも、お客様のニーズに応じた厨房換気設備のソリューションをご提供しております。</p>
        <p>排煙・油煙に関するお悩みやトラブル予防のご相談など、厨房設備に関することならどんなことでもお気軽にお問い合わせください。</p>
      </div>
      <dl class="area__list">
        <div><dt>福岡本社</dt><dd>〒<?= e(company('hq_zip')) ?> <?= e(company('hq_address')) ?></dd></div>
        <div><dt><?= e(company('branch_name')) ?></dt><dd>〒<?= e(company('branch_zip')) ?> <?= e(company('branch_address')) ?></dd></div>
      </dl>
    </div>
  </div>
</section>
