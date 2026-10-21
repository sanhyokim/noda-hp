<?php
/** @var string $current */
// 左カラム。現行サイトと同じく「電話の案内 → 見出し付きメニュー2つ → 会社情報 → バナー」の順。
// 1100px 未満ではメニューボタンで画面右から出す。
?>
<aside id="sidebar" class="sidebar" aria-label="サイトメニュー">
  <div class="sidebar__inner">
    <div class="sidebar__top">
      <a class="sidebar__logo" href="<?= e(url()) ?>" aria-label="<?= e(company('name')) ?> ホーム"><?= picture('logo.png', 240, 78, company('name')) ?></a>
      <button class="sidebar__close" type="button" aria-label="メニューを閉じる"><?= icon('close', 24) ?></button>
    </div>

    <div class="scall">
      <p class="scall__head">お電話でのご相談</p>
      <div class="scall__body">
        <a class="scall__num" href="<?= e(tel_href(company('tel_free'))) ?>"><?= icon('phone', 20) ?><?= e(company('tel_free')) ?></a>
        <p class="scall__hours">受付 <?= e(company('hours')) ?>（<?= e(company('closed')) ?>）</p>
        <a class="btn btn--blue btn--block" href="<?= e(page_url('contact')) ?>"><?= icon('mail', 18) ?>メールで相談する</a>
        <a class="btn btn--line btn--block" href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener"><?= line_icon(22) ?>LINEで相談する</a>
      </div>
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
        <a class="banner" href="<?= e($b['url']) ?>" target="_blank" rel="noopener"><?= picture($b['image'], 1200, 600, $b['alt'] . '（別サイトが開きます）') ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</aside>
