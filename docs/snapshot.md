# 現状のまとめ（2026-10-22 時点）

新しい公式サイトは出来上がり、試験用の場所 https://duct-noda.com/new/ に置いて Boss が確認を済ませた（停止点4：投稿・直し・削除、メール、全ページ OK）。
残りは **本番への切り替え** と、Boss の側で用意するもの（AI 相談のキー、Instagram）。

## どこに何があるか

| もの | 場所 |
|---|---|
| ソース | GitHub `sanhyokim/noda-hp`、ブランチ `stage3-migration`（段階3〜8の作業はすべてここ） |
| 指示書 | `docs/brief/HP-1_v2.md` |
| 最終仕様 | `docs/SPEC.md`（指示書からの変更点と理由） |
| 置き方・本番切り替え | `docs/DEPLOY.md`（A：/new/ に置く、B：本番に切り替える、8：手直しの入れ方） |
| 管理画面の使い方 | `docs/ADMIN_GUIDE.md` |
| Instagram のつなぎ方 | `docs/INSTAGRAM.md` |
| 手元で動かす手順 | `docs/SETUP.md` |
| Lighthouse | `docs/lighthouse/` |
| サーバー | コアサーバー（LiteSpeed、PHP 8.2.35）。ファイルマネージャーの `duct-noda.com/public_html` が現行の WordPress、その中の `new` が新しいサイト |
| データベース | 新しいサイト用に Boss が作ったもの（名前は Boss が /new/ の最初の設定で入力）。現行の WordPress のものとは別 |

## 終わっていること

- 固定ページ17＋404、施工事例168件（旧サイトと同じアドレス）・写真1,128枚、お知らせ、旧アドレスの転送
- デザインは現行サイトを土台に作り直し、Boss の指示を全部反映（人物の AI 画像は、すべて Boss が選んだ写真に差し替え）
- 管理画面（施工事例・お知らせ・カテゴリ・設定）、予約投稿、下書きの確認表示
- お問い合わせ（担当4人へのメールと自動返信。件名で公式 HP からと分かる）
- AI 相談（OpenRouter と Anthropic の両方に対応。キーを貼れば動く）
- Instagram の自動表示（トークンを貼れば動く。それまでは代わりの写真6枚）
- 旧サイトから引き継いだ99件の投稿日の振り分け（管理画面を開いた時に自動で入る）
- 手順書（DEPLOY・ADMIN_GUIDE・INSTAGRAM・SPEC）
- 試験：移行・全アドレス（手元の php と Apache の両方）・フォーム・管理画面、すべて合格。4つの幅（360・768・1280・1920px）× 19画面（固定ページ・404・施工事例）で横スクロールなし

## 残っていること

| こと | だれが | メモ |
|---|---|---|
| 本番への切り替え | Boss（制作側が付き添い） | 前日までに `public_html` の一覧の写真を送ってもらい、移すものの名前を合わせてから。DEPLOY.md B |
| Google アナリティクスの確認 | Boss（設定した人） | 今のサイトの Site Kit 由来のタグ（GT-NGKV3494）は切り替えで無くなる。GTM-N3DNWHNN に GA4 が入っているか確かめる |
| AI 相談のキー | Boss | https://openrouter.ai/keys で作り、管理画面の「設定」→「AI 相談」に貼る |
| Instagram の接続 | Boss | INSTAGRAM.md |
| 業態が分からない6件（27・56・63・85・86・115） | Boss | 分かれば管理画面で直す。分からなければ「その他」のまま |
| 粗い写真の差し替え | Boss | 新規開業の事例3枚＋焼き鳥、消煙機・ウォーターフィルター、NC-02 A/B と施工2枚、ロスター＋店内4枚＋大入り様2枚、消煙機のバナーの大きい版（元が 228px 前後） |
| 表示の速さの調整 | 制作側（Boss と相談） | 本番で https://pagespeed.web.dev/ で測ってから。トップの流れる写真と日本語フォントが主な原因（docs/lighthouse/README.md） |
| 落ち着いた後の片付け | Boss | 1か月ほど問題がなければ `wp-old` と旧 WordPress のデータベースを削除（DEPLOY.md B-8） |

## 作業の覚え書き（制作側）

- 一式の作り直し：`php tools/export-seed.php` → `sh tools/build-package.sh`（dist/upload/）
- 手直し：`sh tools/build-update.sh b37140d`（b37140d は最初に渡した一式のコミット。そこからの累積）。できた2つを Boss に渡し、install.php を毎回ちがう名前（kousin○.php）に変えて開いてもらう。上げた直後は外から見えるまで数分かかることがある
- 確認版（Boss だけが開ける画面のコピー）：https://claude.ai/artifact/PXUmRHajNWBJL9JsnUBLF4 。PHP が動かないので、管理画面・フォーム送信・AI 相談は出ない
- データの手直しは `migration/fixes/NNN-*.sql` に足すと、管理画面を開いた時に1回だけ流れる
