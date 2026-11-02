<?php
declare(strict_types=1);

// パンくずの配列 [[名前, パス], ...]。先頭はホーム。
function breadcrumb_items(array $trail): array
{
    return array_merge([[page('home')['name'], '']], $trail);
}

function organization_ld(): array
{
    return [
        '@type' => 'Organization',
        '@id' => abs_url() . '#organization',
        'name' => company('name'),
        'url' => abs_url(),
        'logo' => abs_url('assets/img/logo.png'),
        'telephone' => company('tel_free'),
        'faxNumber' => company('fax'),
        'foundingDate' => '2005-11',
        'sameAs' => [company('instagram_url')],
    ];
}

function local_business_ld(): array
{
    return [
        '@type' => 'HomeAndConstructionBusiness',
        '@id' => abs_url() . '#localbusiness',
        'name' => company('name'),
        'url' => abs_url(),
        'image' => abs_url('assets/img/og-default.jpg'),
        'telephone' => company('tel_free'),
        'address' => [
            '@type' => 'PostalAddress',
            'postalCode' => company('hq_zip'),
            'addressRegion' => company('hq_region'),
            'addressLocality' => company('hq_locality'),
            'streetAddress' => company('hq_street'),
            'addressCountry' => 'JP',
        ],
        // 営業日（曜日）は site.php の business_days。未設定なら曜日を出さない。
        'openingHoursSpecification' => [array_filter([
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => site('company')['business_days'] ?? null,
            'opens' => '09:00',
            'closes' => '17:00',
        ])],
        'areaServed' => '日本',
        'parentOrganization' => ['@id' => abs_url() . '#organization'],
    ];
}

function breadcrumb_ld(array $items): array
{
    $list = [];
    foreach ($items as $i => [$name, $path]) {
        $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name, 'item' => abs_url($path)];
    }
    return ['@type' => 'BreadcrumbList', 'itemListElement' => $list];
}

/** 施工事例・お知らせの詳しいページ用（見出し・画像・公開日） */
function article_ld(string $headline, string $path, string $published, string $modified, array $images): array
{
    $ld = [
        '@type' => 'Article',
        'headline' => mb_strimwidth($headline, 0, 110, '…'),
        'mainEntityOfPage' => abs_url($path),
        'datePublished' => date('c', strtotime($published)),
        'dateModified' => date('c', strtotime($modified ?: $published)),
        'author' => ['@id' => abs_url() . '#organization'],
        'publisher' => ['@id' => abs_url() . '#organization'],
    ];
    if ($images) {
        $ld['image'] = array_values($images);
    }
    return $ld;
}

function json_ld(array $graph): string
{
    $data = ['@context' => 'https://schema.org', '@graph' => $graph];
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . '</script>';
}
