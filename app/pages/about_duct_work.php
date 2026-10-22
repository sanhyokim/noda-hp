<?php
render('page-head', [
    'label' => 'SERVICE',
    'title' => '店舗・飲食店の|ダクト工事について',
    'lead' => 'ダクト工事は「吸わない」も「吸いすぎる」も失敗です。原因の多くは給排気の選定ミス。店の規模・業態・建物に合わせた設計が必要です。',
    'image' => 'svc-kitchen-hood.jpg', 'ratio' => [3, 2],
]);
// 各サービスの説明は現行ページ https://duct-noda.com/about_duct_work/ の全文。新規開業とアフターフォローは各ページの冒頭文（2026-10-21）。
// 写真も現行ページと同じもの（2026-10-22 に戻した）。消煙機だけは現行の写真が小さすぎるため、同じ消煙機の大きい写真。
$services = [
    ['duct-hood', 'svc-duct-general.jpg', '飲食店厨房の換気扇・レンジフードのダクト工事', ['厨房周りでは、「吸わない」や「効きすぎる」といった換気扇・ダクトの悩みがよく発生します。これは、適切な給排気ダクトを採用していないことが原因である場合が多いです。こうしたトラブルを防ぐためには、飲食店の規模やレイアウトに応じた最適な設計と機器の選定が不可欠です。'], [], '厨房換気扇・レンジフードをもっと詳しく'],
    ['smoke_problems', 'product-shoenki.jpg', '店舗での臭い・煙のトラブルのダクト工事', ['店舗から出る煙や臭いでお客様や近隣住民とトラブルが起きた場合、どのように対処するかご存知でしょうか？何より大切なのは、トラブル発生後の迅速かつ適切な対応です。対処方法が店舗の信用や将来を左右するため、予防対策とともに、万が一の際の対応策をあらかじめ検討しておくことが重要です。'], [], '煙・臭いのトラブルを改善する解決法とは'],
    ['hood-installation', 'contact-office.jpg', '焼肉店 上引きフードのダクト工事', ['従来品と比べ、当社の上引きフードには次の4つのメリットがあります。'], ['店内の空気をよりクリーンに保てる', '工事費用を抑制できる', '冷暖房費を削減できる', 'スリムでコンパクトな設計'], '他店との差別化を実現する上引きフード'],
    ['smokeless_roster', 'about-roaster.jpg', '焼肉店 無煙ロースターのダクト工事', ['安全性に優れ、水蒸気効果で食材をみずみずしく焼き上げる新型火山石無煙ロースター「NEOシリーズNC-22熔岩石」（特許取得）をご紹介いたします。各店舗に合わせたロースターのご提案・工事も承っておりますので、ぜひご相談ください。'], [], '特許を取得した無煙ロースター'],
    ['new_stores', 'svc-newstore.jpg', '新規店舗の開業をお考えの方へ', ['飲食店の新規開業では、換気扇・排煙ダクトの設計を後回しにすると近隣トラブルや営業への支障につながります。図面の段階から給排気のバランスを計算し、開業後の煙・臭い・吸い込み不良を防ぎます。'], [], '新規開業のご相談について'],
    ['afterfollow', 'top-afterfollow.jpg', 'ダクト工事後のアフターフォロー', ['工事完了がゴールではなく、保守点検、メンテナンス（修理）、清掃まで、施工後のお困りごとにもワンストップで対応します。打ち合わせからアフターフォローまで、同じ担当者が責任を持って対応いたします。'], [], 'アフターフォローについて'],
];
$bizList = [
    ['焼肉店', 'yakiniku'], ['焼き鳥店', 'yakitori'], ['居酒屋', 'izakaya'], ['ラーメン店', 'ramen'], ['うなぎ店', 'unagi'], ['鉄板焼き店', 'teppan'], ['カフェ', 'cafe'],
    ['パン屋', 'bakery'], ['旅館', 'ryokan'], ['チェーン店', 'chain'], ['施設', 'facility'], ['セントラルキッチン', 'central'], ['コインランドリー', 'laundry'],
];
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'OUR SERVICES', 'title' => '株式会社野田の|各種ダクト工事サービス', 'id' => 's1', 'lead' => '厨房換気扇・排煙ダクト工事に特化した福岡の専門業者「株式会社野田」では、店舗や飲食店向けのダクトの設計、施工、アフターフォローまでをワンストップで承っております。当社が提供する各種ダクト工事サービスをご紹介します。']); ?>
  <ol class="rows">
    <?php foreach ($services as $i => [$key, $img, $t, $ps, $list, $more]): ?>
      <li class="row">
        <div class="row__img"><?= picture($img, 900, 600, '') ?></div>
        <div class="row__body">
          <h3 class="row__head"><span class="row__no"><?= sprintf('%02d', $i + 1) ?></span><?= e($t) ?></h3>
          <?php foreach ($ps as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
          <?php if ($list): render('bullets', ['items' => $list]); endif; ?>
          <a class="more u-mt" href="<?= e(page_url($key)) ?>"><?= e($more) ?><?= icon('arrow', 16) ?></a>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'INDUSTRIES', 'title' => '対応している業態', 'id' => 's2']); ?>
    <p class="prose u-mb">居酒屋、ラーメン屋、焼鳥屋、焼肉店をはじめ、油煙・煙の多いあらゆる業態の厨房に対応しています。</p>
    <ul class="bizgrid">
      <?php foreach ($bizList as [$b, $img]): ?><li><?= picture('biz-' . $img . '.jpg', 160, 160, '') ?><span><?= e($b) ?></span></li><?php endforeach; ?>
      <li class="bizgrid__more">など</li>
    </ul>
  </div>
</section>
