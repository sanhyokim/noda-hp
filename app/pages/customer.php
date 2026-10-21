<?php
render('page-head', [
    'label' => 'FOR PARTNERS',
    'title' => '設計事務所・|内装施工業者様へ',
    'lead' => '飲食店の換気・排煙の部分を、野田が設計から施工・アフターまで引き受けます。',
    'image' => 'partner-blueprint.jpg', 'ratio' => [16, 9],
]);
$meta['cta_text'] = '図面を添えてお送りください。給排気計算からご提案します。';
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'SCENES', 'title' => 'こんな場面でお役に立てます', 'id' => 's1']); ?>
  <?php render('cards', ['cols' => 3, 'numbered' => true, 'items' => [
      ['給排気計算', ''],
      ['出店基準をクリアする|煙対策プラン', ''],
      ['上引きフード・無煙ロースターの|設置工事', ''],
      ['施工後の煙漏れ・ダクト騒音・|近隣苦情の調査と改善提案', ''],
      ['操作・メンテナンスの指導、|故障時の対応', ''],
  ]]); ?>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'FLOW', 'title' => 'ご依頼の流れ', 'id' => 's2']); ?>
    <ol class="chain chain--5">
      <?php foreach (['図面を共有', '計算・提案', 'お見積り（無料）', '施工', '操作・メンテナンス指導'] as $i => $t): ?>
        <li><span><?= sprintf('%02d', $i + 1) ?></span><?= e($t) ?></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<?php render('related', ['names' => ['大型店・チェーン店'], 'title' => '業者様とのお取引事例']); ?>
