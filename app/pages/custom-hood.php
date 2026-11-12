<?php
// 新設ページ「特注フード・オーダーメイド製作」（Boss 依頼 2026-11-01）。
// 文章は既存の施工事例（梁干渉の変形フード、銅板のデザインフード、特注塗装、かわ屋の大量煙対策、大型山型フード、工場の排煙システム、角ダクト、ベルトカバー）から組んだもの。
// Boss の確認：注文は1台から。製作は「提携工場」（自社工場とは書かない）。製作日数の目安は書かない。
// 写真：上部・デザインフード・ギャラリーは Boss 提供（2026-11-01）。他は移行した施工事例の写真を3:2に切ったもの。変形フードは事例4（カフェ、元640px）。事例165の写真は特注フードではないと Boss 指摘（2026-11-01）。
render('page-head', [
    'label' => 'CUSTOM HOOD',
    'title' => '他店とは一味違う、|記憶に残るフードを。',
    'lead' => 'カウンター越しにお客様の目に入るフードは、お店の「顔」のひとつです。株式会社野田は、銅板や金色・黒の特殊塗装、多角形や筒形など、内装とコンセプトに合わせたフードを一台ずつ設計し、提携工場で製作、当社の職人が取り付けます。見た目の美しさと、煙をしっかり吸い込む性能を両立した、オーダーメイドの特注フードです。',
    'image' => 'custom-hero.jpg', 'ratio' => [3, 2], 'focus' => '50% 40%',
]);
$types = [
    ['お店の顔になる「デザインフード」', 'custom-copper.jpg', '石張りの壁に合わせた銅色のフード', [
        '外側に銅板を貼る、金色やゴールデンブラウン、黒に特殊塗装する、表面を梨地（なしじ）に仕上げて照明に映える質感にするなど、お店のコンセプトに合わせた見た目にできます。',
        '四角だけでなく、多角形や筒形など、形からデザインすることもできます。客席から見上げたときの姿まで考えた、他店にない一台に仕上げます。',
    ]],
    ['空間に溶け込む「変形フード」', 'custom-shape.jpg', '低い天井に合わせて斜めに切り込んだ変形フード（カフェの厨房）', [
        '梁や低い天井に合わせて、フードの形そのものを変えてつくります。斜めの面や切り欠きを入れて空間にすっと納めることで、既製品を無理に付けたときの圧迫感がなく、内装の線を崩しません。',
        '現場をミリ単位で測るので、スペースを無駄にせず、調理の邪魔にもなりません。',
    ]],
    ['存在感で魅せる「大型フード」', 'custom-large.jpg', '焼き鳥店の厨房に取り付けた黒い大型フード', [
        '焼き台の上に構える黒い大型フードのように、大きさそのものをお店の見せ場にすることもできます。こだわりの内装を崩さない外観に仕上げながら、焼き鳥や焼肉の大量の煙と油もしっかり吸い込む設計です。',
        '必要に応じて屋外の消煙機（排煙脱臭装置）ともつなぎ、ご近所への煙や臭いにも配慮します。',
    ]],
];
// デザイン以外の特注（Boss「このページの趣旨はデザイン性。工場用などは『それ以外にもこういうことができます』という言い方で下の方に。小さくしなくてよい」2026-11-01）
$others = [
    ['食品工場・大型厨房向けのフード', 'custom-factory.jpg', '食品工場に取り付けた洗浄対応のフードとビニールカーテン', '毎日水で洗えるように中に勾配をつけた二重天井にする、清掃の水が飛ばないようカーテンを付ける、上に人が乗って点検できるよう吊りの強さを5倍にするなど、衛生管理と安全が求められる現場の決まりに合わせてつくります。'],
    ['ダクトや部品の特注', 'custom-duct.jpg', '地下の梁をよけて組んだ角ダクト', '梁や配管をよけて通す角ダクト、雨や日差しからファンのベルトを守る特注カバーなど、既製品では合わない部品も設計してつくります。'],
];
$gallery = [
    ['custom-gal-gold.jpg', '和の空間に灯る、金色の多角形フード'],
    ['custom-gal-round.jpg', '和紙の照明と並ぶ、筒形のフード'],
    ['custom-gal-island.jpg', 'カウンターを包み込む大型フード'],
    ['custom-gal-copper.jpg', '銅色の外装に、ステンレスのフィルター'],
];
$reasons = [
    ['見た目と吸い込みを|両立する', '見た目だけの特注ではありません。厨房機器の熱や煙の量から必要な排気量を計算し、フードの大きさ・深さ・ダクトの太さ・ファンを決めます。だから、こだわりの形でもしっかり煙を吸い込みます。'],
    ['設計と取り付けは当社、|製作は提携工場', '現場の採寸と設計、取り付けは当社が行い、製作は信頼できる提携工場に依頼しています。図面を引いた担当者が最後まで責任を持つので、測った寸法と細かなご要望がそのまま形になります。'],
    ['現場をミリ単位で採寸', '梁・配管・照明・天井の高さを現地で細かく測り、取り付けたときにぴったり納まるようにつくります。現地調査は無料です。'],
    ['飲食店のダクト工事|ひとすじ', '焼肉・焼き鳥・居酒屋・カフェ・パン屋さん・食品工場まで、施工実績は1,000件以上。さまざまな厨房を手がけてきた経験から、その業態とお店の雰囲気に合う形をご提案します。'],
];
$steps = [
    ['ご相談', 'お電話・LINE・フォームからお気軽にどうぞ。図面や今の厨房の写真があればお送りください。'],
    ['現地調査・採寸（無料）', '梁・天井の高さ・厨房機器の位置を測り、お悩みやご希望をうかがいます。'],
    ['計算と設計', '煙の量から排気量を計算し、フードの形と大きさを決めて図面にします。'],
    ['見た目と仕上げの打ち合わせ', '色・素材・仕上げを、内装に合わせて決めます。内装のイメージ写真があればお見せください。'],
    ['お見積り', '図面と仕上げが決まったら、正式なお見積りをお出しします。'],
    ['製作・取り付け', '提携工場で製作し、当社の職人が営業に支障の出ない日程で取り付けます。'],
    ['アフターフォロー', '取り付け後の点検・清掃・修理も、同じ担当者が対応します。'],
];
$faq = [
    ['フード1台だけでも注文できますか？', 'はい、1台から承ります。厨房のフードはお店に1台のことがほとんどですので、1台のご注文がふつうです。お気軽にご相談ください。'],
    ['特注にすると、既製品より高くなりますか？', '形・大きさ・素材・仕上げによって変わるため、一概には言えません。無料の現地調査のあと、図面とあわせて正式なお見積りをお出しします。ご予算に合わせた形のご提案もいたします。'],
    ['色や素材は選べますか？', 'はい。ステンレスのほか、銅板貼り、黒やゴールデンブラウンなどの特殊塗装、梨地仕上げなどの実績があります。内装のイメージ写真があれば、それに合わせてご提案します。'],
    ['今あるフードやダクトを活かせますか？', '状態によっては活かせます。実際に、今のフードを再利用してダクトだけをやり直し、店内の煙の充満を解消した事例もあります。まずは現地で見せてください。'],
    ['新しく開業するお店でも頼めますか？', 'はい。図面の段階からご相談いただくと、内装や設備と合わせて無理のない形を決められます。'],
];
$works = cases_with_title_words(['特注', '変形', 'デザインフード', '銅板'], 6);
// 素材サンプル（Boss 2026-11-12「多様な素材で想いの通りに希望のフードが叶う」。70種類以上あるので操作しやすく、カッコよく）
$mat = require APP_DIR . '/data/custom-materials.php';
$matItems = $mat['items'];
$matPic = function (string $src, int $w, int $h, string $alt, bool $now): string {
    // 最初の1枚だけすぐ読む。ほかは選ばれた時（とその前後）に JS が読む＝70種類でも重くならない
    [$jpg, $webp] = img_urls($src);
    $a = $now ? '' : 'data-';
    return '<picture>' . ($webp ? '<source type="image/webp" ' . $a . 'srcset="' . e($webp) . '">' : '')
        . '<img ' . $a . 'src="' . e($jpg) . '" width="' . $w . '" height="' . $h . '" alt="' . e($alt) . '" decoding="async"' . ($now ? ' loading="lazy"' : '') . '></picture>';
};
$nn = fn(int $n): string => sprintf('%02d', $n);
?>
<?php // 素材の欄へのご案内（Boss「この機能を目立たせたい」2026-11-12） ?>
<p class="wrap matjump"><a href="#material"><span class="matjump__dots" aria-hidden="true"><?php foreach (array_slice($matItems, 0, 5) as $m): ?><?= picture('assets/img/material/' . $nn($m['no']) . '-chip.jpg', 200, 200, '') ?><?php endforeach; ?></span><span class="matjump__text"><b><?= $mat['total'] ?>種類以上</b>の素材から選べます</span><?= icon('arrow', 16) ?></a></p>

<section class="section wrap" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'GALLERY', 'title' => 'お客様の記憶に残る|特注フード', 'id' => 's3', 'lead' => '同じフードは二つとありません。お店ごとの空間に合わせてつくった特注フードの一部をご紹介します。']); ?>
  <ul class="hoodgal">
    <?php foreach ($gallery as [$img, $cap]): ?>
      <li><figure><span class="hoodgal__img"><?= picture($img, 900, 1200, $cap) ?></span><figcaption><?= e($cap) ?></figcaption></figure></li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="mat" id="material" aria-labelledby="s9" data-mat>
  <div class="section wrap">
    <?php render('section-head', ['label' => 'MATERIAL', 'title' => '多様な素材で、|想いのままのフードを。', 'id' => 's9', 'lead' => '金・銅・黒・銀。同じ形のフードでも、素材を変えるだけでお店の印象はがらりと変わります。' . $mat['total'] . '種類以上の素材から、内装とコンセプトにぴったりの一枚をお選びいただけます。']); ?>
    <div class="mat__stage">
      <div class="mat__view" tabindex="0" aria-roledescription="素材の見本" aria-label="素材ごとの仕上がりイメージ">
        <div class="mat__hoods">
          <?php foreach ($matItems as $i => $m): ?>
            <figure class="mat__hood<?= $i === 0 ? ' is-on' : '' ?>" data-i="<?= $i ?>"<?= $i ? ' aria-hidden="true"' : '' ?>><?= $matPic('assets/img/material/' . $nn($m['no']) . '-hood.jpg', 900, 1200, str_replace('|', '', $m['name']) . 'で仕上げたフードのイメージ', $i === 0) ?></figure>
          <?php endforeach; ?>
        </div>
        <span class="mat__tag">仕上がりイメージ</span>
        <div class="mat__plates" aria-hidden="true">
          <?php foreach ($matItems as $i => $m): ?>
            <span class="mat__plate<?= $i === 0 ? ' is-on' : '' ?>" data-i="<?= $i ?>"><?= $matPic('assets/img/material/' . $nn($m['no']) . '-plate.jpg', 720, 720, '', $i === 0) ?></span>
          <?php endforeach; ?>
          <span class="mat__platecap">素材プレート</span>
        </div>
        <button class="mat__nav mat__nav--prev" type="button" aria-label="前の素材"><?= icon('arrow', 20) ?></button>
        <button class="mat__nav mat__nav--next" type="button" aria-label="次の素材"><?= icon('arrow', 20) ?></button>
      </div>
      <div class="mat__info" aria-live="polite">
        <?php foreach ($matItems as $i => $m): ?>
          <div class="mat__text<?= $i === 0 ? ' is-on' : '' ?>" data-i="<?= $i ?>"<?= $i ? ' hidden' : '' ?>>
            <p class="mat__no"><span>No.</span><?= $nn($m['no']) ?></p>
            <p class="mat__family"><?= e($mat['families'][$m['family']] ?? '') ?></p>
            <h3 class="mat__name"><?= heading_html($m['name']) ?></h3>
            <p class="mat__desc"><?= e($m['desc']) ?></p>
          </div>
        <?php endforeach; ?>
        <p class="mat__total"><b><?= $mat['total'] ?></b><span>種類以上の<br>素材から選べます</span></p>
      </div>
      <div class="mat__pick">
        <div class="mat__filter" role="group" aria-label="色で絞り込む">
          <button class="mat__fbtn is-on" type="button" data-family="" aria-pressed="true">すべて</button>
          <?php foreach ($mat['families'] as $key => $label): ?>
            <button class="mat__fbtn" type="button" data-family="<?= e($key) ?>" aria-pressed="false"><?= e($label) ?><sup><?= count(array_filter($matItems, fn($m) => $m['family'] === $key)) ?></sup></button>
          <?php endforeach; ?>
        </div>
        <ul class="mat__chips">
          <?php foreach ($matItems as $i => $m): ?>
            <li data-family="<?= e($m['family']) ?>"><button class="mat__chip<?= $i === 0 ? ' is-on' : '' ?>" type="button" data-i="<?= $i ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>" title="<?= e(str_replace('|', ' ', $m['name'])) ?>"><span class="mat__swatch"><?= picture('assets/img/material/' . $nn($m['no']) . '-chip.jpg', 200, 200, '') ?></span><span class="mat__chipno"><?= $nn($m['no']) ?></span><span class="u-sr"><?= e(str_replace('|', ' ', $m['name'])) ?></span></button></li>
          <?php endforeach; ?>
          <li class="mat__more" aria-hidden="true"><b>+<?= max(0, $mat['total'] - count($matItems)) ?></b><span>ほかにも</span></li>
        </ul>
      </div>
    </div>
    <p class="mat__note">ここでご紹介しているのは一部です。ほかにも多くの素材があり、お打ち合わせで内装に合うものをご提案します。</p>
  </div>
</section>

<section class="band" aria-labelledby="s1">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'REQUEST', 'title' => 'こんなご要望・お悩みは|ありませんか？', 'id' => 's1']); ?>
    <?php render('bullets', ['icon' => 'alert', 'items' => [
        '他店にない、お客様の記憶に残るフードにしたい',
        '銀色の既製品フードがお店の内装に合わない。見せるフードにしたい',
        '天井の梁（はり）が邪魔をして、既製品のフードが取り付けられない',
        '天井が低く、普通の形のフードでは頭がぶつかる・ダクトの通り道がない',
        '焼き鳥・鶏皮・焼肉など煙と油が多く、既製品では吸いきれない',
    ]]); ?>
    <div class="callout u-mt">
      <h3>ひとつでも当てはまれば、特注フードでかなえられるかもしれません。</h3>
      <p>「既製品では無理」とあきらめる前に、私たち排煙のプロにご相談ください。</p>
    </div>
  </div>
</section>

<section class="section wrap" aria-labelledby="s2">
  <?php render('section-head', ['label' => 'WHAT WE MAKE', 'title' => '野田がつくる特注フード', 'id' => 's2', 'lead' => '形・大きさ・色・素材に決まりはありません。これまでにつくってきた特注フードの一例をご紹介します。']); ?>
  <ol class="rows">
    <?php foreach ($types as [$t, $img, $alt, $ps]): ?>
      <li class="row">
        <div class="row__img"><?= picture($img, 900, 600, $alt) ?></div>
        <div class="row__body">
          <h3 class="row__head"><?= e($t) ?></h3>
          <?php foreach ($ps as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>
  <div class="callout u-mt">
    <h3>ここにない形も、ご相談ください。</h3>
    <p>「こんなフードはできる？」というイメージや写真があれば、それをもとに形と仕上げをご提案します。</p>
  </div>
</section>

<section class="band" aria-labelledby="s4">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'WHY NODA', 'title' => '野田が特注フードを|つくれる理由', 'id' => 's4']); ?>
    <?php render('cards', ['cols' => 4, 'numbered' => true, 'items' => $reasons]); ?>
    <div class="callout u-mt">
      <h3>排煙のプロの豆知識：フードは「大きさ」より「深さ」が大事</h3>
      <p>フードを選ぶとき、つい縦横の大きさばかりに目がいきがちですが、実は「深さ」がとても大事です。深さが足りないと、一度フードの中に入った煙がまた外へ逃げ出してしまいます。特注フードでは、梁や天井の高さに合わせて形を変えながらも、煙をためて吸い込むための深さをしっかり確保するよう設計しています。</p>
    </div>
  </div>
</section>

<section class="section wrap" aria-labelledby="s5">
  <?php render('section-head', ['label' => 'FLOW', 'title' => '完成までの流れ', 'id' => 's5', 'link' => ['工事全体の流れを見る', page_url('flow')]]); ?>
  <ol class="timeline">
    <?php foreach ($steps as $i => [$t, $d]): ?>
      <li class="timeline__item">
        <span class="timeline__dot" aria-hidden="true"><?= $i + 1 ?></span>
        <div class="timeline__body">
          <span class="timeline__label">STEP <?= $i + 1 ?></span>
          <h3><?= e($t) ?></h3>
          <p><?= e($d) ?></p>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>
</section>

<section class="band" aria-labelledby="s6">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'FAQ', 'title' => '特注フードの|よくある質問', 'id' => 's6', 'link' => ['質問をもっと見る', page_url('question')]]); ?>
    <?php render('faq-list', ['items' => $faq]); ?>
  </div>
</section>

<section class="section wrap" aria-labelledby="s8">
  <?php render('section-head', ['label' => 'AND MORE', 'title' => 'それ以外にも、|こんな特注ができます', 'id' => 's8', 'lead' => '見た目にこだわるフードのほかにも、現場の決まりや使い方に合わせた特注を承っています。']); ?>
  <ol class="rows">
    <?php foreach ($others as [$t, $img, $alt, $d]): ?>
      <li class="row">
        <div class="row__img"><?= picture($img, 900, 600, $alt) ?></div>
        <div class="row__body">
          <h3 class="row__head"><?= e($t) ?></h3>
          <p><?= e($d) ?></p>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>
</section>

<section class="band" aria-labelledby="s7">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'WORKS', 'title' => '特注フードの施工事例', 'id' => 's7', 'link' => ['施工事例をもっと見る', page_url('construction_examples')]]); ?>
    <?php if ($works): ?>
      <ul class="grid grid--3 cases">
        <?php foreach ($works as $case): ?><li><?php render('case-card', ['case' => $case]); ?></li><?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="empty">該当する事例は準備中です。</p>
    <?php endif; ?>
  </div>
</section>
