<?php
declare(strict_types=1);

// 移行の変換規則（指示書 3-3・付録B）。migrate.php と試験（tests/migration_test.php）の両方から使う。

// 付録B-1・B-2 のカテゴリ
const MIG_CATEGORIES = [
    'biz' => [
        ['焼肉店', 'yakiniku'],
        ['焼き鳥店', 'yakitori'],
        ['居酒屋・和食店', 'izakaya'],
        ['ラーメン・うどん店', 'ramen'],
        ['カフェ・喫茶店', 'cafe'],
        ['洋食・レストラン', 'restaurant'],
        ['パン屋・弁当・テイクアウト', 'takeout'],
        ['大型店・チェーン店', 'chain'],
        ['施設・工場・その他', 'other'],
    ],
    'work' => [
        ['厨房フード', 'kitchen-hood'],
        ['焼肉 上引きフード', 'upper-hood'],
        ['無煙ロースター', 'roaster'],
        ['焼き鳥専用フード', 'yakitori-hood'],
        ['消煙機', 'shoenki'],
        ['煙・臭い対策', 'smoke'],
        ['換気・給気・ファン工事', 'ventilation'],
        ['メンテナンス・交換', 'maintenance'],
        ['防火・安全対策', 'fire-safety'],
        ['移転・リニューアル', 'renewal'],
    ],
];

// 付録B-3 旧カテゴリ名 → [軸, 新slug]
const MIG_OLD_CATEGORY_MAP = [
    '焼肉屋' => ['biz', 'yakiniku'],
    '焼き鳥屋' => ['biz', 'yakitori'],
    '居酒屋' => ['biz', 'izakaya'],
    'ラーメン屋' => ['biz', 'ramen'],
    '喫茶店' => ['biz', 'cafe'],
    '洋食屋' => ['biz', 'restaurant'],
    'レストラン' => ['biz', 'restaurant'],
    'テイクアウト店' => ['biz', 'takeout'],
    '大型店' => ['biz', 'chain'],
    'その他' => ['biz', 'other'],
    '厨房フード' => ['work', 'kitchen-hood'],
    '焼肉 上引きフード' => ['work', 'upper-hood'],
    '無煙ロースター' => ['work', 'roaster'],
    '焼鳥専用フード' => ['work', 'yakitori-hood'],
    '消煙機' => ['work', 'shoenki'],
    '排煙・煙対策工事' => ['work', 'smoke'],
    'メンテナンス' => ['work', 'maintenance'],
    'リニューアル' => ['work', 'renewal'],
    // 'その他・換気工事他' は B-4 を先に当て、工事の種類が付かなかった時だけ ventilation（map_categories 内で処理）
];

// 付録B-4 題名のキーワード規則
const MIG_KEYWORD_RULES = [
    [['介護', '施設', '教会', '工場', 'コインランドリー', '製麺所', '住宅', 'リビング', 'お家', 'スーパー', 'フードコート'], 'biz', 'other'],
    [['パン', 'ベーカリー', '弁当', '唐揚げ'], 'biz', 'takeout'],
    [['チェーン', 'すき家', '吉野家', '鳥貴族', 'しゃぶ葉', 'TRIAL', '回転寿司'], 'biz', 'chain'],
    [['うなぎ', '鰻', '鉄板焼き', '水炊き', '串揚げ', '寿司', '食堂', '薪焼'], 'biz', 'izakaya'],
    [['カフェ', 'コーヒー', '喫茶'], 'biz', 'cafe'],
    [['ハンバーグ', 'ピザ', '牛カツ', 'ステーキ'], 'biz', 'restaurant'],
    [['うどん'], 'biz', 'ramen'],
    [['防火', 'グリスセパレーター', '火災', '点検口', 'ダンパー'], 'work', 'fire-safety'],
    [['交換', '定期', 'メンテナンス', 'リフレッシュ', '清掃'], 'work', 'maintenance'],
    [['移転', '改装', '改修', 'リニューアル', '総入替', '取替'], 'work', 'renewal'],
    [['給気', '吸気', 'ファン', '換気', '結露', '塩害', '静音', '断熱'], 'work', 'ventilation'],
    [['消煙機', '集塵'], 'work', 'shoenki'],
    [['上引き', 'クリアフード', 'クリアーフード'], 'work', 'upper-hood'],
    [['ロースター', '床下'], 'work', 'roaster'],
];

const MIG_PREFECTURES = [
    '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県',
    '東京都', '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県',
    '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県',
    '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県',
];

/**
 * 旧カテゴリ名と題名から新カテゴリを決める。
 * 戻り値：['biz' => [slug...], 'work' => [slug...], 'needs_review' => bool]
 */
function map_categories(array $oldNames, string $title): array
{
    $out = ['biz' => [], 'work' => []];
    $add = function (string $axis, string $slug) use (&$out): void {
        if (!in_array($slug, $out[$axis], true)) {
            $out[$axis][] = $slug;
        }
    };
    foreach ($oldNames as $name) {
        if (isset(MIG_OLD_CATEGORY_MAP[$name])) {
            [$axis, $slug] = MIG_OLD_CATEGORY_MAP[$name];
            $add($axis, $slug);
        }
    }
    $keywordWork = false;
    foreach (MIG_KEYWORD_RULES as [$words, $axis, $slug]) {
        foreach ($words as $w) {
            if (mb_stripos($title, $w) !== false) {
                $add($axis, $slug);
                if ($axis === 'work') {
                    $keywordWork = true;
                }
                break;
            }
        }
    }
    if (in_array('その他・換気工事他', $oldNames, true) && !$keywordWork) {
        $add('work', 'ventilation');
    }
    $review = false;
    if (!$out['biz']) {
        $add('biz', 'other');
        $review = true;
    }
    if (!$out['work']) {
        $add('work', 'ventilation');
        $review = true;
    }
    return $out + ['needs_review' => $review];
}

/** 題名から場所を取り出す（3-3-8。決めた順番で最初に当たった語）。当たらなければ ''。 */
function location_from_title(string $title): string
{
    foreach (MIG_PREFECTURES as $p) {
        if (str_contains($title, $p)) {
            return $p;
        }
    }
    $han = '\p{Han}';
    $patterns = [
        '/福岡市' . $han . '{1,3}区/u',
        '/北九州市' . $han . '{1,3}区/u',
        '/広島市' . $han . '{1,3}区/u',
        '/(?<!' . $han . ')' . $han . '{1,4}市(?!場)/u',
        '/(?<!' . $han . ')' . $han . '{1,4}町(?!屋)/u',
        '/(?<!' . $han . ')' . $han . '{1,4}郡/u',
    ];
    foreach ($patterns as $re) {
        if (preg_match($re, $title, $m)) {
            return $m[0];
        }
    }
    return '';
}

/**
 * 本文の「エリア」欄（現行の記事にある地名の欄）。地名だけの短い文字ならそのまま返す。
 */
function location_from_area(string $html): string
{
    if (!preg_match('/>\s*エリア\s*<\/h[1-6]>(.*?)(?:<h[1-6]|$)/su', $html, $m)) {
        return '';
    }
    $text = trim(preg_replace('/\s+/u', '', html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    if (preg_match('/^[\p{Han}\p{Hiragana}\p{Katakana}ー]{2,12}$/u', $text) && preg_match('/[都道府県市区町村郡]$/u', $text)) {
        return $text;
    }
    // 「福岡県各店」のように地名以外が混ざる時は、題名と同じ規則で地名だけを取り出す
    return mb_strlen($text) <= 40 ? location_from_title($text) : '';
}

/** WordPress の画像URLを元画像のURLにそろえる（-300x225 などの縮小版の印を外す）。 */
function normalize_image_url(string $url): string
{
    $url = html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $url = preg_replace('/[?#].*$/', '', $url);
    $url = preg_replace('#^http://#', 'https://', $url);
    return preg_replace('/-\d+x\d+(\.(?:jpe?g|png|gif|webp))$/i', '$1', $url);
}

/** 本文の画像を出てくる順に取り出す（重複は1つ）。各要素：['url' => 元画像URL, 'alt' => 代替文字] */
function extract_images(string $html): array
{
    $doc = load_html($html);
    $out = [];
    foreach ($doc->getElementsByTagName('img') as $img) {
        $url = $img->getAttribute('data-full') ?: $img->getAttribute('data-src') ?: $img->getAttribute('src');
        if ($url === '' || !preg_match('#duct-noda\.com/wp-content/uploads/#', $url)) {
            continue;
        }
        $url = normalize_image_url($url);
        if (!isset($out[$url])) {
            $out[$url] = ['url' => $url, 'alt' => trim($img->getAttribute('alt'))];
        }
    }
    return array_values($out);
}

function load_html(string $html): DOMDocument
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><html><body><div id="root">' . $html . '</div></body></html>', LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    return $doc;
}

/**
 * 本文を許可した要素だけに整える（3-3-3・3-3-4）。画像は取り除く（ギャラリーに移すため）。
 * 残す：p, h2〜h4, ul, ol, li, strong, em, a(href), br。h5/h6→h3、h1→h2、b→strong、i→em。
 */
// 旧サイトの保存時に絵文字が「????」に化けている（20件ほど）。化けた跡と、絵文字に付いていた結合文字・後ろの空白を消す
function strip_lost_emoji(string $s): string
{
    return preg_replace('/\?{2,}(?:\x{200D}[\x{2640}\x{2642}])?\x{FE0F}?[ \x{3000}]?/u', '', $s);
}

// 本文に Markdown の太字「**語**」がそのまま入っている記事がある（4件）。太字に直し、対にならない ** は消す
function fix_markdown_bold(string $html): string
{
    $html = preg_replace('/\*\*([^*<>\n]+?)\*\*/u', '<strong>$1</strong>', $html);
    return str_replace('**', '', $html);
}

function sanitize_body(string $html): string
{
    $html = strip_lost_emoji(preg_replace('/<!--.*?-->/s', '', $html));
    $doc = load_html($html);
    $root = $doc->getElementById('root');
    $out = new DOMDocument('1.0', 'UTF-8');
    $body = $out->createElement('div');
    $out->appendChild($body);
    sanitize_children($root, $body, $out);

    // 段落の外に出た文字は段落で包む
    $wrapped = $out->createElement('div');
    $pending = null;
    foreach (iterator_to_array($body->childNodes) as $n) {
        $inline = $n instanceof DOMText || in_array($n->nodeName, ['strong', 'em', 'a', 'br'], true);
        if ($inline) {
            if ($n instanceof DOMText && trim($n->textContent) === '' && $pending === null) {
                continue;
            }
            $pending ??= $wrapped->appendChild($out->createElement('p'));
            $pending->appendChild($n);
        } else {
            $pending = null;
            $wrapped->appendChild($n);
        }
    }

    // 空の要素・前後の改行を除く
    $changed = true;
    while ($changed) {
        $changed = false;
        foreach (iterator_to_array((new DOMXPath($out))->query('.//*', $wrapped)) as $el) {
            if ($el->nodeName === 'br') {
                continue;
            }
            trim_br($el);
            $text = str_replace(["\u{00A0}", "\u{3000}"], '', $el->textContent);
            if (trim($text) === '' && !$el->getElementsByTagName('br')->length) {
                $el->parentNode->removeChild($el);
                $changed = true;
            } elseif (trim($text) === '' && in_array($el->nodeName, ['p', 'h2', 'h3', 'h4', 'li', 'strong', 'em', 'a'], true)) {
                $el->parentNode->removeChild($el);
                $changed = true;
            }
        }
    }

    $parts = [];
    foreach ($wrapped->childNodes as $n) {
        $s = trim($out->saveHTML($n));
        if ($s !== '') {
            $parts[] = $s;
        }
    }
    return fix_markdown_bold(implode("\n", $parts));
}

function trim_br(DOMElement $el): void
{
    foreach (['firstChild', 'lastChild'] as $end) {
        while (($n = $el->$end) && (($n instanceof DOMText && trim(str_replace("\u{00A0}", '', $n->textContent)) === '') || $n->nodeName === 'br')) {
            $el->removeChild($n);
        }
    }
}

function sanitize_children(DOMNode $from, DOMElement $to, DOMDocument $out): void
{
    $rename = ['h1' => 'h2', 'h5' => 'h3', 'h6' => 'h3', 'b' => 'strong', 'i' => 'em'];
    $keep = ['p', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'strong', 'em', 'a', 'br'];
    $drop = ['script', 'style', 'noscript', 'img', 'svg', 'iframe', 'figcaption', 'button', 'form', 'input', 'template'];
    foreach ($from->childNodes as $n) {
        if ($n instanceof DOMText) {
            $to->appendChild($out->createTextNode(preg_replace('/[ \t\r\n]+/u', ' ', $n->textContent)));
            continue;
        }
        if (!$n instanceof DOMElement) {
            continue;
        }
        $name = strtolower($n->nodeName);
        if (in_array($name, $drop, true)) {
            continue;
        }
        $name = $rename[$name] ?? $name;
        if (!in_array($name, $keep, true)) {
            sanitize_children($n, $to, $out); // div・span などは外して中身だけ残す
            continue;
        }
        $el = $out->createElement($name);
        if ($name === 'a') {
            $href = trim($n->getAttribute('href'));
            // 画像ファイルへのリンク（ギャラリーの拡大用）は不要
            if ($href === '' || preg_match('/\.(jpe?g|png|gif|webp)$/i', $href) || preg_match('#^\s*javascript:#i', $href)) {
                sanitize_children($n, $to, $out);
                continue;
            }
            $el->setAttribute('href', $href);
        }
        $to->appendChild($el);
        if ($name !== 'br') {
            sanitize_children($n, $el, $out);
        }
    }
}

/** 本文から説明文を作る（先頭 110 字）。 */
function auto_description(string $bodyHtml): string
{
    $text = html_entity_decode(strip_tags(preg_replace('/<(h[2-4])>.*?<\/\1>/su', '', $bodyHtml)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim(preg_replace('/\s+/u', '', $text));
    return mb_strlen($text) > 110 ? mb_substr($text, 0, 109) . '…' : $text;
}
