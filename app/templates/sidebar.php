<?php
/** @var string $current */
$navLink = function (string $key, ?string $label = null, bool $sub = false) use ($current): string {
    $p = page($key);
    $isCur = $key === $current;
    return '<a class="nav__link' . ($sub ? ' nav__link--sub' : '') . '" href="' . e(page_url($key)) . '"'
        . ($isCur ? ' aria-current="page"' : '') . '>' . e($label ?? ($p['nav'] ?? $p['name'])) . '</a>';
};
?>
<aside id="sidebar" class="sidebar" aria-label="サイトメニュー">
  <div class="sidebar__inner">
    <button class="sidebar__close" type="button" aria-label="メニューを閉じる"><?= icon('close', 24) ?></button>

    <a class="sidebar__logo" href="<?= e(url()) ?>" aria-label="<?= e(company('name')) ?> ホーム">
      <?= picture('logo.png', 240, 78, company('name')) ?>
    </a>

    <div class="sidebar__tel">
      <span class="sidebar__tel-label">お電話でのご相談 <?= e(company('hours')) ?></span>
      <a class="sidebar__tel-num" href="<?= e(tel_href(company('tel_free'))) ?>"><?= icon('phone', 20) ?><?= e(company('tel_free')) ?></a>
    </div>

    <a class="btn btn--blue btn--block" href="<?= e(page_url('contact')) ?>"><?= icon('mail', 18) ?>メール・LINEで相談する</a>

    <nav class="nav" aria-label="サイト内メニュー">
      <ul>
        <?php foreach (site('nav') as $item): ?>
          <li>
            <?= $navLink($item['key'], $item['label'] ?? null) ?>
            <?php if (!empty($item['children'])): ?>
              <ul class="nav__sub">
                <?php foreach ($item['children'] as $child): ?>
                  <li><?= $navLink($child, null, true) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="sidebar__sns">
      <a class="btn btn--line" href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener"><?= icon('chat', 18) ?>LINE</a>
      <a class="btn btn--ig" href="<?= e(company('instagram_url')) ?>" target="_blank" rel="noopener"><?= icon('instagram', 18) ?>Instagram</a>
    </div>

    <address class="sidebar__company">
      <strong><?= e(company('name')) ?></strong>
      〒<?= e(company('hq_zip')) ?><br><?= e(company('hq_address')) ?><br>
      <span class="sidebar__branch"><?= e(company('branch_name')) ?></span><br>
      〒<?= e(company('branch_zip')) ?><br><?= e(company('branch_address')) ?><br>
      TEL <?= e(company('tel_free')) ?> / <?= e(company('tel')) ?><br>
      FAX <?= e(company('fax')) ?>
    </address>

    <div class="sidebar__banners">
      <?php foreach (site('banners') as $b): ?>
        <a class="banner" href="<?= e($b['url']) ?>" target="_blank" rel="noopener">
          <?= picture($b['image'], 480, 240, $b['alt']) ?>
          <span class="banner__label"><?= e($b['alt']) ?><?= icon('external', 14) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</aside>
