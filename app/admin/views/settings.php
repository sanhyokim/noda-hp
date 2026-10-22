<?php
/** @var array $ig */
/** @var array $errors */
/** @var int $contactCount */
/** @var ?string $latestContact */
/** @var array $counts */
$err = fn(string $k): string => isset($errors[$k]) ? '<p class="field__err" id="err-' . $k . '">' . icon('alert', 16) . e($errors[$k]) . '</p>' : '';
$aria = fn(string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
$dt = fn(int $t): string => date('Y年n月j日 H:i', $t);
?>
<div class="bar"><h1 class="ttl">設定</h1></div>

<section class="box" id="contacts">
  <h2 class="box__ttl">お問い合わせの受信一覧</h2>
  <p class="note">ホームページのお問い合わせフォームから届いた内容です（メールと同じ内容を保存しています）。</p>
  <p class="kv"><span>これまでの件数</span><strong><?= number_format($contactCount) ?>件</strong></p>
  <p class="kv"><span>最後に届いた日時</span><strong><?= $latestContact ? e(date('Y年n月j日 H:i', strtotime($latestContact))) : 'まだありません' ?></strong></p>
  <p class="actions-inline"><a class="abtn abtn--line" href="<?= e(admin_url('settings/contacts/')) ?>">受信一覧を見る</a></p>
</section>

<section class="box" id="instagram">
  <h2 class="box__ttl">Instagram の表示</h2>
  <p class="note">トップページの「Instagram - 私たちの日々の仕事」に、最新の投稿6件を自動で出すための設定です。トークン（Instagram から発行される長い英数字）を貼り付けて「保存して接続を試す」を押してください。手順は別紙「Instagram のつなぎ方」にまとめます。</p>
  <p class="kv"><span>今の状態</span><strong>
<?php if (!$ig['has_token']): ?>
    未設定（現行サイトの施工写真6枚を代わりに表示中）
<?php elseif ($ig['cache'] && $ig['error'] === ''): ?>
    つながっています
<?php else: ?>
    トークンは入っていますが、まだ読み込めていません
<?php endif; ?>
  </strong></p>
<?php if ($ig['has_token']): ?>
  <p class="kv"><span>入っているトークン</span><strong>……<?= e($ig['token_tail']) ?>（末尾6文字）</strong></p>
<?php if ($ig['expires']): ?>
  <p class="kv"><span>トークンの期限</span><strong><?= e(date('Y年n月j日', $ig['expires'])) ?>ごろ（期限の10日前から自動で延長します）</strong></p>
<?php endif; ?>
  <p class="kv"><span>最後に読み込んだ日時</span><strong><?= $ig['fetched_at'] ? e($dt($ig['fetched_at'])) : 'まだありません' ?></strong></p>
<?php if ($ig['error'] !== ''): ?>
  <p class="msg msg--error"><?= icon('alert', 20) ?><span>前回の読み込みでうまくいきませんでした：<?= e($ig['error']) ?></span></p>
<?php endif; ?>
<?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="op" value="instagram">
    <div class="field">
      <label class="field__label" for="instagram_token"><?= $ig['has_token'] ? '新しいトークンに入れ替える' : 'トークン' ?> <span class="opt"><?= $ig['has_token'] ? '入れ替える時だけ' : '' ?></span></label>
      <textarea class="input textarea textarea--mono" id="instagram_token" name="instagram_token" rows="3" autocomplete="off" autocapitalize="off" spellcheck="false" placeholder="IGAA… で始まる長い英数字"></textarea>
    </div>
    <div class="actions-inline">
      <button class="abtn abtn--primary" type="submit">保存して接続を試す</button>
<?php if ($ig['has_token']): ?>
      <button class="abtn abtn--line" type="submit" name="clear" value="1" data-confirm="Instagram のトークンを消します。トップページは代わりの写真の表示に戻ります。よろしいですか？">トークンを消す</button>
<?php endif; ?>
    </div>
  </form>
</section>

<section class="box" id="password">
  <h2 class="box__ttl">パスワードの変更</h2>
  <p class="note">ログイン ID：<strong><?= e($user['login_id']) ?></strong></p>
  <form method="post" class="form" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="op" value="password">
    <input type="text" name="username" value="<?= e($user['login_id']) ?>" autocomplete="username" hidden>
    <div class="field">
      <label class="field__label" for="current">今のパスワード</label>
      <input class="input" type="password" id="current" name="current" autocomplete="current-password" required<?= $aria('current') ?>>
      <?= $err('current') ?>
    </div>
    <div class="field">
      <label class="field__label" for="new">新しいパスワード <span class="opt">10文字以上</span></label>
      <input class="input" type="password" id="new" name="new" autocomplete="new-password" minlength="10" required<?= $aria('new') ?>>
      <?= $err('new') ?>
    </div>
    <div class="field">
      <label class="field__label" for="again">新しいパスワード（確認のため、もう一度）</label>
      <input class="input" type="password" id="again" name="again" autocomplete="new-password" required<?= $aria('again') ?>>
      <?= $err('again') ?>
    </div>
    <button class="abtn abtn--primary" type="submit">パスワードを変える</button>
  </form>
</section>

<section class="box" id="export">
  <h2 class="box__ttl">データの書き出し（控え）</h2>
  <p class="note">施工事例<?= number_format($counts['cases']) ?>件・お知らせ<?= number_format($counts['news']) ?>件・カテゴリを、1つのファイル（JSON 形式）にまとめて保存します。写真の置き場所の一覧も入ります。将来の引っ越しや控えのためのもので、普段は使いません。</p>
  <p class="actions-inline"><a class="abtn abtn--line" href="<?= e(admin_url('settings/export/')) ?>" download>ファイルに書き出す</a></p>
</section>
