<?php
// 施工事例の一覧（仮）。絞り込み・ページ送り・詳細ページは段階4で作り、このファイルを置き換える。
$cases = latest_cases(12);
render('page-head', [
    'label' => 'WORKS',
    'title' => '施工事例',
    'image' => 'after-grease.jpg', 'ratio' => [3, 2],
    'lead' => '焼肉店・焼き鳥店・居酒屋・ラーメン店など、これまでに手がけた飲食店の換気扇・排煙ダクト工事をご紹介します。',
]);
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'LATEST', 'title' => '最新の施工事例', 'id' => 's1']); ?>
  <ul class="grid grid--3 cases">
    <?php foreach ($cases as $case): ?>
      <li><?php render('case-card', ['case' => $case]); ?></li>
    <?php endforeach; ?>
  </ul>
  <p class="note">業態・工事の種類での絞り込みと、全168件の一覧・各事例の詳しいページは、次の段階で作ります。</p>
</section>
