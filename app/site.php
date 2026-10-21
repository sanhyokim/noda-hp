<?php
// サイト全体の設定。左カラム・フッター・パンくず・sitemap はすべてこの配列から作る。
// 固定ページを増やす時は 'pages' に1行足し、app/pages/<key>.php を作る。
return [
    'company' => [
        'name' => '株式会社野田',
        'name_en' => 'Noda INC.',
        'tel_free' => '0120-77-3408',
        'tel' => '092-504-5502',
        'fax' => '092-504-5518',
        'hours' => '9:00〜17:00',
        'closed' => '土日休み', // ボス確認済み：営業日は月〜金（祝日の扱いは未確認）
        'hq_zip' => '812-0854',
        'hq_address' => '福岡県福岡市博多区東月隈3-5-4',
        'hq_region' => '福岡県',
        'hq_locality' => '福岡市博多区',
        'hq_street' => '東月隈3-5-4',
        'branch_name' => '広島出張所',
        'branch_zip' => '731-0113',
        'branch_address' => '広島市安佐南区西原1-8-16-405',
        'founded_year' => 2005,
        // 営業日（曜日）。ボス確認済み：月〜金
        'business_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
        'line_url' => 'https://line.me/R/ti/p/@254bbzux',
        'instagram_url' => 'https://www.instagram.com/noda_duct/',
        'instagram_handle' => 'noda_duct',
    ],

    // key => [path, 名前（メニュー・パンくず）, title, description]
    'pages' => [
        'home' => [
            'path' => '',
            'name' => 'ホーム',
            'title' => '飲食店の換気扇・排煙ダクト工事｜福岡発・全国対応の株式会社野田',
            'description' => '焼肉店・焼き鳥店・居酒屋など飲食店専門の換気扇・排煙ダクト工事会社。創業2005年、施工実績1,000件以上。設計から製作・施工・修理まで自社一貫、現地調査無料。福岡・九州・中国地方を中心に全国対応。',
        ],
        'concept' => [
            'path' => 'concept/',
            'name' => 'コンセプト',
            'title' => 'コンセプト｜飲食店専門・自社一貫のダクト工事【株式会社野田】',
            'description' => '株式会社野田は飲食店のダクト工事に特化した専門会社です。提案・設計・製作・施工・メンテナンスまで自社一貫、1mm単位のオーダーメイド設計、施工後のアフターフォローまで。福岡から全国の飲食店を支えます。',
        ],
        'about_duct_work' => [
            'path' => 'about_duct_work/',
            'name' => '店舗・飲食店のダクト工事について',
            'title' => '飲食店のダクト工事サービス一覧｜厨房換気・煙対策・焼肉店向け【株式会社野田】',
            'description' => '厨房換気扇・レンジフードのダクト工事、店舗の煙・臭いトラブル解決、焼肉店の上引きフード、無煙ロースター導入工事の4つを中心に、新規開業から既存店の改善まで対応。店舗に合わせたオーダーメイド設計です。',
        ],
        'new_stores' => [
            'path' => 'new_stores/',
            'name' => '新規店舗の開業をお考えの方へ',
            'title' => '飲食店の新規開業と換気・ダクト工事｜図面段階からご相談を【株式会社野田】',
            'description' => '飲食店の新規開業では、換気扇・排煙ダクトの設計を後回しにすると近隣トラブルや営業への支障につながります。図面の段階から給排気のバランスを計算し、開業後の煙・臭い・吸い込み不良を防ぎます。',
        ],
        'duct-hood' => [
            'path' => 'duct-hood/',
            'name' => '厨房換気扇・レンジフードのダクト工事',
            'title' => '厨房換気扇・レンジフードのダクト工事｜吸わない・効きすぎを解決【株式会社野田】',
            'description' => '厨房が暑い、エアコンが効かない、煙がこもる、ドアが重い。原因の多くは給排気のバランスです。厨房機器の熱量から排気量を計算し、店舗ごとにオーダーメイド設計。博多一双様など改善事例あり。',
        ],
        'smoke_problems' => [
            'path' => 'smoke_problems/',
            'name' => '店舗での煙・臭いのトラブル解決工事',
            'nav' => '煙・臭いのトラブル解決工事',
            'title' => '飲食店の煙・臭いトラブル解決工事｜近隣苦情・客席の煙を根本改善【株式会社野田】',
            'description' => '客席に煙がこもる、近隣から臭いの苦情が来た。原因調査のうえ、消煙機（電気集塵機）の導入、ダクトの延長・位置変更、排気口の移設で根本から解決します。行政の改善指導をクリアした事例もあります。',
        ],
        'hood-installation' => [
            'path' => 'hood-installation/',
            'name' => '焼肉店 上引きフードのダクト工事',
            'title' => '焼肉店の上引きフード工事｜特許取得NC-02クリアーフード【株式会社野田】',
            'description' => '自社特許（2件）の上引きフード「NC-02クリアーフード」。少ない排煙量で従来型と同等の吸煙力、工事費は当社従来型比で約40%減、冷暖房費も節約。スリムで客席の視界を妨げません。店の内装に合わせた特注製作も可能。',
        ],
        'smokeless_roster' => [
            'path' => 'smokeless_roster/',
            'name' => '焼肉店 無煙ロースターのダクト工事',
            'title' => '焼肉店の無煙ロースター導入工事｜特許取得NEO NC-22熔岩石【株式会社野田】',
            'description' => '業界初の特許取得「NEOシリーズ NC-22熔岩石」無煙ロースターの導入とダクト工事。炎の燃え上がりを抑えてダクト火災を防ぎ、富士山産熔岩石の遠赤外線でジューシーに。デモ機で試してから導入できます。',
        ],
        'construction_examples' => [
            'path' => 'construction_examples/',
            'name' => '施工事例',
            'title' => '施工事例｜飲食店の換気・排煙ダクト工事の実績【株式会社野田】',
            'description' => '焼肉店・焼き鳥店・居酒屋・ラーメン店など、飲食店の換気扇・排煙ダクト工事の施工事例を写真付きで紹介。業態と工事の種類で絞り込めます。創業からの施工実績1,000件以上の中から抜粋して掲載。',
        ],
        'flow' => [
            'path' => 'flow/',
            'name' => '施工までの流れ',
            'title' => '施工までの流れ｜お問い合わせから工事完了・アフターまで8ステップ【株式会社野田】',
            'description' => 'お問い合わせ、無料の現地調査、提案・お見積り、契約、詳細資料の確認、施工、立ち会いでの最終チェック、アフターフォローまで。初回から同じ担当者が対応するので、初めての方も安心です。',
        ],
        'afterfollow' => [
            'path' => 'afterfollow/',
            'name' => 'アフターフォロー',
            'title' => 'アフターフォロー｜飲食店ダクトの点検・清掃・ファン交換【株式会社野田】',
            'description' => '工事後も同じ担当者が保守点検・修理・清掃まで対応。油汚れを放置すると吸い込み低下・異音・ダクト火災の原因になります。5項目のチェックリストで、今の状態を確認してください。',
        ],
        'question' => [
            'path' => 'question/',
            'name' => 'よくある質問',
            'title' => 'よくある質問｜飲食店の換気扇・排煙ダクト工事Q&A【株式会社野田】',
            'description' => '対応できるダクトの種類、修理・メンテナンス、特殊な店舗形状、費用の考え方、予算対応、おすすめの材質・フード・ロースター、対応エリア、見積りの依頼方法など、飲食店オーナー様からよくいただく質問にお答えします。',
        ],
        'company' => [
            'path' => 'company/',
            'name' => '会社概要',
            'title' => '会社概要｜飲食店専門のダクト工事会社 株式会社野田（福岡市博多区）',
            'description' => '株式会社野田は2005年11月設立。福岡市博多区本社、広島出張所。飲食店の厨房換気扇・排煙ダクトの設計施工、焼肉店向け上引きフード・無煙ロースターの導入、保守点検・改修を行います。代表 頃安照義。',
        ],
        'customer' => [
            'path' => 'customer/',
            'name' => '設計・施工業者様へ',
            'title' => '設計事務所・内装施工業者様へ｜飲食店の換気・排煙を一貫対応【株式会社野田】',
            'description' => '建築設計事務所・店舗デザイナー・設備工事会社様向け。飲食店の給排気計算、フード・ダクトの設計施工、施工後の煙漏れ・騒音・近隣苦情の調査と改善まで一貫対応。お見積りは無料です。',
        ],
        'contact' => [
            'path' => 'contact/',
            'name' => 'お問い合わせ',
            'title' => 'お問い合わせ｜飲食店の換気・排煙ダクト工事のご相談【株式会社野田】',
            'description' => '換気扇・排煙ダクト工事、煙・臭い対策のご相談は電話・メールフォーム・LINEで。新規開業、既存店の改善・修理、協力会社としてのご連絡も受け付けます。現地調査・お見積りは無料です。',
        ],
        'news' => [
            'path' => 'news/',
            'name' => 'お知らせ',
            'title' => 'お知らせ｜株式会社野田',
            'description' => '株式会社野田からのお知らせと最新の施工事例をお届けします。',
        ],
        'privacypolicy' => [
            'path' => 'privacypolicy/',
            'name' => 'プライバシーポリシー',
            'title' => 'プライバシーポリシー｜株式会社野田',
            'description' => '株式会社野田の個人情報の取り扱いについて。',
        ],
    ],

    // 左カラムのメニュー（上から順）。'children' は一段下げて表示する。
    'nav' => [
        ['key' => 'home'],
        ['key' => 'concept'],
        ['key' => 'about_duct_work', 'label' => 'サービス', 'children' => [
            'about_duct_work', 'new_stores', 'duct-hood', 'smoke_problems', 'hood-installation', 'smokeless_roster',
        ]],
        ['key' => 'construction_examples'],
        ['key' => 'flow'],
        ['key' => 'afterfollow'],
        ['key' => 'question'],
        ['key' => 'company'],
        ['key' => 'customer'],
        ['key' => 'contact'],
    ],

    // PC 上部の横並びメニュー（現行サイトと同じ並び）。'children' はマウスを乗せると開く。
    'gnav' => [
        ['key' => 'home'],
        ['key' => 'concept'],
        ['key' => 'about_duct_work', 'label' => 'サービス', 'children' => [
            'about_duct_work', 'new_stores', 'duct-hood', 'smoke_problems', 'hood-installation', 'smokeless_roster',
        ]],
        ['key' => 'construction_examples'],
        ['key' => 'flow'],
        ['key' => 'afterfollow'],
        ['key' => 'question'],
        ['key' => 'company', 'children' => ['company', 'customer', 'news', 'privacypolicy']],
    ],

    // 左カラムの見出し付きメニュー（現行サイトの「ダクト工事の巧みな技」「会社案内」）
    'side_groups' => [
        ['title' => 'ダクト工事の巧みな技', 'en' => 'SERVICE', 'keys' => [
            'new_stores', 'about_duct_work', 'duct-hood', 'smoke_problems', 'hood-installation', 'smokeless_roster',
        ]],
        ['title' => '会社案内', 'en' => 'COMPANY', 'keys' => [
            'home', 'concept', 'company', 'customer', 'flow', 'afterfollow', 'construction_examples', 'question', 'news', 'contact',
        ]],
    ],

    // フッターのリンク（横並び）
    'footer' => [
        'concept', 'about_duct_work', 'construction_examples', 'flow', 'afterfollow',
        'question', 'company', 'customer', 'news', 'contact', 'privacypolicy',
    ],

    // トップの「お知らせ」5件。現行サイトのトップのお知らせ欄と同じ日付・題名（ボス指示 2026-10-21）。
    // 'wp_id' の施工事例のページへつなぐ。お知らせの管理画面（段階5）ができたら、そちらに移す。
    'top_news' => [
        ['date' => '2026-06-17', 'wp_id' => 4605, 'title' => 'パン屋さんの天井の低い難所をクリアした特注ダクト工事'],
        ['date' => '2026-06-10', 'wp_id' => 4603, 'title' => '大容量の特注「大型山型フード」が誕生！'],
        ['date' => '2026-06-03', 'wp_id' => 4601, 'title' => '平尾の人気店に消煙機を設置しました！'],
        ['date' => '2026-05-20', 'wp_id' => 4570, 'title' => '漆黒の美学。鶏焼肉店のダクト施工が完了！'],
        ['date' => '2026-05-06', 'wp_id' => 4568, 'title' => '「煙を出さない」焼肉店！消煙機導入工事完了'],
    ],

    // 左カラム上の動画と施工事例バナー（現行サイトと同じもの。動画は押したときだけ YouTube を読み込む）
    'side_video' => ['youtube_id' => 'Bd9sD0_vSvU', 'image' => 'side-video.jpg', 'title' => 'この吸引力をご覧ください！（消煙機の動画）'],
    'side_cases' => ['image' => 'side-cases.jpg', 'w' => 540, 'h' => 425, 'alt' => '全てオーダーメイド設計 施工事例'],

    // 左カラム下のバナー。w・h は元画像の大きさ（文字入りなので比率を変えず、元より大きく引き伸ばさない）
    'banners' => [
        ['image' => 'banner-shoenki.png', 'w' => 260, 'h' => 112, 'alt' => '野田の消煙機', 'url' => 'https://shoenki.duct-noda.com/'],
        ['image' => 'banner-cleaning.jpg', 'w' => 1200, 'h' => 600, 'alt' => 'ダクト清掃', 'url' => 'https://www.cleaning-duct.com/'],
    ],
];
