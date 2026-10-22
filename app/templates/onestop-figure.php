<?php
/**
 * 「同じ担当者が対応します！」の図（動くイラスト）。
 * 元の絵（1304×1109）を部品に分け、位置はすべて元の絵に対する % で置く。
 * 輪が描かれ → 3つの丸が順に現れ → 担当者が親指を「おまかせあれ！」と突き出す。
 * 動きを減らす設定の端末と、JS が動かない時は最後の形で止まって見える。
 */
$circles = [
    ['mtg', 'onestop-icon-mtg.png', 190, 191, '打ち合わせ'],
    ['work', 'onestop-icon-work.png', 173, 173, '施工'],
    ['after', 'onestop-icon-after.png', 198, 196, 'アフター<br>フォロー'],
];
?>
<div class="onestop" data-onestop role="img" aria-label="打ち合わせ・施工・アフターフォローを同じ担当者が対応します">
  <svg class="onestop__ring" viewBox="0 0 1304 1109" aria-hidden="true" focusable="false">
    <g transform="rotate(-6 690 470)">
      <ellipse class="onestop__loop" cx="690" cy="470" rx="430" ry="200" pathLength="100"/>
      <path class="onestop__swoosh" d="M330 380C390 320 470 288 560 272" pathLength="100"/>
      <circle class="onestop__dot" r="11">
        <animateMotion dur="7s" repeatCount="indefinite" path="M1120 470a430 200 0 1 1-860 0a430 200 0 1 1 860 0"/>
      </circle>
    </g>
  </svg>
  <?php foreach ($circles as [$key, $img, $w, $h, $label]): ?>
    <span class="onestop__circle onestop__circle--<?= $key ?>" aria-hidden="true">
      <img class="onestop__icon" src="<?= e(asset('img/' . $img)) ?>" width="<?= $w ?>" height="<?= $h ?>" alt="" loading="lazy" decoding="async">
      <span class="onestop__label"><?= $label ?></span>
    </span>
  <?php endforeach; ?>
  <span class="onestop__person" aria-hidden="true">
    <img class="onestop__body" src="<?= e(asset('img/onestop-body.png')) ?>" width="350" height="614" alt="" loading="lazy" decoding="async">
    <span class="onestop__hand">
      <img src="<?= e(asset('img/onestop-hand.png')) ?>" width="159" height="168" alt="" loading="lazy" decoding="async">
      <span class="onestop__spark"><i></i><i></i><i></i></span>
    </span>
  </span>
  <span class="onestop__caption" aria-hidden="true">同じ担当者が対応します！</span>
</div>
