<?php
// 素材サンプルへの案内（Boss「ほかのページでも紹介したい」2026-11-12）。
// 写真が素材ごとにゆっくり切り替わり、見本を押してもその素材になる。ボタンで特注フードのページの素材の欄へ。
$mat = materials();
$byNo = array_column($mat['items'], null, 'no');
$items = array_values(array_filter(array_map(fn($n) => $byNo[$n] ?? null, $mat['teaser'] ?? []))) ?: array_slice($mat['items'], 0, 6); // 案内では6種類だけ（全部は特注フードのページで）
$nn = fn(int $n): string => sprintf('%02d', $n);
$url = page_url('custom-hood') . '#material';
$id = 'mtz-' . e($id ?? 'title');
?>
<section class="section wrap" aria-labelledby="<?= $id ?>">
  <div class="mtz" data-mtz>
    <div class="mtz__img">
      <?php foreach ($items as $i => $m): ?>
        <span class="mtz__hood<?= $i === 0 ? ' is-on' : '' ?>"><?= material_pic('assets/img/material/' . $nn($m['no']) . '-hood.jpg', 900, 1200, $i === 0 ? '素材を変えた特注フードの仕上がりイメージ' : '', $i === 0) ?></span>
      <?php endforeach; ?>
      <span class="mtz__tag">仕上がりイメージ</span>
      <span class="mtz__name" aria-live="polite"><?= e(str_replace('|', ' ', $items[0]['name'])) ?></span>
    </div>
    <div class="mtz__body">
      <p class="mtz__en" aria-hidden="true">MATERIAL</p>
      <h2 class="mtz__title kw" id="<?= $id ?>"><b><?= $mat['total'] ?>種類以上</b>の素材で、<wbr>想いのままのフードを。</h2>
      <p class="mtz__lead">金・銅・黒・銀、色や模様まで。同じ形のフードでも、素材を変えるだけでお店の印象はがらりと変わります。野田の特注フードなら、内装とコンセプトにぴったりの一枚をお選びいただけます。</p>
      <ul class="mtz__chips">
        <?php foreach ($items as $i => $m): ?>
          <li><button class="mtz__chip<?= $i === 0 ? ' is-on' : '' ?>" type="button" data-i="<?= $i ?>" data-name="<?= e(str_replace('|', ' ', $m['name'])) ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="<?= e(str_replace('|', ' ', $m['name'])) ?>"><?= picture('assets/img/material/' . $nn($m['no']) . '-chip.jpg', 200, 200, '') ?></button></li>
        <?php endforeach; ?>
        <li class="mtz__more" aria-hidden="true">+<?= max(0, $mat['total'] - count($items)) ?></li>
      </ul>
      <a class="btn mtz__btn" href="<?= e($url) ?>">素材を選んでみる<?= icon('arrow', 18) ?></a>
    </div>
  </div>
</section>
