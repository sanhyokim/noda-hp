<?php
render('page-head', [
    'label' => 'CONCEPT',
    'title' => '飲食店の換気を、|設計から施工・その後まで。',
    'lead' => '株式会社野田は飲食店のダクト工事に特化した専門会社です。提案・設計・製作・施工・メンテナンスまで自社一貫、1mm単位のオーダーメイド設計、施工後のアフターフォローまで。福岡から全国の飲食店を支えます。',
    'image' => 'concept-workshop.jpg', 'ratio' => [16, 9],
]);
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'OUR COMMITMENT', 'title' => '私たちの4つのこだわり', 'id' => 's1']); ?>
  <ol class="commit">
    <li class="commit__item">
      <div class="commit__img"><?= picture('svc-yakiniku-hood.jpg', 900, 600, '焼肉店の客席に施工した上引きフード') ?></div>
      <h3 class="commit__head"><span class="commit__no">01</span>飲食店のダクト工事に特化している</h3>
      <p>油・煙・熱の多い厨房を20年以上。新規開業も既存店の入れ替えも。</p>
    </li>
    <li class="commit__item">
      <div class="commit__img"><?= picture('flow-consult.jpg', 900, 600, '天井内に通した断熱材付きのダクト') ?></div>
      <h3 class="commit__head"><span class="commit__no">02</span>提案・設計・製作・施工・メンテナンスを自社で一貫</h3>
      <p>外注なし。責任が明確で、要望がそのまま形になる。</p>
    </li>
    <li class="commit__item">
      <div class="commit__img"><?= picture('concept-drawing.jpg', 900, 600, '店の内装に合わせて木で仕上げたフード') ?></div>
      <h3 class="commit__head"><span class="commit__no">03</span>すべてフルオーダーメイド設計</h3>
      <p>1mm単位で寸法調整。施工中の設計変更にも対応。</p>
    </li>
    <li class="commit__item">
      <div class="commit__img"><?= picture('top-afterfollow.jpg', 900, 600, '点検しやすく仕上げたフード下のダクト') ?></div>
      <h3 class="commit__head"><span class="commit__no">04</span>施工後のアフターフォローまでまとめて</h3>
      <p>保守点検・修理・清掃・洗剤の選定まで。</p>
    </li>
  </ol>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'AREA', 'title' => '対応エリア', 'id' => 's2']); ?>
    <div class="area">
      <p class="area__lead">福岡本社と広島出張所を拠点に、<br>九州・中国地方を中心に全国対応。</p>
      <dl class="area__list">
        <div><dt>福岡本社</dt><dd>〒<?= e(company('hq_zip')) ?> <?= e(company('hq_address')) ?></dd></div>
        <div><dt><?= e(company('branch_name')) ?></dt><dd>〒<?= e(company('branch_zip')) ?> <?= e(company('branch_address')) ?></dd></div>
      </dl>
    </div>
  </div>
</section>
