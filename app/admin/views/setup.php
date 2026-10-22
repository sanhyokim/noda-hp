<?php
/** @var array $errors */
/** @var string $loginId */
$err = fn(string $k): string => isset($errors[$k]) ? '<p class="field__err" id="err-' . $k . '">' . icon('alert', 16) . e($errors[$k]) . '</p>' : '';
$aria = fn(string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
?>
<section class="panel panel--narrow">
  <h1 class="ttl">最初の設定</h1>
  <p class="note">管理画面に入るための ID とパスワードを決めてください。この画面は一度しか使えません。</p>
  <form method="post" class="form" novalidate>
    <?= csrf_field() ?>
    <div class="field">
      <label class="field__label" for="login_id">ID <span class="opt">半角英数字</span></label>
      <input class="input" type="text" id="login_id" name="login_id" value="<?= e($loginId) ?>" autocomplete="username" autocapitalize="off" spellcheck="false" required<?= $aria('login_id') ?>>
      <?= $err('login_id') ?>
    </div>
    <div class="field">
      <label class="field__label" for="password">パスワード <span class="opt">10文字以上</span></label>
      <input class="input" type="password" id="password" name="password" autocomplete="new-password" minlength="10" required<?= $aria('password') ?>>
      <?= $err('password') ?>
    </div>
    <div class="field">
      <label class="field__label" for="again">パスワード（確認のため、もう一度）</label>
      <input class="input" type="password" id="again" name="again" autocomplete="new-password" required<?= $aria('again') ?>>
      <?= $err('again') ?>
    </div>
    <button class="abtn abtn--primary abtn--block" type="submit">登録する</button>
  </form>
</section>
