// AI 相談：右下のボタンで会話の窓を開き、/api/chat/ に会話を送って答えを表示する。
// 会話はページを移っても続くよう sessionStorage に置く。「この内容で問い合わせる」で、お問い合わせフォームの相談内容に写す。
// しばらくサイトを見ている人には、ボタンの上から1回だけ声をかける。「担当者から電話をもらう」は名前・電話番号を AI を通さずに送る。
(() => {
  'use strict';
  const KEY = 'noda-chat';
  const root = document.querySelector('[data-chat]');
  const load = () => { try { return JSON.parse(sessionStorage.getItem(KEY)) || null; } catch (_) { return null; } };
  const save = (s) => { try { sessionStorage.setItem(KEY, JSON.stringify(s)); } catch (_) { /* 保存できなくても会話は続ける */ } };
  const track = (event, extra) => { (window.dataLayer = window.dataLayer || []).push(Object.assign({ event }, extra || {})); };

  // ---------- お問い合わせフォームに会話の内容を写す（?from=chat で来た時） ----------
  const fillContact = () => {
    if (!/[?&]from=chat\b/.test(location.search)) return;
    const st = load();
    const box = document.querySelector('textarea[name="message"]');
    if (!st || !st.messages || !st.messages.length || !box || box.value.trim() !== '') return;
    const lines = ['【AI相談でのご質問】（相談番号 ' + st.conv.slice(0, 6).toUpperCase() + '）'];
    st.messages.filter((m) => m.role === 'user').forEach((m) => lines.push('・' + m.content));
    lines.push('', '（ここに、お店の場所・業態・ご希望などを書き足してください）');
    box.value = lines.join('\n').slice(0, 2000);
  };
  fillContact();

  if (!root) return;
  root.hidden = false;
  const fab = root.querySelector('.aic__fab');
  const panel = root.querySelector('.aic__panel');
  const log = root.querySelector('.aic__log');
  const form = root.querySelector('[data-form]');
  const input = form.querySelector('textarea');
  const sendBtn = form.querySelector('.aic__send');
  const starters = root.querySelector('[data-starters]');
  const hand = root.querySelector('[data-hand]');
  const handToggle = root.querySelector('[data-hand-toggle]');
  const handBody = root.querySelector('[data-hand-body]');
  const formLabel = root.querySelector('[data-form-label]');
  const scrim = root.querySelector('.aic__scrim');
  const nudge = root.querySelector('[data-nudge]');
  const cb = root.querySelector('[data-cb]');
  const end = root.querySelector('[data-end]');
  const endBtn = root.querySelector('[data-end-open]');
  const code = () => state.conv.slice(0, 6).toUpperCase();
  const pages = JSON.parse(root.dataset.pages || '{}');
  const rid = () => [...crypto.getRandomValues(new Uint8Array(16))].map((b) => b.toString(16).padStart(2, '0')).join('');

  let state = load() || { conv: rid(), messages: [], open: false };
  let busy = false;

  // 答えの中のサイト内アドレス（/flow/ など）と消煙機・ダクト清掃の専用サイトだけをリンクにする（外のサイトは新しいタブで）
  const esc = (s) => s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const paths = Object.keys(pages).filter((p) => p.length > 1).sort((a, b) => b.length - a.length).map((p) => p.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
  const linkRe = paths.length ? new RegExp('(?<![\\w/.:@-])(' + paths.join('|') + ')(?![\\w/-])', 'g') : null;
  const render = (text) => {
    let html = esc(text);
    if (linkRe) html = html.replace(linkRe, (p) => `<a href="${p}"${/^https?:/.test(p) ? ' target="_blank" rel="noopener"' : ''}>${esc(pages[p] || p)}</a>`);
    return html.split(/\n{2,}/).map((para) => `<p>${para.replace(/\n/g, '<br>')}</p>`).join('');
  };

  const add = (role, text, cls) => {
    const div = document.createElement('div');
    div.className = `aic__msg aic__msg--${role === 'user' ? 'me' : 'ai'}${cls ? ' ' + cls : ''}`;
    div.innerHTML = role === 'user' ? `<p>${esc(text).replace(/\n/g, '<br>')}</p>` : render(text);
    log.appendChild(div);
    log.scrollTop = log.scrollHeight;
    return div;
  };

  const restore = () => {
    state.messages.forEach((m) => add(m.role, m.content));
    if (state.cbDone) add('ai', state.cbDone, 'is-done');
    starters.hidden = state.messages.length > 0;
    endBtn.hidden = state.messages.length < 2;
    panel.classList.toggle('has-talk', state.messages.length > 0);
    formLabel.textContent = state.messages.length ? 'この内容で問い合わせる' : 'フォームで問い合わせる';
  };

  // 開け閉めは、ボタンの位置からふわっと出て、ふわっと戻る（動きを減らす設定の人には動かさない）
  let closeTimer = 0;
  const setOpen = (open, focus = true, instant = false) => {
    state.open = open;
    save(state);
    clearTimeout(closeTimer);
    if (open) {
      panel.hidden = false;
      if (instant) panel.classList.add('is-in');
      else requestAnimationFrame(() => requestAnimationFrame(() => panel.classList.add('is-in')));
    } else {
      panel.classList.remove('is-in');
      closeTimer = setTimeout(() => { if (!state.open) panel.hidden = true; }, matchMedia('(prefers-reduced-motion: reduce)').matches ? 200 : 280);
    }
    fab.setAttribute('aria-expanded', String(open));
    root.classList.toggle('is-open', open);
    document.documentElement.classList.toggle('aic-open', open);
    if (open) {
      log.scrollTop = log.scrollHeight;
      if (focus && matchMedia('(min-width: 640px)').matches) input.focus();
      else if (focus) panel.querySelector('.aic__close').focus();
    } else if (focus) {
      fab.focus();
    }
  };

  const autosize = () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 132) + 'px';
    input.style.overflowY = input.scrollHeight > 132 ? 'auto' : 'hidden'; // 入りきらない時だけ、スクロールの棒を出す
  };

  const send = async (text) => {
    text = text.trim();
    if (!text || busy) return;
    busy = true;
    sendBtn.disabled = true;
    // 最後のやり取りから30分たっていたら、前の相談は終わったものとして、新しい相談番号で始める（前の会話は画面に残す）
    if (state.messages.length && state.last && Date.now() - state.last > 30 * 60 * 1000) {
      const div = document.createElement('p');
      div.className = 'aic__divider';
      div.textContent = 'ここから新しいご相談';
      log.appendChild(div);
      state = { conv: rid(), messages: [], open: true };
    }
    starters.hidden = true;
    if (handOpen) setHand(false);
    hideNudge(false);
    add('user', text);
    state.messages.push({ role: 'user', content: text });
    save(state);
    input.value = '';
    autosize();
    const wait = add('ai', '', 'is-typing');
    wait.innerHTML = '<span class="aic__dots" aria-label="答えを考えています"><i></i><i></i><i></i></span><span class="aic__wait"></span>';
    // 待ち時間が長い時は、止まっていないことが分かるように言葉を添える
    const waitText = wait.querySelector('.aic__wait');
    const waitTimers = [
      setTimeout(() => { waitText.textContent = '答えを考えています'; }, 2500),
      setTimeout(() => { waitText.textContent = 'もう少しお待ちください'; }, 9000),
    ];
    track('ai_chat_send', { chat_turn: Math.ceil(state.messages.length / 2) });
    let reply = '';
    let ok = false;
    // 答えはできた分から少しずつ届く。届いた文字は、人が打っているように順に出す（読み上げは、全部そろってから1回だけ）
    let live = null;
    let target = '';
    let shown = '';
    let frame = 0;
    const calm = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const draw = () => {
      frame = 0;
      if (!live) {
        waitTimers.forEach(clearTimeout);
        wait.remove();
        live = add('ai', '', 'is-live');
        live.setAttribute('aria-hidden', 'true');
      }
      const back = target.length - shown.length;
      if (back <= 0) return;
      const near = log.scrollHeight - log.scrollTop - log.clientHeight < 80;
      // 遅れが大きい時（まとめて届いた時）ほど速く出して、待たせない
      shown = calm ? target : target.slice(0, shown.length + Math.max(1, Math.ceil(back / 14)));
      live.innerHTML = render(shown);
      if (near) log.scrollTop = log.scrollHeight;
      if (shown.length < target.length) frame = requestAnimationFrame(draw);
    };
    const caughtUp = () => new Promise((done) => {
      const check = () => (!frame || shown.length >= target.length ? done() : requestAnimationFrame(check));
      check();
    });
    try {
      const res = await fetch(root.dataset.endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Noda-Chat': '1' },
        body: JSON.stringify({ conv: state.conv, page: location.pathname, messages: state.messages, stream: true }),
      });
      let data = {};
      if (res.body && (res.headers.get('Content-Type') || '').includes('ndjson')) {
        const reader = res.body.getReader();
        const dec = new TextDecoder();
        let buf = '';
        const take = (line) => {
          if (!line.trim()) return; // 最初に届く空白の行は読み飛ばす
          let j;
          try { j = JSON.parse(line); } catch (_) { return; }
          if (j.done) { data = j; return; }
          if (typeof j.d === 'string') {
            target += j.d;
            if (!frame) frame = requestAnimationFrame(draw);
          }
        };
        for (;;) {
          const { value, done } = await reader.read();
          if (done) break;
          buf += dec.decode(value, { stream: true });
          let nl;
          while ((nl = buf.indexOf('\n')) >= 0) {
            take(buf.slice(0, nl));
            buf = buf.slice(nl + 1);
          }
        }
        take(buf + dec.decode());
        ok = res.ok && !!data.ok;
      } else {
        data = await res.json().catch(() => ({}));
        ok = res.ok && !!data.ok;
      }
      reply = data.reply || '';
      if (ok && live) await caughtUp();
    } catch (_) {
      reply = '';
      ok = false;
    }
    waitTimers.forEach(clearTimeout);
    wait.remove();
    if (frame) cancelAnimationFrame(frame);
    if (!reply) reply = '通信がうまくいきませんでした。電波の良い所でもう一度お試しいただくか、お電話・お問い合わせフォームからご相談ください。';
    // 仕上がった答えに置き換える（ここで読み上げられる）。途中まで出ていた答えは、同じ場所で差し替えて画面が動かないようにする
    const top = log.scrollTop;
    const fin = add('ai', reply, ok ? '' : 'is-error');
    if (live) {
      live.replaceWith(fin);
      log.scrollTop = top;
    }
    if (ok) {
      state.messages.push({ role: 'assistant', content: reply });
      state.last = Date.now();
      // 答えが担当者への相談をすすめている時は、たたんだ欄にそっと印を付ける（勝手には開かない）
      if (/問い合わせ|電話|LINE|現地調査|担当者/.test(reply) && !handOpen) handToggle.classList.add('is-hint');
    } else {
      state.messages.pop(); // 答えられなかった質問は履歴から外す（次に送り直せるように）
    }
    // 長くなりすぎた会話は、古い分から送らない（サーバー側の上限は 20 往復）
    if (state.messages.length > 36) state.messages = state.messages.slice(-36);
    save(state);
    endBtn.hidden = state.messages.length < 2;
    panel.classList.toggle('has-talk', state.messages.length > 0);
    formLabel.textContent = state.messages.length ? 'この内容で問い合わせる' : 'フォームで問い合わせる';
    busy = false;
    sendBtn.disabled = false;
    if (matchMedia('(min-width: 640px)').matches) input.focus();
  };

  fab.addEventListener('click', () => {
    hideNudge(false);
    setOpen(!state.open);
    if (state.open) track('ai_chat_open');
  });
  root.querySelector('.aic__close').addEventListener('click', () => setOpen(false));
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape' || !state.open || document.querySelector('.lb[open], dialog[open]')) return;
    if (handOpen) { setHand(false); handToggle.focus(); return; } // 相談の窓が開いている時は、まずそれを閉じる
    setOpen(false);
  });
  starters.addEventListener('click', (e) => {
    const b = e.target.closest('.aic__chip');
    if (b) send(b.textContent);
  });
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    send(input.value);
  });
  input.addEventListener('input', autosize);
  input.addEventListener('keydown', (e) => {
    // 日本語の変換中の Enter では送らない。スマホでは改行、PC では Enter で送る（Shift+Enter で改行）
    if (e.key !== 'Enter' || e.isComposing || e.keyCode === 229 || e.shiftKey) return;
    if (!matchMedia('(min-width: 640px)').matches) return;
    e.preventDefault();
    send(input.value);
  });
  // 問い合わせ・LINE・電話へ進んだ印を残す。LINE は、相談番号と質問を入力欄に入れた状態で開く
  root.addEventListener('click', (e) => {
    const a = e.target.closest('[data-handoff]');
    if (!a) return;
    if (a.hasAttribute('data-line') && root.dataset.lineOa) {
      const qs = state.messages.filter((m) => m.role === 'user').map((m) => '・' + m.content.replace(/\s+/g, ' ').slice(0, 120));
      const text = qs.length
        ? ['【HPのAI相談から】相談番号 ' + code(), '', 'AIに相談した内容：', ...qs.slice(-5), '', '写真を送ります。'].join('\n')
        : '【HPから】写真を送って相談します。';
      a.href = root.dataset.lineOa + encodeURIComponent(text.slice(0, 900));
    }
    track('ai_chat_handoff', { handoff: a.classList.contains('aic__hbtn--form') ? 'form' : a.hasAttribute('data-line') ? 'line' : 'tel' });
    const body = JSON.stringify({ conv: state.conv });
    fetch(root.dataset.endpoint + 'handoff/', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Noda-Chat': '1' }, body, keepalive: true }).catch(() => {});
  });

  // ---------- 相談を終える（締めの画面：次の一歩・役に立ったか・新しい相談） ----------
  const view = (name) => {
    // name：'chat'（会話）/ 'end'（締め）/ 'cb'（電話をもらう）
    panel.classList.toggle('is-cb', name === 'cb');
    panel.classList.toggle('is-end', name === 'end');
    cb.hidden = name !== 'cb';
    end.hidden = name !== 'end';
    endBtn.hidden = name !== 'chat' || state.messages.length < 2;
  };
  const rateBox = end.querySelector('[data-rate]');
  const showRated = () => {
    const done = state.rated !== undefined && state.rated !== null;
    rateBox.querySelectorAll('[data-rate-v]').forEach((b) => { b.disabled = done; b.classList.toggle('is-on', done && String(state.rated) === b.dataset.rateV); });
    end.querySelector('[data-rate-thanks]').hidden = !done;
  };
  endBtn.addEventListener('click', () => {
    view('end');
    end.querySelector('[data-code]').innerHTML = '相談番号 <strong>' + code() + '</strong><br>お電話やLINEの時にお伝えいただくと、話が早く進みます';
    showRated();
    end.querySelector('.aic__end-ttl').setAttribute('tabindex', '-1');
    end.querySelector('.aic__end-ttl').focus();
    track('ai_chat_end');
  });
  end.querySelector('[data-end-close]').addEventListener('click', () => { view('chat'); input.focus(); });
  rateBox.addEventListener('click', (e) => {
    const b = e.target.closest('[data-rate-v]');
    if (!b || b.disabled) return;
    state.rated = Number(b.dataset.rateV);
    save(state);
    showRated();
    track('ai_chat_rate', { chat_rating: state.rated });
    fetch(root.dataset.endpoint + 'rate/', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Noda-Chat': '1' }, body: JSON.stringify({ conv: state.conv, rating: state.rated }), keepalive: true }).catch(() => {});
  });
  end.querySelector('[data-new]').addEventListener('click', () => {
    state = { conv: rid(), messages: [], open: true };
    save(state);
    log.querySelectorAll('.aic__msg, .aic__divider').forEach((m, i) => { if (i > 0 || m.classList.contains('aic__divider')) m.remove(); });
    restore();
    view('chat');
    input.focus();
    track('ai_chat_new');
  });

  // ---------- 声かけ：サイトを合わせて40秒ほど見た人に、1回の訪問で1回だけ ----------
  const NKEY = 'noda-chat-nudge';
  const NDAYS = 7; // ×で閉じた人には、7日間は出さない
  const nload = () => { try { return JSON.parse(sessionStorage.getItem(NKEY)) || { t: 0, done: false }; } catch (_) { return { t: 0, done: false }; } };
  const nsave = (n) => { try { sessionStorage.setItem(NKEY, JSON.stringify(n)); } catch (_) { /* 保存できなくても動く */ } };
  const closedRecently = () => { try { return Date.now() - Number(localStorage.getItem(NKEY + '-x') || 0) < NDAYS * 86400000; } catch (_) { return false; } };
  let ns = nload();
  let nudgeTimer = 0;
  let nudgeAuto = 0;
  function hideNudge(byUser) {
    clearInterval(nudgeTimer);
    clearTimeout(nudgeAuto);
    ns.done = true;
    nsave(ns);
    if (byUser) { try { localStorage.setItem(NKEY + '-x', String(Date.now())); } catch (_) { /* 何もしない */ } }
    if (nudge && !nudge.hidden) {
      nudge.classList.remove('is-in');
      setTimeout(() => { nudge.hidden = true; }, 250);
    }
  }
  const showNudge = () => {
    if (!nudge || state.open || state.messages.length) return hideNudge(false);
    ns.done = true;
    nsave(ns);
    nudge.hidden = false;
    requestAnimationFrame(() => requestAnimationFrame(() => nudge.classList.add('is-in')));
    root.classList.add('is-nudged');
    fab.classList.remove('is-ping');
    void fab.offsetWidth;
    fab.classList.add('is-ping');
    track('ai_chat_nudge_show');
    nudgeAuto = setTimeout(() => hideNudge(false), 45000); // 見ないままなら、しばらくして静かに消す
  };
  if (nudge && !ns.done && !state.messages.length && !closedRecently()) {
    // 画面を見ている時間だけ数える（ページを移っても続きから）
    nudgeTimer = setInterval(() => {
      if (document.visibilityState !== 'visible' || state.open) return;
      ns.t += 1;
      if (ns.t % 5 === 0) nsave(ns);
      if (ns.t >= 40) { clearInterval(nudgeTimer); showNudge(); }
    }, 1000);
  }
  if (nudge) {
    nudge.querySelector('[data-nudge-close]').addEventListener('click', () => { track('ai_chat_nudge_close'); hideNudge(true); });
    nudge.querySelector('[data-nudge-open]').addEventListener('click', () => { track('ai_chat_nudge_click'); hideNudge(false); setOpen(true); });
    nudge.addEventListener('click', (e) => {
      const b = e.target.closest('[data-nudge-ask]');
      if (!b) return;
      track('ai_chat_nudge_click');
      hideNudge(false);
      setOpen(true);
      send(b.textContent);
    });
  }

  // ---------- 担当者から電話をもらう ----------
  const cbMsg = cb.querySelector('[data-cb-msg]');
  const cbShow = (open) => {
    view(open ? 'cb' : 'chat');
    if (open) {
      cbMsg.hidden = true;
      cb.querySelector('input[name="name"]').focus();
      track('ai_chat_callback_open');
    } else {
      input.focus();
    }
  };
  root.querySelectorAll('[data-cb-open]').forEach((b) => b.addEventListener('click', () => cbShow(true)));
  cb.querySelector('[data-cb-close]').addEventListener('click', () => cbShow(false));
  cb.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = cb.querySelector('button[type="submit"]');
    if (btn.disabled) return;
    const fd = new FormData(cb);
    const data = {};
    ['name', 'phone', 'prefecture', 'time'].forEach((k) => { data[k] = String(fd.get(k) || ''); });
    cb.querySelectorAll('[data-err]').forEach((p) => { p.hidden = true; p.textContent = ''; });
    cb.querySelectorAll('[aria-invalid]').forEach((el) => el.removeAttribute('aria-invalid'));
    cbMsg.hidden = true;
    btn.disabled = true;
    let res = null;
    try {
      const r = await fetch(root.dataset.endpoint + 'callback/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Noda-Chat': '1' },
        body: JSON.stringify({ conv: state.conv, callback: data }),
      });
      res = await r.json().catch(() => null);
    } catch (_) {
      res = null;
    }
    btn.disabled = false;
    if (!res) res = { ok: false, errors: {}, message: '通信がうまくいきませんでした。お手数ですが、お電話でご連絡ください。' };
    if (!res.ok) {
      const errs = res.errors || {};
      let first = null;
      Object.keys(errs).forEach((k) => {
        const p = cb.querySelector(`[data-err="${k}"]`);
        const el = cb.querySelector(`[name="${k}"]`);
        if (p) { p.textContent = errs[k]; p.hidden = false; }
        if (el) { el.setAttribute('aria-invalid', 'true'); first = first || el; }
      });
      if (first) first.focus();
      else { cbMsg.textContent = res.message; cbMsg.hidden = false; }
      return;
    }
    track('ai_chat_callback_sent');
    cb.reset();
    cbShow(false);
    // 受け付けの知らせは画面に残すだけで、AI には送らない（名前・電話番号が入るため）
    add('ai', res.message, 'is-done');
    state.cbDone = res.message;
    save(state);
  });

  // ---------- 担当者にくわしく相談する（ふだんはたたんでおき、押したら開く） ----------
  // 開いた状態は覚えない（ページを移った時や、次に質問した時は、たたんだ1行に戻す）
  let handOpen = false;
  const setHand = (open) => {
    handOpen = open;
    handBody.hidden = scrim.hidden = !open;
    handToggle.setAttribute('aria-expanded', String(open));
    hand.classList.toggle('is-open', open);
    if (open) handToggle.classList.remove('is-hint');
  };
  handToggle.addEventListener('click', () => {
    setHand(!handOpen);
    if (handOpen) track('ai_chat_hand_open');
  });
  root.querySelectorAll('[data-hand-close]').forEach((b) => b.addEventListener('click', () => { setHand(false); handToggle.focus(); }));
  handBody.addEventListener('click', (e) => { if (e.target.closest('[data-cb-open]')) setHand(false); });
  setHand(false);

  restore();
  if (state.open) setOpen(true, false, true);
  // この訪問で最初に開いたページだけ、ボタンを下から出して、まわりに輪を3回広げて気づいてもらう
  try {
    if (!state.open && !sessionStorage.getItem('noda-chat-intro')) {
      sessionStorage.setItem('noda-chat-intro', '1');
      root.classList.add('is-intro');
    }
  } catch (_) { /* 何もしない */ }
})();
