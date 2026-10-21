<?php
render('page-head', [
    'label' => 'COMPANY',
    'title' => '会社概要',
    'lead' => '株式会社野田は2005年11月設立。福岡市博多区本社、広島出張所。飲食店の厨房換気扇・排煙ダクトの設計施工、焼肉店向け上引きフード・無煙ロースターの導入、保守点検・改修を行います。',
]);
$rows = [
    ['商号', company('name')],
    ['代表者', '頃安 照義'],
    ['設立', '2005年11月'],
    ['本社', '〒' . company('hq_zip') . ' ' . company('hq_address')],
    [company('branch_name'), '〒' . company('branch_zip') . ' ' . company('branch_address')],
    ['電話', company('tel_free') . '・' . company('tel')],
    ['FAX', company('fax')],
    ['営業時間', company('hours')],
    ['事業内容', '空調設備工事、設計・プランニング、施工管理・設置工事、保守・定期点検、改修工事、技術コンサルティング'],
];
$mapQuery = rawurlencode(company('hq_address'));
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'MESSAGE', 'title' => 'ご挨拶', 'id' => 's1']); ?>
  <div class="greeting">
    <div class="greeting__img"><?= picture('company-ceo.jpg', 600, 800, '代表取締役 頃安照義') ?></div>
    <div class="greeting__text prose">
      <p>福岡を拠点に、九州・中国地方を中心に、飲食店向けの排煙ダクト・厨房換気設備を20年以上、設計・施工・メンテナンスまで自社一貫で行ってきました。</p>
      <p>店舗の快適さと、法令・近隣への配慮を両立させること。単なる設備工事業者ではなく「飲食店の環境づくりのパートナー」でありたいと考えています。</p>
      <p>これから開業される方も、今の店を良くしたい方も、お気軽にご相談ください。</p>
      <p class="greeting__sign"><small>代表取締役</small>頃安 照義</p>
    </div>
  </div>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'PROFILE', 'title' => '会社概要', 'id' => 's2']); ?>
    <table class="table">
      <tbody>
        <?php foreach ($rows as [$k, $v]): ?><tr><th scope="row"><?= e($k) ?></th><td><?= e($v) ?></td></tr><?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="section wrap" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'HISTORY', 'title' => '沿革', 'id' => 's3']); ?>
  <ol class="history">
    <li><time>2005年11月</time><span>設立</span></li>
    <li><time>2025年</time><span>創業20周年</span></li>
  </ol>
</section>

<section class="section wrap" aria-labelledby="s4">
  <?php render('section-head', ['label' => 'ACCESS', 'title' => 'アクセス', 'id' => 's4']); ?>
  <div class="split">
    <div class="map" data-map-src="https://www.google.com/maps?q=<?= e($mapQuery) ?>&amp;output=embed">
      <button class="map__btn btn btn--outline" type="button"><?= icon('pin', 18) ?>地図を表示する（Googleマップ）</button>
      <p class="map__addr">〒<?= e(company('hq_zip')) ?> <?= e(company('hq_address')) ?></p>
    </div>
    <div class="split__img"><?= picture('company-office.jpg', 900, 600, '株式会社野田 本社') ?></div>
  </div>
</section>

<section class="section wrap" aria-labelledby="s5">
  <?php render('section-head', ['label' => 'LINKS', 'title' => '関連サイト', 'id' => 's5']); ?>
  <ul class="grid grid--2">
    <?php foreach (site('banners') as $b): ?>
      <li><a class="card linkcard" href="<?= e($b['url']) ?>" target="_blank" rel="noopener"><span class="linkcard__img"><?= picture($b['image'], 480, 240, '') ?></span><span class="linkcard__t"><?= e($b['alt']) ?><?= icon('external', 16) ?></span></a></li>
    <?php endforeach; ?>
  </ul>
</section>
