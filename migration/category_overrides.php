<?php
// 自動の振り分けで「要確認」になった記事を、本文と写真を読んで直したもの（停止点3のボスの指示、2026-10-21）。
// キーは現行サイトの記事番号（wp_id）。biz・work は付録Bの slug。
// 'review' => true は「本文・写真からも店の種類が分からない」ため要確認のまま残すもの。
return [
    2425 => ['biz' => ['other'], 'work' => ['ventilation']],                                   // 地下階の空調用の角ダクト。飲食店ではない
    2455 => ['biz' => ['yakiniku'], 'work' => ['roaster', 'fire-safety', 'maintenance']],        // 無煙ロースターの火災後の復旧
    2463 => ['biz' => ['izakaya'], 'work' => ['kitchen-hood', 'ventilation']],                  // 和食店。銅板貼りフード＋給気工事
    2649 => ['biz' => ['other'], 'work' => ['kitchen-hood', 'ventilation', 'fire-safety'], 'review' => true], // 天井内ファン・断熱・給気・火災防止。店の種類の記載なし
    2665 => ['biz' => ['restaurant'], 'work' => ['kitchen-hood', 'fire-safety']],               // 有機野菜とジビエの店。防火ダンパー設置
    2845 => ['biz' => ['takeout'], 'work' => ['kitchen-hood', 'ventilation']],                  // ベーカリー（題名は「ベーカーリー」表記）
    2996 => ['biz' => ['izakaya'], 'work' => ['kitchen-hood']],                                 // 新規居酒屋
    3026 => ['biz' => ['other'], 'work' => ['kitchen-hood', 'ventilation', 'fire-safety'], 'review' => true], // 「飲食店」とだけ記載
    3070 => ['biz' => ['other'], 'work' => ['kitchen-hood'], 'review' => true],                 // 「飲食店」とだけ記載
    3293 => ['biz' => ['other'], 'work' => ['smoke'], 'review' => true],                        // 「新規飲食店」とだけ記載
    3301 => ['biz' => ['other'], 'work' => ['kitchen-hood', 'smoke'], 'review' => true],        // 「お客様店舗」とだけ記載
    3642 => ['biz' => ['restaurant'], 'work' => ['kitchen-hood', 'smoke', 'ventilation', 'fire-safety']], // 中洲川端のレストラン。グリスフィルター・断熱
    3775 => ['biz' => ['yakiniku'], 'work' => ['upper-hood', 'ventilation']],                   // 焼肉店の結露対策
    3804 => ['biz' => ['other'], 'work' => ['kitchen-hood', 'ventilation'], 'review' => true],  // 店の種類の記載なし。低い天井のフード＋給気口
    3857 => ['biz' => ['izakaya', 'chain'], 'work' => ['kitchen-hood', 'smoke', 'ventilation']], // 手羽先で有名なチェーン店
    3961 => ['biz' => ['chain', 'izakaya'], 'work' => ['kitchen-hood', 'ventilation']],         // 回転寿司チェーン。写真は厨房フード
    4144 => ['biz' => ['yakiniku'], 'work' => ['kitchen-hood', 'smoke', 'upper-hood', 'ventilation']], // 昼カレー・夜焼肉の店
];
