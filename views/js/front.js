/*
 * m4p_heroslider
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 */
/**
 * Home hero: a video slide moves on when its video ends (or after data-max-s seconds), a photo slide
 * after data-photo-ms. The progress bar of the current slide fills as it plays. Motion stops while the
 * slider is off-screen, on a hidden tab, after the pause button and with reduced motion. A video loads
 * only when its slide comes up (the next one is preloaded) and nothing but the first photo loads before
 * the page has settled; the first frame of a video waits underneath as a photo.
 */
(function () {
  'use strict';

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  // Videos and the photos of later slides wait this long after the page load, past the LCP.
  var SETTLE_MS = 1500;

  function pad(n) { return (n < 10 ? '0' : '') + n; }

  function init(root) {
    var slides = Array.prototype.slice.call(root.querySelectorAll('.m4p-hero__slide'));
    var steps = Array.prototype.slice.call(root.querySelectorAll('[data-go]'));
    var pause = root.querySelector('[data-pause]');
    var count = root.querySelector('.m4p-hero__count');
    var photoMs = parseInt(root.getAttribute('data-photo-ms'), 10) || 7000;
    var current = 0;
    var playing = !reduce;
    var visible = false;
    var elapsed = 0;
    var last = 0;
    var raf = 0;
    var settled = false;

    function video(n) { return slides[n].querySelector('video'); }

    function load(v) {
      if (settled && v && !v.src) { v.src = v.getAttribute('data-src'); v.preload = 'auto'; }
    }

    function fill(n, ratio) {
      if (steps[n]) { steps[n].querySelector('i').style.transform = 'scaleX(' + ratio + ')'; }
    }

    function run() {
      window.cancelAnimationFrame(raf);
      root.classList.toggle('is-paused', !playing);
      if (pause) { pause.setAttribute('aria-label', root.getAttribute(playing ? 'data-label-pause' : 'data-label-play')); }
      var v = video(current);
      if (!(playing && visible && !document.hidden) || slides.length < 2 && !v) {
        if (v) { v.pause(); }
        return;
      }
      if (v) {
        load(v);
        if (!v.src) { return; }
        var started = v.play();
        if (started && started.catch) { started.catch(function () { playing = false; run(); }); }
      }
      last = performance.now();
      raf = window.requestAnimationFrame(loop);
    }

    function loop(now) {
      var v = video(current);
      var ratio;
      if (v) {
        var limit = parseFloat(v.getAttribute('data-max-s')) || Infinity;
        var length = Math.min(v.duration || Infinity, limit);
        ratio = isFinite(length) ? v.currentTime / length : 0;
      } else {
        elapsed += now - last;
        last = now;
        ratio = elapsed / photoMs;
      }
      if (ratio >= 1 && slides.length > 1) { go(current + 1); return; }
      fill(current, Math.min(ratio, 1));
      raf = window.requestAnimationFrame(loop);
    }

    function go(n) {
      if (!settled && n !== current) { settle(); }
      var previous = video(current);
      if (previous) { previous.pause(); }
      n = (n + slides.length) % slides.length;
      slides.forEach(function (slide, k) {
        slide.classList.toggle('is-active', k === n);
        slide.inert = k !== n;
      });
      steps.forEach(function (step, k) {
        if (k === n) { step.setAttribute('aria-current', 'true'); } else { step.removeAttribute('aria-current'); }
        fill(k, k < n ? 1 : 0);
      });
      if (count) { count.textContent = pad(n + 1) + ' / ' + pad(slides.length); }
      current = n;
      elapsed = 0;
      var v = video(current);
      if (v && v.readyState) { v.currentTime = 0; }
      if (slides.length > 1) { load(video((current + 1) % slides.length)); }
      run();
    }

    slides.forEach(function (slide, n) {
      var v = video(n);
      if (!v) { return; }
      v.addEventListener('playing', function () { slide.classList.add('is-playing'); });
      v.addEventListener('ended', function () {
        if (n !== current) { return; }
        if (slides.length > 1) { go(current + 1); } else { v.currentTime = 0; run(); }
      });
    });
    steps.forEach(function (step, n) { step.addEventListener('click', function () { go(n); }); });
    Array.prototype.forEach.call(root.querySelectorAll('[data-step]'), function (button) {
      button.addEventListener('click', function () { go(current + parseInt(button.getAttribute('data-step'), 10)); });
    });
    if (pause) { pause.addEventListener('click', function () { playing = !playing; run(); }); }
    document.addEventListener('visibilitychange', run);
    new IntersectionObserver(function (entries) { visible = entries[0].isIntersecting; run(); }, { threshold: 0.5 }).observe(root);
    go(0);
    function settle() {
      settled = true;
      Array.prototype.forEach.call(root.querySelectorAll('[data-srcset], [data-src]:not(video)'), function (el) {
        if (el.hasAttribute('data-srcset')) { el.srcset = el.getAttribute('data-srcset'); }
        if (el.hasAttribute('data-src')) { el.src = el.getAttribute('data-src'); }
      });
      if (slides.length > 1) { load(video((current + 1) % slides.length)); }
      run();
    }
    if (document.readyState === 'complete') {
      window.setTimeout(settle, SETTLE_MS);
    } else {
      window.addEventListener('load', function () { window.setTimeout(settle, SETTLE_MS); }, { once: true });
    }
  }

  function start() {
    Array.prototype.forEach.call(document.querySelectorAll('[data-m4p-hero]'), init);
  }

  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', start); } else { start(); }
})();
