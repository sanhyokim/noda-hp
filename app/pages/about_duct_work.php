<?php
render('page-head', [
    'label' => 'SERVICE',
    'title' => '店舗・飲食店の|ダクト工事について',
    'lead' => 'ダクト工事は「吸わない」も「吸いすぎる」も失敗です。原因の多くは給排気の選定ミス。店の規模・業態・建物に合わせた設計が必要です。',
    'image' => 'svc-duct-general.jpg', 'ratio' => [3, 2],
]);
$bizList = ['焼肉店', '焼き鳥店', '居酒屋', 'ラーメン店', 'うなぎ店', '鉄板焼き店', 'カフェ', 'パン屋', '旅館', 'チェーン店', '施設', 'セントラルキッチン', 'コインランドリー'];
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'SERVICES', 'title' => 'サービス一覧', 'id' => 's1']); ?>
  <?php render('service-cards', ['lastKey' => 'afterfollow']); ?>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'INDUSTRIES', 'title' => '対応している業態', 'id' => 's2']); ?>
    <ul class="tags">
      <?php foreach ($bizList as $b): ?><li><?= e($b) ?></li><?php endforeach; ?>
      <li class="tags__more">など</li>
    </ul>
  </div>
</section>
