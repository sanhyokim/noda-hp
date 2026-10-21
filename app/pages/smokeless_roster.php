<?php
render('page-head', [
    'label' => 'SMOKELESS ROASTER',
    'title' => '焼肉店 無煙ロースターの|ダクト工事',
    'lead' => '業界初の特許取得「NEOシリーズ NC-22熔岩石」無煙ロースターの導入とダクト工事。炎の燃え上がりを抑えてダクト火災を防ぎ、富士山産熔岩石の遠赤外線でジューシーに。デモ機で試してから導入できます。',
    'image' => 'svc-roaster.jpg', 'ratio' => [3, 2],
]);
?>
<p class="wrap note">※「業界初」：安全性と水蒸気効果で日本初の特許取得（特許番号 4620759）</p>

<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'NC-22', 'title' => 'NEOシリーズ NC-22熔岩石の|3つの特長', 'id' => 's1']); ?>
  <div class="split split--rev">
    <?php render('cards', ['cols' => 1, 'numbered' => true, 'items' => [
        ['安全性が高い', '鋳物ロストルが肉の炭化と炎の燃え上がりを抑え、ダクトへの引火を防ぐ。'],
        ['水蒸気効果で|みずみずしく焼ける', ''],
        ['富士山産の熔岩石で|遠赤外線が強い', '短時間で中からじっくり火が通る。'],
    ]]); ?>
    <div class="split__img"><?= picture('product-nc22.jpg', 900, 600, 'NEOシリーズ NC-22熔岩石') ?></div>
  </div>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'INSTALLATION', 'title' => '床下ダクト・点検口・|防火対策まで一式', 'id' => 's2', 'link' => ['施工事例を見る', page_url('construction_examples') . '?work=roaster']]); ?>
    <div class="grid grid--2">
      <div class="point">
        <p class="label">DEMO</p>
        <h3 class="h3-like kw">デモ機で試してから<wbr>決められます</h3>
        <p class="point__text">使い方・メンテナンス・網交換の手順・火災予防を実機で説明します。</p>
      </div>
      <div class="point">
        <p class="label">OTHER BRANDS</p>
        <h3 class="h3-like kw">他社製ロースターにも対応</h3>
      </div>
    </div>
  </div>
</section>

<section class="section wrap" aria-labelledby="s4">
  <?php render('section-head', ['label' => 'VOICE', 'title' => 'お客様の声', 'id' => 's4']); ?>
  <?php render('voice', ['name' => '焼肉食堂おおいり様', 'meta' => '福岡市西区・焼肉店', 'text' => '新規開店時、デモ機の実演と試食が決め手に。']); ?>
</section>

<?php render('related', ['names' => ['無煙ロースター']]); ?>

<p class="wrap note note--foot">特許番号 4620759（安全性と水蒸気効果で日本初の特許取得）</p>
