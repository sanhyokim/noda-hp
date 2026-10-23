<?php
/** @var array $rows */
/** @var int $total */
/** @var int $page */
/** @var int $pages */
/** @var array $pageNames 公開ページのアドレス => 名前 */
/** @var string $q */
$where = function (string $path) use ($pageNames): string {
    $path = (string)parse_url($path, PHP_URL_PATH);
    return $pageNames[$path] ?? ($path === url() ? 'トップページ' : $path);
};
?>
<div class="bar">
  <a class="back" href="<?= e(admin_url('settings/')) ?>"><?= icon('arrow', 18, 'back__icon') ?>設定</a>
</div>
<h1 class="ttl ttl--form">AI 相談の会話の記録</h1>
<form class="search" method="get" action="<?= e(admin_url('settings/chats/')) ?>" role="search">
  <label class="u-sr" for="q">相談番号か言葉で探す</label>
  <input class="input" type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="相談番号（例：A3F9C2）か言葉で探す" enterkeyhint="search" autocapitalize="characters">
  <button class="abtn abtn--line" type="submit">探す</button>
</form>
<p class="count"><?= $q !== '' ? '「' . e($q) . '」で' : '全部で' ?><strong><?= number_format($total) ?></strong>件（新しい順。<?= (int)(chat_config('log_days') ?: 365) ?>日を過ぎた記録は自動で消えます）<?php if ($q !== ''): ?> <a href="<?= e(admin_url('settings/chats/')) ?>">すべて表示</a><?php endif; ?></p>
<p class="note">LINE や「担当者から電話をもらう」で届いた相談番号（6文字の英数字）を入れると、その会話が出ます。</p>
<p class="note">お客様がどんなことで迷っているかを知るための記録です。お名前などは入力しないようお願いしていますが、もし書かれていても外に出さないようご注意ください。</p>

<?php if (!$rows): ?>
<div class="empty"><p><?= $q !== '' ? '見つかりませんでした。相談番号の打ち間違いがないか確かめてください。' : 'まだ会話はありません。' ?></p></div>
<?php else: ?>
<ul class="inq inq--chat">
<?php foreach ($rows as $r):
    $first = '';
    foreach ($r['messages'] as $m) {
        if (($m['role'] ?? '') === 'user') { $first = (string)$m['content']; break; }
    }
?>
  <li>
    <details class="inq__item"<?= $q !== '' && count($rows) === 1 ? ' open' : '' ?>>
      <summary class="inq__sum">
        <time datetime="<?= e(date('c', strtotime($r['started_at']))) ?>"><?= e(date('Y.m.d H:i', strtotime($r['started_at']))) ?><small class="inq__code">相談番号 <?= e(chat_code($r['conv_key'])) ?></small></time>
        <span class="inq__who inq__who--q"><?= e(mb_strimwidth($first, 0, 90, '…')) ?></span>
        <span class="inq__pref"><?= (int)$r['turns'] ?>往復・<?= e($where($r['page'])) ?>から<?php if ($r['handed_off']): ?> <span class="badge badge--published">問い合わせへ進んだ</span><?php endif; ?><?php if (!empty($r['unanswered'])): ?> <span class="badge badge--review">答えられなかった質問あり</span><?php endif; ?><?php if (isset($r['rating']) && $r['rating'] !== null): ?> <span class="badge<?= (int)$r['rating'] === 1 ? ' badge--published' : '' ?>"><?= (int)$r['rating'] === 1 ? '役に立った' : '役に立たなかった' ?></span><?php endif; ?></span>
        <?= icon('chev', 18, 'inq__chev') ?>
      </summary>
      <ol class="talk">
<?php foreach ($r['messages'] as $m): $me = ($m['role'] ?? '') === 'user'; ?>
        <li class="talk__msg talk__msg--<?= $me ? 'me' : 'ai' ?>"><span class="talk__who"><?= $me ? 'お客様' : 'AI' ?></span><p><?= nl2br(e((string)($m['content'] ?? ''))) ?></p></li>
<?php endforeach; ?>
      </ol>
<?php if (!empty($r['unanswered'])): ?>
      <p class="talk__todo"><a class="abtn abtn--line abtn--sm" href="<?= e(admin_url('settings/answers/')) ?>#todo">AI に答えを教える</a></p>
<?php endif; ?>
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
