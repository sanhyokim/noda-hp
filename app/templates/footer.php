<footer class="footer">
  <div class="frame footer__inner">
    <div class="footer__brand">
      <a class="footer__logo" href="<?= e(url()) ?>" aria-label="<?= e(company('name')) ?> ホーム"><?= picture('logo.png', 240, 60, company('name')) ?></a>
      <p class="footer__desc">飲食店専門の換気扇・排煙ダクト工事会社。<br>福岡本社・広島出張所から、全国に対応します。</p>
      <p class="footer__tel"><a href="<?= e(tel_href(company('tel_free'))) ?>"><?= icon('phone', 18) ?><?= e(company('tel_free')) ?></a><span>受付 <?= e(company('hours')) ?>（<?= e(company('closed')) ?>）</span></p>
      <address class="footer__addr">
        本社 〒<?= e(company('hq_zip')) ?> <?= e(company('hq_address')) ?><br>
        <?= e(company('branch_name')) ?> 〒<?= e(company('branch_zip')) ?> <?= e(company('branch_address')) ?><br>
        TEL <?= e(company('tel')) ?>　FAX <?= e(company('fax')) ?>
      </address>
    </div>
    <nav class="footer__nav" aria-label="フッターメニュー">
      <?php foreach (site('side_groups') as $g): ?>
        <div>
          <p class="footer__head"><?= e($g['title']) ?></p>
          <ul>
            <?php foreach ($g['keys'] as $key): ?>
              <li><a href="<?= e(page_url($key)) ?>"><?= e(page($key)['nav'] ?? page($key)['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </nav>
  </div>
  <div class="frame footer__bottom">
    <a href="<?= e(page_url('privacypolicy')) ?>">プライバシーポリシー</a>
    <p class="footer__copy">Copyright © Noda INC. All Rights Reserved.</p>
  </div>
</footer>
