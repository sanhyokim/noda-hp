// 管理画面の動き：本文の入力欄、写真の追加・並べ替え、確認の表示、保存前に離れる時の注意。
(() => {
  'use strict';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const svg = (d) => `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${d}</svg>`;
  const ICON_ARROW = svg('<path d="M5 12h14M13 6l6 6-6 6"/>');
  const ICON_CLOSE = svg('<path d="M6 6l12 12M18 6 6 18"/>');

  // ---------- パスワードの表示・非表示 ----------
  $$('[data-pw-toggle]').forEach((b) => {
    b.addEventListener('click', () => {
      const input = document.getElementById(b.getAttribute('aria-controls'));
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      b.textContent = show ? '隠す' : '表示';
      b.setAttribute('aria-pressed', String(show));
    });
  });

  // ---------- 削除などの前の確認 ----------
  document.addEventListener('click', (e) => {
    const b = e.target.closest('button[data-confirm]');
    if (b && !window.confirm(b.dataset.confirm)) e.preventDefault();
  });
  $$('form[data-confirm]').forEach((f) => {
    f.addEventListener('submit', (e) => {
      if (!window.confirm(f.dataset.confirm)) e.preventDefault();
    });
  });

  // 保存できなかった時は、まとめの欄へ
  const errBox = $('[data-focus]');
  if (errBox) errBox.focus();

  // ---------- 説明文の文字数 ----------
  $$('textarea[data-count]').forEach((t) => {
    const out = t.parentElement.querySelector('[data-count-out]');
    const max = Number(t.dataset.count);
    const show = () => {
      const n = [...t.value].length;
      out.textContent = n === 0 ? `検索結果には${max}字ほどまで表示されます。` : `${n}字（検索結果には${max}字ほどまで表示されます）`;
    };
    t.addEventListener('input', show);
    show();
  });

  const form = $('[data-edit-form]');
  if (!form) return;
  let dirty = false; // 保存していない変更があるか

  // ---------- 本文の入力欄 ----------
  const editor = $('[data-editor]', form);
  if (editor) {
    const area = $('.editor__area', editor);
    const src = $('.editor__src', editor);
    const linkRow = $('[data-link-row]', editor);
    const linkInput = $('#link-url', editor);
    let saved = null;
    const sync = () => { src.value = area.innerHTML; };
    try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (_) { /* 古いブラウザ */ }

    const blockTag = () => {
      const sel = window.getSelection();
      if (!sel.rangeCount || !area.contains(sel.anchorNode)) return '';
      let n = sel.anchorNode.nodeType === 1 ? sel.anchorNode : sel.anchorNode.parentElement;
      while (n && n !== area) {
        if (/^(H2|H3|H4|P|LI)$/.test(n.tagName)) return n.tagName;
        n = n.parentElement;
      }
      return '';
    };
    const inList = (tag) => {
      const sel = window.getSelection();
      if (!sel.rangeCount) return false;
      let n = sel.anchorNode.nodeType === 1 ? sel.anchorNode : sel.anchorNode.parentElement;
      while (n && n !== area) { if (n.tagName === tag) return true; n = n.parentElement; }
      return false;
    };
    const refresh = () => {
      if (!area.contains(document.getSelection().anchorNode)) return;
      const tag = blockTag();
      $$('[data-cmd]', editor).forEach((b) => {
        const c = b.dataset.cmd;
        let on = false;
        if (c === 'h3' || c === 'h4') on = tag === c.toUpperCase();
        else if (c === 'bold') on = document.queryCommandState('bold');
        else if (c === 'ul') on = inList('UL');
        else if (c === 'ol') on = inList('OL');
        if (c !== 'link') b.setAttribute('aria-pressed', String(on));
      });
    };
    document.addEventListener('selectionchange', refresh);

    const ensureFocus = () => {
      if (!area.contains(document.getSelection().anchorNode)) {
        area.focus();
        const r = document.createRange();
        r.selectNodeContents(area);
        r.collapse(false);
        const s = window.getSelection();
        s.removeAllRanges();
        s.addRange(r);
      }
    };

    $('.editor__tools', editor).addEventListener('mousedown', (e) => {
      if (e.target.closest('button')) e.preventDefault(); // 押しても選んだ文字が外れないように
    });
    $('.editor__tools', editor).addEventListener('click', (e) => {
      const b = e.target.closest('[data-cmd]');
      if (!b) return;
      const c = b.dataset.cmd;
      ensureFocus();
      if (c === 'h3' || c === 'h4') {
        document.execCommand('formatBlock', false, blockTag() === c.toUpperCase() ? '<p>' : `<${c}>`);
      } else if (c === 'bold') {
        document.execCommand('bold');
      } else if (c === 'ul') {
        document.execCommand('insertUnorderedList');
      } else if (c === 'ol') {
        document.execCommand('insertOrderedList');
      } else if (c === 'link') {
        const sel = window.getSelection();
        saved = sel.rangeCount ? sel.getRangeAt(0).cloneRange() : null;
        const a = sel.anchorNode && (sel.anchorNode.nodeType === 1 ? sel.anchorNode : sel.anchorNode.parentElement).closest('a');
        linkInput.value = a && area.contains(a) ? a.getAttribute('href') : '';
        linkRow.hidden = false;
        linkInput.focus();
        return;
      }
      sync();
      refresh();
    });

    const restore = () => {
      area.focus();
      if (saved) {
        const s = window.getSelection();
        s.removeAllRanges();
        s.addRange(saved);
      }
    };
    const closeLink = () => { linkRow.hidden = true; saved = null; };
    const applyLink = () => {
      let url = linkInput.value.trim();
      if (url === '') { closeLink(); return; }
      if (/^www\./i.test(url)) url = 'https://' + url;
      if (!/^(https?:\/\/|mailto:|tel:|\/)/i.test(url)) {
        linkInput.setCustomValidity('https:// で始まるアドレスを入れてください');
        linkInput.reportValidity();
        return;
      }
      linkInput.setCustomValidity('');
      restore();
      const sel = window.getSelection();
      if (sel.isCollapsed) {
        const a = document.createElement('a');
        a.href = url;
        a.textContent = url;
        document.execCommand('insertHTML', false, a.outerHTML);
      } else {
        document.execCommand('createLink', false, url);
      }
      closeLink();
      sync();
    };
    $('[data-link-ok]', editor).addEventListener('click', applyLink);
    linkInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); applyLink(); }
      if (e.key === 'Escape') { closeLink(); restore(); }
    });
    linkInput.addEventListener('input', () => linkInput.setCustomValidity(''));
    $('[data-link-remove]', editor).addEventListener('click', () => {
      restore();
      const sel = window.getSelection();
      if (sel.isCollapsed) {
        const a = sel.anchorNode && (sel.anchorNode.nodeType === 1 ? sel.anchorNode : sel.anchorNode.parentElement).closest('a');
        if (a && area.contains(a)) {
          const r = document.createRange();
          r.selectNodeContents(a);
          sel.removeAllRanges();
          sel.addRange(r);
        }
      }
      document.execCommand('unlink');
      closeLink();
      sync();
    });
    $('[data-link-cancel]', editor).addEventListener('click', () => { closeLink(); restore(); });

    // 貼り付けは文字だけ（空行で段落を分け、1行の改行はそのまま）
    const esc = (s) => s.replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    area.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.clipboardData || window.clipboardData).getData('text/plain').replace(/\r\n?/g, '\n').trim();
      if (!text) return;
      const paras = text.split(/\n\s*\n/).map((p) => p.split('\n').map((l) => esc(l.trim())).filter(Boolean).join('<br>')).filter(Boolean);
      if (paras.length === 1 && !text.includes('\n')) document.execCommand('insertText', false, text);
      else document.execCommand('insertHTML', false, paras.map((p) => `<p>${p}</p>`).join(''));
      sync();
    });
    // 写真を本文に落とさない（写真は下の「写真」欄へ）
    ['dragover', 'drop'].forEach((t) => area.addEventListener(t, (e) => {
      if (e.dataTransfer && [...e.dataTransfer.types].includes('Files')) e.preventDefault();
    }));
    area.addEventListener('focus', () => {
      if (area.innerHTML.trim() === '') {
        area.innerHTML = '<p><br></p>';
        const r = document.createRange();
        r.setStart(area.firstChild, 0);
        r.collapse(true);
        const s = window.getSelection();
        s.removeAllRanges();
        s.addRange(r);
      }
    });
    area.addEventListener('blur', () => {
      if (area.textContent.trim() === '' && !area.querySelector('li')) area.innerHTML = '';
      sync();
    });
    area.addEventListener('input', sync);
    form.addEventListener('submit', sync);
  }

  // ---------- 写真 ----------
  const box = $('[data-photos]', form);
  let uploading = 0;
  if (box) {
    const grid = $('[data-photo-grid]', box);
    const json = $('[data-photo-json]', box);
    const msg = $('[data-photo-msg]', box);
    const input = $('[data-photo-input]', box);
    const drop = $('[data-drop]', box);
    const items = new Map(); // li => {id, tmp, thumb, main, loading}
    let errors = [];

    const say = (text, ok = false) => {
      msg.textContent = text;
      msg.classList.toggle('is-ok', ok);
    };
    const order = () => [...grid.children].map((li) => items.get(li));
    const sync = () => {
      const list = order();
      const ready = list.filter((p) => !p.loading);
      if (ready.length && !ready.some((p) => p.main)) ready[0].main = true;
      list.forEach((p, i) => {
        const li = p.el;
        $('.ph__no', li).textContent = i + 1;
        $('img', li).alt = `写真${i + 1}`;
        $('.ph__grip', li).setAttribute('aria-label', `写真${i + 1}を動かす`);
        $('.ph__left', li).disabled = i === 0;
        $('.ph__right', li).disabled = i === list.length - 1;
        li.classList.toggle('is-main', !!p.main);
        $('.ph__main', li).hidden = !p.main;
        const sm = $('.ph__setmain', li);
        sm.textContent = p.main ? '代表の写真' : '代表にする';
        sm.disabled = !!p.main;
      });
      json.value = JSON.stringify(ready.map((p) => ({ id: p.id || null, tmp: p.tmp || null, thumb: p.thumb, main: !!p.main })));
    };

    // el は動かさず、間の写真を動かす（指で押さえている間に el を動かすと、つかみが外れるため）
    const moveTo = (el, target) => {
      const kids = [...grid.children];
      const ei = kids.indexOf(el);
      const ti = kids.indexOf(target);
      if (ei < 0 || ti < 0 || ei === ti) return;
      if (ti > ei) {
        for (let i = ei + 1; i <= ti; i++) grid.insertBefore(kids[i], el);
      } else {
        for (let i = ei - 1; i >= ti; i--) grid.insertBefore(kids[i], el.nextSibling);
      }
    };

    const make = (p) => {
      const li = document.createElement('li');
      li.className = 'ph';
      li.innerHTML = `<div class="ph__img"><img src="" alt="" width="400" height="300"><span class="ph__no"></span>`
        + `<button type="button" class="ph__grip"><span aria-hidden="true">⠿</span></button><span class="ph__main" hidden>代表</span></div>`
        + `<div class="ph__bar"><button type="button" class="ph__setmain">代表にする</button>`
        + `<button type="button" class="ph__left" aria-label="1つ前へ">${ICON_ARROW}</button>`
        + `<button type="button" class="ph__right" aria-label="1つ後ろへ">${ICON_ARROW}</button>`
        + `<button type="button" class="ph__del" aria-label="この写真を外す">${ICON_CLOSE}</button></div>`;
      $('img', li).src = p.thumb || p.preview || '';
      p.el = li;
      items.set(li, p);
      li.classList.toggle('is-loading', !!p.loading);

      $('.ph__setmain', li).addEventListener('click', () => {
        order().forEach((q) => { q.main = q === p; });
        sync();
        dirty = true;
      });
      $('.ph__left', li).addEventListener('click', () => {
        if (li.previousElementSibling) moveTo(li, li.previousElementSibling);
        sync();
        dirty = true;
      });
      $('.ph__right', li).addEventListener('click', () => {
        if (li.nextElementSibling) moveTo(li, li.nextElementSibling);
        sync();
        dirty = true;
      });
      $('.ph__del', li).addEventListener('click', () => {
        const next = li.nextElementSibling || li.previousElementSibling;
        items.delete(li);
        li.remove();
        if (p.main) order().forEach((q, i) => { q.main = i === 0; });
        sync();
        dirty = true;
        say('写真を外しました。「保存する」を押すと確定します。', true);
        if (next) $('.ph__del', next).focus();
        else input.focus();
      });

      // つまんで並べ替え（指でもマウスでも）
      const grip = $('.ph__grip', li);
      let active = false;
      grip.addEventListener('pointerdown', (e) => {
        if (e.button !== 0) return;
        e.preventDefault();
        active = true;
        grip.setPointerCapture(e.pointerId);
        li.classList.add('is-drag');
      });
      grip.addEventListener('pointermove', (e) => {
        if (!active) return;
        if (e.clientY < 90) window.scrollBy(0, -14);
        else if (e.clientY > window.innerHeight - 90) window.scrollBy(0, 14);
        const hit = document.elementFromPoint(e.clientX, e.clientY);
        const target = hit && hit.closest('.ph');
        if (target && target !== li && target.parentElement === grid) moveTo(li, target);
      });
      const end = () => {
        if (!active) return;
        active = false;
        li.classList.remove('is-drag');
        sync();
        dirty = true;
      };
      grip.addEventListener('pointerup', end);
      grip.addEventListener('pointercancel', end);
      grip.addEventListener('lostpointercapture', end);
      grip.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { e.preventDefault(); if (li.previousElementSibling) moveTo(li, li.previousElementSibling); sync(); grip.focus(); }
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { e.preventDefault(); if (li.nextElementSibling) moveTo(li, li.nextElementSibling); sync(); grip.focus(); }
      });
      return li;
    };

    let initial = [];
    try { initial = JSON.parse(json.value || '[]'); } catch (_) { initial = []; }
    initial.forEach((p) => grid.appendChild(make({ id: p.id, tmp: p.tmp, thumb: p.thumb, main: !!p.main })));
    sync();

    // 大きな写真・iPhone の HEIC はここで縮めて JPG にしてから送る（向きは端末側で直る）
    const OK_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    const LONG = 2400;
    const decode = async (file) => {
      if ('createImageBitmap' in window) {
        try { return await createImageBitmap(file, { imageOrientation: 'from-image' }); } catch (_) { /* 次の方法で */ }
      }
      return new Promise((resolve, reject) => {
        const img = new Image();
        const u = URL.createObjectURL(file);
        img.onload = () => { URL.revokeObjectURL(u); resolve(img); };
        img.onerror = () => { URL.revokeObjectURL(u); reject(new Error('decode')); };
        img.src = u;
      });
    };
    const prepare = async (file) => {
      const fine = OK_TYPES.includes(file.type) && file.size <= 4 * 1024 * 1024;
      if (fine) return { blob: file, name: file.name };
      let img;
      try { img = await decode(file); } catch (_) {
        if (OK_TYPES.includes(file.type)) return { blob: file, name: file.name };
        throw new Error(`「${file.name}」は読み込めない形式です。写真アプリで JPG にしてから選んでください。`);
      }
      const w = img.width;
      const h = img.height;
      const s = Math.min(1, LONG / Math.max(w, h));
      const c = document.createElement('canvas');
      c.width = Math.round(w * s);
      c.height = Math.round(h * s);
      const ctx = c.getContext('2d');
      ctx.fillStyle = '#fff';
      ctx.fillRect(0, 0, c.width, c.height);
      ctx.drawImage(img, 0, 0, c.width, c.height);
      const blob = await new Promise((r) => c.toBlob(r, 'image/jpeg', 0.88));
      if (!blob) return { blob: file, name: file.name };
      return { blob, name: file.name.replace(/\.[^.]+$/, '') + '.jpg' };
    };

    const queue = [];
    let running = false;
    const run = async () => {
      if (running) return;
      running = true;
      while (queue.length) {
        const [file, p] = queue.shift();
        try {
          const { blob, name } = await prepare(file);
          if (blob.size > 15 * 1024 * 1024) throw new Error(`「${file.name}」が大きすぎます（1枚15MBまで）。`);
          const fd = new FormData();
          fd.append('token', box.dataset.token);
          fd.append('photo', blob, name);
          const res = await fetch(box.dataset.upload, { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-CSRF-Token': box.dataset.csrf, Accept: 'application/json' } });
          let data = {};
          try { data = await res.json(); } catch (_) { data = {}; }
          if (!res.ok || !data.tmp) throw new Error(data.error || `「${file.name}」を送れませんでした。電波の良い所でもう一度お試しください。`);
          p.tmp = data.tmp;
          p.thumb = data.thumb;
          p.loading = false;
          const img = $('img', p.el);
          img.src = data.thumb;
          if (p.preview) URL.revokeObjectURL(p.preview);
          p.el.classList.remove('is-loading');
        } catch (err) {
          errors.push(err.message);
          items.delete(p.el);
          p.el.remove();
          if (p.preview) URL.revokeObjectURL(p.preview);
        }
        uploading--;
        sync();
        if (uploading === 0) {
          say(errors.length ? errors.join('\n') : '写真を追加しました。「保存する」を押すと確定します。', !errors.length);
          errors = [];
        }
      }
      running = false;
    };

    const add = (files) => {
      const list = [...files].filter((f) => /^image\//.test(f.type) || /\.(jpe?g|png|webp|heic|heif)$/i.test(f.name));
      if (!list.length) {
        say('写真のファイルを選んでください（JPG・PNG・WebP）。');
        return;
      }
      say(`${list.length}枚の写真を読み込んでいます…`, true);
      list.forEach((file) => {
        const p = { loading: true, preview: URL.createObjectURL(file), main: false };
        grid.appendChild(make(p));
        uploading++;
        queue.push([file, p]);
      });
      sync();
      dirty = true;
      run();
    };
    input.addEventListener('change', () => { add(input.files); input.value = ''; });
    ['dragenter', 'dragover'].forEach((t) => drop.addEventListener(t, (e) => { e.preventDefault(); drop.classList.add('is-over'); }));
    ['dragleave', 'drop'].forEach((t) => drop.addEventListener(t, () => drop.classList.remove('is-over')));
    drop.addEventListener('drop', (e) => { e.preventDefault(); add(e.dataTransfer.files); });
    // 欄の外に落としても、ブラウザが写真を開いてしまわないように
    window.addEventListener('dragover', (e) => e.preventDefault());
    window.addEventListener('drop', (e) => { if (!drop.contains(e.target)) e.preventDefault(); });
  }

  // ---------- 保存 ----------
  form.addEventListener('input', () => { dirty = true; });
  form.addEventListener('change', () => { dirty = true; });
  form.addEventListener('submit', (e) => {
    if (uploading > 0) {
      e.preventDefault();
      window.alert('写真を読み込んでいます。終わるまで少しお待ちください。');
      return;
    }
    dirty = false;
    $$('.savebar .abtn', form).forEach((b) => b.classList.add('is-busy'));
  });
  window.addEventListener('beforeunload', (e) => {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });
})();
