<?php
// 文章は現行ページ https://duct-noda.com/smokeless_roster/ の全文（お客様インタビューを含む、2026-10-21）。写真も現行ページのもの。
// 現行ページの「水蒸気効果」の説明は「安全性」の文がそのまま重複していたため、特長の名前どおりの一文にした。
render('page-head', [
    'label' => 'SMOKELESS ROASTER',
    'title' => '業界初！|特許を取得した|無煙ロースター',
    'lead' => '焼肉店オーナー様、もっと美味しく焼ける無煙ロースターをお探しではありませんか？高い安全性で安心、水蒸気効果でジューシーに焼ける、新型の火山石無煙ロースター「NEOシリーズ NC-22熔岩石」をご紹介します。',
    'image' => 'svc-roaster.jpg', 'ratio' => [3, 2],
]);
$sizes = ['roaster-rostle.jpg' => [228, 171], 'media/cases/1/45f41b125387-800.jpg' => [640, 852], 'product-nc22.jpg' => [480, 316]];
$features = [
    ['安全性が高い', 'roaster-rostle.jpg', '鋳物ロストル', ['鋳物ロストルが肉の炭化や炎の燃え上がりを抑え、ダクトへの引火も防止。火災リスクを低減できる安心設計です。']],
    ['水蒸気効果でお肉をみずみずしく焼ける', 'media/cases/1/45f41b125387-800.jpg', '無煙ロースターを設置したテーブル席', ['水蒸気の効果で、お肉をみずみずしく焼き上げます。']],
    ['富士山産出の熔岩石を使うことでもっとおいしく', 'product-nc22.jpg', '富士山産の熔岩石を敷いたNEOシリーズ NC-22熔岩石', ['さらに富士山産の熔岩石を使用することで、非常に高い遠赤外線効果を実現。「短時間で加熱できる」「食材を中からじっくりと焼き上げる」などのメリットがあります。', 'またCO₂排出量も抑えた、地球環境にも優しいエコ設計です。']],
];
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'NC-22', 'title' => '「NEOシリーズNC-22熔岩石」の特長', 'id' => 's1']); ?>
  <p class="prose u-mb">「NEOシリーズNC-22熔岩石」は、日本ではじめて「安全性＋水蒸気効果」の2点から特許（特許番号4620759）を取得した無煙ロースターです。</p>
  <ol class="rows">
    <?php foreach ($features as $i => [$t, $img, $alt, $ps]): ?>
      <li class="row row--thumb">
        <div class="row__img"><?= picture($img, ...$sizes[$img], alt: $alt) ?></div>
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
    <?php render('section-head', ['label' => 'CONSULT', 'title' => 'まずはご相談ください。', 'id' => 's2', 'link' => ['施工事例を見る', page_url('construction_examples') . '?work=roaster']]); ?>
    <div class="prose">
      <p>無煙ロースター「NEOシリーズ NC-22熔岩石」は、当社が取り扱う自信の製品です。他の焼肉店様との差別化や、店舗の魅力アップにつながる高性能ロースターをお探しのオーナー様は、この機会にぜひご検討ください。</p>
      <p>また、当社では「NC-22熔岩石」以外にも多彩な無煙ロースターを取り揃えています。店舗ごとのご予算やニーズに合わせた最適な製品をご提案いたしますので、お気軽にお問い合わせください。</p>
    </div>
    <ul class="gallery u-mt">
      <li><?= picture('roaster-gallery1.jpg', 228, 171, '無煙ロースターを設置したカウンター席') ?></li>
      <li><?= picture('roaster-gallery2.jpg', 228, 171, '無煙ロースターを設置したテーブル席') ?></li>
      <li><?= picture('roaster-gallery3.jpg', 228, 171, '無煙ロースターを設置したカウンター席') ?></li>
      <li><?= picture('roaster-gallery4.jpg', 228, 171, '無煙ロースターを設置した座敷') ?></li>
    </ul>
    <div class="grid grid--2 u-mt">
      <div class="point">
        <p class="label">DEMO</p>
        <h3 class="h3-like kw">デモ機で試してから<wbr>決められます</h3>
        <p class="point__text">使い方・メンテナンス・網交換の手順・火災予防を実機で説明します。実際にお肉を焼いて試食もできます。</p>
      </div>
      <div class="point">
        <p class="label">INSTALLATION</p>
        <h3 class="h3-like kw">床下ダクト・点検口・<wbr>防火対策まで一式</h3>
        <p class="point__text">ロースターの設置に合わせ、床下のダクト・点検口・防火対策まで一式で施工します。</p>
      </div>
    </div>
  </div>
</section>

<section class="section wrap" aria-labelledby="s4">
  <?php render('section-head', ['label' => 'INTERVIEW', 'title' => 'お客様インタビュー', 'id' => 's4']); ?>
  <?php render('interview', [
      'name' => '焼肉食堂おおいり',
      'image' => 'voice-ooiri-store.jpg', 'imageSize' => [629, 478],
      'info' => [['所在地', '福岡市西区拾六町1丁目18-1']],
      'link' => ['焼肉食堂おおいりのホームページ', 'https://satol.co.jp/ooyakiniku.php'],
      'title' => 'カタログやホームページだけでは実際のロースターの使い勝手などがわからない。',
      'qa' => [
          ['当社に工事を依頼しようと思われたきっかけは何ですか？', ['焼肉店の新規開店にあたり、焼肉ロースターをどのメーカーにするか悩んでいました。各社のカタログやホームページはありましたが、サイズ感や実際の使い勝手をイメージするのが難しく困っていました。そんな中、株式会社野田さんではデモ機（実演機）を用意されているということで、実際に目の前で実演をしていただけました。', '日常のメンテナンス方法や操作方法の説明だけでなく、実際にお肉を焼いて試食させてもらったことで、「どのように焼けるのか」や火山石ロースターの遠赤外線効果を具体的に確認できました。購入前にすべての疑問を解消できたことが、依頼を決める大きなきっかけになりました。']],
          ['どんなことを希望されましたか？', 'ロースターを実際に触って、サイズ感や質感、使い方、注意すべき点などを詳しく確認したいと希望しました。カタログではわからない細かな点を丁寧に説明いただき、新たな疑問も出ましたが、それらもすべて解決できたため、安心して購入することができました。'],
          ['施工後の変化はどうでしたか？', '施工前に操作方法やメンテナンス方法を詳しく確認できたおかげで、事前に従業員向けの勉強会やメンテナンス指導を行うことができました。開店前にしっかりと理解・習得できていたため、オープン時に慌てることもなく、スムーズに営業を開始することができました。'],
      ],
      'work' => [
          '当社ではデモ機（見本機）を用いて、実際の使用方法や使用上の注意点、日々のメンテナンス方法を詳しくご説明しています。さらに網交換の手順や火災予防対策まで、細かな点も丁寧にお伝えしております。',
          'また、火山石ロースターの特長である「遠赤外線効果でお肉がふっくらジューシーに焼き上がる」という魅力を、お客様ご自身の目でしっかりご確認いただけます。',
      ],
      'photos' => [['voice-ooiri-work1.jpg', 240, 180], ['voice-ooiri-work2.jpg', 240, 180]],
  ]); ?>
</section>

<?php render('related', ['names' => ['無煙ロースター']]); ?>

<p class="wrap note note--foot">特許番号 4620759（安全性と水蒸気効果で日本初の特許取得）</p>
