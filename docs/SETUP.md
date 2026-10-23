# 手元で動かす手順（開発用）

このサイトは PHP だけで動きます。特別な準備（ビルドなど）はいりません。

## 1. 必要なもの
- PHP 8.2 以上（GD・mbstring・PDO MySQL が使えること）
  - 本番（コアサーバー）では、公開前と `/new/` での確認時に、管理画面で PHP 8.2 以上を選び、その版が最新のセキュリティ修正版になっていることを確かめてください。2026-10-21 に PHP 本体の重大な不具合（通称 php2shell）の修正版が出る予定です。版番号は php.net の正式発表で確認します。
- MySQL 8 または MariaDB 10.6 以上（段階3から使います。段階1・2はなくても表示できます）

## 2. 設定ファイルを作る
1. `app/config.sample.php` をコピーして、同じ場所に `app/config.php` という名前で置きます。
2. 中身を書き換えます。
   - `base_url`：`'auto'` のままで、置いたフォルダに合わせて決まります（手元は `/`、試験用の場所は `/new/`）。サーバーへの置き方は `docs/DEPLOY.md`。
   - `db`：データベースの名前・ユーザー・パスワード。空のままだと、トップは見本用のダミーデータで表示されます。
   - `debug`：手元では `true`、本番では必ず `false`。

## 3. 起動する
リポジトリの一番上のフォルダで次を実行します。

```
php -S 127.0.0.1:8080 -t public public/index.php
```

ブラウザで http://127.0.0.1:8080/ を開くとトップページが出ます。

## 4. データベース（段階3で使用）
Docker を使う場合の例：

```
docker run -d --name noda-db -e MARIADB_ROOT_PASSWORD=root -e MARIADB_DATABASE=noda \
  -e MARIADB_USER=noda -e MARIADB_PASSWORD=noda -p 3306:3306 mariadb:10.11
```

`app/config.php` の `db` を `host=127.0.0.1 / name=noda / user=noda / pass=noda` にします。
テーブルの作成は段階5の `/admin/setup.php` で行います（段階3では移行スクリプトが作ります）。

### 現行サイトの施工事例を移す（段階3）
1. `php migration/fetch.php` … 現行サイトから記事 168 件の情報を取って `migration/source/` に保存（保存済みなので普段は不要）。
2. `php migration/migrate.php` … テーブル作成、カテゴリ投入、記事の取り込み、写真の取得と縮小版づくり、`migration/report.csv` の出力。何度実行しても同じ結果になります。最初の1回は写真の取得と変換で7分ほどかかり、2回目からは1秒ほどです。
- 写真は `public/wp-content/uploads/`（元の URL と同じ場所）と `public/media/cases/`（表示用）にできます。合わせて約1GBあるため git には入れていません。
- 試験：`php tests/migration_test.php`（カテゴリの振り分けと場所の取り出し、題名20件）。

## 5. 画像を置く
ボスから届いた画像（ファイル名は指示書の付録C）は `public/assets/img/` に置くだけで表示が切り替わります。
置いていない画像は、灰色の枠にファイル名が出ます。

## 6. フォルダの役割
| フォルダ | 中身 |
|---|---|
| `public/` | サーバーの公開フォルダに丸ごと置くもの（入口の index.php、CSS・JS・画像） |
| `app/` | 設定・部品・各ページの中身（公開フォルダの外に置く） |
| `app/site.php` | ページの一覧・メニュー・フッター・会社情報。ページを増やす時はここに1行足す |
| `app/pages/` | ページごとの中身（1ページ1ファイル） |
| `app/templates/` | 左カラム・フッター・相談の呼びかけ帯などの共通部品 |
| `storage/` | 書き込み用（キャッシュ・記録・お問い合わせの添付） |
| `design/` | トップの見本（案C）。参照用で公開しない |
| `docs/` | 手順書・仕様書 |
