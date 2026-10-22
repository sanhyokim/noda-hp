<?php
/** @var array $todo 答えられなかった質問（log, idx, q, code, at） */
/** @var array $answers 登録済みの答え */
/** @var array $form */
/** @var array $errors */
$err = fn(string $k): string => isset($errors[$k]) ? '<p class="field__err" id="err-' . $k . '">' . icon('alert', 16) . e($errors[$k]) . '</p>' : '';
$aria = fn(string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
?>
<div class="bar">
  <a class="back" href="<?= e(admin_url('settings/')) ?>#chat"><?= icon('arrow', 18, 'back__icon') ?>設定</a>
</div>
<h1 class="ttl ttl--form">AI に教える答え</h1>
<p class="note">AI 相談で答えられなかった質問に、ここで答えを書いておくと、次から AI がその答えをもとにお答えします。保存したらすぐ使われます。</p>

<section class="box" id="todo">
  <h2 class="box__ttl">AI が答えられなかった質問<span class="box__count"><?= count($todo) ?>件</span></h2>
<?php if (!$todo): ?>
  <p class="note">いまはありません。AI が「担当者に確認します」と答えた質問が、ここに並びます。</p>
<?php else: ?>
  <p class="field__help">「答えを書く」を押すと、下の欄に質問が入ります。答えなくてよい質問は「外す」で一覧から消せます。</p>
  <ul class="qa">
<?php foreach ($todo as $t): ?>
    <li class="qa__item">
      <p class="qa__q"><?= e($t['q']) ?></p>
      <p class="qa__meta"><?= e(date('Y.m.d H:i', strtotime($t['at']))) ?>・<a href="<?= e(admin_url('settings/chats/') . '?q=' . $t['code']) ?>">相談番号 <?= e($t['code']) ?> の会話</a></p>
      <div class="qa__btns">
        <a class="abtn abtn--primary abtn--sm" href="?log=<?= (int)$t['log'] ?>&amp;idx=<?= (int)$t['idx'] ?>#form">答えを書く</a>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="op" value="dismiss">
          <input type="hidden" name="log" value="<?= (int)$t['log'] ?>">
          <input type="hidden" name="idx" value="<?= (int)$t['idx'] ?>">
          <button class="abtn abtn--line abtn--sm" type="submit">外す</button>
        </form>
      </div>
    </li>
<?php endforeach; ?>
  </ul>
<?php endif; ?>
</section>

<section class="box" id="form">
  <h2 class="box__ttl"><?= $form['id'] ? '答えを直す' : '答えを足す' ?></h2>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="op" value="save">
    <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">
    <input type="hidden" name="log" value="<?= (int)$form['log'] ?>">
    <input type="hidden" name="idx" value="<?= (int)$form['idx'] ?>">
    <div class="field">
      <label class="field__label" for="question">お客様の質問</label>
      <input class="input" type="text" id="question" name="question" value="<?= e($form['question']) ?>" maxlength="500" placeholder="例：日曜日に工事してもらえますか？" required<?= $aria('question') ?>>
      <?= $err('question') ?>
    </div>
    <div class="field">
      <label class="field__label" for="answer">答え</label>
      <textarea class="input textarea" id="answer" name="answer" rows="5" maxlength="2000" placeholder="例：お店の営業に合わせて、日曜・夜間の工事もご相談に応じます。日程は現地調査のときに決めます。" required<?= $aria('answer') ?>><?= e($form['answer']) ?></textarea>
      <?= $err('answer') ?>
      <p class="field__help">お客様に話すように、そのまま書いてください。似た聞き方の質問にも、AI がこの答えをもとに言葉を合わせて答えます。金額や日数を書くと、AI もその数字を伝えます。</p>
    </div>
    <div class="actions-inline">
      <button class="abtn abtn--primary" type="submit">保存する</button>
<?php if ($form['id'] || $form['log']): ?>
      <a class="abtn abtn--line" href="<?= e(admin_url('settings/answers/')) ?>">やめる</a>
<?php endif; ?>
    </div>
  </form>
</section>

<section class="box" id="list">
  <h2 class="box__ttl">教えた答え<span class="box__count"><?= count($answers) ?>件</span></h2>
<?php if (!$answers): ?>
  <p class="note">まだありません。</p>
<?php else: ?>
  <ul class="qa">
<?php foreach ($answers as $a): ?>
    <li class="qa__item">
      <p class="qa__q"><?= e($a['question']) ?></p>
      <p class="qa__a"><?= nl2br(e($a['answer'])) ?></p>
      <div class="qa__btns">
        <a class="abtn abtn--line abtn--sm" href="?edit=<?= (int)$a['id'] ?>#form">直す</a>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="op" value="delete">
          <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
          <button class="abtn abtn--line abtn--sm" type="submit" data-confirm="この答えを消します。よろしいですか？">消す</button>
        </form>
      </div>
    </li>
<?php endforeach; ?>
  </ul>
<?php endif; ?>
</section>
