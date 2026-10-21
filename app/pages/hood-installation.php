<?php
// 文章は現行ページ https://duct-noda.com/hood-installation/ の全文（お客様インタビューを含む、2026-10-21）。
// 現行ページで2回続けて出てくる特許の段落は1回にまとめた。写真は現行ページのもの。
render('page-head', [
    'label' => 'YAKINIKU HOOD',
    'title' => '2つの特許を取得した、|他店に差をつける|上引きフード',
    'lead' => '焼肉店のオーナー様、現在お使いの上引きフードの吸煙力にご満足されていますか？当社では、店内の空気をきれいに保ち、工事費用と冷暖房費を抑えられる、スリムでコンパクトな吸煙力の高い上引きフードをご提案しています。',
    'image' => 'svc-yakiniku-hood.jpg', 'ratio' => [3, 2],
]);
$strengths = [
    ['店内の空気がきれいになる', ['従来型のフードでは取り除けなかった煙をスピーディーに吸収。焼肉店特有の「煙・臭い」の問題を根本から解決します。', '清潔でクリアな空気により、他店との差別化を実現できます。']],
    ['工事費用を大幅に削減', ['従来の「しちりんフード」と比べ、工事費用を約40％も抑えられます。コストを抑えつつ高い吸煙力を導入可能です。']],
    ['冷暖房費も節約可能', ['「NC-02クリアーフード」は排煙量182m³/hという省エネ設計でありながら、従来型フード（排煙量500m³/h）に匹敵するパワフルな性能を発揮します。そのため、冷暖房費の大幅な削減が可能です。']],
    ['スリムでコンパクト設計', ['従来の大型フードは圧迫感があり、お客様同士のコミュニケーションを妨げていました。その点、「NC-02クリアーフード」はスリムでスタイリッシュな格納式設計。視界や会話の邪魔にならず、店内をすっきりと快適に保ちます。', 'さらに、自社製造の独自ファンを採用し、細くてシンプルなダクトを使用できるため、従来のスパイラルダクトよりも空間を広く使えます。']],
];
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'NC-02', 'title' => '自慢の上引きフード|「NC-02クリアーフード」', 'id' => 's1']); ?>
  <div class="hoodtop">
    <div class="hoodtop__products">
      <figure class="product"><?= picture('product-nc02-a.jpg', 232, 353, 'NC-02クリアーフード Aタイプ') ?><figcaption>Aタイプ</figcaption></figure>
      <figure class="product"><?= picture('product-nc02-b.jpg', 232, 353, 'NC-02クリアーフード Bタイプ') ?><figcaption>Bタイプ</figcaption></figure>
    </div>
    <div class="prose">
      <p>当社の「NC-02クリアーフード」は、2つの特許を取得した画期的な上引きフードです（特許番号：3460996／3460998）。</p>
      <p>特殊な構造によって、従来製品よりも以下の点で優れた性能を実現しています。</p>
    </div>
  </div>
  <ol class="rows u-mt">
    <?php foreach ($strengths as $i => [$t, $ps]): ?>
      <li class="row row--text">
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
    <?php render('section-head', ['label' => 'COMPARE', 'title' => '従来型フードとの比較', 'id' => 's2']); ?>
    <figure class="compare"><?= picture('hood-compare.jpg', 710, 220, '従来のフードとNC-02クリアーフードの比較') ?></figure>
    <div class="prose u-mt">
      <p>従来型フードと比較すると、「NC-02クリアーフード」のスリムなシルエットは一目瞭然です。</p>
      <p>「こんなにスリムで本当に煙を吸えるの？」と疑問に感じる方もいるかもしれません。しかし、実はフードが大きいと、かえって煙の流れを妨げてしまい、吸煙力が低下する原因になるのです。</p>
      <p>「NC-02クリアーフード」のスリムな設計は、煙が自然に上昇する気流を邪魔せず、効率よく集煙します。煙の性質を利用したスマートな設計だからこそ、コンパクトでも高い吸煙力を実現できるのです。</p>
    </div>
  </div>
</section>

<section class="section wrap" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'CONSULT', 'title' => 'まずはご相談ください。', 'id' => 's3']); ?>
  <div class="prose">
    <p>厨房換気扇・排煙ダクト工事に特化した福岡の専門業者「株式会社野田」では、さまざまなメリットを持つ「NC-02クリアーフード」（特許取得）を取り扱っております。他の焼肉店様に差をつけるチャンスですので、ぜひ一度お問い合わせください。</p>
    <p>当社では、「NC-02クリアーフード」以外にもさまざまな上引きフードを取り扱っており、各店舗様のご予算・ご要望に合わせたご提案が可能です。内装に合わせた特注塗装も可能です。</p>
  </div>
  <ul class="gallery u-mt">
    <li><?= picture('hood-gallery1.jpg', 228, 171, '上引きフードを設置した焼肉店の客席') ?></li>
    <li><?= picture('hood-gallery2.jpg', 228, 171, '和室の座敷に設置した上引きフード') ?></li>
    <li><?= picture('svc-newstore.jpg', 1080, 720, 'テーブル席に並ぶ上引きフード') ?></li>
    <li><?= picture('hood-gallery3.jpg', 768, 1024, '壁際の席に設置した上引きフード') ?></li>
  </ul>
</section>

<section class="section wrap" aria-labelledby="s4">
  <?php render('section-head', ['label' => 'INTERVIEW', 'title' => 'お客様インタビュー', 'id' => 's4']); ?>
  <?php render('interview', [
      'name' => '黒毛和牛 ガブクロ',
      'image' => 'voice-gabukuro-store.jpg', 'imageSize' => [640, 427],
      'info' => [['所在地', '福岡市早良区西新1-9-22 長崎屋ビル1F']],
      'link' => ['ガブクロのInstagram', 'https://www.instagram.com/gabukuro_kurogewagyu/'],
      'title' => '焼肉の煙が店内に充満する。なんとかしたい。',
      'qa' => [
          ['当社に工事を依頼しようと思われたきっかけは何ですか？', ['もともと宮崎県で営業していましたが、より多くのお客様に宮崎牛の美味しさを知っていただきたいという思いから、福岡に移転しました。その際、地元（宮崎）の業者に店舗の工事をすべて任せたのですが、焼肉の煙の吸い込みが悪く、店内が煙で充満するようになりました。お客様からも「服に臭いがつくのが気になる」との声が寄せられるようになり、最終的には工事のやり直しを巡って裁判にまでなってしまいました。', 'そんな折に、排煙・ダクト工事を専門にしている（株）野田さんを知り、改めて工事をお願いしました。']],
          ['どんなことを希望されましたか？', 'まず現状の問題点を伝え、とにかく焼肉の煙を確実に店外に排出してほしいとお願いしました。'],
          ['施工後の変化はどうでしたか？', '店内に煙が充満することが完全になくなり、お客様からのクレームも一切なくなりました。また、既存の排煙器具を活かして工事を行っていただいたため、費用面でも大きな負担なく改善できました。'],
      ],
      'work' => [
          '既存の排煙器具（上引きフード）の取り付け方やダクト経路に問題があり、ファンや排煙器具が本来の性能を発揮できていませんでした。',
          'そのため、基準設定値に基づいてダクト設計を再度行い、既存のダクトをすべて撤去した上で、排煙器具は再利用する形で施工をやり直しました。',
      ],
      'photos' => [['voice-gabukuro-work.jpg', 480, 180]],
  ]); ?>
</section>

<?php render('related', ['names' => ['焼肉 上引きフード']]); ?>

<p class="wrap note note--foot">工事費用の約40％削減は、従来の「しちりんフード」との比較です。</p>
