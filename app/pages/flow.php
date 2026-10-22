<?php
// 人物のAI画像は現行サイトと同じ場所に一時的に戻している（Boss 2026-10-22。Boss が1枚ずつ差し替えを依頼する）。ファイル名は ai-*.jpg
// 差し替え済み：STEP2「現場調査〜お打ち合わせ」→ flow-survey.jpg（Boss 選定 2026-10-22）、STEP7「工事完了・最終チェック」→ flow-check.jpg（同日）、STEP6「施工開始」→ flow-start.jpg（同日）、STEP8「アフターフォロー」→ flow-aftercare.jpg（同日、16:9 を3:2枠で左右を少し切る）
render('page-head', [
    'label' => 'FLOW',
    'title' => 'お問い合わせから|施工までの流れ',
    'lead' => 'お問い合わせから工事完了、その後のアフターフォローまで、8つのステップでご案内します。現場調査とお見積りは無料です。',
    'image' => 'flow-consult.jpg', 'ratio' => [3, 2],
]);
// 各ステップの文章は現行ページ https://duct-noda.com/flow/ の全文（2026-10-21）。
$steps = [
    ['お問い合わせ', 'ai-flow-call.jpg', ['お気軽にお電話、メールフォーム、またはLINEからご連絡ください。'], ['新規飲食店の開業を検討中の方', '既存店舗で煙や臭いに関するトラブルやお悩みを抱えている方', '店外への煙や臭い漏れが原因でトラブルやクレームを経験された方', 'ホール内に煙や臭いが充満し、集客に不安を感じている方']],
    ['現場調査〜お打ち合わせ', 'flow-survey.jpg', ['設置環境をチェックするために現場調査を実施し、その後、詳細なお打ち合わせをさせていただきます。現場調査は無料です。ご不明な点やご要望がございましたら、どうぞお気軽にお知らせください。'], []],
    ['ご提案・お見積もり', 'flow-estimate.jpg', ['調査結果とご要望に基づき、導入（または取り替え）プランとお見積もり書を作成し、詳しくご説明いたします。お客様の目的やご予算、環境に合わせた最適なご提案をいたします。', 'また、計算書や設計図、施工図など資料作成のみをご希望の場合は、別途費用を申し受けることで対応可能です。'], []],
    ['ご契約', 'flow-contract.jpg', ['ご提案やお見積もり内容にご納得いただけましたら、そのままご契約となります。'], []],
    ['詳細資料と工程の確認', 'flow-schedule.jpg', ['ご契約後、プランや施工に関する詳細な資料と工程表をご提示し、さらにお打ち合わせを行います。'], []],
    ['施工開始', 'flow-start.jpg', ['施工を開始いたします。着工後の仕様変更にもできる限り柔軟に対応いたしますので、どうぞお気軽にご相談ください。'], []],
    ['工事完了・最終チェック', 'flow-check.jpg', ['施工完了後は、お客様にご立ち会いいただき、実際にダクト設備を運転していただきながら機能のチェックを行います。同時に、取り扱い方法についても詳しくご説明いたしますので、何か気になる点がございましたら、どうぞお気軽にご質問ください。'], []],
    ['アフターフォロー', 'flow-aftercare.jpg', ['初回の打ち合わせからアフターフォローまで、一貫して同じ担当者が対応いたします。不具合やトラブルが発生した際には迅速に駆けつけますので、いつでもご連絡ください。'], []],
];
$faq = [
    ['工事にかかる費用はどのくらいでしょうか？', '建物の構造、設置場所、用途などにより異なります。まずはお気軽にご相談ください。現地調査とお見積りは無料です。'],
    ['こちらの予算に合わせた提案をしていただけますか？', 'もちろんです。ご予算に応じて柔軟に対応いたします。'],
    ['見積もりをお願いしたいのですが……。', 'お電話、メールフォーム、LINEからお気軽にご依頼ください。現地調査とお見積りは無料です。新規オープンのサポートから既存店舗のダクト入れ替えや修理、清掃まで対応しております。排煙・油煙に関するお悩みやトラブル回避のご相談もお気軽にどうぞ。'],
];
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => '8 STEPS', 'title' => '8つのステップ', 'id' => 's1']); ?>
  <ol class="timeline">
    <?php foreach ($steps as $i => [$t, $img, $ps, $list]): ?>
      <li class="timeline__item">
        <span class="timeline__dot" aria-hidden="true"><?= $i + 1 ?></span>
        <div class="timeline__body timeline__body--img">
          <div class="timeline__text">
            <span class="timeline__label">STEP <?= $i + 1 ?></span>
            <h3><?= e($t) ?></h3>
            <?php foreach ($ps as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
            <?php if ($list): ?>
              <p class="timeline__sub">こんな悩みをお持ちの方へ</p>
              <?php render('bullets', ['items' => $list]); ?>
            <?php endif; ?>
            <?php if ($i === 0): ?><a class="more" href="<?= e(page_url('contact')) ?>">お問い合わせページはこちら<?= icon('arrow', 16) ?></a><?php endif; ?>
            <?php if ($i === 7): ?><a class="more" href="<?= e(page_url('afterfollow')) ?>">アフターフォローについて<?= icon('arrow', 16) ?></a><?php endif; ?>
          </div>
          <div class="timeline__img"><?= picture($img, 600, 400, '') ?></div>
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
