<?php
render('page-head', ['label' => 'PRIVACY POLICY', 'title' => 'プライバシーポリシー', 'lead' => '株式会社野田の個人情報の取り扱いについて。']);
// 本文は現行ページ https://duct-noda.com/privacypolicy/ の内容をそのまま移す（取得後に差し替え）。
$articles = ['個人情報の取得と利用目的', '第三者提供', '管理', '開示・訂正・削除', 'Cookieとアクセス解析', '本ポリシーの変更'];
?>
<div class="section wrap">
  <div class="policy prose">
    <p class="notice">本文は現行サイトのページから移す準備中です（見出しのみ表示しています）。</p>
    <?php foreach ($articles as $i => $t): ?>
      <section class="policy__item"><h2><?= $i + 1 ?>. <?= e($t) ?></h2></section>
    <?php endforeach; ?>
    <section class="policy__item">
      <h2>お問い合わせ窓口</h2>
      <p><?= e(company('name')) ?><br>〒<?= e(company('hq_zip')) ?> <?= e(company('hq_address')) ?><br>TEL <?= e(company('tel')) ?></p>
    </section>
  </div>
</div>
