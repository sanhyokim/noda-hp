<?php
/** @var ?string $error */
/** @var string $loginId */
?>
<section class="panel panel--narrow login">
  <h1 class="ttl">ログイン</h1>
  <p class="note">ID とパスワードを入れてください。</p>
<?php if ($error): ?>
  <p class="msg msg--error" role="alert"><?= icon('alert', 20) ?><span><?= e($error) ?></span></p>
<?php endif; ?>
  <form method="post" class="form" novalidate>
    <?= csrf_field() ?>
    <div class="field">
      <label class="field__label" for="login_id">ID</label>
      <input class="input" type="text" id="login_id" name="login_id" value="<?= e($loginId) ?>" autocomplete="username" autocapitalize="off" spellcheck="false" required>
    </div>
    <div class="field">
      <label class="field__label" for="password">パスワード</label>
      <div class="pw">
        <input class="input" type="password" id="password" name="password" autocomplete="current-password" required>
        <button class="pw__toggle" type="button" data-pw-toggle aria-controls="password" aria-pressed="false">表示</button>
      </div>
    </div>
    <button class="abtn abtn--primary abtn--block" type="submit">ログインする</button>
  </form>
  <p class="note note--sub">5回続けて間違えると、15分間ログインできなくなります。2時間何も操作しないと、自動でログアウトします。</p>
</section>
