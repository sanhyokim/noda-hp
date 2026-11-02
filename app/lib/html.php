<?php
declare(strict_types=1);

// 本文の HTML を整える（移行スクリプトと管理ページの保存で共通）。

function load_html(string $html): DOMDocument
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><html><body><div id="root">' . $html . '</div></body></html>', LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    return $doc;
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
            // 使えるのは http(s)・メール・電話・サイト内のリンクだけ（javascript: などは外す）
            if ($href === '' || preg_match('/\.(jpe?g|png|gif|webp)$/i', $href) || !preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $href)) {
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

/**
 * 本文を許可した要素だけに整える（指示書 3-3-3・3-3-4、管理ページの保存時も同じ）。画像は取り除く（写真は本文の下の一覧に出すため）。
 * 残す：p, h2〜h4, ul, ol, li, strong, em, a(href), br。h5/h6→h3、h1→h2、b→strong、i→em。
 */
function clean_body_html(string $html): string
{
    $html = preg_replace('/<!--.*?-->/s', '', $html);
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
    return implode("\n", $parts);
}

/** 題名から URL 用の名前を作る（日本語のまま。記号と空白を除き、旧サイトと同じく長すぎないよう 40 字まで）。 */
function make_slug(string $title): string
{
    $s = mb_strtolower(trim($title));
    $s = preg_replace('/[\s\x{3000}]+/u', '-', $s);
    $s = preg_replace('/[\/\\?#%&=+:;"\'<>\[\]{}|^`!*(),.@$~]+/u', '', $s);
    $s = trim(preg_replace('/-+/', '-', $s), '-');
    return mb_substr($s, 0, 40);
}
