<?php
// AI 相談（右下のボタンと会話の窓）。API キーが入っている時だけ layout から読み込む。中身は assets/js/chat.js が動かす。
/** @var string $current */
$pagesForLinks = [];
foreach (site('pages') as $p) {
    $pagesForLinks[url($p['path'])] = $p['name'];
}
$starters = ['店内に煙がこもる', '近所から臭いの苦情が来た', '新しくお店を開く予定', '費用の目安を知りたい', '対応エリアを知りたい'];

// しばらくサイトを見ている人に、ボタンの上から1回だけ声をかける（見ているページに合わせた言葉と、そのまま聞ける質問2つ）
$nudges = [
    'home' => ['飲食店の換気・ダクトのこと、電話の前に気軽に聞いてみませんか？', ['店内に煙がこもる', '新しくお店を開く予定']],
    'about_duct_work' => ['うちの店にはどんな工事が必要？ まずは気軽に聞いてみてください。', ['換気扇の吸い込みが弱い', 'ダクトの掃除も頼める？']],
    'new_stores' => ['開業の準備、換気やダクトのことで迷っていませんか？', ['物件を決める前に相談したい', '工事はいつ頃頼めばいい？']],
    'duct-hood' => ['換気扇の吸い込みが弱い・音が気になる…お困りではありませんか？', ['換気扇の吸い込みが弱い', '音や振動が気になる']],
    'smoke_problems' => ['近所からの臭いの苦情、どこから手をつけるか迷っていませんか？', ['近所から臭いの苦情が来た', '消煙機ってどんなもの？']],
    'hood-installation' => ['焼肉店のフード、どの形がお店に合うか迷っていませんか？', ['上引きと無煙ロースターの違い', '今あるフードを活かせる？']],
    'smokeless_roster' => ['無煙ロースターの入れ替えや増設、気軽に聞いてみませんか？', ['上引きと無煙ロースターの違い', '営業しながら工事できる？']],
    'construction_examples' => ['似たお店の工事、うちでも頼めるか聞いてみませんか？', ['うちの業態でも頼める？', '対応エリアを知りたい']],
    'question' => ['ここに無い質問も、AI にそのまま聞けます。', ['営業しながら工事できる？', '費用の目安を知りたい']],
    'customer' => ['設計・施工のご依頼の条件、気軽に聞いてみませんか？', ['図面から施工まで頼める？', '対応エリアを知りたい']],
];
$nudgeDefault = ['工事のこと、電話の前に AI に聞いてみませんか？', ['工事の流れを知りたい', '対応エリアを知りたい']];
$nudge = in_array($current, ['contact', 'privacypolicy'], true) ? null : ($nudges[$current] ?? $nudgeDefault);
// 野田の「N」のマーク（ロゴと同じ形）
// LINE で開く時に、相談番号と質問を入力欄に入れておく（https://line.me/R/oaMessage/<LINE の ID>/?<文>）
$lineId = preg_match('#/(@[A-Za-z0-9._\-]+)$#', (string)company('line_url'), $lm) ? $lm[1] : '';
$lineOa = $lineId !== '' ? 'https://line.me/R/oaMessage/' . rawurlencode($lineId) . '/?' : '';
$mark = '<svg viewBox="96 90 320 332" width="20" height="20" aria-hidden="true" focusable="false"><path fill="currentColor" d="M108 102h105v306H108zM224 102l62 113v193l-62-112zM298 102h102v306H298z"/></svg>';
?>
<div class="aic" data-chat data-endpoint="<?= e(url('api/chat/')) ?>" data-contact="<?= e(page_url('contact')) ?>" data-pages="<?= e(json_encode($pagesForLinks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>" data-line-oa="<?= e($lineOa) ?>" hidden>
<?php if ($nudge): ?>
  <div class="aic__nudge" data-nudge hidden>
    <button class="aic__nudge-x" type="button" aria-label="閉じる" data-nudge-close><?= icon('close', 16) ?></button>
    <p class="aic__nudge-who"><span class="aic__mark aic__mark--sm"><?= $mark ?></span>野田の AI 相談</p>
    <button class="aic__nudge-q" type="button" data-nudge-open><?= e($nudge[0]) ?></button>
    <div class="aic__nudge-chips">
<?php foreach ($nudge[1] as $s): ?>
      <button type="button" class="aic__chip aic__chip--sm" data-nudge-ask><?= e($s) ?></button>
<?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
  <button class="aic__fab" type="button" aria-controls="aic-panel" aria-expanded="false">
    <span class="aic__fab-icon"><?= icon('chat', 24) ?></span>
    <span class="aic__fab-text">AIに相談<small>24時間・無料</small></span>
  </button>
  <section class="aic__panel" id="aic-panel" role="dialog" aria-labelledby="aic-title" hidden>
    <header class="aic__head">
      <span class="aic__mark" aria-hidden="true"><?= $mark ?></span>
      <div class="aic__heading">
        <p class="aic__title" id="aic-title">野田の AI 相談</p>
        <p class="aic__sub">電話では聞きにくいことも、お気軽にどうぞ</p>
      </div>
      <button class="aic__end-btn" type="button" data-end-open hidden>相談を終える</button>
      <button class="aic__close" type="button" aria-label="閉じる"><?= icon('close', 22) ?></button>
    </header>
    <div class="aic__log" role="log" aria-live="polite" aria-relevant="additions">
      <div class="aic__msg aic__msg--ai"><p>こんにちは。<?= e(company('name')) ?>の AI 相談です。飲食店の換気扇・ダクト・煙や臭いのことなど、気になることを何でもお聞きください。</p></div>
      <div class="aic__starters" data-starters>
        <p class="aic__starters-ttl">よくあるご相談</p>
<?php foreach ($starters as $s): ?>
        <button type="button" class="aic__chip"><?= e($s) ?></button>
<?php endforeach; ?>
      </div>
      <div class="aic__direct" data-direct>
        <p class="aic__direct-ttl">担当者と直接話したい方</p>
        <a class="aic__direct-tel" href="<?= e(tel_href(company('tel_free'))) ?>"><?= icon('phone', 18) ?><?= e(company('tel_free')) ?></a>
        <p class="aic__direct-note">平日 <?= e(company('hours')) ?>（<?= e(company('closed')) ?>）。写真を送ってのご相談は <a href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener">LINE</a> から</p>
      </div>
    </div>
    <div class="aic__hand" data-hand hidden>
      <p class="aic__hand-ttl">担当者にくわしく相談する</p>
      <div class="aic__hand-btns">
        <a class="aic__hbtn aic__hbtn--form" href="<?= e(page_url('contact')) ?>?from=chat" data-handoff><?= icon('mail', 18) ?>この内容で問い合わせる</a>
        <button class="aic__hbtn aic__hbtn--call" type="button" data-cb-open><?= icon('phone', 18) ?>電話をもらう</button>
        <a class="aic__hbtn aic__hbtn--line" href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener" data-handoff data-line><?= line_icon(18) ?>LINEで写真を送る</a>
      </div>
      <p class="aic__hand-tel">お急ぎの方は <a href="<?= e(tel_href(company('tel_free'))) ?>" data-handoff><?= e(company('tel_free')) ?></a>（平日 <?= e(company('hours')) ?>）</p>
    </div>
    <div class="aic__end" data-end hidden>
      <p class="aic__end-ttl">ご相談ありがとうございました</p>
      <p class="aic__end-lead">くわしいことや、お店に合った工事のご提案は、担当者がお答えします。現地調査とお見積りは無料です。</p>
      <div class="aic__end-btns">
        <a class="aic__hbtn aic__hbtn--form" href="<?= e(page_url('contact')) ?>?from=chat" data-handoff><?= icon('mail', 18) ?>この内容で問い合わせる</a>
        <button class="aic__hbtn aic__hbtn--call" type="button" data-cb-open><?= icon('phone', 18) ?>電話をもらう</button>
        <a class="aic__hbtn aic__hbtn--line" href="<?= e(company('line_url')) ?>" target="_blank" rel="noopener" data-handoff data-line><?= line_icon(18) ?>LINEで写真を送る</a>
      </div>
      <div class="aic__rate" data-rate>
        <p class="aic__rate-q" id="aic-rate-q">この相談は役に立ちましたか？</p>
        <div class="aic__rate-btns" role="group" aria-labelledby="aic-rate-q">
          <button type="button" class="aic__rate-btn" data-rate-v="1">はい</button>
          <button type="button" class="aic__rate-btn" data-rate-v="0">いいえ</button>
        </div>
        <p class="aic__rate-thanks" data-rate-thanks role="status" hidden>ご回答ありがとうございます。今後の改善に使わせていただきます。</p>
      </div>
      <div class="aic__end-foot">
        <button class="aic__cb-back" type="button" data-end-close>会話に戻る</button>
        <button class="aic__cb-back" type="button" data-new>新しい相談を始める</button>
      </div>
      <p class="aic__end-code" data-code></p>
    </div>
    <form class="aic__cb" data-cb hidden novalidate>
      <p class="aic__cb-ttl"><?= icon('phone', 20) ?>担当者から電話をもらう</p>
      <p class="aic__cb-lead">お名前と電話番号を送ると、営業日（平日 <?= e(company('hours')) ?>）に担当者からお電話します。ここに入れた内容は AI には送られず、野田の担当者にだけ届きます。これまでの会話も担当者にお伝えします。</p>
      <div class="aic__cb-field">
        <label for="aic-cb-name">お名前 <span class="aic__req">必須</span></label>
        <input id="aic-cb-name" name="name" type="text" maxlength="50" autocomplete="name" required>
        <p class="aic__cb-err" data-err="name" hidden></p>
      </div>
      <div class="aic__cb-field">
        <label for="aic-cb-phone">電話番号 <span class="aic__req">必須</span></label>
        <input id="aic-cb-phone" name="phone" type="tel" maxlength="20" autocomplete="tel" inputmode="tel" placeholder="例：090-1234-5678" required>
        <p class="aic__cb-err" data-err="phone" hidden></p>
      </div>
      <div class="aic__cb-field">
        <label for="aic-cb-pref">お店の都道府県 <span class="aic__req">必須</span></label>
        <select id="aic-cb-pref" name="prefecture" required>
          <option value="">選んでください</option>
<?php foreach (CONTACT_PREFS as $pref): ?>
          <option><?= e($pref) ?></option>
<?php endforeach; ?>
        </select>
        <p class="aic__cb-err" data-err="prefecture" hidden></p>
      </div>
      <fieldset class="aic__cb-field aic__cb-times">
        <legend>電話の都合のよい時間帯</legend>
<?php foreach (CHAT_CALLBACK_TIMES as $k => $label): ?>
        <label class="aic__cb-time"><input type="radio" name="time" value="<?= e($k) ?>"<?= $k === 'any' ? ' checked' : '' ?>><span><?= e($label) ?></span></label>
<?php endforeach; ?>
      </fieldset>
      <p class="aic__cb-msg" data-cb-msg role="alert" hidden></p>
      <div class="aic__cb-btns">
        <button class="aic__hbtn aic__hbtn--form" type="submit">電話をお願いする</button>
        <button class="aic__cb-back" type="button" data-cb-close>会話に戻る</button>
      </div>
      <p class="aic__cb-note"><a href="<?= e(page_url('privacypolicy')) ?>#ai-chat" target="_blank" rel="noopener">個人情報の扱い</a>に同意のうえ送ってください。</p>
    </form>
    <form class="aic__form" data-form>
      <label class="u-sr" for="aic-input">相談内容</label>
      <textarea id="aic-input" rows="1" maxlength="800" placeholder="例：焼き鳥店で煙が客席に流れてくる" enterkeyhint="send"></textarea>
      <button class="aic__send" type="submit" aria-label="送る"><?= icon('send', 22) ?></button>
    </form>
    <p class="aic__note">AIがサイトの情報をもとにお答えします。費用や工期は担当者からご案内します。会話の欄には、お名前・電話番号を書かないでください。<a href="<?= e(page_url('privacypolicy')) ?>#ai-chat">入力内容の扱い</a></p>
  </section>
</div>
