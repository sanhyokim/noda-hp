<?php
/** @var array $meta  title, description, path, og_image, og_type, trail, jsonld */
/** @var string $content */
/** @var string $current */
$gtm = (string)config('gtm_id', '');
$canonical = abs_url($meta['path'] ?? '');
$ogImage = $meta['og_image'] ?? abs_url('assets/img/og-default.jpg');
$trail = $meta['trail'] ?? null;
$graph = [organization_ld(), breadcrumb_ld(breadcrumb_items($trail ?? []))];
foreach (($meta['jsonld'] ?? []) as $ld) {
    $graph[] = $ld;
}
$fonts = 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;900&display=swap';
?><!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ($gtm !== ''): ?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= e($gtm) ?>');</script>
<?php endif; ?>
<title><?= e($meta['title']) ?></title>
<meta name="description" content="<?= e($meta['description']) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:title" content="<?= e($meta['title']) ?>">
<meta property="og:description" content="<?= e($meta['description']) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:type" content="<?= e($meta['og_type'] ?? 'website') ?>">
<meta property="og:site_name" content="<?= e(company('name')) ?>">
<meta property="og:locale" content="ja_JP">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#0B4F82">
<?php if (image_exists('favicon.ico')): ?>
<link rel="icon" href="<?= e(asset('img/favicon.ico')) ?>" sizes="any">
<link rel="icon" href="<?= e(asset('img/favicon-32.png')) ?>" type="image/png" sizes="32x32">
<link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>">
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= e($fonts) ?>" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="<?= e($fonts) ?>"></noscript>
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<?= json_ld($graph) ?>

<script>document.documentElement.classList.add('js')</script>
</head>
<body class="page-<?= e($current) ?>">
<?php if ($gtm !== ''): ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= e($gtm) ?>" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>
<?php endif; ?>
<a class="skip" href="#main">本文へ移動</a>

<header class="topbar">
  <a class="topbar__logo" href="<?= e(url()) ?>" aria-label="<?= e(company('name')) ?> ホーム"><?= picture('logo.png', 240, 78, company('name')) ?></a>
  <a class="topbar__btn" href="<?= e(tel_href(company('tel_free'))) ?>" aria-label="電話をかける <?= e(company('tel_free')) ?>"><?= icon('phone', 22) ?></a>
  <button class="topbar__btn topbar__menu" type="button" aria-controls="sidebar" aria-expanded="false" aria-label="メニューを開く"><?= icon('menu', 24) ?></button>
</header>

<div class="shell">
  <?php render('sidebar', ['current' => $current]); ?>
  <div class="backdrop" hidden></div>

  <div class="body">
    <main id="main" tabindex="-1">
      <?php if ($trail !== null): ?>
        <?php render('breadcrumb', ['items' => breadcrumb_items($trail)]); ?>
      <?php endif; ?>
      <?= $content ?>
      <?php render('cta', ['text' => $meta['cta_text'] ?? null]); ?>
    </main>
    <?php render('footer'); ?>
  </div>
</div>

<nav class="dock" aria-label="お問い合わせ">
  <a class="dock__tel" href="<?= e(tel_href(company('tel_free'))) ?>"><?= icon('phone', 20) ?>電話</a>
  <a class="dock__line" href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener"><?= icon('chat', 20) ?>LINE</a>
  <a class="dock__mail" href="<?= e(page_url('contact')) ?>"><?= icon('mail', 20) ?>メール</a>
</nav>

<script src="<?= e(asset('js/site.js')) ?>" defer></script>
</body>
</html>
