#!/bin/sh
# 置いた後の手直しを、変わったファイルだけの小さな zip にする（指示書 段階7）。
# 出来上がり：dist/update/noda-site-1-of-1.zip と install.php（2つを同じフォルダへ上げて install.php を開くと上書きされる）
# 設定ファイル・データベース・管理画面で上げた写真には触らない。消えたファイルはサーバーに残る（害はない）。
# 実行：sh tools/build-update.sh <前に渡した時のコミット>
set -eu

[ $# -eq 1 ] || { echo "使い方：sh tools/build-update.sh <前に渡した時のコミット>"; exit 1; }
ROOT=$(cd "$(dirname "$0")/.." && pwd)
OUT="$ROOT/dist/update"
STAGE="$ROOT/dist/update-pkg"
cd "$ROOT"

rm -rf "$OUT" "$STAGE"
mkdir -p "$OUT" "$STAGE"
# 公開フォルダの中身は一番上に、app/ はそのまま（サーバーでは公開フォルダの中に app/ がある）
git diff --name-only --diff-filter=AMR "$1" HEAD -- public app migration/fixes migration/schema.sql migration/seed.sql | while read -r f; do
  case "$f" in
    app/config.php|public/wp-content/*) continue ;;
    public/*) to=${f#public/} ;;
    *) to=$f ;;
  esac
  mkdir -p "$STAGE/$(dirname "$to")"
  cp -p "$f" "$STAGE/$to"
done
[ -n "$(ls -A "$STAGE")" ] || { echo "変わったファイルがありません"; exit 1; }

(cd "$STAGE" && zip -q -r "$OUT/noda-site-1-of-1.zip" .)
cp tools/install.php "$OUT/install.php"
echo "できました：dist/update/"
unzip -Z1 "$OUT/noda-site-1-of-1.zip"
