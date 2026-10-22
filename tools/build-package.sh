#!/bin/sh
# サーバーに上げるファイル一式を作る（指示書 段階7）。
# 出来上がり：dist/upload/ の noda-site-1-of-N.zip … と install.php（全部を FTP で置きたいフォルダへ上げる）
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
# 置いた後のデータの手直し（管理画面を開いた時に1回だけ流れる）
mkdir -p "$PKG/migration/fixes"
cp migration/fixes/*.sql "$PKG/migration/fixes/"
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

# zip（1つ90MBまでに分ける。大きすぎるファイルは送る途中で切れやすいため。写真はもう縮んでいるので、そのまま入れる）
(cd "$PKG" && find . -type f | sed 's#^\./##' | sort) > "$DIST/files.txt"
(cd "$PKG" && python3 - "$DIST/files.txt" "$DIST/part" <<'PY'
import os, sys
files = open(sys.argv[1], encoding='utf-8').read().splitlines()
limit, parts, cur, size = 90 * 1024 * 1024, [], [], 0
for f in files:
    s = os.path.getsize(f)
    if cur and size + s > limit:
        parts.append(cur); cur, size = [], 0
    cur.append(f); size += s
parts.append(cur)
for n, group in enumerate(parts, 1):
    open(f'{sys.argv[2]}-{n}.txt', 'w', encoding='utf-8').write('\n'.join(group) + '\n')
print(len(parts))
PY
) > "$DIST/count.txt"
N=$(cat "$DIST/count.txt")
i=1
while [ "$i" -le "$N" ]; do
  (cd "$PKG" && zip -q -n .jpg:.jpeg:.webp:.png:.gif:.mp4:.woff2 "$DIST/upload/noda-site-$i-of-$N.zip" -@ < "$DIST/part-$i.txt")
  i=$((i + 1))
done
cp tools/install.php "$DIST/upload/install.php"

echo "できました：dist/upload/（zip $N 個と install.php）"
ls -la "$DIST/upload"
echo "ファイル数：$(wc -l < "$DIST/files.txt")"
