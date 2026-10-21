<?php
/** @var ?string $lastKey 'afterfollow' なら6枚目をアフターフォローにする（既定は店舗・飲食店のダクト工事について） */
// 説明文は各ページの冒頭文と同じ（現行サイトの各ページの内容から。Boss の指摘で一文から戻した 2026-10-21）
$services = [
    ['duct-hood', 'svc-kitchen-hood.jpg', '厨房換気扇・|レンジフードの|ダクト工事', '厨房が暑い、エアコンが効かない、煙がこもる、ドアが重い。原因の多くは給排気のバランスです。厨房機器の熱量から排気量を計算し、店舗ごとにオーダーメイド設計します。'],
    ['smoke_problems', 'svc-smoke.jpg', '煙・臭いの|トラブル解決工事', '客席に煙がこもる、近隣から臭いの苦情が来た。原因調査のうえ、消煙機（電気集塵機）の導入、ダクトの延長・位置変更、排気口の移設で根本から解決します。'],
    ['hood-installation', 'svc-yakiniku-hood.jpg', '焼肉店 |上引きフードの|ダクト工事', '自社特許（2件）の上引きフード「NC-02クリアーフード」。少ない排煙量で従来型と同等の吸煙力、工事費は当社従来型比で約40%減、冷暖房費も節約。スリムで客席の視界を妨げません。'],
    ['smokeless_roster', 'svc-roaster.jpg', '焼肉店 |無煙ロースターの|ダクト工事', '業界初の特許取得「NEOシリーズ NC-22熔岩石」無煙ロースターの導入とダクト工事。炎の燃え上がりを抑えてダクト火災を防ぎ、富士山産熔岩石の遠赤外線でジューシーに。デモ機で試してから導入できます。'],
    ['new_stores', 'svc-newstore.jpg', '新規店舗の開業を|お考えの方へ', '飲食店の新規開業では、換気扇・排煙ダクトの設計を後回しにすると近隣トラブルや営業への支障につながります。図面の段階から給排気のバランスを計算し、開業後の煙・臭い・吸い込み不良を防ぎます。'],
    ['about_duct_work', 'svc-duct-general.jpg', '店舗・飲食店の|ダクト工事について', 'ダクト工事は「吸わない」も「吸いすぎる」も失敗です。原因の多くは給排気の選定ミス。店の規模・業態・建物に合わせた設計が必要です。'],
];
if (($lastKey ?? '') === 'afterfollow') {
    $services[5] = ['afterfollow', 'top-afterfollow.jpg', 'ダクト工事後の|アフターフォロー', '工事後も同じ担当者が保守点検・修理・清掃まで対応。油汚れを放置すると吸い込み低下・異音・ダクト火災の原因になります。'];
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
