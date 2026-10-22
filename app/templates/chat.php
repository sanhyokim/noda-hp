<?php
// AI 相談（右下のボタンと会話の窓）。API キーが入っている時だけ layout から読み込む。中身は assets/js/chat.js が動かす。
$pagesForLinks = [];
foreach (site('pages') as $p) {
    $pagesForLinks[url($p['path'])] = $p['name'];
}
$starters = ['店内に煙がこもる', '近所から臭いの苦情が来た', '新しくお店を開く予定', '費用の目安を知りたい', '対応エリアを知りたい'];
?>
<div class="aic" data-chat data-endpoint="<?= e(url('api/chat/')) ?>" data-contact="<?= e(page_url('contact')) ?>" data-pages="<?= e(json_encode($pagesForLinks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>" hidden>
  <button class="aic__fab" type="button" aria-controls="aic-panel" aria-expanded="false">
    <span class="aic__fab-icon"><?= icon('chat', 24) ?></span>
    <span class="aic__fab-text">AIに相談<small>24時間・無料</small></span>
  </button>
  <section class="aic__panel" id="aic-panel" role="dialog" aria-labelledby="aic-title" hidden>
    <header class="aic__head">
      <span class="aic__avatar" aria-hidden="true"><?= icon('chat', 22) ?></span>
      <div class="aic__heading">
        <p class="aic__title" id="aic-title">AI 相談</p>
        <p class="aic__sub">電話では聞きにくいことも、お気軽にどうぞ</p>
      </div>
      <button class="aic__close" type="button" aria-label="閉じる"><?= icon('close', 22) ?></button>
    </header>
    <div class="aic__log" role="log" aria-live="polite" aria-relevant="additions">
      <div class="aic__msg aic__msg--ai"><p>こんにちは。<?= e(company('name')) ?>の AI 相談です。飲食店の換気扇・ダクト・煙や臭いのことなど、気になることを何でもお聞きください。</p></div>
      <div class="aic__starters" data-starters>
<?php foreach ($starters as $s): ?>
        <button type="button" class="aic__chip"><?= e($s) ?></button>
<?php endforeach; ?>
      </div>
    </div>
    <div class="aic__hand" data-hand hidden>
      <p class="aic__hand-ttl">担当者にくわしく相談する</p>
      <div class="aic__hand-btns">
        <a class="aic__hbtn aic__hbtn--form" href="<?= e(page_url('contact')) ?>?from=chat" data-handoff><?= icon('mail', 18) ?>この内容で問い合わせる</a>
        <a class="aic__hbtn aic__hbtn--line" href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener" data-handoff><?= line_icon(18) ?>LINEで写真を送る</a>
        <a class="aic__hbtn aic__hbtn--tel" href="<?= e(tel_href(company('tel_free'))) ?>" data-handoff><?= icon('phone', 18) ?><?= e(company('tel_free')) ?></a>
      </div>
    </div>
    <form class="aic__form" data-form>
      <label class="u-sr" for="aic-input">相談内容</label>
      <textarea id="aic-input" rows="1" maxlength="800" placeholder="例：焼き鳥店で煙が客席に流れてくる" enterkeyhint="send"></textarea>
      <button class="aic__send" type="submit" aria-label="送る"><?= icon('send', 22) ?></button>
    </form>
    <p class="aic__note">AIがサイトの情報をもとにお答えします。費用や工期は担当者からご案内します。お名前・電話番号は入力しないでください。<a href="<?= e(page_url('privacypolicy')) ?>#ai-chat">入力内容の扱い</a></p>
  </section>
</div>
