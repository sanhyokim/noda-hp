<?php
render('page-head', [
    'label' => 'FLOW',
    'title' => 'お問い合わせから|施工までの流れ',
    'lead' => '現地調査とお見積りは無料です。最初の打ち合わせから工事後まで、同じ担当者が対応します。',
    'image' => 'flow-consult.jpg', 'ratio' => [3, 2],
]);
$steps = [
    ['お問い合わせ', '電話・メールフォーム・LINE。煙の写真があると話が早く進みます。'],
    ['現地調査・お打ち合わせ（無料）', '店舗の状況・建物・厨房機器を確認。'],
    ['ご提案・お見積り', '導入・取り替えプランとお見積書。計算書・設計図・施工図などの資料のみのご依頼も可能（別途費用）。'],
    ['ご契約', ''],
    ['詳細資料と工程の確認', '詳細資料と工程表をお渡しし、再度打ち合わせ。'],
    ['施工', '着工後の仕様変更にも、可能な限り柔軟に対応。'],
    ['工事完了・最終チェック', '立ち会いのもと運転・機能を確認し、取り扱いをご説明。'],
    ['アフターフォロー', '同じ担当者が対応。不具合の際は迅速に駆けつけます。'],
];
$faq = [
    ['工事にかかる費用はどのくらいでしょうか？', '建物の構造・設置場所・用途で大きく異なるため、現地調査（無料）のうえお見積りします。'],
    ['こちらの予算に合わせた提案をしていただけますか？', 'はい。ご予算に応じて柔軟にご提案します。'],
    ['見積もりをお願いしたいのですが……。', '新規オープンの支援から、既存店のダクト入れ替え・修理・清掃、排煙・油煙のご相談まで承ります。お問い合わせページからご連絡ください。'],
];
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => '8 STEPS', 'title' => '8つのステップ', 'id' => 's1']); ?>
  <ol class="timeline">
    <?php foreach ($steps as $i => [$t, $d]): ?>
      <li class="timeline__item">
        <span class="timeline__dot" aria-hidden="true"><?= $i + 1 ?></span>
        <div class="timeline__body">
          <span class="timeline__label">STEP <?= $i + 1 ?></span>
          <h3><?= e($t) ?></h3>
          <?php if ($d !== ''): ?><p><?= e($d) ?></p><?php endif; ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'FAQ', 'title' => 'よくある質問', 'id' => 's2', 'link' => ['質問をもっと見る', page_url('question')]]); ?>
    <?php render('faq-list', ['items' => $faq]); ?>
  </div>
</section>
