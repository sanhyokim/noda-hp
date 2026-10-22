// AI 相談：右下のボタンで会話の窓を開き、/api/chat/ に会話を送って答えを表示する。
// 会話はページを移っても続くよう sessionStorage に置く。「この内容で問い合わせる」で、お問い合わせフォームの相談内容に写す。
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
    const lines = ['【AI相談でのご質問】'];
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
  const pages = JSON.parse(root.dataset.pages || '{}');
  const rid = () => [...crypto.getRandomValues(new Uint8Array(16))].map((b) => b.toString(16).padStart(2, '0')).join('');

  let state = load() || { conv: rid(), messages: [], open: false };
  let busy = false;

  // 答えの中のサイト内アドレス（/flow/ など）だけをリンクにする
  const esc = (s) => s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const paths = Object.keys(pages).filter((p) => p.length > 1).sort((a, b) => b.length - a.length).map((p) => p.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
  const linkRe = paths.length ? new RegExp('(?<![\\w/.:@-])(' + paths.join('|') + ')(?![\\w/-])', 'g') : null;
  const render = (text) => {
    let html = esc(text);
    if (linkRe) html = html.replace(linkRe, (p) => `<a href="${p}">${esc(pages[p] || p)}</a>`);
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
    starters.hidden = state.messages.length > 0;
    hand.hidden = state.messages.length < 2;
  };

  const setOpen = (open, focus = true) => {
    state.open = open;
    save(state);
    panel.hidden = !open;
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
  };

  const send = async (text) => {
    text = text.trim();
    if (!text || busy) return;
    busy = true;
    sendBtn.disabled = true;
    starters.hidden = true;
    add('user', text);
    state.messages.push({ role: 'user', content: text });
    save(state);
    input.value = '';
    autosize();
    const wait = add('ai', '', 'is-typing');
    wait.innerHTML = '<span class="aic__dots" aria-label="答えを考えています"><i></i><i></i><i></i></span>';
    track('ai_chat_send', { chat_turn: Math.ceil(state.messages.length / 2) });
    let reply = '';
    let ok = false;
    try {
      const res = await fetch(root.dataset.endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Noda-Chat': '1' },
        body: JSON.stringify({ conv: state.conv, page: location.pathname, messages: state.messages }),
      });
      const data = await res.json().catch(() => ({}));
      reply = data.reply || '';
      ok = res.ok && !!data.ok;
    } catch (_) {
      reply = '';
    }
    wait.remove();
    if (!reply) reply = '通信がうまくいきませんでした。電波の良い所でもう一度お試しいただくか、お電話・お問い合わせフォームからご相談ください。';
    add('ai', reply, ok ? '' : 'is-error');
    if (ok) {
      state.messages.push({ role: 'assistant', content: reply });
    } else {
      state.messages.pop(); // 答えられなかった質問は履歴から外す（次に送り直せるように）
    }
    // 長くなりすぎた会話は、古い分から送らない（サーバー側の上限は 20 往復）
    if (state.messages.length > 36) state.messages = state.messages.slice(-36);
    save(state);
    hand.hidden = state.messages.length < 2;
    busy = false;
    sendBtn.disabled = false;
    if (matchMedia('(min-width: 640px)').matches) input.focus();
  };

  fab.addEventListener('click', () => {
    setOpen(!state.open);
    if (state.open) track('ai_chat_open');
  });
  root.querySelector('.aic__close').addEventListener('click', () => setOpen(false));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && state.open && !document.querySelector('.lb[open], dialog[open]')) setOpen(false);
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
  hand.addEventListener('click', (e) => {
    const a = e.target.closest('[data-handoff]');
    if (!a) return;
    track('ai_chat_handoff', { handoff: a.classList.contains('aic__hbtn--form') ? 'form' : a.classList.contains('aic__hbtn--line') ? 'line' : 'tel' });
    const body = JSON.stringify({ conv: state.conv });
    fetch(root.dataset.endpoint + 'handoff/', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Noda-Chat': '1' }, body, keepalive: true }).catch(() => {});
  });

  restore();
  if (state.open) setOpen(true, false);
})();
