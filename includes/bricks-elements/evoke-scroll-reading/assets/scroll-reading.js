/* EVK Scroll Reading — frontend
 * Wymaga GSAP + ScrollTrigger + SplitText (ładowane przez Bricks Animator).
 */
(function () {
  'use strict';

  function initAll() {
    document.querySelectorAll('[data-evk-sr]').forEach(function (el) {
      var cfg;
      try { cfg = JSON.parse(el.getAttribute('data-evk-sr')); }
      catch (e) { return; }

      // Cel: cały kontener (nestable — children to dowolne elementy Bricks)
      var splitTypeMap = { words: 'words', chars: 'chars', lines: 'lines' };
      var splitBy = splitTypeMap[cfg.splitType] || 'words';

      var split = new SplitText(el, {
        type: splitBy,
        wordsClass: 'evk-sr-word',
        charsClass: 'evk-sr-char',
        linesClass: 'evk-sr-line',
      });

      var targets = split[splitBy];

      gsap.set(targets, { color: cfg.colorDim });

      gsap.timeline({
        scrollTrigger: {
          trigger: el,
          start:   cfg.start  || 'top 90%',
          end:     cfg.end    || 'bottom 20%',
          scrub:   cfg.scrub > 0 ? cfg.scrub : false,
        },
      }).to(targets, {
        color:   cfg.colorActive,
        stagger: cfg.stagger || 0.05,
        ease:    'none',
      });
    });
  }

  function waitForGSAP(cb, tries) {
    tries = tries || 0;
    if (window.gsap && window.ScrollTrigger && window.SplitText) {
      gsap.registerPlugin(ScrollTrigger, SplitText);
      cb();
    } else if (tries < 50) {
      setTimeout(function () { waitForGSAP(cb, tries + 1); }, 100);
    } else {
      console.warn('[EVK Scroll Reading] Brak GSAP/ScrollTrigger/SplitText.');
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      if (bricksIsFrontend) { waitForGSAP(initAll); }
    });
  } else {
    if (bricksIsFrontend) { waitForGSAP(initAll); }
  }
})();
