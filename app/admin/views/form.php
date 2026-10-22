<?php
/** @var string $kind cases|news */
/** @var ?array $item 編集中の記事（新規は null） */
/** @var array $form 画面に出す値 */
/** @var array $errors 項目名 => 文 */
/** @var array $cats 施工事例のみ：['biz' => [id => 名前], 'work' => [...]] */
/** @var array $photoList 施工事例のみ */
/** @var string $token 施工事例のみ：写真アップロード用 */
$isCase = $kind === 'cases';
$label = $isCase ? '施工事例' : 'お知らせ';
$base = admin_url($kind . '/');
$err = fn(string $k): string => isset($errors[$k]) ? '<p class="field__err" id="err-' . $k . '">' . icon('alert', 16) . e($errors[$k]) . '</p>' : '';
$aria = fn(string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
$publicUrl = $item ? url(($isCase ? '' : 'news/') . rawurlencode($item['slug']) . '/') : null;
$axes = ['biz' => ['業態', 'お店の種類。1つ以上選んでください。'], 'work' => ['工事の種類', '行った工事。当てはまるものをすべて選んでください。']];
?>
<div class="bar">
  <a class="back" href="<?= e($base) ?>"><?= icon('arrow', 18, 'back__icon') ?><?= e($label) ?>の一覧</a>
</div>
<h1 class="ttl ttl--form"><?= $item ? e($label) . 'を編集' : e($label) . 'を新しく書く' ?></h1>
<?php if ($item): ?>
<p class="pubinfo">
  <span class="badge badge--<?= e($item['status']) ?>"><?= $item['status'] === 'published' ? '公開中' : '下書き' ?></span>
<?php if ($item['status'] === 'published'): ?>
  <a href="<?= e($publicUrl) ?>" target="_blank" rel="noopener">公開ページを見る<?= icon('external', 16) ?></a>
<?php else: ?>
  <span>まだ公開されていません</span>
<?php endif; ?>
</p>
<?php endif; ?>

<?php if ($errors): ?>
<div class="msg msg--error" role="alert" tabindex="-1" data-focus>
  <?= icon('alert', 20) ?>
  <div><p><strong>保存できませんでした。</strong>赤い印の所を直してください。</p>
    <ul><?php foreach ($errors as $k => $m): ?><li><a href="#f-<?= e($k) ?>"><?= e($m) ?></a></li><?php endforeach; ?></ul>
  </div>
</div>
<?php endif; ?>

<form class="edit" method="post" id="edit-form" data-edit-form novalidate>
  <?= csrf_field() ?>
  <div class="edit__main">
    <div class="field" id="f-title">
      <label class="field__label" for="title">題名 <span class="req">必須</span></label>
      <input class="input input--title" type="text" id="title" name="title" value="<?= e($form['title']) ?>" maxlength="255" required<?= $aria('title') ?>>
      <?= $err('title') ?>
    </div>

    <div class="field" id="f-body">
      <span class="field__label" id="body-label">本文</span>
      <p class="field__help">見出し・太字・箇条書き・リンクが使えます。ほかのソフトから貼り付けた文字は、飾りを外して文字だけ入ります。</p>
      <div class="editor" data-editor>
        <div class="editor__tools" role="toolbar" aria-label="本文の飾り" aria-controls="body-area">
          <button type="button" data-cmd="h3" aria-pressed="false">見出し</button>
          <button type="button" data-cmd="h4" aria-pressed="false">小見出し</button>
          <button type="button" data-cmd="bold" aria-pressed="false"><b>太字</b></button>
          <button type="button" data-cmd="ul" aria-pressed="false">箇条書き</button>
          <button type="button" data-cmd="ol" aria-pressed="false">番号付き</button>
          <button type="button" data-cmd="link">リンク</button>
        </div>
        <div class="editor__link" data-link-row hidden>
          <label class="u-sr" for="link-url">リンク先のアドレス</label>
          <input class="input" type="url" id="link-url" placeholder="https:// で始まるアドレス" inputmode="url">
          <button class="abtn abtn--primary abtn--sm" type="button" data-link-ok>付ける</button>
          <button class="abtn abtn--line abtn--sm" type="button" data-link-remove>外す</button>
          <button class="abtn abtn--line abtn--sm" type="button" data-link-cancel>やめる</button>
        </div>
        <div class="editor__area prose" id="body-area" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="body-label" data-placeholder="ここに本文を書きます"><?= $form['body_html'] ?></div>
        <textarea class="editor__src" name="body" hidden><?= e($form['body_html']) ?></textarea>
      </div>
    </div>

<?php if ($isCase): ?>
    <div class="field" id="f-photos">
      <span class="field__label">写真</span>
      <p class="field__help">何枚でも追加できます。左上の「<span aria-hidden="true">⠿</span>」をつまんで動かすと並べ替えられます。「代表」の写真が一覧やトップに出ます。</p>
      <div class="photos" data-photos data-upload="<?= e(admin_url('upload/')) ?>" data-token="<?= e($token) ?>" data-csrf="<?= e(csrf_token()) ?>">
        <ol class="photos__grid" data-photo-grid aria-label="写真の一覧（並び順）"></ol>
        <label class="drop" data-drop>
          <input class="u-sr" type="file" accept="image/jpeg,image/png,image/webp,image/heic,image/heif" multiple data-photo-input>
          <span class="drop__icon" aria-hidden="true"><?= icon('image', 28) ?></span>
          <span class="drop__main">写真を選ぶ</span>
          <span class="drop__sub">ここに写真をドラッグしても追加できます（1枚15MBまで。大きな写真は自動で縮めます）</span>
        </label>
        <div class="photos__msg" data-photo-msg role="status" aria-live="polite"></div>
        <input type="hidden" name="photos" value="<?= e(json_encode($photoList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>" data-photo-json>
      </div>
    </div>
<?php endif; ?>
  </div>

  <div class="edit__side">
    <section class="box">
      <h2 class="box__ttl">公開の設定</h2>
      <fieldset class="field">
        <legend class="field__label">状態</legend>
        <div class="seg">
          <label><input type="radio" name="status" value="published"<?= $form['status'] === 'published' ? ' checked' : '' ?>><span>公開する</span></label>
          <label><input type="radio" name="status" value="draft"<?= $form['status'] !== 'published' ? ' checked' : '' ?>><span>下書き</span></label>
        </div>
      </fieldset>
      <div class="field" id="f-published_at">
        <label class="field__label" for="published_at">投稿日 <span class="req">必須</span></label>
        <input class="input input--date" type="date" id="published_at" name="published_at" value="<?= e($form['published_at']) ?>" required<?= $aria('published_at') ?>>
        <?= $err('published_at') ?>
      </div>
<?php if ($isCase): ?>
      <div class="field">
        <label class="field__label" for="location">場所 <span class="opt">任意</span></label>
        <input class="input" type="text" id="location" name="location" value="<?= e($form['location']) ?>" maxlength="100" placeholder="例：福岡市博多区">
      </div>
<?php endif; ?>
    </section>

<?php if ($isCase): foreach ($axes as $axis => [$axisLabel, $help]): ?>
    <fieldset class="box field" id="f-<?= $axis ?>"<?= $aria($axis) ?>>
      <legend class="box__ttl"><?= e($axisLabel) ?> <span class="req">公開時は必須</span></legend>
      <p class="field__help"><?= e($help) ?></p>
      <div class="checks">
<?php foreach ($cats[$axis] as $cid => $name): ?>
        <label class="check"><input type="checkbox" name="<?= $axis ?>[]" value="<?= (int)$cid ?>"<?= in_array($cid, $form[$axis], true) ? ' checked' : '' ?>><span><?= e($name) ?></span></label>
<?php endforeach; ?>
      </div>
      <?= $err($axis) ?>
    </fieldset>
<?php endforeach; endif; ?>

    <details class="box more"<?= isset($errors['slug']) ? ' open' : '' ?>>
      <summary class="box__ttl more__sum">詳しい設定<?= icon('chev', 18) ?></summary>
      <p class="field__help">ページのアドレスや、検索結果での見え方を変えたい時だけ使います。空のままで大丈夫です。</p>
<?php if ($isCase): ?>
      <div class="field">
        <label class="field__label" for="seo_title">検索結果に出る題名 <span class="opt">任意</span></label>
        <input class="input" type="text" id="seo_title" name="seo_title" value="<?= e($form['seo_title']) ?>" maxlength="255" placeholder="空なら「題名｜施工事例【株式会社野田】」">
      </div>
      <div class="field">
        <label class="field__label" for="seo_description">検索結果に出る説明文 <span class="opt">任意</span></label>
        <textarea class="input textarea" id="seo_description" name="seo_description" rows="4" maxlength="500" placeholder="空なら本文の最初の110字ほどを使います" data-count="120"><?= e($form['seo_description']) ?></textarea>
        <p class="field__help" data-count-out></p>
      </div>
<?php endif; ?>
      <div class="field" id="f-slug">
        <label class="field__label" for="slug">ページのアドレス <span class="opt">任意</span></label>
        <div class="slug"><span class="slug__pre"><?= e(preg_replace('#^https?://#', '', abs_url($isCase ? '' : 'news/'))) ?></span><input class="input" type="text" id="slug" name="slug" value="<?= e($form['slug']) ?>" maxlength="191" placeholder="空なら題名から作ります" autocapitalize="off" spellcheck="false"><span class="slug__post">/</span></div>
        <p class="field__help">変えると、前のアドレスで来た人も新しいページへ自動で案内します。</p>
      </div>
    </details>
  </div>

  <div class="savebar">
    <div class="savebar__in">
      <button class="abtn abtn--line" type="submit" name="do" value="preview">保存して確認</button>
      <button class="abtn abtn--primary" type="submit" name="do" value="save">保存する</button>
    </div>
  </div>
</form>

<?php if ($item): ?>
<form class="danger" method="post" action="<?= e($base . $item['id'] . '/delete/') ?>" data-confirm="「<?= e($item['title']) ?>」を削除します。公開ページからも消えます。よろしいですか？">
  <?= csrf_field() ?>
  <h2 class="danger__ttl">この<?= e($label) ?>を削除する</h2>
  <p class="note note--sub">削除すると公開ページから消え、一覧にも出なくなります。</p>
  <button class="abtn abtn--danger" type="submit">削除する</button>
</form>
<?php endif; ?>
