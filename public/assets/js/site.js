/* 株式会社野田 公式サイト：スマホメニュー、地図の読み込み、フォームの添付チェック、表示時のふわっと上がる動き */
(function () {
  'use strict';
  var doc = document.documentElement;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

  /* ---- 最初の画面の写真：最初の表示が終わってから付ける（CSS の .hero.is-on） ---- */
  var hero = document.querySelector('.hero');
  if (hero) {
    requestAnimationFrame(function () {
      requestAnimationFrame(function () { hero.classList.add('is-on'); });
    });
  }

  /* ---- 広告の成果の計測：電話・LINE を押した時と、問い合わせを送った時に GTM へ知らせる ---- */
  var track = function (event, extra) {
    (window.dataLayer = window.dataLayer || []).push(Object.assign({ event: event }, extra || {}));
  };
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href');
    if (/^tel:/i.test(href)) track('tel_click', { link_url: href });
    else if (/^https:\/\/(line\.me|lin\.ee)\//i.test(href)) track('line_click', { link_url: href });
  });
  // 送った直後の1回だけ（読み込み直しでは数えない）
  var sent = document.querySelector('[data-conv="contact_submit"]');
  if (sent) {
    var key = 'noda-conv-' + location.search;
    var done = false;
    try { done = sessionStorage.getItem(key) === '1'; sessionStorage.setItem(key, '1'); } catch (err) {}
    if (!done) track('contact_submit');
  }

  /* ---- スマホメニュー（1100px 未満） ---- */
  var openBtn = document.querySelector('.topbar__menu');
  var closeBtn = document.querySelector('.sidebar__close');
  var sidebar = document.getElementById('sidebar');
  var backdrop = document.querySelector('.backdrop');
  var lastFocus = null;

  function setOpen(open) {
    doc.classList.toggle('menu-open', open);
    backdrop.hidden = !open;
    openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      lastFocus = document.activeElement;
      sidebar.setAttribute('role', 'dialog');
      sidebar.setAttribute('aria-modal', 'true');
      closeBtn.focus();
    } else {
      sidebar.removeAttribute('role');
      sidebar.removeAttribute('aria-modal');
      if (lastFocus) lastFocus.focus();
    }
  }
  if (openBtn && sidebar && backdrop) {
    openBtn.addEventListener('click', function () { setOpen(true); });
    closeBtn.addEventListener('click', function () { setOpen(false); });
    backdrop.addEventListener('click', function () { setOpen(false); });
    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && doc.classList.contains('menu-open')) setOpen(false);
    });
    window.matchMedia('(min-width: 1100px)').addEventListener('change', function (m) {
      if (m.matches && doc.classList.contains('menu-open')) setOpen(false);
    });
  }

  /* ---- 地図：押したときだけ Google マップを読み込む ---- */
  document.querySelectorAll('.map[data-map-src]').forEach(function (box) {
    var btn = box.querySelector('.map__btn');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var f = document.createElement('iframe');
      f.src = box.getAttribute('data-map-src');
      f.title = '地図（Googleマップ）';
      f.loading = 'lazy';
      f.referrerPolicy = 'no-referrer-when-downgrade';
      box.appendChild(f);
      box.classList.add('is-loaded');
      btn.hidden = true;
    });
  });

  /* ---- 左カラムの動画：押したときだけ YouTube を読み込む（読み込めない環境ではリンクで YouTube を開く） ---- */
  document.querySelectorAll('.svideo[data-yt]').forEach(function (a) {
    a.addEventListener('click', function (ev) {
      ev.preventDefault();
      var box = document.createElement('div');
      box.className = 'banner svideo is-playing';
      var f = document.createElement('iframe');
      f.src = 'https://www.youtube-nocookie.com/embed/' + a.getAttribute('data-yt') + '?autoplay=1&rel=0';
      f.title = a.getAttribute('data-yt-title');
      f.allow = 'autoplay; encrypted-media; picture-in-picture';
      f.allowFullscreen = true;
      box.appendChild(f);
      a.replaceWith(box);
    });
  });

  /* ---- お問い合わせ：添付ファイルの数と大きさを送る前に確かめる（最終チェックはサーバー側） ---- */
  document.querySelectorAll('input[type="file"][data-max-files]').forEach(function (input) {
    var maxFiles = parseInt(input.getAttribute('data-max-files'), 10);
    var maxSize = parseInt(input.getAttribute('data-max-size'), 10);
    var msg = document.createElement('p');
    msg.className = 'field__err';
    msg.id = input.id + '-err';
    msg.hidden = true;
    input.insertAdjacentElement('afterend', msg);
    input.addEventListener('change', function () {
      var text = '';
      if (input.files.length > maxFiles) {
        text = '添付できるのは' + maxFiles + 'ファイルまでです。';
      } else {
        Array.prototype.forEach.call(input.files, function (f) {
          if (!text && f.size > maxSize) text = '「' + f.name + '」が10MBを超えています。';
        });
      }
      msg.textContent = text;
      msg.hidden = !text;
      if (text) {
        input.setAttribute('aria-invalid', 'true');
        input.setAttribute('aria-describedby', msg.id);
        input.value = '';
      } else {
        input.removeAttribute('aria-invalid');
        input.removeAttribute('aria-describedby');
      }
    });
  });

  /* ---- 写真の切り替え枠：5.5秒ごとに次の写真へ（画面に見えている間だけ） ---- */
  if (!reduce.matches) {
    document.querySelectorAll('[data-slides]').forEach(function (box) {
      var items = box.querySelectorAll('.slides__item');
      if (items.length < 2) return;
      var i = 0, timer = null;
      var next = function () {
        items[i].classList.remove('is-on');
        i = (i + 1) % items.length;
        items[i].classList.add('is-on');
      };
      var start = function () { if (!timer) timer = setInterval(next, 5500); };
      var stop = function () { clearInterval(timer); timer = null; };
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (en) { en[0].isIntersecting ? start() : stop(); }).observe(box);
      } else {
        start();
      }
    });
  }

  /* ---- 「同じ担当者が対応します！」の図：画面に入ったら動き出す ---- */
  if (!reduce.matches && 'IntersectionObserver' in window) {
    document.querySelectorAll('[data-onestop]').forEach(function (fig) {
      fig.classList.add('is-ready');
      var seen = new IntersectionObserver(function (en) {
        if (!en[0].isIntersecting) return;
        fig.classList.add('is-play');
        seen.disconnect();
      }, { threshold: 0.35 });
      seen.observe(fig);
    });
  }

  /* ---- 施工事例の写真：押すと拡大（矢印・左右のスワイプで前後、Esc か背景を押すと閉じる） ---- */
  var lb = document.getElementById('lightbox');
  var shots = document.querySelectorAll('[data-lb] [data-lb-index]');
  if (lb && shots.length) {
    var lbImg = lb.querySelector('.lb__img'), lbCap = lb.querySelector('.lb__cap');
    var lbBtns = lb.querySelectorAll('button'), cur = 0, back = null, x0 = null;
    var show = function (i) {
      cur = (i + shots.length) % shots.length;
      var a = shots[cur], th = a.querySelector('img');
      lbImg.src = a.href;
      lbImg.width = a.getAttribute('data-w');
      lbImg.height = a.getAttribute('data-h');
      lbImg.alt = th ? th.alt : a.textContent;
      lbCap.textContent = (cur + 1) + ' / ' + shots.length;
      new Image().src = shots[(cur + 1) % shots.length].href;
    };
    var open = function (i) {
      back = document.activeElement;
      show(i);
      lb.hidden = false;
      doc.classList.add('lb-open');
      lbBtns[0].focus();
    };
    var close = function () {
      lb.hidden = true;
      doc.classList.remove('lb-open');
      lbImg.removeAttribute('src');
      if (back) back.focus();
    };
    lb.classList.toggle('lb--single', shots.length < 2);
    shots.forEach(function (a, i) {
      a.addEventListener('click', function (ev) { ev.preventDefault(); open(i); });
    });
    document.querySelectorAll('[data-lb-open]').forEach(function (b) {
      b.addEventListener('click', function () { open(+b.getAttribute('data-lb-open')); });
    });
    lbBtns[0].addEventListener('click', close);
    lbBtns[1].addEventListener('click', function () { show(cur - 1); });
    lbBtns[2].addEventListener('click', function () { show(cur + 1); });
    lb.addEventListener('click', function (ev) { if (ev.target === lb || ev.target.classList.contains('lb__fig')) close(); });
    lb.addEventListener('touchstart', function (ev) { x0 = ev.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', function (ev) {
      var dx = ev.changedTouches[0].clientX - x0;
      if (x0 !== null && Math.abs(dx) > 50) show(cur + (dx < 0 ? 1 : -1));
      x0 = null;
    });
    document.addEventListener('keydown', function (ev) {
      if (lb.hidden) return;
      if (ev.key === 'Escape') close();
      else if (ev.key === 'ArrowLeft') show(cur - 1);
      else if (ev.key === 'ArrowRight') show(cur + 1);
      else if (ev.key === 'Tab') {
        /* 拡大表示の中だけでフォーカスを回す */
        var list = Array.prototype.filter.call(lbBtns, function (b) { return b.offsetParent; });
        var k = list.indexOf(document.activeElement);
        ev.preventDefault();
        list[(k + (ev.shiftKey ? -1 : 1) + list.length) % list.length].focus();
      }
    });
  }

  /* ---- 施工写真の残りを表示／スマホの絞り込みは選んでいるチップが見える位置まで送る ---- */
  document.querySelectorAll('[data-photos-more]').forEach(function (b) {
    b.addEventListener('click', function () {
      var g = document.querySelector('.photos');
      g.classList.add('is-all');
      var first = g.querySelector('.photos__rest a');
      if (first) first.focus();
    });
  });
  document.querySelectorAll('.filter__chips').forEach(function (ul) {
    var on = ul.querySelector('.is-on');
    if (on && ul.scrollWidth > ul.clientWidth) ul.scrollLeft = on.offsetLeft - 20;
  });

  /* ---- お問い合わせフォーム：送る前に必須項目と添付を確かめる（サーバー側でも同じ確認をする） ---- */
  var focusMe = document.querySelector('[data-focus]');
  if (focusMe) {
    focusMe.scrollIntoView({ block: 'center' });
    focusMe.focus({ preventScroll: true });
  }
  var cform = document.querySelector('form[data-validate]');
  if (cform) {
    var msgs = {
      name: 'お名前を入力してください。', prefecture: '都道府県を選んでください。', phone: '電話番号を入力してください。',
      email: 'メールアドレスを入力してください。', message: 'ご相談内容を入力してください。', agree: 'プライバシーポリシーへの同意が必要です。'
    };
    var setErr = function (el, text) {
      var box = el.closest('.field');
      var p = box.querySelector('.field__err');
      var id = 'err-' + el.name.replace(/\W/g, '');
      if (!text) {
        if (p) p.remove();
        el.removeAttribute('aria-invalid');
        el.removeAttribute('aria-describedby');
        return;
      }
      if (!p) {
        p = document.createElement('p');
        p.className = 'field__err';
        p.id = id;
        box.appendChild(p);
      }
      p.textContent = text;
      el.setAttribute('aria-invalid', 'true');
      el.setAttribute('aria-describedby', p.id);
    };
    var check = function (el) {
      var val = el.type === 'checkbox' ? el.checked : el.value.trim() !== '';
      var text = '';
      if (el.required && !val) text = msgs[el.name] || '入力してください。';
      else if (el.name === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value.trim())) text = 'メールアドレスの形が正しくありません。もう一度ご確認ください。';
      else if (el.name === 'phone' && val && el.value.replace(/\D/g, '').length < 10) text = '電話番号は、市外局番から数字で入力してください（例：092-504-5502）。';
      else if (el.type === 'file' && el.files.length) {
        var max = +el.dataset.maxSize, ok = /\.(jpe?g|png|hei[cf]|pdf)$/i;
        if (el.files.length > +el.dataset.maxFiles) text = '添付できるのは' + el.dataset.maxFiles + 'ファイルまでです。';
        Array.prototype.forEach.call(el.files, function (f) {
          if (text) return;
          if (!ok.test(f.name)) text = '「' + f.name + '」は添付できない種類のファイルです。写真（jpg・png・heic）か PDF を選んでください。';
          else if (f.size > max) text = '「' + f.name + '」は10MBを超えています。10MB以下のファイルにしてください。';
        });
      }
      setErr(el, text);
      return !text;
    };
    var fields = cform.querySelectorAll('input[required], select[required], textarea[required], input[name="email"], input[name="phone"], input[type="file"]');
    Array.prototype.forEach.call(fields, function (el) {
      // 一度間違いを出した項目は、直したらすぐ消す
      el.addEventListener(el.tagName === 'SELECT' || el.type === 'checkbox' || el.type === 'file' ? 'change' : 'input', function () {
        if (el.getAttribute('aria-invalid') === 'true' || el.type === 'file') check(el);
      });
    });
    cform.addEventListener('submit', function (ev) {
      var first = null;
      Array.prototype.forEach.call(fields, function (el) {
        if (!check(el) && !first) first = el;
      });
      if (first) {
        ev.preventDefault();
        first.focus();
        return;
      }
      var btn = cform.querySelector('[type="submit"]');
      if (cform.dataset.sending) { ev.preventDefault(); return; }
      cform.dataset.sending = '1';
      btn.setAttribute('aria-disabled', 'true');
      btn.firstChild.textContent = '送信しています…';
    });
  }

  /* ---- 施工までの流れ：番号がスクロールについてきて、縦の線が今の番号まで青く伸びる（Boss 2026-10-22） ---- */
  var tl = document.querySelector('.timeline');
  if (tl) {
    var tlItems = Array.prototype.slice.call(tl.querySelectorAll('.timeline__item'));
    var gh = document.querySelector('.gh');
    var tlTick = false;
    var tlUpdate = function () {
      tlTick = false;
      var stick = (gh ? Math.max(0, gh.getBoundingClientRect().bottom) : 0) + 20;
      tl.style.setProperty('--tl-top', stick + 'px');
      // 画面の上から4割の線まで青く伸ばし、線が届いた番号を塗る（最後の番号の先には伸ばさない）
      var top = tl.getBoundingClientRect().top;
      var line = Math.max(stick + 28, window.innerHeight * 0.4);
      var dots = tlItems.map(function (it) { var d = it.querySelector('.timeline__dot').getBoundingClientRect(); return d.top + d.height / 2; });
      var fill = Math.max(0, Math.min(line, dots[dots.length - 1]) - top);
      tl.style.setProperty('--tl-fill', fill + 'px');
      var now = -1;
      dots.forEach(function (c, i) { if (c <= line + 1) now = i; });
      tlItems.forEach(function (it, i) {
        it.classList.toggle('is-done', i <= now);
        it.classList.toggle('is-now', i === now);
      });
    };
    var tlQueue = function () {
      if (!tlTick) { tlTick = true; requestAnimationFrame(tlUpdate); }
    };
    tl.classList.add('is-live');
    window.addEventListener('scroll', tlQueue, { passive: true });
    window.addEventListener('resize', tlQueue);
    tlUpdate();
  }

  /* ---- アフターフォロー：チェックした数でメーターの針と文が変わる（清掃サイトの Self Check と同じ演出） ---- */
  var gauge = document.querySelector('[data-gauge]');
  if (gauge) {
    var boxes = Array.prototype.slice.call(document.querySelectorAll('.selfcheck__item input'));
    var copy = [
      ['まずはチェックを', 'お店に当てはまるものを押してください。ひとつでも当てはまれば、点検のタイミングです。'],
      ['点検をおすすめします', 'ダクトに油が溜まり始めているサインです。無料の点検で、ダクトの内側の状態を確かめましょう。'],
      ['早めの清掃が必要です', 'ダクトの中に油だまりができている可能性が高い状態です。火災の危険が高まる前に、点検・清掃をご相談ください。'],
      ['今すぐご相談ください', 'すべてのサインが出ています。ダクト火災の危険が高い状態です。まずはお電話ください。']
    ];
    var gUpdate = function () {
      var n = boxes.filter(function (b) { return b.checked; }).length;
      var all = boxes.length;
      gauge.querySelector('[data-gauge-arc]').setAttribute('stroke-dashoffset', (408.4 * (1 - n / all)).toFixed(1));
      gauge.querySelector('[data-gauge-needle]').style.transform = 'rotate(' + (-90 + 180 * n / all) + 'deg)';
      gauge.querySelector('[data-gauge-num]').textContent = n;
      var c = n === 0 ? copy[0] : n <= 2 ? copy[1] : n < all ? copy[2] : copy[3];
      gauge.querySelector('[data-gauge-level]').textContent = c[0];
      gauge.querySelector('[data-gauge-msg]').textContent = c[1];
      gauge.classList.toggle('is-high', n >= 3);
    };
    boxes.forEach(function (b) { b.addEventListener('change', gUpdate); });
    gUpdate();
  }

  /* ---- 表示時の動き：各セクション直下の要素と、格子の中身を1回だけ ---- */
  if (reduce.matches || !('IntersectionObserver' in window)) return;
  var groups = [];
  document.querySelectorAll('main .section').forEach(function (sec) {
    var kids = [];
    Array.prototype.forEach.call(sec.children, function (el) {
      if (el.matches('.grid, .svcs, .problems, .reasons, .steps, .ig, .news__list, .commit, .timeline, .numlist, .faq, .photos')) {
        groups.push(Array.prototype.slice.call(el.children));
      } else {
        kids.push(el);
      }
    });
    groups.push(kids);
  });
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (en.isIntersecting) {
        en.target.classList.add('is-in');
        io.unobserve(en.target);
      }
    });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
  groups.forEach(function (list) {
    list.forEach(function (el, i) {
      el.classList.add('reveal');
      el.style.setProperty('--delay', (Math.min(i, 3) * 0.08).toFixed(2) + 's');
      io.observe(el);
    });
  });

  /* ---- 特注フードの素材サンプル：素材を選ぶとフードと素材プレートが切り替わる（2026-11-12）
     50種類以上あっても重くならないよう、写真は選んだ素材とその前後だけを読む ---- */
  document.querySelectorAll('[data-mat]').forEach(function (root) {
    var hoods = root.querySelectorAll('.mat__hood');
    var plates = root.querySelectorAll('.mat__plate');
    var texts = root.querySelectorAll('.mat__text');
    var chips = root.querySelectorAll('.mat__chip');
    var fbtns = root.querySelectorAll('.mat__fbtn');
    var more = root.querySelector('.mat__more');
    var view = root.querySelector('.mat__view');
    var cur = 0;
    var load = function (i) {
      [hoods[i], plates[i]].forEach(function (el) {
        if (!el) return;
        el.querySelectorAll('[data-srcset]').forEach(function (s) { s.srcset = s.dataset.srcset; s.removeAttribute('data-srcset'); });
        el.querySelectorAll('[data-src]').forEach(function (s) { s.src = s.dataset.src; s.removeAttribute('data-src'); });
      });
    };
    var visible = function () {
      return Array.prototype.filter.call(chips, function (c) { return !c.parentNode.hidden; })
        .map(function (c) { return +c.dataset.i; });
    };
    var show = function (i, scroll) {
      if (i === cur || !hoods[i]) return;
      load(i);
      [hoods, plates, texts, chips].forEach(function (list) {
        list[cur].classList.remove('is-on');
        list[i].classList.add('is-on');
      });
      hoods[cur].setAttribute('aria-hidden', 'true');
      hoods[i].removeAttribute('aria-hidden');
      texts[cur].hidden = true;
      texts[i].hidden = false;
      chips[cur].setAttribute('aria-pressed', 'false');
      chips[i].setAttribute('aria-pressed', 'true');
      cur = i;
      // 前後も先に読んでおく（次へを押した時に待たせない）
      var v = visible(), k = v.indexOf(i);
      if (k > -1) { load(v[(k + 1) % v.length]); load(v[(k - 1 + v.length) % v.length]); }
      if (scroll) {
        var strip = chips[i].closest('.mat__chips');
        if (strip.scrollWidth > strip.clientWidth) {
          var li = chips[i].parentNode;
          strip.scrollTo({ left: li.offsetLeft - (strip.clientWidth - li.offsetWidth) / 2, behavior: reduce.matches ? 'auto' : 'smooth' });
        }
      }
    };
    var step = function (d) {
      var v = visible(), k = v.indexOf(cur);
      if (!v.length) return;
      show(v[k < 0 ? 0 : (k + d + v.length) % v.length], true);
    };
    chips.forEach(function (c) {
      c.addEventListener('click', function () { show(+c.dataset.i, true); });
      // マウスを乗せたら先に読んでおく
      c.addEventListener('pointerenter', function () { load(+c.dataset.i); });
    });
    root.querySelector('.mat__nav--prev').addEventListener('click', function () { step(-1); });
    root.querySelector('.mat__nav--next').addEventListener('click', function () { step(1); });
    view.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') { step(-1); e.preventDefault(); }
      if (e.key === 'ArrowRight') { step(1); e.preventDefault(); }
    });
    // スマホ：写真を左右にはらって切り替え
    var x0 = null, y0 = 0;
    view.addEventListener('pointerdown', function (e) { if (e.pointerType !== 'mouse') { x0 = e.clientX; y0 = e.clientY; } });
    view.addEventListener('pointerup', function (e) {
      if (x0 === null) return;
      var dx = e.clientX - x0, dy = e.clientY - y0;
      x0 = null;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) step(dx < 0 ? 1 : -1);
    });
    view.addEventListener('pointercancel', function () { x0 = null; });
    // 色で絞り込む
    fbtns.forEach(function (b) {
      b.addEventListener('click', function () {
        var f = b.dataset.family;
        fbtns.forEach(function (o) { o.classList.toggle('is-on', o === b); o.setAttribute('aria-pressed', o === b ? 'true' : 'false'); });
        chips.forEach(function (c) { c.parentNode.hidden = !!f && c.parentNode.dataset.family !== f; });
        if (more) more.hidden = !!f;
        var v = visible();
        if (v.length && v.indexOf(cur) < 0) show(v[0], false);
        var strip = root.querySelector('.mat__chips');
        strip.scrollLeft = 0;
      });
    });
    // 見える所まで来たら、最初の前後の写真も読んでおく
    if (hoods.length > 1 && 'IntersectionObserver' in window) {
      var pre = new IntersectionObserver(function (es) {
        if (es[0].isIntersecting) { load(1); load(hoods.length - 1); pre.disconnect(); }
      }, { rootMargin: '300px' });
      pre.observe(view);
    }
  });

  /* ---- 素材サンプルへの案内：写真が素材ごとにゆっくり切り替わる。見本を押すとその素材に（押したら自動はやめる） ---- */
  document.querySelectorAll('[data-mtz]').forEach(function (root) {
    var hoods = root.querySelectorAll('.mtz__hood');
    var chips = root.querySelectorAll('.mtz__chip');
    var name = root.querySelector('.mtz__name');
    var cur = 0, timer = null, seen = false;
    var load = function (i) {
      var el = hoods[i];
      if (!el) return;
      el.querySelectorAll('[data-srcset]').forEach(function (s) { s.srcset = s.dataset.srcset; s.removeAttribute('data-srcset'); });
      el.querySelectorAll('[data-src]').forEach(function (s) { s.src = s.dataset.src; s.removeAttribute('data-src'); });
    };
    var show = function (i) {
      if (i === cur) return;
      load(i);
      var img = hoods[i].querySelector('img');
      var go = function () {
        hoods[cur].classList.remove('is-on'); chips[cur].classList.remove('is-on'); chips[cur].setAttribute('aria-pressed', 'false');
        hoods[i].classList.add('is-on'); chips[i].classList.add('is-on'); chips[i].setAttribute('aria-pressed', 'true');
        if (name) name.textContent = chips[i].dataset.name;
        cur = i;
        load((i + 1) % hoods.length);
      };
      // 読み終わってから切り替える（途中の真っ暗を見せない）
      if (img && !img.complete) img.addEventListener('load', go, { once: true }); else go();
    };
    var stop = function () { clearInterval(timer); timer = null; };
    var start = function () {
      if (timer || reduce.matches || hoods.length < 2) return;
      timer = setInterval(function () { show((cur + 1) % hoods.length); }, 3200);
    };
    chips.forEach(function (c) {
      c.addEventListener('click', function () { stop(); seen = true; show(+c.dataset.i); });
      c.addEventListener('pointerenter', function () { load(+c.dataset.i); });
    });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (es) {
        if (es[0].isIntersecting) { load(1); if (!seen) start(); } else stop();
      }, { threshold: 0.35 }).observe(root);
    }
  });
})();
