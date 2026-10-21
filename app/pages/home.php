<?php
// トップページ。区画の順番・文言は design/top-reference.html と付録A-1 に合わせる。
$years = years_in_business();
$feed = latest_feed(5);
$cases = latest_cases(6);
$ig = instagram_items();

$problems = [
    ['smoke', '店内に煙がこもる', '焼肉・焼き鳥の煙が客席に流れ、服や髪に臭いが残る。'],
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

<section class="hero" aria-labelledby="hero-title">
  <div class="hero__bg"><?= picture('hero-top.jpg', 1600, 900, '', ['priority' => true]) ?></div>
  <div class="hero__veil" aria-hidden="true"></div>
  <div class="hero__inner wrap">
    <div class="hero__badges rise">
      <span class="glass"><span class="glass__dot" aria-hidden="true"></span>飲食店専門のフード・ダクト施工会社</span>
      <span class="glass glass--num"><span class="glass__en">SINCE 2005</span>飲食店の換気一筋<b><?= $years ?></b>年</span>
    </div>
    <h1 id="hero-title" class="hero__title kw rise rise--1">飲食店の<wbr>煙・臭い・換気、<br>専門会社が<wbr>設計から<wbr>施工まで。</h1>
    <span class="hero__rule" aria-hidden="true"></span>
    <p class="hero__lead rise rise--2">焼肉店・焼き鳥店・居酒屋・ラーメン店。油と煙の多い厨房の換気扇・排煙ダクト工事を、福岡から全国へ。創業2005年、累計1,000件以上の施工実績があります。</p>
    <div class="hero__actions rise rise--3">
      <a class="btn btn--orange btn--lg" href="<?= e(tel_href(company('tel_free'))) ?>"><?= icon('phone', 22) ?><span><small>受付 <?= e(company('hours')) ?></small><?= e(company('tel_free')) ?></span></a>
      <a class="btn btn--glass btn--lg" href="<?= e(page_url('contact')) ?>">無料相談・現地調査を申し込む<?= icon('arrow', 18) ?></a>
    </div>
  </div>
</section>

<section class="stats" aria-label="野田の実績">
  <dl class="stats__grid">
    <div class="stat"><dt class="stat__cap">創業からの施工実績</dt><dd class="stat__num">1,000<small>件以上</small></dd></div>
    <div class="stat"><dt class="stat__cap">2005年 福岡で創業・飲食店の換気専門</dt><dd class="stat__num"><small class="pre">創業</small><?= $years ?><small>年</small></dd></div>
    <div class="stat"><dt class="stat__cap">福岡本社・広島出張所から</dt><dd class="stat__num">全国<small>対応</small></dd></div>
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

<section class="section wrap news" aria-labelledby="news-title">
  <div class="news__head">
    <p class="label">NEWS</p>
    <h2 class="h2" id="news-title">お知らせ</h2>
    <a class="more" href="<?= e(page_url('news')) ?>">一覧を見る<?= icon('arrow', 16) ?></a>
  </div>
  <ul class="news__list">
    <?php foreach ($feed as $f): ?>
      <li>
        <a class="news__row" href="<?= e($f['url']) ?>">
          <time datetime="<?= e($f['published_at']) ?>"><?= e(format_date($f['published_at'])) ?></time>
          <span class="chip <?= $f['type'] === 'case' ? 'chip--solid' : 'chip--outline' ?>"><?= $f['type'] === 'case' ? '施工事例' : 'お知らせ' ?></span>
          <span class="news__title"><?= e($f['title']) ?></span>
          <?= icon('arrow', 18, 'news__arrow') ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="section wrap" aria-labelledby="problems-title">
  <?php render('section-head', ['label' => 'PROBLEMS', 'title' => 'こんなお悩み、|ありませんか。', 'id' => 'problems-title']); ?>
  <ul class="grid grid--4 problems">
    <?php foreach ($problems as [$ic, $t, $d]): ?>
      <li>
        <div class="card problem">
          <span class="problem__icon"><?= icon($ic, 24) ?></span>
          <h3 class="problem__title"><?= e($t) ?></h3>
          <p class="problem__text"><?= e($d) ?></p>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="band" aria-labelledby="service-title">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'SERVICE', 'title' => 'ダクト工事の巧みな技', 'id' => 'service-title', 'lead' => '店舗の規模・業態・建物に合わせて、フードもダクトも一つひとつオーダーメイドで設計します。']); ?>
    <?php render('service-cards'); ?>
  </div>
</section>

<section class="section wrap" aria-labelledby="reason-title">
  <?php render('section-head', ['label' => 'REASON', 'title' => '野田が選ばれる理由', 'id' => 'reason-title']); ?>
  <div class="bento">
    <article class="bento__item bento__item--main">
      <span class="bento__bg-no" aria-hidden="true">01</span>
      <span class="bento__no">01</span>
      <h3 class="bento__title kw">飲食店専門だから、<br>煙・油・熱の<wbr>現場を<wbr>知っている</h3>
      <p class="bento__text">焼肉・焼き鳥・ラーメンなど油煙の多い厨房を20年以上。一般のダクト業者では難しい「店に合った」給排気の設計ができます。</p>
    </article>
    <article class="bento__item bento__item--card">
      <span class="bento__no">02</span>
      <h3 class="bento__title kw">提案・設計・製作・施工を<wbr>自社で一貫</h3>
      <p class="bento__text">外注をはさまないので、要望がそのまま形になり、責任の所在も明確です。</p>
    </article>
    <article class="bento__item bento__item--card">
      <span class="bento__no">03</span>
      <h3 class="bento__title">すべてオーダーメイド設計</h3>
      <p class="bento__text">梁・低い天井・ビルの高層階など難しい現場ほど、経験の差が出ます。</p>
    </article>
    <article class="bento__item bento__item--wide">
      <div class="bento__copy">
        <span class="bento__no">04</span>
        <h3 class="bento__title kw">工事後も続く<wbr>アフターフォロー</h3>
        <p class="bento__text">点検・清掃・ファン交換まで。ダクト火災を防ぐ定期メンテナンスをご提案します。</p>
        <a class="more" href="<?= e(page_url('afterfollow')) ?>">アフターフォローについて<?= icon('arrow', 16) ?></a>
      </div>
      <div class="bento__img"><?= picture('top-afterfollow.jpg', 900, 600, '') ?></div>
    </article>
  </div>
</section>

<section class="section wrap" aria-labelledby="works-title">
  <?php render('section-head', ['label' => 'WORKS', 'title' => '最新の|施工事例', 'id' => 'works-title', 'link' => ['施工事例をもっと見る', page_url('construction_examples')]]); ?>
  <ul class="grid grid--3 cases">
    <?php foreach ($cases as $case): ?>
      <li><?php render('case-card', ['case' => $case]); ?></li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="band" aria-labelledby="flow-title">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'FLOW', 'title' => 'お問い合わせから|施工までの流れ', 'id' => 'flow-title', 'link' => ['詳しい流れを見る', page_url('flow')]]); ?>
    <ol class="steps">
      <?php foreach ($steps as $i => [$t, $d]): ?>
        <li class="step">
          <span class="step__dot" aria-hidden="true"><?= $i + 1 ?></span>
          <span class="step__label">STEP <?= $i + 1 ?></span>
          <h3 class="step__title"><?= e($t) ?></h3>
          <p class="step__text"><?= e($d) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="section wrap" aria-labelledby="partner-title">
  <div class="partner">
    <span class="partner__icon"><?= icon('blueprint', 28) ?></span>
    <div class="partner__text">
      <p class="label">FOR PARTNERS</p>
      <h2 class="partner__title" id="partner-title">設計事務所・内装施工業者様へ</h2>
      <p>給排気計算、フード・ダクトの設計相談、出店基準をクリアする煙対策プランのご相談を承ります。</p>
    </div>
    <a class="btn btn--blue" href="<?= e(page_url('customer')) ?>">業者様向けページへ<?= icon('arrow', 18) ?></a>
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
  <?php if ($ig[0]['fallback']): ?>
    <p class="ig__cta"><a class="btn btn--outline" href="<?= e(company('instagram_url')) ?>" target="_blank" rel="noopener"><?= icon('instagram', 18) ?>Instagram で見る</a></p>
  <?php endif; ?>
</section>
