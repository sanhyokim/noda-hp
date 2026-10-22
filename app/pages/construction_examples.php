<?php
// 施工事例の一覧：業態・工事の種類での絞り込み（各1つまで）と、12件ずつのページ送り（4-3）。
// URL は ?biz=<slug>&work=<slug>&page=N。絞り込み付きの canonical は絞り込みなしの一覧。
$cats = case_categories();
$pick = function (string $axis) use ($cats): ?string {
    $v = $_GET[$axis] ?? null;
    return is_string($v) && isset($cats[$axis][$v]) ? $v : null;
};
$biz = $pick('biz');
$work = $pick('work');
$pageNo = max(1, (int)($_GET['page'] ?? 1));
$list = case_list($biz, $work, $pageNo);
$pages = max(1, (int)ceil($list['total'] / CASES_PER_PAGE));

if ($pageNo > $pages) {
    http_response_code(404);
    $meta = ['title' => 'ページが見つかりません｜株式会社野田', 'description' => 'お探しのページが見つかりません。', 'path' => 'construction_examples/', 'trail' => [['施工事例', 'construction_examples/']]];
    require APP_DIR . '/pages/404.php';
    return;
}

// 一覧のアドレス。絞り込みを押した後は、ページの頭ではなく絞り込みの位置から見せる
$listUrl = function (?string $b, ?string $w, int $n = 1, bool $jump = true): string {
    $q = http_build_query(array_filter(['biz' => $b, 'work' => $w, 'page' => $n > 1 ? $n : null]));
    return page_url('construction_examples') . ($q !== '' ? '?' . $q : '') . ($jump ? '#works-list' : '');
};

$filterName = implode('×', array_filter([$biz ? $cats['biz'][$biz] : null, $work ? $cats['work'][$work] : null]));
if ($filterName !== '') {
    $meta['title'] = $filterName . 'の' . $meta['title'];
} elseif ($pageNo > 1) {
    $meta['title'] = str_replace('施工事例｜', '施工事例（' . $pageNo . 'ページ目）｜', $meta['title']);
    $meta['path'] = 'construction_examples/?page=' . $pageNo;
}
$meta['head_links'] = array_filter([
    'prev' => $pageNo > 1 ? $listUrl($biz, $work, $pageNo - 1, false) : null,
    'next' => $pageNo < $pages ? $listUrl($biz, $work, $pageNo + 1, false) : null,
]);

$counts = ['biz' => case_counts('biz', $work), 'work' => case_counts('work', $biz)];
$rows = [
    'biz' => ['BUSINESS', '業態', $biz],
    'work' => ['WORK', '工事の種類', $work],
];

render('page-head', [
    'label' => 'WORKS',
    'title' => '施工事例',
    'image' => 'after-grease.jpg', 'ratio' => [3, 2],
    'lead' => '創業からの施工実績1,000件以上の中から抜粋して掲載しています。業態・工事の種類で絞り込めます。',
]);
$from = ($pageNo - 1) * CASES_PER_PAGE + 1;
$to = min($list['total'], $pageNo * CASES_PER_PAGE);
?>
<section class="section wrap works-list" id="works-list" aria-labelledby="works-title">
  <h2 class="u-sr" id="works-title">施工事例の一覧</h2>
  <div class="filter" role="group" aria-label="絞り込み">
    <?php foreach ($rows as $axis => [$en, $ja, $sel]): ?>
      <div class="filter__row">
        <p class="filter__label" id="filter-<?= $axis ?>"><span><?= $en ?></span><?= $ja ?></p>
        <ul class="filter__chips" aria-labelledby="filter-<?= $axis ?>">
          <li><a class="fchip<?= $sel === null ? ' is-on' : '' ?>" href="<?= e($axis === 'biz' ? $listUrl(null, $work) : $listUrl($biz, null)) ?>"<?= $sel === null ? ' aria-current="true"' : '' ?>>すべて</a></li>
          <?php foreach ($cats[$axis] as $slug => $name): $n = $counts[$axis][$slug] ?? 0; $on = $sel === $slug; ?>
            <li>
              <?php if ($on): ?>
                <a class="fchip is-on" href="<?= e($axis === 'biz' ? $listUrl(null, $work) : $listUrl($biz, null)) ?>" aria-current="true" title="この絞り込みを外す"><?= e($name) ?><span class="fchip__n"><?= $n ?></span><?= icon('close', 13, 'fchip__x') ?></a>
              <?php elseif ($n === 0): ?>
                <span class="fchip is-zero" aria-disabled="true"><?= e($name) ?><span class="fchip__n">0</span></span>
              <?php else: ?>
                <a class="fchip" href="<?= e($axis === 'biz' ? $listUrl($slug, $work) : $listUrl($biz, $slug)) ?>"><?= e($name) ?><span class="fchip__n"><?= $n ?></span></a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="works-list__status" aria-live="polite">
    <p class="works-list__count">
      <?php if ($filterName !== ''): ?><span class="works-list__filter"><?= e($filterName) ?></span><?php endif; ?>
      <b><?= number_format($list['total']) ?></b>件<?php if ($list['total'] > 0): ?><span class="works-list__range">（<?= $from ?>〜<?= $to ?>件目）</span><?php endif; ?>
    </p>
    <?php if ($biz || $work): ?>
      <a class="more" href="<?= e($listUrl(null, null)) ?>">絞り込みを解除<?= icon('close', 14) ?></a>
    <?php endif; ?>
  </div>

  <?php if ($list['rows']): ?>
    <ul class="grid grid--3 cases">
      <?php foreach ($list['rows'] as $case): ?>
        <li><?php render('case-card', ['case' => $case]); ?></li>
      <?php endforeach; ?>
    </ul>
    <?php render('pager', ['page' => $pageNo, 'pages' => $pages, 'url' => fn(int $n) => $listUrl($biz, $work, $n)]); ?>
  <?php else: ?>
    <div class="empty">
      <p>該当する事例はありません。</p>
      <a class="more" href="<?= e($listUrl(null, null)) ?>">絞り込みを解除してすべての事例を見る<?= icon('arrow', 16) ?></a>
    </div>
  <?php endif; ?>
</section>
