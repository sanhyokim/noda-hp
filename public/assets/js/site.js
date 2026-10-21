/* 株式会社野田 公式サイト：スマホメニューと、表示時のふわっと上がる動き */
(function () {
  'use strict';
  var doc = document.documentElement;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

  /* ---- スマホメニュー（1024px 未満） ---- */
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
    window.matchMedia('(min-width: 1024px)').addEventListener('change', function (m) {
      if (m.matches && doc.classList.contains('menu-open')) setOpen(false);
    });
  }

  /* ---- 表示時の動き：各セクション直下の要素と、格子の中身を1回だけ ---- */
  if (reduce.matches || !('IntersectionObserver' in window)) return;
  var groups = [];
  document.querySelectorAll('main .section').forEach(function (sec) {
    var kids = [];
    Array.prototype.forEach.call(sec.children, function (el) {
      if (el.matches('.grid, .bento, .steps, .ig, .news__list')) {
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
