<?php
/** @var array $todo 答えられなかった質問（log, idx, q, code, at） */
/** @var array $suggest AI のおすすめ（Q&A に足すとよい質問。id, q, why, points, at） */
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

<section class="box" id="suggest">
  <h2 class="box__ttl">Q&amp;A に足すとよい質問（AI のおすすめ）<span class="box__count"><?= count($suggest) ?>件</span></h2>
<?php if (!$suggest): ?>
  <p class="note">いまはありません。毎朝のまとめメールを送る時に、前の日の質問から AI が選んでここに並べます。</p>
<?php else: ?>
  <p class="field__help">前の日の AI 相談から、答えられなかった質問や、今の Q&amp;A に無い新しい質問を AI が選びました。「答えを書く」を押すと、下の欄に質問が入ります。</p>
  <ul class="qa">
<?php foreach ($suggest as $t): ?>
    <li class="qa__item">
      <p class="qa__q"><?= e($t['q']) ?></p>
      <p class="qa__meta"><?= e(date('Y.m.d', strtotime($t['at'] ?? 'now'))) ?><?= ($t['why'] ?? '') !== '' ? '・' . e($t['why']) : '' ?></p>
<?php if (($t['points'] ?? '') !== ''): ?>
      <p class="qa__a">答えに書くとよいこと：<?= e($t['points']) ?></p>
<?php endif; ?>
      <div class="qa__btns">
        <a class="abtn abtn--primary abtn--sm" href="?sug=<?= e($t['id']) ?>#form">答えを書く</a>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="op" value="dismiss_suggest">
          <input type="hidden" name="sug" value="<?= e($t['id']) ?>">
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
    <input type="hidden" name="sug" value="<?= e($form['sug']) ?>">
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
    <div class="field">
      <label class="check"><input type="checkbox" name="on_faq" value="1"<?= $form['on_faq'] ? ' checked' : '' ?>><span>よくある質問のページにも載せる</span></label>
      <p class="field__help">印を付けると、ホームページの「よくある質問」（<a href="<?= e(page_url('question')) ?>" target="_blank" rel="noopener">開く</a>）の「その他のご質問」に、この質問と答えがそのまま出ます。</p>
    </div>
    <div class="actions-inline">
      <button class="abtn abtn--primary" type="submit">保存する</button>
<?php if ($form['id'] || $form['log'] || $form['sug'] !== ''): ?>
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
      <p class="qa__q"><?= e($a['question']) ?><?php if (!empty($a['on_faq'])): ?> <span class="badge">よくある質問に掲載</span><?php endif; ?></p>
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
