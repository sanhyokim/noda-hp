<?php
// 設定ファイルの見本。これをコピーして app/config.php を作り、中身を書き換えてください。
// app/config.php は git に入れません（パスワードを含むため）。
return [
    // サイトを置く場所。'auto' なら置いたフォルダから決める（/new/ に置けば /new/、本番の場所に移せば /）。
    // 決めて書く時は '/' や '/new/' のように最初と最後に「/」を付ける。
    'base_url' => 'auto',

    // サイトの正式なアドレス（canonical や OGP に使う）。最後の「/」は付けません。
    'site_origin' => 'https://duct-noda.com',

    // 試験用の場所（/new/）では true にして、検索エンジンに載らないようにする。本番では false。
    'noindex' => false,

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

    // お問い合わせフォームのメール（指示書 4-6）
    'mail' => [
        // 届け先（4件すべてに送る。1件目だけドメインが noda-duct.com で正しい）
        'to' => ['info@noda-duct.com', 'koroyasu@duct-noda.com', 'azuma@duct-noda.com', 'nagamizu@duct-noda.com'],
        'from' => 'noreply@duct-noda.com',
        'from_name' => '株式会社野田',
        // 送り方：'mail'（サーバーの送信機能。普段はこれ）/ 'smtp'（届かない時に切り替える）/ 'log'（試験用。送らずに storage/logs/mail/ に保存）
        'transport' => 'mail',
        // transport を 'smtp' にした時だけ使う（コアサーバーのメールアカウントの情報）
        'smtp' => ['host' => '', 'port' => 587, 'user' => '', 'pass' => '', 'secure' => 'tls'],
    ],

    // AI チャット（右下の「AIに相談」）。api_key が空なら表示しない。
    // api_key は Anthropic（Claude）のコンソールで発行したキー。git には入れない。ふつうは管理画面の「設定」に貼る（そちらが優先）。
    'chat' => [
        'api_key' => '',
        'model' => 'claude-opus-5-5',
        'monthly_limit' => 3000,   // 1か月に答える回数の上限（使いすぎ防止。超えたら電話・フォームへ案内）
        'per_ip_10min' => 15,      // 同じ人が10分間に送れる回数
        'per_ip_day' => 60,        // 同じ人が1日に送れる回数
        'base_url' => 'https://api.anthropic.com',
        // 管理ページに出す「今月の費用の目安」の計算用（1百万トークンあたりのドル。Anthropic の料金表 2026年10月時点）
        'price_usd' => ['input' => 4.0, 'output' => 20.0, 'cache_read' => 0.2, 'cache_write' => 5.0],
        'yen_per_usd' => 155,
        'log_days' => 365,         // 会話の記録を残す日数（過ぎた分は自動で消す）
    ],

    // 試験用：「今日の日付」を固定したい時だけ 'YYYY-MM-DD' を入れる（例 '2027-01-01'）。普段は null。
    'today_override' => null,
];
