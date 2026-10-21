<?php
render('page-head', [
    'label' => 'KITCHEN HOOD',
    'title' => '厨房換気扇・レンジフードの|ダクト工事',
    'lead' => '厨房が暑い、エアコンが効かない、煙がこもる、ドアが重い。原因の多くは給排気のバランスです。厨房機器の熱量から排気量を計算し、店舗ごとにオーダーメイド設計します。',
    'image' => 'svc-kitchen-hood.jpg', 'ratio' => [3, 2],
]);
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'SYMPTOMS', 'title' => 'こんな症状はありませんか', 'id' => 's1']); ?>
  <?php render('bullets', ['icon' => 'alert', 'items' => [
      '新規でどれを選べばいいか分からない', '厨房が非常に暑い', 'エアコンの効きが悪い', '煙や臭いがこもる・客席が油でべたつく', 'ドアが重くて開け閉めしにくい',
  ]]); ?>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'CAUSE', 'title' => '原因は「吸わない」か|「効きすぎ」のどちらか', 'id' => 's2']); ?>
    <?php render('cards', ['cols' => 2, 'items' => [
        ['吸わない', '厨房機器の熱量計算の不足、取り付け位置、ファンの選定ミス。'],
        ['効きすぎる', 'ドアが重い、「ヒューヒュー」と音がする、エアコンが効かず電気代が上がる。'],
    ]]); ?>
  </div>
</section>

<section class="section wrap" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'DESIGN', 'title' => '野田の設計の進め方', 'id' => 's3']); ?>
  <p class="prose">店舗の構造・規模・空調・レイアウト・要望から設計。完全オーダーメイド、1mm単位で変更可。既存店の相談も可。</p>
</section>

<section class="section wrap" aria-labelledby="s4">
  <?php render('section-head', ['label' => 'VOICE', 'title' => 'お客様の声', 'id' => 's4']); ?>
  <?php render('voice', ['name' => '博多一双様', 'meta' => '福岡市内3店舗・とんこつラーメン', 'text' => '以前2社に依頼しても改善せず。既存ダクトを全て撤去し設計から見直した結果、臭いが大幅に改善し、厨房の熱気も排出。臭いの苦情はゼロに。']); ?>
</section>

<?php render('related', ['names' => ['厨房フード']]); ?>
