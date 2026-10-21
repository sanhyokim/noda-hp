<?php
render('page-head', [
    'label' => 'FAQ',
    'title' => 'よくある質問',
    'lead' => '飲食店オーナー様からよくいただく質問にお答えします。質問を押すと答えが開きます。',
]);
$groups = [
    '工事について' => [
        ['対応していただけるのはどのようなダクトでしょうか？', '排気・給気ダクトの種類・形状・設置環境を問わず対応します。'],
        ['店舗の形状が特殊なのですが、対応していただけますか？', '特殊な形状でも、店舗に合った最適なダクトをご提案します。'],
        ['現在使用しているダクトの交換にも対応していただけますか？', '対応可能です。既存ダクトの入れ替え実績が多数あります。'],
        ['おすすめのダクトはありますか？', '居酒屋・ラーメン店・焼鳥店・焼肉店には、サビにくくメンテナンス回数を抑えられるステンレス製をおすすめしています。'],
    ],
    '費用・見積りについて' => [
        ['工事にかかる費用はどのくらいでしょうか？', '建物の構造・設置場所・用途で大きく異なるため、現地調査（無料）のうえお見積りします。'],
        ['こちらの予算に合わせた提案をしていただけますか？', 'はい。ご予算に応じて柔軟にご提案します。'],
        ['見積もりをお願いしたいのですが……。', '新規オープンの支援から、既存店のダクト入れ替え・修理・清掃、排煙・油煙のご相談まで承ります。お問い合わせページからご連絡ください。'],
    ],
    '製品・対応エリアについて' => [
        ['おすすめの上引きフードはありますか？', '自社特許の「NC-02クリアーフード」です。'],
        ['おすすめのロースターはありますか？', '特許取得の「NEOシリーズ NC-22熔岩石」です。'],
        ['メンテナンスや修理までお願いできますか？', '設計・施工から修理・清掃まで一括で対応します。'],
        ['対応エリアを教えてください。', '福岡・九州・中国地方を中心に、全国対応です。その他の地域もご相談ください。'],
    ],
];
$links = ['おすすめの上引きフードはありますか？' => 'hood-installation', 'おすすめのロースターはありますか？' => 'smokeless_roster'];
$ld = [];
$i = 0;
?>
<?php foreach ($groups as $g => $items): $i++; ?>
  <section class="section wrap" aria-labelledby="g<?= $i ?>">
    <?php render('section-head', ['label' => sprintf('Q%02d', $i), 'title' => $g, 'id' => 'g' . $i]); ?>
    <div class="faq">
      <?php foreach ($items as [$q, $a]): $ld[] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]]; ?>
        <details class="faq__item">
          <summary><span class="faq__q" aria-hidden="true">Q</span><span class="faq__qt"><?= e($q) ?></span><span class="faq__toggle" aria-hidden="true"></span></summary>
          <div class="faq__a"><span class="faq__al" aria-hidden="true">A</span><p><?= e($a) ?><?php if (isset($links[$q])): ?> <a href="<?= e(page_url($links[$q])) ?>"><?= e(page($links[$q])['name']) ?>のページへ</a><?php endif; ?></p></div>
        </details>
      <?php endforeach; ?>
    </div>
  </section>
<?php endforeach; ?>
<?php $meta['jsonld'][] = ['@type' => 'FAQPage', 'mainEntity' => $ld]; ?>
