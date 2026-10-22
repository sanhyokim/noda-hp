<?php
/** @var array $rows */
/** @var int $total */
/** @var int $page */
/** @var int $pages */
/** @var array $pageNames 公開ページのアドレス => 名前 */
$where = function (string $path) use ($pageNames): string {
    $path = (string)parse_url($path, PHP_URL_PATH);
    return $pageNames[$path] ?? ($path === url() ? 'トップページ' : $path);
};
?>
<div class="bar">
  <a class="back" href="<?= e(admin_url('settings/')) ?>"><?= icon('arrow', 18, 'back__icon') ?>設定</a>
</div>
<h1 class="ttl ttl--form">AI 相談の会話の記録</h1>
<p class="count">全部で<strong><?= number_format($total) ?></strong>件（新しい順。<?= (int)(chat_config('log_days') ?: 365) ?>日を過ぎた記録は自動で消えます）</p>
<p class="note">お客様がどんなことで迷っているかを知るための記録です。お名前などは入力しないようお願いしていますが、もし書かれていても外に出さないようご注意ください。</p>

<?php if (!$rows): ?>
<div class="empty"><p>まだ会話はありません。</p></div>
<?php else: ?>
<ul class="inq inq--chat">
<?php foreach ($rows as $r):
    $first = '';
    foreach ($r['messages'] as $m) {
        if (($m['role'] ?? '') === 'user') { $first = (string)$m['content']; break; }
    }
?>
  <li>
    <details class="inq__item">
      <summary class="inq__sum">
        <time datetime="<?= e(date('c', strtotime($r['started_at']))) ?>"><?= e(date('Y.m.d H:i', strtotime($r['started_at']))) ?></time>
        <span class="inq__who inq__who--q"><?= e(mb_strimwidth($first, 0, 90, '…')) ?></span>
        <span class="inq__pref"><?= (int)$r['turns'] ?>往復・<?= e($where($r['page'])) ?>から<?php if ($r['handed_off']): ?> <span class="badge badge--published">問い合わせへ進んだ</span><?php endif; ?></span>
        <?= icon('chev', 18, 'inq__chev') ?>
      </summary>
      <ol class="talk">
<?php foreach ($r['messages'] as $m): $me = ($m['role'] ?? '') === 'user'; ?>
        <li class="talk__msg talk__msg--<?= $me ? 'me' : 'ai' ?>"><span class="talk__who"><?= $me ? 'お客様' : 'AI' ?></span><p><?= nl2br(e((string)($m['content'] ?? ''))) ?></p></li>
<?php endforeach; ?>
      </ol>
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
