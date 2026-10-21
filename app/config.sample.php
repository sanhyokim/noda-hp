<?php
// 設定ファイルの見本。これをコピーして app/config.php を作り、中身を書き換えてください。
// app/config.php は git に入れません（パスワードを含むため）。
return [
    // サイトを置く場所。本番は '/'、試験用の場所は '/new/'。最初と最後の「/」を付けてください。
    'base_url' => '/',

    // サイトの正式なアドレス（canonical や OGP に使う）。最後の「/」は付けません。
    'site_origin' => 'https://duct-noda.com',

    // 本番では false（エラーを画面に出さず storage/logs/ に記録する）。
    'debug' => false,

    // データベース。空のままならダミーデータで表示します（段階1の確認用）。
    'db' => [
        'host' => 'localhost',
        'name' => '',
        'user' => '',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],

    // Google タグマネージャーのコンテナ ID。空にすると読み込みません。
    'gtm_id' => 'GTM-N3DNWHNN',

    // 試験用：「今日の日付」を固定したい時だけ 'YYYY-MM-DD' を入れる（例 '2027-01-01'）。普段は null。
    'today_override' => null,
];
