<?php
// トップページ。区画の順番・文言は design/top-reference.html と付録A-1 に合わせる。
$years = years_in_business();
$feed = top_news();
$cases = latest_cases(6);
$ig = instagram_items();

$problems = [
    ['smoke', '店内に煙がこもる', '服や髪に臭いがつきやすくなった。お客様から煙で目が痛いと言われた。'],
    ['wave', '近隣から臭いの苦情', '排気口の向きや高さ、消煙機の導入で解決できます。'],
    ['fan', '換気扇の音・吸い込み不良', 'ファンの劣化や給気不足が原因のことが多くあります。'],
    ['store', '新規開業の換気設計', '図面の段階からご相談を。設計業者様との連携も可能です。'],
];
$steps = [
    ['お問い合わせ', '電話・メール・LINEで。写真を送っていただくとスムーズです。'],
    ['現地調査（無料）', '店舗の状況・建物・厨房機器を確認します。'],
    ['設計・お見積り', '給排気の計算をもとに、最適なフード・ダクトをご提案。'],
    ['製作・施工', '自社でフードを製作し、営業に支障が出ない日程で施工。'],
    ['アフターフォロー', '定期点検・清掃・ファン交換まで長くお付き合いします。'],
];
$marquee = ['焼肉店', '焼き鳥店', '居酒屋', 'ラーメン店', 'うなぎ店', '鉄板焼き店', 'カフェ', 'パン屋', '旅館・ホテル', 'チェーン店', 'セントラルキッチン', 'コインランドリー'];
?>

<?php ob_start(); // 最初の画面・数字の帯・流れる帯は画面幅いっぱいに出すので、layout の $meta['top'] に渡す ?>
<section class="hero" aria-labelledby="hero-title">
  <div class="hero__tiles" aria-hidden="true">
    <?php for ($r = 1; $r <= 3; $r++): ?>
      <div class="hero__row hero__row--<?= $r ?>">
        <div class="hero__track">
          <?= picture('hero-row' . $r . '.jpg', 3192, 300, '', ['priority' => true]) ?>
          <?= picture('hero-row' . $r . '.jpg', 3192, 300, '', ['priority' => true]) ?>
        </div>
      </div>
    <?php endfor; ?>
  </div>
  <div class="hero__veil" aria-hidden="true"></div>
  <div class="hero__inner frame">
    <p class="hero__since rise"><span>飲食店のフード・ダクトに</span><span>関わり続けて<b><?= $years ?></b>年</span></p>
    <p class="hero__catch rise rise--1">空気が変われば、<br>店舗が変わる。</p>
    <h1 id="hero-title" class="hero__title kw rise rise--2">飲食店の<wbr>煙・臭い・換気、<br>専門会社が<wbr>設計から<wbr>施工まで。</h1>
    <div class="hero__actions rise rise--3">
      <a class="btn btn--white btn--lg hero__cta" href="<?= e(page_url('contact')) ?>">無料相談・現地調査を申し込む<?= icon('arrow', 20) ?></a>
    </div>
  </div>
</section>

<section class="stats" aria-label="野田の実績">
  <dl class="stats__grid">
    <div class="stat"><dt class="stat__cap">創業からの施工実績</dt><dd class="stat__num">1,000<small>件以上</small></dd></div>
    <div class="stat"><dt class="stat__cap kw">2005年 福岡で創業・<wbr>飲食店の換気専門</dt><dd class="stat__num"><small class="pre">創業</small><?= $years ?><small>年</small></dd></div>
    <div class="stat"><dt class="stat__cap kw">福岡本社・<wbr>広島出張所から</dt><dd class="stat__num">全国<small>対応</small></dd></div>
    <div class="stat"><dt class="stat__cap">設計・製作・施工・修理</dt><dd class="stat__num">自社一貫</dd></div>
  </dl>
</section>

<div class="marquee" aria-label="対応している業態">
  <div class="marquee__track">
    <?php for ($r = 0; $r < 2; $r++): ?>
      <ul class="marquee__list"<?= $r ? ' aria-hidden="true"' : '' ?>>
        <?php foreach ($marquee as $m): ?><li><?= e($m) ?></li><?php endforeach; ?>
      </ul>
    <?php endfor; ?>
  </div>
</div>
<?php $meta['top'] = ob_get_clean(); ?>

<section class="section wrap" aria-labelledby="news-title">
  <?php render('section-head', ['label' => 'NEWS', 'title' => 'お知らせ', 'id' => 'news-title']); ?>
  <ul class="news__list">
    <?php foreach ($feed as $f): ?>
      <li>
        <a class="news__row" href="<?= e($f['url']) ?>">
          <time datetime="<?= e($f['published_at']) ?>"><?= e(format_date($f['published_at'])) ?></time>
          <span class="chip <?= $f['type'] === 'case' ? 'chip--solid' : 'chip--outline' ?>"><?= $f['type'] === 'case' ? '施工事例' : 'お知らせ' ?></span>
          <span class="news__title"><?= e($f['title']) ?></span>
          <?= icon('arrow', 16, 'news__arrow') ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <a class="anniv-band" href="<?= e(page_url('company')) ?>#s20">
    <span class="anniv-band__num" aria-hidden="true">20<small>th</small></span>
    <span class="anniv-band__text"><b>おかげさまで創業20周年を迎えました</b>20年で培った技術と信頼を礎に、21年目からも皆様の期待を超える価値を。</span>
    <span class="more">ごあいさつを読む<?= icon('arrow', 16) ?></span>
  </a>
</section>

<section class="section wrap" aria-labelledby="intro-title">
  <?php render('section-head', ['label' => 'ABOUT', 'title' => '飲食店の換気・給排気を|自社一貫でトータルサポート', 'id' => 'intro-title']); ?>
  <div class="intro">
    <div class="intro__text prose">
      <p>焼肉店・焼き鳥店・居酒屋・ラーメン店。油と煙の多い厨房の換気扇・排煙ダクト工事を、福岡から全国へ。創業2005年、累計1,000件以上の施工実績があります。</p>
      <p>当社は排煙・油煙対策のスペシャリストとして、居酒屋、ラーメン屋、焼鳥屋、焼肉店などの飲食店オーナー様を、新規開店からメンテナンスおよび清掃までトータルでサポートいたします。</p>
      <a class="more" href="<?= e(page_url('concept')) ?>">野田のコンセプト<?= icon('arrow', 16) ?></a>
    </div>
    <div class="intro__img"><?= picture('concept-drawing.jpg', 1108, 739, '野田が施工した店内のフードとダクト') ?></div>
  </div>
  <?php // 3項目の文章は現行サイトのトップの同じ区画から（明らかな重複の一文だけ除いた）。写真は AI 生成の人物画像を除き実写に置き換え ?>
  <div class="rows u-mt">
    <div class="row">
      <div class="row__img row__img--contain"><?= picture('after-onestop.jpg', 800, 680, '打ち合わせ・施工・アフターフォローを同じ担当者が対応します') ?></div>
      <div class="row__body">
        <h3 class="row__head">安心の自社一貫対応</h3>
        <p>当社は換気扇やダクトのご提案から設計、施工まで、すべて自社で承っています。オーナー様のご希望・ご要望をしっかりうかがってそれを設計に反映し、その設計を施工により忠実に形にする――。</p>
        <p>自社一貫対応により、ミスなく、作業の無駄なく、責任を持って進行できます。</p>
      </div>
    </div>
    <div class="row">
      <div class="row__img"><?= picture('after-grease.jpg', 900, 600, '厨房フードの内部') ?></div>
      <div class="row__body">
        <h3 class="row__head">メンテナンス・清掃でトラブル防止</h3>
        <p>使えば使うほどダクト内部は油やホコリで汚れていき、その汚れがダクトの不具合や故障、ひいては火事（ダクト火災）の原因になることも……。大惨事を招かないためには、日頃から定期的なメンテナンス・清掃を行うことが重要です。</p>
        <a class="more u-mt" href="<?= e(page_url('afterfollow')) ?>">アフターフォローについて<?= icon('arrow', 16) ?></a>
      </div>
    </div>
    <div class="row">
      <div class="row__img"><?= picture('concept-fullorder.jpg', 900, 600, '現場に合わせて組んだダクト') ?></div>
      <div class="row__body">
        <h3 class="row__head">私たち専門業者へご相談ください</h3>
        <p>給気・排気ダクトは、単に設置すれば良いものではありません。飲食店の規模やレイアウトに応じて、適した種類・大きさ・形状のダクトを選定し、設計する必要があります。そうしなければ、本来の性能を発揮できず、後々トラブルが発生する恐れがあります。</p>
        <p>しかし、ダクトを専門に取り扱っていない業者では、「店舗に適したダクト」の選定は難しく、出店時の選定ミスが原因で、相次ぐ故障の修理に年間数十万円もの費用がかかることもあります。</p>
        <p>長期間にわたり確実に機能するダクトを実現するために、豊富なダクト設計・施工の経験を持つ当社が、最適な選定から施工までしっかりとサポートいたします。</p>
      </div>
    </div>
  </div>
</section>

<section class="section wrap" aria-labelledby="problems-title">
  <?php render('section-head', ['label' => 'PROBLEMS', 'title' => 'お店でこんなお悩み、|ありませんか？', 'id' => 'problems-title']); ?>
  <ul class="problems">
    <?php foreach ($problems as [$ic, $t, $d]): ?>
      <li class="problem">
        <span class="problem__icon"><?= icon($ic, 26) ?></span>
        <h3 class="problem__title"><?= e($t) ?></h3>
        <p class="problem__text"><?= e($d) ?></p>
      </li>
    <?php endforeach; ?>
  </ul>
  <p class="problems__answer kw">身に覚えがある場合は、<wbr><strong>ファンやダクトの設計</strong>に<wbr>原因があるかもしれません。</p>
</section>

<section class="section wrap" aria-labelledby="service-title">
  <?php render('section-head', ['label' => 'SERVICE', 'title' => 'ダクト工事の巧みな技', 'id' => 'service-title', 'lead' => '店舗の規模・業態・建物に合わせて、フードもダクトも一つひとつオーダーメイドで設計します。']); ?>
  <?php render('service-cards'); ?>
</section>

<section class="section wrap" aria-labelledby="reason-title">
  <?php render('section-head', ['label' => 'REASON', 'title' => '野田が選ばれる理由', 'id' => 'reason-title']); ?>
  <ol class="reasons">
    <li class="reason reason--main">
      <span class="reason__no">01</span>
      <h3 class="reason__title kw">飲食店専門だから、<wbr>煙・油・熱の<wbr>現場を<wbr>知っている</h3>
      <p class="reason__text">焼肉・焼き鳥・ラーメンなど油煙の多い厨房を20年以上。一般のダクト業者では難しい「店に合った」給排気の設計ができます。</p>
    </li>
    <li class="reason">
      <span class="reason__no">02</span>
      <h3 class="reason__title kw">提案・設計・製作・施工を<wbr>自社で一貫</h3>
      <p class="reason__text">外注をはさまないので、要望がそのまま形になり、責任の所在も明確です。</p>
    </li>
    <li class="reason">
      <span class="reason__no">03</span>
      <h3 class="reason__title">すべてオーダーメイド設計</h3>
      <p class="reason__text">梁・低い天井・ビルの高層階など難しい現場ほど、経験の差が出ます。</p>
    </li>
    <li class="reason reason--img">
      <div class="reason__body">
        <span class="reason__no">04</span>
        <h3 class="reason__title kw">工事後も続く<wbr>アフターフォロー</h3>
        <p class="reason__text">点検・清掃・ファン交換まで。ダクト火災を防ぐ定期メンテナンスをご提案します。</p>
        <a class="more" href="<?= e(page_url('afterfollow')) ?>">アフターフォローについて<?= icon('arrow', 16) ?></a>
      </div>
      <div class="reason__img"><?= picture('top-afterfollow.jpg', 900, 600, '') ?></div>
    </li>
  </ol>
</section>

<section class="section wrap" aria-labelledby="works-title">
  <?php render('section-head', ['label' => 'WORKS', 'title' => '最新の施工事例', 'id' => 'works-title', 'link' => ['施工事例をもっと見る', page_url('construction_examples')]]); ?>
  <ul class="grid grid--3 cases">
    <?php foreach ($cases as $case): ?>
      <li><?php render('case-card', ['case' => $case]); ?></li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="section wrap" aria-labelledby="flow-title">
  <?php render('section-head', ['label' => 'FLOW', 'title' => 'お問い合わせから|施工までの流れ', 'id' => 'flow-title', 'link' => ['詳しい流れを見る', page_url('flow')]]); ?>
  <ol class="steps">
    <?php foreach ($steps as $i => [$t, $d]): ?>
      <li class="step">
        <span class="step__label">STEP<b><?= sprintf('%02d', $i + 1) ?></b></span>
        <h3 class="step__title"><?= e($t) ?></h3>
        <p class="step__text"><?= e($d) ?></p>
      </li>
    <?php endforeach; ?>
  </ol>
</section>

<section class="section wrap" aria-labelledby="partner-title">
  <div class="partner">
    <div class="partner__bg" aria-hidden="true"><?= picture('partner-blueprint.jpg', 1080, 608, '') ?></div>
    <div class="partner__text">
      <p class="label label--light">FOR PARTNERS</p>
      <h2 class="partner__title kw" id="partner-title">設計事務所・<wbr>内装施工業者様へ</h2>
      <p>給排気計算、フード・ダクトの設計相談、出店基準をクリアする煙対策プランのご相談を承ります。</p>
    </div>
    <a class="btn btn--white" href="<?= e(page_url('customer')) ?>">業者様向けページへ<?= icon('arrow', 18) ?></a>
  </div>
</section>

<section class="section wrap" aria-labelledby="ig-title">
  <?php render('section-head', ['label' => 'INSTAGRAM', 'title' => '私たちの日々の仕事', 'id' => 'ig-title', 'link' => ['@' . company('instagram_handle') . ' をフォロー', company('instagram_url')]]); ?>
  <ul class="ig">
    <?php foreach ($ig as $item): ?>
      <li>
        <a class="ig__item" href="<?= e($item['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($item['alt']) ?>（Instagram）">
          <?php if ($item['fallback']): ?>
            <?= picture($item['image'], 600, 600, '') ?>
          <?php else: ?>
            <img src="<?= e($item['image']) ?>" width="600" height="600" alt="" loading="lazy" decoding="async">
          <?php endif; ?>
          <span class="ig__mark" aria-hidden="true"><?= icon('instagram', 22) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
