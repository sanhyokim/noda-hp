<?php
/** @var array $case find_case() の結果（router が用意） */
// 施工事例の詳しいページ /<slug>/（4-4）。日付は出さない。
// 指示書では写真は本文の下だが、事例は写真が主役なので、代表写真を題名のすぐ下に大きく出し、
// 本文の後に全写真の一覧（押すと拡大）を置く。
$images = case_images((int)$case['id'], $case['thumbnail_image_id'] !== null ? (int)$case['thumbnail_image_id'] : null);
const GALLERY_FIRST = 12; // 写真の一覧で最初に並べる枚数（残りはボタンで表示。拡大表示ではすべて順に見られる）
$first = $images[0] ?? null;
$path = rawurlencode($case['slug']) . '/';
$meta = [
    'title' => $case['seo_title'] !== '' ? $case['seo_title'] : $case['title'] . '｜施工事例【株式会社野田】',
    'description' => $case['seo_description'] !== '' ? $case['seo_description'] : (auto_description($case['body_html']) ?: $case['title'] . 'の施工事例です。株式会社野田（福岡）の飲食店のフード・ダクト工事。'),
    'path' => $path,
    'og_type' => 'article',
    'trail' => [[page('construction_examples')['name'], page('construction_examples')['path']], [$case['title'], $path]],
];
if ($first) {
    $meta['og_image'] = abs_url($first['path']);
}
$meta['jsonld'] = [article_ld($case['title'], $path, $case['published_at'], (string)($case['updated_at'] ?? ''), array_map(fn($im) => abs_url($im['path']), array_slice($images, 0, 3)))];
$listUrl = fn(string $axis, string $slug): string => page_url('construction_examples') . '?' . $axis . '=' . rawurlencode($slug) . '#works-list';
$webp = fn(string $p): string => is_file(PUBLIC_DIR . '/' . preg_replace('/\.(jpe?g|png)$/i', '.webp', $p)) ? preg_replace('/\.(jpe?g|png)$/i', '.webp', $p) : $p;
$alt = fn(int $i): string => $case['title'] . '（写真' . ($i + 1) . '）';

// 同じ業態・同じ工事の事例（2つ目の欄では1つ目に出した事例を除く）
$shown = [(int)$case['id']];
$related = [];
foreach (['biz' => '同じ業態', 'work' => '同じ工事'] as $axis => $kind) {
    $slug = array_key_first($case['cats'][$axis]);
    if ($slug === null) {
        continue;
    }
    $rows = related_cases($axis, $slug, $shown);
    if (!$rows) {
        continue;
    }
    $shown = array_merge($shown, array_map('intval', array_column($rows, 'id')));
    $related[] = [$axis, $slug, $case['cats'][$axis][$slug], $kind, $rows];
}
$adj = adjacent_cases($case);
?>
<article class="cd">
  <header class="cd__head wrap">
    <p class="cd__en">WORKS</p>
    <h1 class="cd__title kw"><?= e($case['title']) ?></h1>
    <div class="cd__meta">
      <?php if ($case['cats']['biz'] || $case['cats']['work']): ?>
        <ul class="chips cd__chips" aria-label="業態・工事の種類">
          <?php foreach (['biz', 'work'] as $axis): foreach ($case['cats'][$axis] as $slug => $name): ?>
            <li><a class="chip<?= $axis === 'biz' ? ' chip--solid' : ' chip--outline' ?>" href="<?= e($listUrl($axis, $slug)) ?>"><?= e($name) ?></a></li>
          <?php endforeach; endforeach; ?>
        </ul>
      <?php endif; ?>
      <?php if ($case['location'] !== ''): ?>
        <p class="cd__loc"><?= icon('pin', 16) ?><span class="u-sr">施工場所：</span><?= e($case['location']) ?></p>
      <?php endif; ?>
    </div>
  </header>

  <?php if ($first): ?>
    <figure class="cd__kv wrap">
      <button class="cd__kv-btn" type="button" data-lb-open="0" aria-label="写真を拡大する（<?= count($images) ?>枚）">
        <?php [$kvJpg, $kvWebp] = img_urls($first['path_thumb']); // 後ろにぼかして敷くのは代表写真の幅800pxの版（スマホでは手前の写真と同じアドレスなので読み込みは1回） ?>
        <span class="cd__kv-bg" aria-hidden="true" style="background-image:url('<?= e($kvJpg) ?>')<?php if ($kvWebp): ?>;background-image:image-set(url('<?= e($kvWebp) ?>') type('image/webp'), url('<?= e($kvJpg) ?>') type('image/jpeg'))<?php endif; ?>"></span>
        <?= picture($first['path'], (int)$first['width'], (int)$first['height'], $alt(0), ['priority' => true, 'class' => 'cd__kv-img', 'small' => $first['path_thumb'], 'sizes' => '(min-width: 1100px) 900px, 100vw']) ?>
        <span class="cd__kv-count"><?= icon('image', 16) ?><?= count($images) ?>枚の写真を見る</span>
      </button>
    </figure>
  <?php endif; ?>

  <div class="section wrap cd__main">
    <h2 class="u-sr">施工の内容</h2>
    <div class="prose cd__body">
      <?= $case['body_html'] ?>
    </div>
    <dl class="cd__spec">
      <?php if ($case['cats']['biz']): ?><div><dt>業態</dt><dd><?php foreach ($case['cats']['biz'] as $n): ?><span><?= e($n) ?></span><?php endforeach; ?></dd></div><?php endif; ?>
      <?php if ($case['cats']['work']): ?><div><dt>工事の種類</dt><dd><?php foreach ($case['cats']['work'] as $n): ?><span><?= e($n) ?></span><?php endforeach; ?></dd></div><?php endif; ?>
      <?php if ($case['location'] !== ''): ?><div><dt>エリア</dt><dd><?= e($case['location']) ?></dd></div><?php endif; ?>
      <?php if ($images): ?><div><dt>写真</dt><dd><?= count($images) ?>枚</dd></div><?php endif; ?>
    </dl>
  </div>

  <?php if (count($images) > 1): ?>
    <section class="section wrap" aria-labelledby="photo-title">
      <?php render('section-head', ['label' => 'PHOTO', 'title' => '施工写真（' . count($images) . '枚）', 'id' => 'photo-title']); ?>
      <ul class="photos" data-lb>
        <?php foreach ($images as $i => $img): ?>
          <li<?= $i >= GALLERY_FIRST ? ' class="photos__rest"' : '' ?>>
            <a class="photos__item" href="<?= e(url($webp($img['path']))) ?>" data-lb-index="<?= $i ?>" data-w="<?= (int)$img['width'] ?>" data-h="<?= (int)$img['height'] ?>">
              <?= picture($img['path_thumb'], (int)$img['width'], (int)$img['height'], $alt($i)) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php if (count($images) > GALLERY_FIRST): ?>
        <p class="photos__more"><button class="btn btn--outline" type="button" data-photos-more>残り<?= count($images) - GALLERY_FIRST ?>枚の写真を表示<?= icon('chev', 16) ?></button></p>
      <?php endif; ?>
    </section>
  <?php elseif ($first): ?>
    <ul class="photos" data-lb hidden>
      <li><a class="photos__item" href="<?= e(url($webp($first['path']))) ?>" data-lb-index="0" data-w="<?= (int)$first['width'] ?>" data-h="<?= (int)$first['height'] ?>"><?= e($alt(0)) ?></a></li>
    </ul>
  <?php endif; ?>

  <?php foreach ($related as $k => [$axis, $slug, $name, $kind, $rows]): ?>
    <section class="section wrap" aria-labelledby="rel-<?= $axis ?>">
      <?php render('section-head', ['label' => $axis === 'biz' ? 'SAME BUSINESS' : 'SAME WORK', 'title' => $name . 'の施工事例', 'id' => 'rel-' . $axis, 'link' => [$name . 'の事例をすべて見る', $listUrl($axis, $slug)]]); ?>
      <ul class="grid grid--3 cases">
        <?php foreach ($rows as $r): ?><li><?php render('case-card', ['case' => $r]); ?></li><?php endforeach; ?>
      </ul>
    </section>
  <?php endforeach; ?>

  <nav class="section wrap" aria-label="前後の施工事例">
    <div class="cd__pn">
      <?php foreach (['older' => 'ひとつ前の事例', 'newer' => '新しい事例'] as $k => $label): $a = $adj[$k]; ?>
        <?php if ($a): ?>
          <a class="cd__pn-item cd__pn-item--<?= $k ?>" href="<?= e(url(rawurlencode($a['slug']) . '/')) ?>">
            <span class="cd__pn-img"><?= picture($a['image'] ?: 'media/none.jpg', (int)($a['image_w'] ?: 800), (int)($a['image_h'] ?: 533), '') ?></span>
            <span class="cd__pn-text"><span class="cd__pn-label"><?= $k === 'older' ? icon('arrow', 14) . $label : $label . icon('arrow', 14) ?></span><span class="cd__pn-title"><?= e($a['title']) ?></span></span>
          </a>
        <?php else: ?>
          <span class="cd__pn-item cd__pn-item--<?= $k ?> is-empty" aria-hidden="true"></span>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <p class="cd__back"><a class="btn btn--outline" href="<?= e(page_url('construction_examples')) ?>">施工事例の一覧へ</a></p>
  </nav>
</article>

<div class="lb" id="lightbox" role="dialog" aria-modal="true" aria-label="写真の拡大表示" hidden>
  <figure class="lb__fig"><img class="lb__img" alt=""><figcaption class="lb__cap" aria-live="polite"></figcaption></figure>
  <button class="lb__btn lb__close" type="button" aria-label="閉じる"><?= icon('close', 26) ?></button>
  <button class="lb__btn lb__prev" type="button" aria-label="前の写真"><?= icon('arrow', 26) ?></button>
  <button class="lb__btn lb__next" type="button" aria-label="次の写真"><?= icon('arrow', 26) ?></button>
</div>
