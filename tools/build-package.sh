#!/bin/sh
# サーバーに上げるファイル一式を作る（指示書 段階7）。
# 出来上がり：dist/upload/noda-site.zip と dist/upload/install.php（この2つを FTP で置きたいフォルダへ上げる）
# zip の中は「公開フォルダの中に app/ などを置く」形（コアサーバー向け。app/ storage/ vendor/ migration/ は .htaccess で閉じる）。
# 実行：sh tools/build-package.sh（先に php tools/export-seed.php で migration/seed.sql を最新にしておく）
set -eu

ROOT=$(cd "$(dirname "$0")/.." && pwd)
DIST="$ROOT/dist"
PKG="$DIST/pkg"
cd "$ROOT"

[ -f migration/seed.sql ] || { echo "migration/seed.sql がありません。先に php tools/export-seed.php を実行してください"; exit 1; }
[ -d public/media/cases ] || { echo "public/media/cases がありません（php migration/migrate.php で作る）"; exit 1; }

rm -rf "$DIST"
mkdir -p "$PKG" "$DIST/upload"

# 公開ファイル（旧サイトの写真の控え wp-content/ は持っていかない。本番の場所には元からある）
rsync -a --exclude 'wp-content/' --exclude '.DS_Store' public/ "$PKG/"

# プログラム・保存場所（git に入っているものだけ。app/config.php は最初の設定画面で作る）
git ls-files app storage | while read -r f; do
  mkdir -p "$PKG/$(dirname "$f")"
  cp -p "$f" "$PKG/$f"
done

# データベースの元
mkdir -p "$PKG/migration"
cp migration/schema.sql migration/seed.sql "$PKG/migration/"
printf 'Require all denied\n' > "$PKG/migration/.htaccess"

# 部品（開発用は入れない）
mkdir -p "$DIST/composer"
cp composer.json composer.lock "$DIST/composer/"
composer install -q -d "$DIST/composer" --no-dev --prefer-dist --optimize-autoloader --no-interaction
mv "$DIST/composer/vendor" "$PKG/vendor"
printf 'Require all denied\n' > "$PKG/vendor/.htaccess"

# 確かめ：持っていってはいけないもの
for bad in app/config.php storage/logs/php-error.log; do
  [ ! -e "$PKG/$bad" ] || { echo "入ってはいけないファイルがあります: $bad"; exit 1; }
done
for need in index.php .htaccess app/.htaccess storage/.htaccess vendor/autoload.php migration/seed.sql app/admin/setup.php; do
  [ -e "$PKG/$need" ] || { echo "足りないファイルがあります: $need"; exit 1; }
done

# zip（写真はもう縮んでいるので、そのまま入れる）
(cd "$PKG" && zip -q -r -n .jpg:.jpeg:.webp:.png:.gif:.mp4:.woff2 "$DIST/upload/noda-site.zip" .)
cp tools/install.php "$DIST/upload/install.php"

echo "できました：dist/upload/"
ls -la "$DIST/upload"
echo "ファイル数：$(unzip -Z1 "$DIST/upload/noda-site.zip" | wc -l)"
