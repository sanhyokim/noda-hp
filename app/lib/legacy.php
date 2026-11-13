<?php
declare(strict_types=1);

// 旧サイト（WordPress）のアドレスから新しいアドレスへの転送先（付録B-5）。
// 記事そのもの（/<slug>/）は同じアドレスのまま。ここで扱うのはカテゴリ一覧と施工事例一覧の2ページ目以降。

// 旧カテゴリ名（URL の日本語をほどいたもの）→ 新しい絞り込み
const LEGACY_CATEGORIES = [
    '焼肉屋' => 'biz=yakiniku',
    '焼き鳥屋' => 'biz=yakitori',
    '居酒屋' => 'biz=izakaya',
    '炉端焼き店' => 'biz=izakaya', // 対応表に無い旧カテゴリ。炉端焼きは居酒屋・和食店に含める
    'ラーメン屋' => 'biz=ramen',
    '喫茶店' => 'biz=cafe',
    '洋食屋' => 'biz=restaurant',
    'レストラン' => 'biz=restaurant',
    'テイクアウト店' => 'biz=takeout',
    '大型店' => 'biz=chain',
    'その他' => 'biz=other',
    '厨房フード' => 'work=kitchen-hood',
    '焼肉-上引きフード' => 'work=upper-hood',
    '無煙ロースター' => 'work=roaster',
    '焼鳥専用フード' => 'work=yakitori-hood',
    '消煙機' => 'work=shoenki',
    '排煙・煙対策工事' => 'work=smoke',
    'メンテナンス' => 'work=maintenance',
    'リニューアル' => 'work=renewal',
    'その他・換気工事他' => 'work=ventilation',
];

// 転送先のアドレス（サイト内のパス）。転送しないものは null
function legacy_redirect(string $path): ?string
{
    // /construction_examples/page/3/ → /construction_examples/?page=3
    if (preg_match('#^construction_examples/page/(\d+)/$#', $path, $m)) {
        return url('construction_examples/' . ((int)$m[1] > 1 ? '?page=' . (int)$m[1] : ''));
    }
    // /category/焼肉屋/ （2ページ目以降 /category/焼肉屋/page/2/ も同じ絞り込みの1ページ目へ）
    if (preg_match('#^category/([^/]+)/(?:page/\d+/)?$#u', $path, $m)) {
        $q = LEGACY_CATEGORIES[mb_strtolower($m[1])] ?? null;
        return url('construction_examples/' . ($q !== null ? '?' . $q : ''));
    }
    return null;
}
