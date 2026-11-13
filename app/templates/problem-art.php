<?php
/**
 * トップ「お悩み」欄のイラスト。線は墨色、面は青と薄いグレー、困りごとの印だけ橙。
 * 使い方: render('problem-art', ['art' => 'smoke'])
 */
$ink = 'stroke="#1B1F26" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"';
$arts = [
    // 店内に煙がこもる: 吸い込みきれずに横へ広がる煙
    'smoke' => '
      <path d="M41 6h14v12H41z" fill="#C9D0DA" ' . $ink . '/>
      <path d="M30 18h36l12 16H18z" fill="#065ECB" ' . $ink . '/>
      <path d="M18 34h60" ' . $ink . ' fill="none"/>
      <path d="M26 58c-6 0-9-4-8-8 1-4 5-6 9-5 1-5 6-8 11-7 3-4 9-5 13-2 4-3 11-2 13 3 5-1 10 2 10 7 4 1 6 5 4 8-1 3-4 4-7 4H26z" fill="#EDF1F7" ' . $ink . '/>
      <path d="M17 64c-5 0-7-3-6-6s4-4 7-3c1-3 5-4 7-2 3-1 6 1 6 4 2 1 2 4 0 6-1 1-2 1-3 1z" fill="#EDF1F7" ' . $ink . '/>
      <path d="M79 64c5 0 7-3 6-6s-4-4-7-3c-1-3-5-4-7-2-3-1-6 1-6 4-2 1-2 4 0 6 1 1 2 1 3 1z" fill="#EDF1F7" ' . $ink . '/>
      <path d="M15 47H5m0 0l4-4m-4 4l4 4M81 47h10m0 0l-4-4m4 4l-4 4" fill="none" stroke="#C9530A" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M28 72h40v6a8 8 0 0 1-8 8H36a8 8 0 0 1-8-8z" fill="#C9D0DA" ' . $ink . '/>
      <path d="M22 72h52" ' . $ink . ' fill="none"/>
      <path d="M40 66c-2-2 2-4 0-6M48 66c-2-2 2-4 0-6M56 66c-2-2 2-4 0-6" fill="none" ' . $ink . '/>',
    // 近隣から臭いの苦情: 店の排気口から隣家へ流れる臭い
    'wave' => '
      <path d="M6 40h30v46H6z" fill="#EDF1F7" ' . $ink . '/>
      <path d="M4 40l17-12 17 12" fill="#065ECB" ' . $ink . '/>
      <path d="M13 66h10v20H13zM26 52h6v8h-6z" fill="#fff" ' . $ink . '/>
      <path d="M36 48h10v8H36z" fill="#C9D0DA" ' . $ink . '/>
      <path d="M50 46c4-3 8 3 12 0s8 3 12 0M50 54c4-3 8 3 12 0s8 3 12 0" fill="none" stroke="#C9530A" stroke-width="2.4" stroke-linecap="round"/>
      <path d="M62 66h28v20H62z" fill="#fff" ' . $ink . '/>
      <path d="M58 68l18-12 18 12" fill="#C9D0DA" ' . $ink . '/>
      <path d="M72 74h8v12h-8z" fill="#EDF1F7" ' . $ink . '/>
      <path d="M66 8h24a4 4 0 0 1 4 4v14a4 4 0 0 1-4 4h-9l-7 7v-7h-8a4 4 0 0 1-4-4V12a4 4 0 0 1 4-4z" fill="#C9530A" ' . $ink . '/>
      <path d="M78 13v8" stroke="#fff" stroke-width="3" stroke-linecap="round"/><circle cx="78" cy="25.5" r="1.8" fill="#fff"/>',
    // 換気扇の音・吸い込み不良: 弱った羽根と異音
    'fan' => '
      <rect x="10" y="14" width="60" height="60" rx="6" fill="#EDF1F7" ' . $ink . '/>
      <circle cx="40" cy="44" r="24" fill="#fff" ' . $ink . '/>
      <circle cx="40" cy="44" r="17" fill="none" stroke="#A9C4F5" stroke-width="1.4"/>
      <g fill="#065ECB" ' . $ink . '><path transform="rotate(0 40 44)" d="M40 44C35 35 38 25 47 23c4 6 2 15-7 21z"/><path transform="rotate(90 40 44)" d="M40 44C35 35 38 25 47 23c4 6 2 15-7 21z"/><path transform="rotate(180 40 44)" d="M40 44C35 35 38 25 47 23c4 6 2 15-7 21z"/><path transform="rotate(270 40 44)" d="M40 44C35 35 38 25 47 23c4 6 2 15-7 21z"/></g>
      <circle cx="40" cy="44" r="4.5" fill="#1B1F26"/>
      <g fill="#1B1F26"><circle cx="16" cy="20" r="1.8"/><circle cx="64" cy="20" r="1.8"/><circle cx="16" cy="68" r="1.8"/><circle cx="64" cy="68" r="1.8"/></g>
      <path d="M77 34c4 4 4 16 0 20M84 28c7 7 7 25 0 32" fill="none" stroke="#C9530A" stroke-width="2.4" stroke-linecap="round"/>',
    // 新規開業の換気設計: 図面と新しい店舗、屋根のダクト
    'store' => '
      <path d="M48 6h40v34H48z" fill="#EDF1F7" ' . $ink . '/>
      <path d="M56 6v34M64 6v34M72 6v34M80 6v34M48 14h40M48 22h40M48 30h40" stroke="#A9C4F5" stroke-width="1.2"/>
      <path d="M56 32V16h18v8h8" fill="none" stroke="#065ECB" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M48 6h40v34H48z" fill="none" ' . $ink . '/>
      <path d="M22 30h8v14h-8z" fill="#C9D0DA" ' . $ink . '/>
      <path d="M18 24h16v6H18z" fill="#C9D0DA" ' . $ink . '/>
      <path d="M10 44h60v42H10z" fill="#fff" ' . $ink . '/>
      <path d="M6 44h68l-4 12H10z" fill="#065ECB" ' . $ink . '/>
      <path d="M20 56l-2 6M32 56l-1 6M44 56v6M56 56l1 6M68 56l1 6" stroke="#1B1F26" stroke-width="2.4" stroke-linecap="round"/>
      <path d="M16 68h22v18H16zM46 68h18v12H46z" fill="#EDF1F7" ' . $ink . '/>
      <path d="M4 86h88" ' . $ink . ' fill="none"/>',
];
$svg = $arts[$art] ?? '';
?>
<svg class="problem__art" viewBox="0 0 96 96" width="72" height="72" aria-hidden="true" focusable="false"><?= $svg ?></svg>
