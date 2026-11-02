<?php
/** @var string $content */
/** @var ?array $user */
$title = $title ?? '管理画面';
$nav = $nav ?? null;
$menu = ['cases' => '施工事例', 'news' => 'お知らせ', 'categories' => 'カテゴリ', 'settings' => '設定'];
$fonts = 'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Noto+Sans+JP:wght@400;500;700&display=swap';
?><!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?>｜<?= e(company('name')) ?> 管理画面</title>
<meta name="theme-color" content="#1B1F26">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= e($fonts) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</head>
<body<?= $user ? '' : ' class="is-guest"' ?>>
<header class="ah">
  <div class="ah__in">
    <a class="ah__brand" href="<?= e($user ? admin_url('cases/') : admin_url('login/')) ?>">
      <img class="ah__logo" src="<?= e(asset('img/logo.png')) ?>" width="240" height="60" alt="<?= e(company('name')) ?>">
      <span class="ah__name">管理画面</span>
    </a>
<?php if ($user): ?>
    <div class="ah__tools">
      <a class="ah__tool" href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= icon('external', 18) ?><span>サイトを見る</span></a>
      <form method="post" action="<?= e(admin_url('logout/')) ?>">
        <?= csrf_field() ?>
        <button class="ah__tool" type="submit" aria-label="ログアウト"><?= icon('logout', 18) ?><span>ログアウト</span></button>
      </form>
    </div>
<?php endif; ?>
  </div>
<?php if ($user): ?>
  <nav class="an" aria-label="管理メニュー">
    <ul class="an__list">
<?php foreach ($menu as $k => $label): ?>
      <li><a href="<?= e(admin_url($k . '/')) ?>"<?= $nav === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach; ?>
    </ul>
  </nav>
<?php endif; ?>
</header>
<main class="am" id="main">
<?php if (!empty($flash)): ?>
  <p class="msg msg--<?= e($flash[0]) ?>" role="status"><?= icon($flash[0] === 'error' ? 'alert' : 'check', 20) ?><span><?= e($flash[1]) ?></span></p>
<?php endif; ?>
<?= $content ?>
</main>
</body>
</html>
