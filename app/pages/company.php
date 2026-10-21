<?php
// ご挨拶と20周年の文章は現行サイト（会社概要ページと20周年のお知らせ）の全文（2026-10-21）。
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
    ['営業時間', company('hours') . '（' . company('closed') . '）'],
    ['事業内容', '空調設備工事、設計・プランニング、施工管理・設置工事、保守・定期点検、改修工事、技術コンサルティング'],
];
$mapQuery = rawurlencode(company('hq_address'));
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'MESSAGE', 'title' => 'ご挨拶', 'id' => 's1']); ?>
  <div class="greeting">
    <div class="greeting__img"><?= picture('company-ceo.jpg', 600, 800, '代表取締役 頃安照義') ?></div>
    <div class="greeting__text prose">
      <p>株式会社野田 代表の頃安照義でございます。当社のサイトにご訪問いただき、心より御礼申し上げます。</p>
      <p>私たちは福岡を拠点とし、九州一円および中国地方を中心に、<strong>飲食店専門の排煙ダクト・厨房換気設備の設計・施工・メンテナンスを20年以上、すべて自社一貫体制で提供</strong>してまいりました。この間、多くのお客様に支えられ、2025年に創業20周年を迎えることができました。これもひとえに、皆様の温かいご支援のおかげと深く感謝しております。</p>
      <p>私たちが大切にしているのは、「ただの設備工事」ではなく、<strong>飲食店の環境づくりのパートナーであること</strong>です。</p>
      <p><strong>オーナー様の夢とお客様の最高の時間を“空気”で支える</strong>──この覚悟を胸に、店舗の快適性と法令・近隣への配慮を両立しながら、これまで多くの現場でご信頼をいただいてきました。「野田に頼んで本当に良かった」と言っていただける瞬間こそ、私たちの誇りであり、最高の報酬です。</p>
      <p>これからお店を開業される方、あるいは既存店舗の改善をご検討のオーナー様──あと一歩踏み出すか迷っているなら、ぜひその想いをお聞かせください。私たちの経験と技術、そして情熱をすべて注ぎ込み、全力でお力になることをお約束します。</p>
      <p>一緒に、愛されるお店を創りましょう。</p>
      <p class="greeting__sign"><small>代表取締役</small>頃安 照義</p>
    </div>
  </div>
</section>

<section class="section wrap" aria-labelledby="s20">
  <?php render('section-head', ['label' => '20TH ANNIVERSARY', 'title' => '創業20周年の感謝を胸に、|さらなる飛躍を目指して', 'id' => 's20']); ?>
  <div class="anniv">
    <p class="anniv__mark" aria-hidden="true"><b>20</b><span>th<br>ANNIVERSARY</span></p>
    <div class="prose">
      <p>おかげさまで株式会社野田は、2025年に創業20周年を迎えました。この20年間、多くのお客様との出会いに恵まれ、共に成長させていただけたことは、私たちにとって最大の誇りです。これまでのご愛顧に、改めて厚く御礼申し上げます。</p>
      <p>私たちはこの節目を、単なる通過点ではなく「新たなスタートライン」と捉えています。20年で培った技術と信頼を礎に、時代の変化を捉え、皆様の期待を超える価値を創造し続けてまいります。</p>
      <p>21年目からの株式会社野田も、変わらぬご支援を賜りますようお願い申し上げます。</p>
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
  <?php // 本社の外観写真は載せない（ボス決定 2026-10-21） ?>
  <div class="map map--wide" data-map-src="https://www.google.com/maps?q=<?= e($mapQuery) ?>&amp;output=embed">
    <button class="map__btn btn btn--outline" type="button"><?= icon('pin', 18) ?>地図を表示する（Googleマップ）</button>
    <p class="map__addr">〒<?= e(company('hq_zip')) ?> <?= e(company('hq_address')) ?></p>
  </div>
</section>

<section class="section wrap" aria-labelledby="s5">
  <?php render('section-head', ['label' => 'LINKS', 'title' => '関連サイト', 'id' => 's5']); ?>
  <ul class="grid grid--2">
    <?php foreach (site('banners') as $b): ?>
      <li><a class="card linkcard" href="<?= e($b['url']) ?>" target="_blank" rel="noopener"><span class="linkcard__img"><?= picture($b['image'], $b['w'], $b['h'], '') ?></span><span class="linkcard__t"><?= e($b['alt']) ?><?= icon('external', 16) ?></span></a></li>
    <?php endforeach; ?>
  </ul>
</section>
