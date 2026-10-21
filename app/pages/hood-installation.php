<?php
render('page-head', [
    'label' => 'YAKINIKU HOOD',
    'title' => '焼肉店 上引きフードの|ダクト工事',
    'lead' => '自社特許（2件）の上引きフード「NC-02クリアーフード」。少ない排煙量で従来型と同等の吸煙力、工事費は当社従来型比で約40%減、冷暖房費も節約。スリムで客席の視界を妨げません。',
    'image' => 'svc-yakiniku-hood.jpg', 'ratio' => [3, 2],
]);
?>
<p class="wrap note">※ 特許番号 3460996 / 3460998。約40%減は当社従来品「しちりんフード」との比較です。</p>

<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'NC-02', 'title' => 'NC-02クリアーフードの|4つの強み', 'id' => 's1']); ?>
  <?php render('cards', ['cols' => 4, 'numbered' => true, 'items' => [
      ['煙をしっかり吸う', '排煙量182m³/hで、従来型の500m³/hに匹敵する吸煙力。'],
      ['工事費を抑える', '当社従来の「しちりんフード」と比べ約40%削減。'],
      ['冷暖房費を節約', '排煙量が少ないので、店内の空気を捨てすぎない。'],
      ['スリムで|邪魔にならない', '格納式でコンパクト。客席の視界と会話を妨げない。'],
  ]]); ?>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'WHY', 'title' => 'なぜ吸えるのか', 'id' => 's2']); ?>
    <p class="prose">大きなフードは煙の上昇気流を妨げます。気流を邪魔しない形と、自社製ファンで細いシンプルなダクトが使える設計です。</p>
  </div>
</section>

<section class="section wrap" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'LINEUP', 'title' => 'Aタイプ・Bタイプと、|その他のフード', 'id' => 's3', 'lead' => 'NC-02以外のフードも取り扱い。予算に応じて提案します。内装に合わせた特注塗装も可能です。']); ?>
  <div class="grid grid--2">
    <figure class="product"><?= picture('product-nc02-a.jpg', 900, 600, 'NC-02クリアーフード Aタイプ') ?><figcaption>Aタイプ</figcaption></figure>
    <figure class="product"><?= picture('product-nc02-b.jpg', 900, 600, 'NC-02クリアーフード Bタイプ') ?><figcaption>Bタイプ</figcaption></figure>
  </div>
</section>

<section class="section wrap" aria-labelledby="s4">
  <?php render('section-head', ['label' => 'VOICE', 'title' => 'お客様の声', 'id' => 's4']); ?>
  <?php render('voice', ['name' => 'ガブクロ様', 'meta' => '福岡市早良区西新・黒毛和牛焼肉', 'text' => '移転後、前の業者の工事で店内に煙が充満し大きなトラブルに。野田が既存ダクトを全撤去し、基準値に基づいて再設計、使える排煙器具は再利用して施工。煙とクレームが解消。']); ?>
</section>

<?php render('related', ['names' => ['焼肉 上引きフード']]); ?>

<p class="wrap note note--foot">特許番号 3460996 / 3460998</p>
