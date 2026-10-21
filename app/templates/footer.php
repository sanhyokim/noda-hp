<footer class="footer">
  <div class="wrap footer__inner">
    <nav aria-label="フッターメニュー">
      <ul class="footer__links">
        <?php foreach (site('footer') as $key): ?>
          <li><a href="<?= e(page_url($key)) ?>"><?= e(page($key)['nav'] ?? page($key)['name']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <p class="footer__copy">Copyright © Noda INC. All Rights Reserved.</p>
  </div>
</footer>
