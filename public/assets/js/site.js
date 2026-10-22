/* 株式会社野田 公式サイト：スマホメニュー、地図の読み込み、フォームの添付チェック、表示時のふわっと上がる動き */
(function () {
  'use strict';
  var doc = document.documentElement;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

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

  /* ---- 表示時の動き：各セクション直下の要素と、格子の中身を1回だけ ---- */
  if (reduce.matches || !('IntersectionObserver' in window)) return;
  var groups = [];
  document.querySelectorAll('main .section').forEach(function (sec) {
    var kids = [];
    Array.prototype.forEach.call(sec.children, function (el) {
      if (el.matches('.grid, .svcs, .problems, .reasons, .steps, .ig, .news__list, .commit, .timeline, .numlist, .faq')) {
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
})();
