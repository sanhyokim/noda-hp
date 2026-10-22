<?php
/** @var array $rows */
/** @var int $total */
/** @var int $page */
/** @var int $pages */
$methods = ['phone' => '電話', 'email' => 'メール', 'line' => 'LINE'];
?>
<div class="bar">
  <a class="back" href="<?= e(admin_url('settings/')) ?>"><?= icon('arrow', 18, 'back__icon') ?>設定</a>
</div>
<h1 class="ttl ttl--form">お問い合わせの受信一覧</h1>
<p class="count">全部で<strong><?= number_format($total) ?></strong>件（新しい順）</p>

<?php if (!$rows): ?>
<div class="empty"><p>まだお問い合わせはありません。</p></div>
<?php else: ?>
<ul class="inq">
<?php foreach ($rows as $r): ?>
  <li>
    <details class="inq__item">
      <summary class="inq__sum">
        <time datetime="<?= e(date('c', strtotime($r['created_at']))) ?>"><?= e(date('Y.m.d H:i', strtotime($r['created_at']))) ?></time>
        <span class="inq__who"><?= e($r['name']) ?><?= $r['company'] !== '' ? '<small>' . e($r['company']) . '</small>' : '' ?></span>
        <span class="inq__pref"><?= e($r['prefecture']) ?></span>
        <?= icon('chev', 18, 'inq__chev') ?>
      </summary>
      <dl class="inq__body">
        <dt>電話番号</dt><dd><a href="<?= e(tel_href($r['phone'])) ?>"><?= e($r['phone']) ?></a></dd>
        <dt>メール</dt><dd><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a></dd>
<?php if ($r['contact_method'] !== ''): ?>
        <dt>希望の連絡方法</dt><dd><?= e($methods[$r['contact_method']] ?? $r['contact_method']) ?></dd>
<?php endif; ?>
        <dt>内容</dt><dd class="inq__msg"><?= nl2br(e($r['message'])) ?></dd>
<?php if (!empty($r['attachment_path'])): ?>
        <dt>添付ファイル</dt><dd><?= e(implode('、', array_map('basename', (array)(json_decode($r['attachment_path'], true) ?? [$r['attachment_path']])))) ?>（メールに添付されています）</dd>
<?php endif; ?>
      </dl>
    </details>
  </li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<?php if ($pages > 1): ?>
<nav class="pager" aria-label="ページ送り">
<?php if ($page > 1): ?>
  <a class="pager__step" href="?page=<?= $page - 1 ?>">前へ</a>
<?php endif; ?>
  <span class="pager__now"><?= $page ?> / <?= $pages ?>ページ</span>
<?php if ($page < $pages): ?>
  <a class="pager__step" href="?page=<?= $page + 1 ?>">次へ</a>
<?php endif; ?>
</nav>
<?php endif; ?>
