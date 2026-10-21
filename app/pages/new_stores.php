<?php
render('page-head', [
    'label' => 'NEW STORES',
    'title' => '新規開業の換気・ダクトは、|図面の段階で|ご相談ください',
    'lead' => '飲食店の新規開業では、換気扇・排煙ダクトの設計を後回しにすると近隣トラブルや営業への支障につながります。図面の段階から給排気のバランスを計算し、開業後の煙・臭い・吸い込み不良を防ぎます。',
    'image' => 'svc-newstore.jpg', 'ratio' => [3, 2],
]);
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'BEFORE OPENING', 'title' => '開業前に決めておくべき|3つのこと', 'id' => 's1']); ?>
  <?php render('cards', ['cols' => 3, 'numbered' => true, 'items' => [
      ['吸引力の計算', ''], ['給排気のバランス', ''], ['排気口の位置と|近隣への配慮', ''],
  ]]); ?>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'TROUBLES', 'title' => '後回しにすると起きるトラブル', 'id' => 's2']); ?>
    <div class="split">
      <ol class="numlist">
        <li><h3>油煙で近隣の外壁が汚れた</h3><p>揚げ物中心の店。原因はメンテナンス不足。</p></li>
        <li><h3>煙と臭いで近隣が窓を開けられない</h3><p>焼鳥店。排気口が住宅に近かったため、位置変更工事で解決。</p></li>
        <li><h3>臭いが洗濯物につく</h3><p>焼肉店。高性能機器への入れ替えで解決。</p></li>
      </ol>
      <div class="split__img"><?= picture('flow-consult.jpg', 900, 600, '') ?></div>
    </div>
  </div>
</section>

<section class="section wrap" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'WHAT WE DO', 'title' => '野田ができること', 'id' => 's3']); ?>
  <p class="prose">機器の提案・設計・設置・既存ダクトの入れ替え・メンテナンス・清掃まで一貫。</p>
</section>

<section class="section wrap" aria-labelledby="s4">
  <div class="partner">
    <span class="partner__icon"><?= icon('blueprint', 28) ?></span>
    <div class="partner__text">
      <p class="label">FOR PARTNERS</p>
      <h2 class="partner__title" id="s4">設計事務所・内装業者様との連携も可能</h2>
    </div>
    <a class="btn btn--blue" href="<?= e(page_url('customer')) ?>">業者様向けページへ<?= icon('arrow', 18) ?></a>
  </div>
</section>
