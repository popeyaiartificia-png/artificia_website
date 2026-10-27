/* Artificia AI site script: menu, click-to-load embeds, curriculum ruler, motion. */
(function () {
  'use strict';
  var d = document, root = d.documentElement;

  // Mobile menu
  var top = d.querySelector('[data-top]');
  var toggle = top && top.querySelector('.nav-toggle');
  if (toggle) {
    var setOpen = function (open) {
      top.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', String(open));
    };
    toggle.addEventListener('click', function () { setOpen(!top.classList.contains('is-open')); });
    d.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && top.classList.contains('is-open')) { setOpen(false); toggle.focus(); }
    });
    top.querySelectorAll('.nav a').forEach(function (a) { a.addEventListener('click', function () { setOpen(false); }); });
  }

  // Videos and the map load nothing until the visitor asks for them.
  d.addEventListener('click', function (e) {
    var b = e.target.closest('.vid[data-src], .map__facade[data-src]');
    if (!b) return;
    var f = d.createElement('iframe');
    f.src = b.getAttribute('data-src');
    f.title = b.getAttribute('aria-label') || 'Map';
    f.allow = 'autoplay; fullscreen';
    f.setAttribute('allowfullscreen', '');
    if (b.classList.contains('vid')) {
      var w = d.createElement('div');
      w.className = 'vid is-playing';
      w.appendChild(f);
      b.replaceWith(w);
    } else {
      b.replaceWith(f);
    }
  });

  // Curriculum: pointing at a module marks its days on the ruler.
  d.querySelectorAll('.curr').forEach(function (sec) {
    var ruler = sec.querySelector('.ruler'), hl = sec.querySelector('.ruler__hl');
    if (!ruler || !hl) return;
    sec.querySelectorAll('.mod[data-days]').forEach(function (m) {
      var n = (m.getAttribute('data-days').match(/\d+/g) || []).map(Number);
      if (!n.length) return;
      var a = n[0], b = n[n.length - 1];
      m.addEventListener('mouseenter', function () {
        hl.style.left = ((a - 1) / 60 * 100) + '%';
        hl.style.width = ((b - a + 1) / 60 * 100) + '%';
        ruler.classList.add('is-hl');
      });
      m.addEventListener('mouseleave', function () { ruler.classList.remove('is-hl'); });
    });
  });

  // Motion runs only when the head script promised it (no reduced-motion preference) and GSAP loaded.
  var gsap = window.gsap, ST = window.ScrollTrigger;
  if (!root.classList.contains('motion') || !gsap || !ST) { root.classList.remove('motion'); return; }
  window.__motion = true;
  gsap.registerPlugin(ST);
  var out = 'expo.out';
  var once = function (el, start) { return { trigger: el, start: start || 'top 85%', once: true }; };

  // The authored moment: the hero sketch draws itself, then the doors settle in.
  d.querySelectorAll('.hero').forEach(function (hero) {
    var tl = gsap.timeline({ delay: 0.1 });
    var lines = hero.querySelectorAll('.draw .ln');
    if (lines.length) tl.to(lines, { strokeDashoffset: 0, duration: 0.8, ease: 'power2.inOut', stagger: 0.09 });
    var doors = hero.querySelectorAll('.doors > li');
    if (doors.length) tl.to(doors, { opacity: 1, y: 0, duration: 0.7, ease: out, stagger: 0.08 }, 0.2);
  });

  // Each heading's margin tick is written as the section arrives.
  d.querySelectorAll('.sec__head .tick path').forEach(function (p) {
    gsap.fromTo(p, { strokeDasharray: 1, strokeDashoffset: 1 },
      { strokeDashoffset: 0, duration: 0.5, ease: 'power2.out', scrollTrigger: once(p.closest('.sec__head'), 'top 75%') });
  });

  d.querySelectorAll('.sec:not(.hero) [data-reveal="list"]').forEach(function (l) {
    gsap.from(l.children, { y: 16, opacity: 0, duration: 0.7, ease: out, stagger: 0.06, scrollTrigger: once(l) });
  });
  d.querySelectorAll('[data-reveal="fade"]').forEach(function (el) {
    gsap.from(el, { y: 12, opacity: 0, duration: 0.8, ease: out, scrollTrigger: once(el) });
  });

  // Outcomes: each dimension line is measured out, then its number counts up.
  d.querySelectorAll('.dim').forEach(function (dim, i) {
    var line = dim.querySelector('.dim__line'), num = dim.querySelector('[data-count]');
    var tl = gsap.timeline({ scrollTrigger: once(dim, 'top 88%'), delay: (i % 5) * 0.08 });
    if (line) tl.fromTo(line, { '--k': 0 }, { '--k': 1, duration: 0.8, ease: out });
    if (num) {
      var text = num.textContent, nums = text.match(/\d+/g) || [], o = { t: 0 };
      var paint = function () {
        var k = 0;
        num.textContent = text.replace(/\d+/g, function () { return String(Math.round(Number(nums[k++]) * o.t)); });
      };
      paint();
      tl.to(o, { t: 1, duration: 1.1, ease: 'power3.out', onUpdate: paint }, 0.1);
    }
  });

  // Process steps: the rail fills with scroll and each step lights when the line reaches it.
  d.querySelectorAll('.steps__track').forEach(function (t) {
    var fill = t.querySelector('.steps__fill');
    if (fill) gsap.fromTo(fill, { scaleY: 0 }, { scaleY: 1, ease: 'none', scrollTrigger: { trigger: t, start: 'top 65%', end: 'bottom 65%', scrub: 0.4 } });
    t.querySelectorAll('.step').forEach(function (st) {
      ST.create({ trigger: st, start: 'top 65%', onEnter: function () { st.classList.add('is-on'); }, onLeaveBack: function () { st.classList.remove('is-on'); } });
    });
  });

  // Curriculum: the day line runs to day 30, pauses, and keeps going to day 60.
  d.querySelectorAll('.ruler').forEach(function (r) {
    var segs = r.querySelectorAll('.ruler__seg');
    if (segs.length < 2) return;
    gsap.timeline({ scrollTrigger: once(r, 'top 80%') })
      .from(segs[0], { scaleX: 0, duration: 1, ease: 'power2.inOut' })
      .from(segs[1], { scaleX: 0, duration: 1, ease: 'power2.inOut' }, '+=0.3');
  });

  // Case study: the system is drawn node by node.
  d.querySelectorAll('.flow').forEach(function (fl) {
    var tl = gsap.timeline({ scrollTrigger: once(fl, 'top 75%') });
    fl.querySelectorAll('.flow__node').forEach(function (n) {
      tl.from(n, { opacity: 0, y: 14, duration: 0.45, ease: out });
      var link = n.querySelector('.flow__link');
      if (link && getComputedStyle(link).display !== 'none') tl.from(link, { scaleY: 0, duration: 0.3, ease: 'power1.inOut' }, '-=0.1');
    });
  });

  // Before and after: the arrow is drawn between them.
  d.querySelectorAll('.case__arrow .ln').forEach(function (p) {
    gsap.fromTo(p, { strokeDasharray: 1, strokeDashoffset: 1 }, { strokeDashoffset: 0, duration: 0.7, ease: 'power2.inOut', scrollTrigger: once(p.closest('.case')) });
  });
})();
