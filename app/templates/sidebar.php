<?php
/** @var string $current */
// 左カラム。現行サイトと同じく「電話の案内 → 動画・施工事例バナー → 見出し付きメニュー2つ → 会社情報 → バナー」の順。
// 1100px 未満ではメニューボタンで画面右から出す。
?>
<aside id="sidebar" class="sidebar" aria-label="サイトメニュー">
  <div class="sidebar__inner">
    <div class="sidebar__top">
      <a class="sidebar__logo" href="<?= e(url()) ?>" aria-label="<?= e(company('name')) ?> ホーム"><?= picture('logo.png', 240, 60, company('name')) ?></a>
      <button class="sidebar__close" type="button" aria-label="メニューを閉じる"><?= icon('close', 24) ?></button>
    </div>

    <div class="scall">
      <p class="scall__head">お電話でのご相談</p>
      <div class="scall__body">
        <a class="scall__num" href="<?= e(tel_href(company('tel_free'))) ?>"><span class="scall__icon"><?= icon('phone', 16) ?></span><?= e(company('tel_free')) ?></a>
        <p class="scall__hours">受付 <?= e(company('hours')) ?>（<?= e(company('closed')) ?>）</p>
        <a class="btn btn--blue btn--block" href="<?= e(page_url('contact')) ?>"><?= icon('mail', 18) ?>メールで相談する</a>
        <a class="btn btn--line btn--block" href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener"><?= line_icon(22) ?>LINEで相談する</a>
      </div>
    </div>

    <?php $v = site('side_video'); $c = site('side_cases'); ?>
    <div class="sidebar__banners">
      <a class="banner svideo" href="https://www.youtube.com/watch?v=<?= e($v['youtube_id']) ?>" target="_blank" rel="noopener" data-yt="<?= e($v['youtube_id']) ?>" data-yt-title="<?= e($v['title']) ?>">
        <?= picture($v['image'], 640, 360, $v['title'] . '（動画を再生）') ?>
        <span class="svideo__play" aria-hidden="true"></span>
      </a>
      <a class="banner" href="<?= e(page_url('construction_examples')) ?>"><?= picture($c['image'], $c['w'], $c['h'], $c['alt']) ?></a>
      <?php // 特注フードのページへの案内（Boss 提案 2026-11-01、2026-11-12 素材の切り替わりで一新）。文字は画像に入れず重ねるので、途中で切れない
        $hbMat = materials(); $hbChips = array_slice($hbMat['teaser'] ?? [1, 2, 10, 3, 13, 7], 0, 6); ?>
      <a class="banner hb2" href="<?= e(page_url('custom-hood')) ?>">
        <span class="hb2__slides" aria-hidden="true">
          <?php foreach (['01', '02', '10', '03'] as $k): ?>
            <span class="hb2__slide"><?= material_pic('assets/img/material/side-' . $k . '.jpg', 520, 520, '', true) ?></span>
          <?php endforeach; ?>
        </span>
        <span class="hb2__en" aria-hidden="true">CUSTOM HOOD</span>
        <span class="hb2__text">
          <span class="hb2__title"><small>野田の</small>特注フード</span>
          <span class="hb2__chips" aria-hidden="true">
            <?php foreach ($hbChips as $no): ?><span class="hb2__chip"><?= material_pic('assets/img/material/' . sprintf('%02d', $no) . '-chip.jpg', 200, 200, '', true) ?></span><?php endforeach; ?>
          </span>
          <span class="hb2__sub"><span><b><?= (int) ($hbMat['total'] ?? 70) ?></b>種類以上の素材から</span><?= icon('arrow', 12) ?></span>
        </span>
      </a>
    </div>

    <nav class="snav" aria-label="サイト内メニュー">
      <?php foreach (site('side_groups') as $g): ?>
        <div class="snav__group">
          <p class="snav__title"><span><?= e($g['en']) ?></span><?= e($g['title']) ?></p>
          <ul>
            <?php foreach ($g['keys'] as $key): ?>
              <?php $p = page($key); ?>
              <li><a class="snav__link" href="<?= e(page_url($key)) ?>"<?= $key === $current ? ' aria-current="page"' : '' ?>><?= e($p['nav'] ?? $p['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </nav>

    <address class="sidebar__company">
      <strong><?= e(company('name')) ?></strong>
      <span class="sidebar__office">本社</span>
      〒<?= e(company('hq_zip')) ?><br><?= e(company('hq_address')) ?>
      <span class="sidebar__office"><?= e(company('branch_name')) ?></span>
      〒<?= e(company('branch_zip')) ?><br><?= e(company('branch_address')) ?>
      <dl class="sidebar__nums">
        <dt>TEL</dt><dd><?= e(company('tel_free')) ?><br><?= e(company('tel')) ?></dd>
        <dt>FAX</dt><dd><?= e(company('fax')) ?></dd>
        <dt>営業</dt><dd><?= e(company('hours')) ?>（<?= e(company('closed')) ?>）</dd>
      </dl>
    </address>

    <div class="sidebar__banners">
      <?php foreach (site('banners') as $b): ?>
        <a class="banner" href="<?= e($b['url']) ?>" target="_blank" rel="noopener"><?= picture($b['image'], $b['w'], $b['h'], $b['alt'] . '（別サイトが開きます）') ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</aside>
