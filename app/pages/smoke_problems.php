<?php
// 現行サイトの人物AI画像（ai-*.jpg）は、2026-10-22 に Boss が選んだ写真へすべて差し替え済み。
// 差し替え済み：01 謝罪と説明 → smoke-trouble.jpg（4:3 を上寄りで3:2に切り、天井の黒ずみを残す）
// 文章は現行ページ https://duct-noda.com/smoke_problems/ の全文（お客様インタビューを含む、2026-10-21）。
// 写真は現行ページのものと、移行した施工事例（焼鳥八朔様）の写真。
render('page-head', [
    'label' => 'SMOKE & ODOR',
    'title' => '煙・臭いのトラブルを|改善する解決法とは',
    'lead' => 'どんなに料理が美味しくても、店内や近隣に煙が舞って迷惑をかけると、お店の印象や評判が大きく損なわれます。小さなトラブルであっても放置せず、すぐに改善することが重要です。',
    'image' => 'svc-smoke.jpg', 'ratio' => [3, 2],
]);
?>
<section class="section wrap" aria-labelledby="s1">
  <?php render('section-head', ['label' => 'TROUBLES', 'title' => 'このような経験をお持ちの|オーナー様はありませんか？', 'id' => 's1']); ?>
  <?php render('bullets', ['icon' => 'alert', 'items' => [
      'ホールの煙がひどくて、複数のお客様からクレームを受けた',
      '店舗から出る臭いが原因で、近隣の方々とトラブルになってしまった',
  ]]); ?>
</section>

<section class="band" aria-labelledby="s2">
  <div class="section wrap">
    <?php render('section-head', ['label' => 'SOLUTIONS', 'title' => '店舗の信用を回復するための|トラブル解決法', 'id' => 's2']); ?>
    <p class="prose u-mb">飲食店での煙や臭いのトラブルは、実際に少なくありません。そこで店舗の信用を回復するために重要になるのが、トラブルを招いた後の対応。トラブル後の対処が店舗の将来を左右するといっても過言ではありません。</p>
    <ol class="rows">
      <li class="row">
        <div class="row__img"><?= picture('smoke-trouble.jpg', 900, 600, '煙で黒ずんだ天井を見上げる店主とスタッフ') ?></div>
        <div class="row__body">
          <h3 class="row__head"><span class="row__no">01</span>謝罪と説明</h3>
          <p>トラブルが発生した際には、まず関係各所に誠心誠意謝罪することが重要です。その際、「どこに問題があったのか」「いつまでにどのように改善するのか」を具体的に説明することで、相手の理解を得やすくなります。そのような対策方法をアドバイスいたします。</p>
        </div>
      </li>
      <li class="row row--text">
        <div class="row__body">
          <h3 class="row__head"><span class="row__no">02</span>煙や臭いの浄化対策</h3>
          <p>業界トップクラスの集塵能力を持つ当社の消煙機を用いて、煙や臭いの原因となっているオイルミストを強力に除去します。これにより、店舗や店外の空気環境を大幅に改善し、お客様や従業員の健康被害を未然に防ぐだけでなく、床の滑りや衣服の汚損を防ぎます。</p>
          <p>また、設備機器へのオイルミストの付着を防ぐことで、機器の寿命を延ばし、メンテナンスコストの削減にも寄与します。</p>
          <ul class="figs">
            <li><figure><?= picture('smoke-esp.jpg', 228, 171, '消臭消煙対策の電気集塵機') ?><figcaption>消臭消煙対策<wbr>電気集塵機</figcaption></figure></li>
            <li><figure><?= picture('smoke-water.jpg', 228, 171, '油煙除去ウォーターフィルター') ?><figcaption>油煙除去<wbr>ウォーターフィルター</figcaption></figure></li>
          </ul>
        </div>
      </li>
      <li class="row row--text">
        <div class="row__body">
          <h3 class="row__head"><span class="row__no">03</span>ダクト交換・延長</h3>
          <p>トラブルの原因となる給排気ダクトの高性能なものへの交換工事や、風向きや周辺環境を考慮した煙や臭いの排出ダクトの位置変更工事を承っております。</p>
          <p>位置変更の際には、住宅のない方向（空き地や林など）へのダクト移設や、建物の最上階までのダクト延長などを行います。</p>
        </div>
      </li>
    </ol>
    <div class="callout u-mt">
      <h3>3つのトラブル解決法で再発を防止</h3>
      <p>当社では、これら3つを通してトラブルの再発防止に努めています。さまざまな換気扇・ダクト施工に関わった経験から迅速かつ正確にトラブルの原因を突き止め最適な改善方法をご提案しますので、ぜひご相談ください。</p>
    </div>
  </div>
</section>

<section class="section wrap" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'INTERVIEW', 'title' => 'お客様インタビュー', 'id' => 's3']); ?>
  <?php render('interview', [
      'name' => '焼鳥八朔',
      'image' => 'voice-hassaku-store.jpg', 'imageSize' => [400, 400],
      'info' => [['所在地', '福岡市中央区舞鶴2丁目7-15']],
      'link' => ['焼鳥八朔のInstagram', 'https://www.instagram.com/yakitori_hassaku/'],
      'title' => '近隣住民との煙トラブルを解消したい。',
      'qa' => [
          ['当社に工事を依頼しようと思われたきっかけは何ですか？', '開店当初より、焼き鳥を焼く際の煙が店外に漏れ、近隣住民からの苦情や交通信号が見えにくくなるといった問題が発生していました。決め手となったのは、福岡県環境汚染課からの改善指導があり、株式会社野田に依頼しました。'],
          ['どんなことを希望されましたか？', '住民説明会も開催されたため、株式会社野田に施工内容とその効果について詳細に説明していただきました。'],
          ['施工後の変化はどうでしたか？', ['福岡県環境汚染課の目視判定および臭気判定士による臭気判定をクリアしました。', '特に、煙で曇っていた周辺道路が全く煙らなくなり、煙に起因するトラブルは一切なくなりました。また、立ち上げ工事を行っていただいたことで、臭いに関する苦情もなくなりました。']],
      ],
      'work' => [
          '施工前に住民説明会の開催要望がありましたので、予定している設備の仕様書を用いて、設置後の改善計画を丁寧に説明いたしました。',
          '特に、消煙機の過去の施工事例や油煙除去率など、具体的な資料を基にご納得いただけるよう努めました。',
          '施工においては、集塵機の設置だけでなく、立ち上げ工事も行い、煙や臭気対策を万全にいたしました。',
      ],
      'photos' => [['hassaku-esp-front.jpg', 480, 640], ['hassaku-esp-close.jpg', 480, 640]],
  ]); ?>
</section>

<section class="section wrap" aria-labelledby="s4">
  <a class="feature" href="https://shoenki.duct-noda.com/" target="_blank" rel="noopener">
    <span class="feature__img"><?= picture('product-shoenki.jpg', 900, 600, '') ?></span>
    <span class="feature__body">
      <span class="label">SHOENKI</span>
      <span class="feature__title" id="s4">消煙機について詳しく</span>
      <span class="feature__text">業界トップクラスの集塵能力を持つ「野田の消煙機」は、別サイトで仕組みや導入事例をご紹介しています。</span>
      <span class="more">野田の消煙機を見る<?= icon('external', 16) ?></span>
    </span>
  </a>
</section>

<?php render('related', ['names' => ['煙・臭い対策', '消煙機']]); ?>
