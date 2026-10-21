<?php
render('page-head', [
    'label' => 'CONTACT',
    'title' => 'お問い合わせ',
    'lead' => '現地調査・お見積りは無料です。「煙の写真」を送っていただくだけでもご相談いただけます。',
    'image' => 'contact-office.jpg', 'ratio' => [3, 2],
]);
$prefs = ['北海道','青森県','岩手県','宮城県','秋田県','山形県','福島県','茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県','新潟県','富山県','石川県','福井県','山梨県','長野県','岐阜県','静岡県','愛知県','三重県','滋賀県','京都府','大阪府','兵庫県','奈良県','和歌山県','鳥取県','島根県','岡山県','広島県','山口県','徳島県','香川県','愛媛県','高知県','福岡県','佐賀県','長崎県','熊本県','大分県','宮崎県','鹿児島県','沖縄県'];
$old = $form['old'] ?? [];
$err = $form['errors'] ?? [];
$v = fn(string $k) => e((string)($old[$k] ?? ''));
$errMsg = fn(string $k) => isset($err[$k]) ? '<p class="field__err" id="err-' . $k . '">' . e($err[$k]) . '</p>' : '';
?>
<div class="section wrap">
  <div class="grid grid--2 contact-ways">
    <section class="way" aria-labelledby="w1">
      <span class="way__icon"><?= icon('phone', 26) ?></span>
      <h2 class="way__title" id="w1">お電話</h2>
      <a class="way__tel" href="<?= e(tel_href(company('tel_free'))) ?>"><?= e(company('tel_free')) ?></a>
      <p class="way__text">受付 <?= e(company('hours')) ?></p>
    </section>
    <section class="way way--line" aria-labelledby="w2">
      <span class="way__icon"><?= line_icon(30) ?></span>
      <h2 class="way__title" id="w2">LINE</h2>
      <p class="way__text">写真をそのまま送れます。</p>
      <a class="btn btn--line" href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener"><?= line_icon(22) ?>友だち追加して相談する</a>
    </section>
  </div>
</div>

<section class="section wrap" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'MAIL FORM', 'title' => 'メールフォーム', 'id' => 's3', 'lead' => '* は必須項目です。']); ?>
  <?php if (!empty($form['done'])): ?>
    <div class="notice notice--ok" role="status">お問い合わせを受け付けました。営業日 9:00〜17:00 に担当者よりご連絡します。</div>
  <?php elseif (!empty($form['failed'])): ?>
    <div class="notice notice--err" role="alert">送信できませんでした。お電話ください（<?= e(company('tel_free')) ?>）。</div>
  <?php endif; ?>
  <form class="form" method="post" action="<?= e(page_url('contact')) ?>" enctype="multipart/form-data" novalidate data-validate>
    <input type="hidden" name="csrf" value="<?= e($form['csrf'] ?? '') ?>">
    <input type="hidden" name="t" value="<?= time() ?>">
    <div class="hp" aria-hidden="true"><label>空欄のままにしてください<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

    <div class="field"><label for="f-name">お名前 <span class="req">*</span></label><input id="f-name" name="name" type="text" required autocomplete="name" value="<?= $v('name') ?>"><?= $errMsg('name') ?></div>
    <div class="field"><label for="f-company">法人名・店舗名</label><input id="f-company" name="company" type="text" autocomplete="organization" value="<?= $v('company') ?>"></div>
    <div class="field"><label for="f-pref">都道府県 <span class="req">*</span></label>
      <select id="f-pref" name="prefecture" required>
        <option value="">選んでください</option>
        <?php foreach ($prefs as $p): ?><option<?= ($old['prefecture'] ?? '') === $p ? ' selected' : '' ?>><?= e($p) ?></option><?php endforeach; ?>
      </select><?= $errMsg('prefecture') ?></div>
    <div class="field-row">
      <div class="field"><label for="f-tel">電話番号 <span class="req">*</span></label><input id="f-tel" name="phone" type="tel" required autocomplete="tel" inputmode="tel" value="<?= $v('phone') ?>"><?= $errMsg('phone') ?></div>
      <div class="field"><label for="f-mail">メールアドレス <span class="req">*</span></label><input id="f-mail" name="email" type="email" required autocomplete="email" value="<?= $v('email') ?>"><?= $errMsg('email') ?></div>
    </div>
    <fieldset class="field"><legend>ご希望の連絡方法</legend>
      <div class="radios">
        <?php foreach (['電話', 'メール'] as $m): ?><label class="radio"><input type="radio" name="contact_method" value="<?= e($m) ?>"<?= ($old['contact_method'] ?? '電話') === $m ? ' checked' : '' ?>><span><?= e($m) ?></span></label><?php endforeach; ?>
      </div>
    </fieldset>
    <div class="field"><label for="f-msg">ご相談内容 <span class="req">*</span></label><textarea id="f-msg" name="message" rows="7" required><?= $v('message') ?></textarea><?= $errMsg('message') ?></div>
    <div class="field"><label for="f-file">写真・図面の添付（jpg / png / heic / pdf、1ファイル10MBまで・3ファイルまで）</label><input id="f-file" name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.png,.heic,.pdf,image/jpeg,image/png,image/heic,application/pdf" data-max-files="3" data-max-size="10485760"><?= $errMsg('attachments') ?></div>
    <div class="field"><label class="check"><input type="checkbox" name="agree" value="1" required<?= !empty($old['agree']) ? ' checked' : '' ?>><span><a href="<?= e(page_url('privacypolicy')) ?>" target="_blank">プライバシーポリシー</a>に同意する <span class="req">*</span></span></label><?= $errMsg('agree') ?></div>
    <div class="form__submit"><button class="btn btn--orange btn--lg" type="submit">この内容で送信する<?= icon('arrow', 20) ?></button></div>
  </form>
</section>

<section class="section wrap" aria-labelledby="s4">
  <div class="partner">
    <span class="partner__icon"><?= icon('clock', 28) ?></span>
    <div class="partner__text">
      <p class="label">AFTER CONTACT</p>
      <h2 class="partner__title" id="s4">お問い合わせ後の流れ</h2>
      <p>現地調査（無料）からご提案・お見積り、施工、アフターフォローまで。</p>
    </div>
    <a class="btn btn--blue" href="<?= e(page_url('flow')) ?>">施工までの流れを見る<?= icon('arrow', 18) ?></a>
  </div>
</section>
