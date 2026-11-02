<?php
/** @var array $errors */
/** @var array $in host, name, user, pass, trial */
$err = fn(string $k): string => isset($errors[$k]) ? '<p class="field__err" id="err-' . $k . '">' . icon('alert', 16) . e($errors[$k]) . '</p>' : '';
$aria = fn(string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
?>
<section class="panel panel--narrow">
  <h1 class="ttl">最初の設定（1/2）</h1>
  <p class="note">サーバーの管理画面で作ったデータベースの情報を入れてください。ホームページがここに施工事例などを保存します。</p>
<?php if (isset($errors['conn'])): ?>
  <div class="msg msg--error" role="alert"><?= icon('alert', 20) ?><span><?= e($errors['conn']) ?></span></div>
<?php endif; ?>
  <form method="post" class="form" novalidate>
    <?= csrf_field() ?>
    <div class="field">
      <label class="field__label" for="host">データベースの場所（ホスト名）</label>
      <input class="input" type="text" id="host" name="host" value="<?= e($in['host']) ?>" autocapitalize="off" spellcheck="false" required<?= $aria('host') ?>>
      <p class="field__help">ふつうは localhost のままで大丈夫です。</p>
      <?= $err('host') ?>
    </div>
    <div class="field">
      <label class="field__label" for="name">データベース名</label>
      <input class="input" type="text" id="name" name="name" value="<?= e($in['name']) ?>" autocapitalize="off" spellcheck="false" required<?= $aria('name') ?>>
      <?= $err('name') ?>
    </div>
    <div class="field">
      <label class="field__label" for="user">ユーザー名</label>
      <input class="input" type="text" id="user" name="user" value="<?= e($in['user']) ?>" autocomplete="off" autocapitalize="off" spellcheck="false" required<?= $aria('user') ?>>
      <?= $err('user') ?>
    </div>
    <div class="field">
      <label class="field__label" for="pass">パスワード</label>
      <input class="input" type="password" id="pass" name="pass" value="" autocomplete="off">
    </div>
    <div class="field">
      <label class="check"><input type="checkbox" name="trial" value="1"<?= $in['trial'] ? ' checked' : '' ?>><span>試しに置いている場所なので、Google などの検索に載せない</span></label>
      <p class="field__help">/new/ に置いて確かめている間は、印を付けたままにしてください。本番の場所では外します。</p>
    </div>
    <button class="abtn abtn--primary abtn--block" type="submit">つながるか確かめて次へ</button>
  </form>
</section>
