<?php
/** @var ?string $lastKey 'afterfollow' なら6枚目をアフターフォローにする（既定は店舗・飲食店のダクト工事について） */
$services = [
    ['duct-hood', 'svc-kitchen-hood.jpg', '厨房換気扇・|レンジフードの|ダクト工事', '給気と排気のバランスを取った厨房フードの新設・改修。'],
    ['smoke_problems', 'svc-smoke.jpg', '煙・臭いの|トラブル解決工事', '近隣苦情・店内の煙だまりを、原因調査から根本解決。'],
    ['hood-installation', 'svc-yakiniku-hood.jpg', '焼肉店 |上引きフードの|ダクト工事', '客席ごとの上引きフードを店の内装に合わせて特注製作。'],
    ['smokeless_roster', 'svc-roaster.jpg', '焼肉店 |無煙ロースターの|ダクト工事', '床下ダクト・点検口・防火対策まで含めた一式施工。'],
    ['new_stores', 'svc-newstore.jpg', '新規店舗の開業を|お考えの方へ', '図面段階からの換気設計で、開業後のトラブルを防ぎます。'],
    ['about_duct_work', 'svc-duct-general.jpg', '店舗・飲食店の|ダクト工事について', 'ダクト工事の基本と、専門業者に頼むべき理由。'],
];
if (($lastKey ?? '') === 'afterfollow') {
    $services[5] = ['afterfollow', 'top-afterfollow.jpg', 'ダクト工事後の|アフターフォロー', '保守点検・修理・清掃まで、同じ担当者が対応。'];
}
?>
<ul class="svcs">
  <?php foreach ($services as $i => [$key, $img, $t, $d]): ?>
    <li>
      <a class="svc" href="<?= e(page_url($key)) ?>">
        <span class="svc__head">
          <span class="svc__title kw"><?= str_replace('|', '<wbr>', e($t)) ?></span>
          <span class="svc__no" aria-hidden="true">CASE <?= sprintf('%02d', $i + 1) ?></span>
        </span>
        <span class="svc__img"><?= picture($img, 900, 600, '') ?></span>
        <span class="svc__text"><?= e($d) ?></span>
        <span class="svc__more">詳しくはこちら<?= icon('arrow', 16) ?></span>
      </a>
    </li>
  <?php endforeach; ?>
</ul>
