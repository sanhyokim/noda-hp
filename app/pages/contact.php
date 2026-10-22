<?php
render('page-head', [
    'label' => 'CONTACT',
    'title' => 'お問い合わせ',
    'lead' => '当社にご興味いただきありがとうございます。お客様の現状をお聞きして最適なご提案をさせていただきます。ご依頼やご相談は、下記のメールフォームもしくはお電話、LINEにてお気軽にご連絡ください。現地調査・お見積りは無料です。「煙の写真」を送っていただくだけでもご相談いただけます。',
    'image' => 'contact-office.jpg', 'ratio' => [3, 2],
]);
$form = $form ?? [];
$old = $form['old'] ?? [];
$err = $form['errors'] ?? [];
$v = fn(string $k) => e((string)($old[$k] ?? ''));
$errMsg = fn(string $k) => isset($err[$k]) ? '<p class="field__err" id="err-' . $k . '">' . e($err[$k]) . '</p>' : '';
// 間違いのある項目に印を付け、読み上げでも直下の説明につなぐ
$inv = fn(string $k) => isset($err[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
$sentView = !empty($form['done']) || !empty($form['failed']);
?>
<div class="section wrap">
  <div class="grid grid--2 contact-ways">
    <section class="way" aria-labelledby="w1">
      <span class="way__icon"><?= icon('phone', 26) ?></span>
      <h2 class="way__title" id="w1">お電話</h2>
      <a class="way__tel" href="<?= e(tel_href(company('tel_free'))) ?>"><?= e(company('tel_free')) ?></a>
      <p class="way__text">受付 <?= e(company('hours')) ?>（<?= e(company('closed')) ?>）</p>
    </section>
    <section class="way way--line" aria-labelledby="w2">
      <span class="way__icon"><?= line_icon(30) ?></span>
      <h2 class="way__title" id="w2">LINE</h2>
      <p class="way__text">写真をそのまま送れます。</p>
      <a class="btn btn--line" href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener"><?= line_icon(22) ?>友だち追加して相談する</a>
    </section>
  </div>
</div>

<section class="section wrap" id="form" aria-labelledby="s3">
  <?php render('section-head', ['label' => 'MAIL FORM', 'title' => 'メールフォーム', 'id' => 's3', 'lead' => $sentView ? '' : '* は必須項目です。']); ?>
  <?php if (!empty($form['done'])): ?>
    <div class="notice notice--ok sent" role="status" tabindex="-1" data-focus>
      <p class="sent__title"><?= icon('check', 22) ?>お問い合わせを受け付けました</p>
      <p>ありがとうございます。営業日 <?= e(company('hours')) ?> に担当者よりご連絡します（土日・祝日にいただいた場合は翌営業日以降になります）。ご入力のメールアドレスに、受け付けの控えをお送りしました。</p>
      <p class="sent__sub">控えのメールが届かない場合は、迷惑メールのフォルダもご確認ください。お急ぎの場合はお電話（<a href="<?= e(tel_href(company('tel_free'))) ?>"><?= e(company('tel_free')) ?></a>）でもどうぞ。</p>
    </div>
  <?php elseif (!empty($form['failed'])): ?>
    <div class="notice notice--err" role="alert" tabindex="-1" data-focus>送信できませんでした。お手数ですが、お電話ください（<a href="<?= e(tel_href(company('tel_free'))) ?>"><?= e(company('tel_free')) ?></a>・受付 <?= e(company('hours')) ?>）。</div>
  <?php else: ?>
  <?php if (!empty($form['reject'])): ?>
    <div class="notice notice--err" role="alert" tabindex="-1" data-focus><?= e($form['reject']) ?></div>
  <?php elseif ($err): ?>
    <div class="notice notice--err" role="alert" tabindex="-1" data-focus>送信できませんでした。赤い字の項目をご確認ください。</div>
  <?php endif; ?>
  <form class="form" method="post" action="<?= e(page_url('contact')) ?>#form" enctype="multipart/form-data" novalidate data-validate>
    <input type="hidden" name="csrf" value="<?= e($form['csrf'] ?? '') ?>">
    <input type="hidden" name="t" value="<?= e($form['t'] ?? '') ?>">
<?php if (($form['from'] ?? '') === 'chat'): ?>
    <input type="hidden" name="from" value="chat">
<?php endif; ?>
    <div class="hp" aria-hidden="true"><label>空欄のままにしてください<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

    <div class="field"><label for="f-name">お名前 <span class="req">*</span></label><input id="f-name" name="name" type="text" required maxlength="100" autocomplete="name" value="<?= $v('name') ?>"<?= $inv('name') ?>><?= $errMsg('name') ?></div>
    <div class="field"><label for="f-company">法人名・店舗名</label><input id="f-company" name="company" type="text" maxlength="200" autocomplete="organization" value="<?= $v('company') ?>"></div>
    <div class="field"><label for="f-pref">都道府県 <span class="req">*</span></label>
      <select id="f-pref" name="prefecture" required<?= $inv('prefecture') ?>>
        <option value="">選んでください</option>
        <?php foreach (CONTACT_PREFS as $p): ?><option<?= ($old['prefecture'] ?? '') === $p ? ' selected' : '' ?>><?= e($p) ?></option><?php endforeach; ?>
      </select><?= $errMsg('prefecture') ?></div>
    <div class="field-row">
      <div class="field"><label for="f-tel">電話番号 <span class="req">*</span></label><input id="f-tel" name="phone" type="tel" required maxlength="30" autocomplete="tel" inputmode="tel" placeholder="例：092-504-5502" value="<?= $v('phone') ?>"<?= $inv('phone') ?>><?= $errMsg('phone') ?></div>
      <div class="field"><label for="f-mail">メールアドレス <span class="req">*</span></label><input id="f-mail" name="email" type="email" required maxlength="255" autocomplete="email" value="<?= $v('email') ?>"<?= $inv('email') ?>><?= $errMsg('email') ?></div>
    </div>
    <fieldset class="field"><legend>ご希望の連絡方法</legend>
      <div class="radios">
        <?php foreach (CONTACT_METHODS as $key => $label): ?><label class="radio"><input type="radio" name="contact_method" value="<?= e($key) ?>"<?= ($old['contact_method'] ?? 'phone') === $key ? ' checked' : '' ?>><span><?= e($label) ?></span></label><?php endforeach; ?>
      </div>
    </fieldset>
    <div class="field"><label for="f-msg">ご相談内容 <span class="req">*</span></label><textarea id="f-msg" name="message" rows="7" required maxlength="5000" placeholder="例：焼き鳥店です。煙が客席に流れてくるので相談したいです。（お店の場所・業態・お困りごとなど）"<?= $inv('message') ?>><?= $v('message') ?></textarea><?= $errMsg('message') ?></div>
    <div class="field"><label for="f-file">写真・図面の添付（jpg / png / heic / pdf、1ファイル10MBまで・3ファイルまで）</label><input id="f-file" name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.png,.heic,.heif,.pdf,image/jpeg,image/png,image/heic,image/heif,application/pdf" data-max-files="3" data-max-size="10485760"<?= $inv('attachments') ?>><?= $errMsg('attachments') ?><?php if ($old): ?><p class="field__help">お手数ですが、添付ファイルはもう一度お選びください。</p><?php endif; ?></div>
    <div class="field"><label class="check"><input type="checkbox" name="agree" value="1" required<?= !empty($old['agree']) ? ' checked' : '' ?><?= $inv('agree') ?>><span><a href="<?= e(page_url('privacypolicy')) ?>" target="_blank">プライバシーポリシー</a>に同意する <span class="req">*</span></span></label><?= $errMsg('agree') ?></div>
    <div class="form__submit"><button class="btn btn--orange btn--lg" type="submit">この内容で送信する<?= icon('arrow', 20) ?></button></div>
  </form>
  <?php endif; ?>
</section>

<section class="section wrap" aria-labelledby="s4">
  <div class="partner">
    <div class="partner__bg" aria-hidden="true"><?= picture('flow-consult.jpg', 1080, 720, '') ?></div>
    <div class="partner__text">
      <p class="label label--light">AFTER CONTACT</p>
      <h2 class="partner__title kw" id="s4">お問い合わせ後の流れ</h2>
      <p>現地調査（無料）からご提案・お見積り、施工、アフターフォローまで。</p>
    </div>
    <a class="btn btn--white" href="<?= e(page_url('flow')) ?>">施工までの流れを見る<?= icon('arrow', 18) ?></a>
  </div>
</section>
