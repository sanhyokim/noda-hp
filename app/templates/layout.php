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
$fonts = 'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Noto+Sans+JP:wght@400;500;700;900&display=swap';
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
<meta name="theme-color" content="#0A39B4">
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

<div class="util">
  <div class="frame util__inner">
    <p class="util__copy">飲食店の換気扇・排煙ダクト工事｜福岡発・全国対応の<?= e(company('name')) ?></p>
    <p class="util__tel"><span class="util__tel-l">お電話でのご相談</span><span>受付 <?= e(company('hours')) ?>（<?= e(company('closed')) ?>）</span><a href="<?= e(tel_href(company('tel_free'))) ?>"><?= icon('phone', 15) ?><?= e(company('tel_free')) ?></a></p>
  </div>
</div>

<header class="gh">
  <div class="frame gh__inner">
    <a class="gh__logo" href="<?= e(url()) ?>" aria-label="<?= e(company('name')) ?> ホーム"><?= picture('logo.png', 240, 60, company('name'), ['priority' => true]) ?></a>
    <?php render('gnav', ['current' => $current]); ?>
    <div class="gh__actions">
      <a class="gh__contact" href="<?= e(page_url('contact')) ?>">お問い合わせ</a>
      <a class="gh__sns" href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener" aria-label="LINEで相談する"><img src="<?= e(asset('img/line-icon-black.png')) ?>" width="30" height="30" alt="" decoding="async"></a>
      <a class="gh__sns gh__sns--ig" href="<?= e(company('instagram_url')) ?>" target="_blank" rel="noopener" aria-label="Instagram"><svg width="30" height="30" viewBox="0 0 30 30" aria-hidden="true"><rect width="30" height="30" rx="7" fill="#111"/><rect x="7.5" y="7.5" width="15" height="15" rx="4.2" fill="none" stroke="#fff" stroke-width="1.9"/><circle cx="15" cy="15" r="3.6" fill="none" stroke="#fff" stroke-width="1.9"/><circle cx="19.4" cy="10.6" r="1.1" fill="#fff"/></svg></a>
    </div>
    <a class="gh__btn gh__tel" href="<?= e(tel_href(company('tel_free'))) ?>" aria-label="電話をかける <?= e(company('tel_free')) ?>"><?= icon('phone', 22) ?></a>
    <button class="gh__btn gh__menu topbar__menu" type="button" aria-controls="sidebar" aria-expanded="false" aria-label="メニューを開く"><?= icon('menu', 24) ?><span>MENU</span></button>
  </div>
</header>

<?= $meta['top'] ?? '' ?>

<div class="frame shell">
  <div class="body">
    <main id="main" tabindex="-1">
      <?php if ($trail !== null): ?>
        <?php render('breadcrumb', ['items' => breadcrumb_items($trail)]); ?>
      <?php endif; ?>
      <?= $content ?>
      <?php render('cta', ['text' => $meta['cta_text'] ?? null]); ?>
    </main>
  </div>
  <?php render('sidebar', ['current' => $current]); ?>
  <div class="backdrop" hidden></div>
</div>

<?php render('footer'); ?>

<a class="side-tab" href="<?= e(page_url('contact')) ?>"><?= icon('chat', 26) ?><span>お問い合わせはこちら</span></a>

<nav class="dock" aria-label="お問い合わせ">
  <a class="dock__tel" href="<?= e(tel_href(company('tel_free'))) ?>"><?= icon('phone', 20) ?>電話</a>
  <a class="dock__line" href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener"><?= line_icon(22) ?>LINE</a>
  <a class="dock__mail" href="<?= e(page_url('contact')) ?>"><?= icon('mail', 20) ?>メール</a>
</nav>

<script src="<?= e(asset('js/site.js')) ?>" defer></script>
</body>
</html>
