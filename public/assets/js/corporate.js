/* Corporate training page: the department tabs, programme panels and trained count.
   Everything still works, and reads, without it. */
(function () {
  'use strict';
  var d = document, root = d.documentElement;
  var still = matchMedia('(prefers-reduced-motion: reduce)').matches;
  // The hero, joins and FAQs share one contour map. Create canvases before site.js draws that page-anchored field.
  d.querySelectorAll('.corporate-page > .faq, .corporate-page > .journey-hero, .corporate-page > .co-kit, .corporate-page > .co-dept').forEach(function (s) { var c = d.createElement('canvas'); c.className = 'topo-bg'; c.setAttribute('aria-hidden', 'true'); s.prepend(c); });
  // What you keep: a clicked card turns neon; clicking it again switches it off. Each card on its own.
  d.addEventListener('click', function (e) {
    var card = e.target.closest && e.target.closest('.co-doc');
    if (card) card.classList.toggle('is-lit');
  });
  // Once the loader has gone, so the first moments are seen.
  var whenIn = function (fn) {
    if (root.classList.contains('is-in')) return fn();
    var mo = new MutationObserver(function () { if (root.classList.contains('is-in')) { mo.disconnect(); fn(); } });
    mo.observe(root, { attributes: true, attributeFilter: ['class'] });
  };

  // The hero hub: one clock, 11 s a loop. Before (work piles up, teams jitter) → train (beams draw, every team's
  // papers fly into the AI core, its ring fills) → after (packets fly back out; each team flips as its first one lands)
  // → reset. Every frame is computed from the clock alone, so pausing, scrolling away or a slow tab can never desync it.
  var hubDriver = function (hub) {
    var C = 11, TRAIN = 2.4, AFTER = 4.7, RESET = 10.3;
    var q = function (sel) { return [].slice.call(hub.querySelectorAll(sel)); };
    var pods = q('[data-co-pod]'), beams = q('[data-co-beam]'), papers = q('[data-co-paper]'), packets = q('[data-co-packet]'), waves = q('[data-co-wave]');
    var progress = hub.querySelector('[data-co-progress]'), bar = hub.querySelector('[data-co-bar]');
    var stepN = hub.querySelector('[data-co-step-n]'), step = hub.querySelector('[data-co-step]'), count = hub.querySelector('[data-co-count]'), latest = hub.querySelector('[data-co-latest]');
    if (pods.length !== beams.length || !progress) return null;
    var lens = beams.map(function (b) { return b.getTotalLength(); });
    var at = function (k, f) { return beams[k].getPointAtLength(Math.max(0, Math.min(1, f)) * lens[k]); };
    var clamp = function (v) { return v < 0 ? 0 : v > 1 ? 1 : v; };
    var ease = function (v) { return v * v * (3 - 2 * v); };
    var side = pods.map(function (p, k) { return at(k, 1).x < 360 ? -1 : 1; });
    var flip = pods.map(function (p, k) { return AFTER + k * .32 + .6; });
    var names = pods.map(function (p) { return p.querySelector('.co-pod__name').textContent; });
    var outs = pods.map(function (p) { return p.querySelector('.co-pod__after').textContent; });
    var texts = {}, set = function (el, key, v) { if (el && texts[key] !== v) { texts[key] = v; el.textContent = v; } };
    var STEPS = { before: ['01', 'Before: every team works by hand'], train: ['02', 'Training on your teams’ own files'], after: ['03', 'After: AI at work in every team'] };
    hub.classList.add('is-live');

    return function (t) {
      var phase = t < TRAIN ? 'before' : t < AFTER ? 'train' : t < RESET ? 'after' : 'reset';
      if (hub.dataset.phase !== phase) hub.dataset.phase = phase;
      var undo = clamp((t - RESET) / .55), done = 0, last = -1;
      beams.forEach(function (b, k) { b.style.strokeDashoffset = String(1 - ease(clamp((t - 2.5 - k * .14) / .5)) * (1 - undo)); });
      progress.style.strokeDashoffset = String(1 - ease(clamp((t - TRAIN) / (AFTER - TRAIN))) * (1 - undo));
      pods.forEach(function (p, k) {
        var on = t >= flip[k] && t < RESET;
        if (on) { done++; if (last < 0 || flip[k] > flip[last]) last = k; }
        p.classList.toggle('is-done', on);
        p.classList.toggle('is-feeding', t >= TRAIN && !on && t < RESET);
      });
      papers.forEach(function (el, i) {
        var k = +el.getAttribute('data-co-paper'), j = i % 3, s = side[k], end = at(k, 1);
        var hx = end.x - s * (14 + j * 7), hy = end.y + (j - 1) * 17;
        var jig = t < TRAIN ? Math.sin(t * 9 + i * 1.7) * 2.2 : 0;
        var f = clamp((t - 2.7 - k * .14 - j * .18) / .9), e = ease(f), x = hx, y = hy + jig, o = 1;
        if (t >= RESET) o = undo; else if (f > 0) { var c = at(k, 1 - e), m = clamp(f * 3); x = hx + (c.x - hx) * m; y = hy + (c.y - hy) * m; o = f > .85 ? (1 - f) / .15 : 1; }
        el.setAttribute('transform', 'translate(' + x.toFixed(1) + ' ' + y.toFixed(1) + ') rotate(' + (s * (8 + j * 9) + e * 260 + jig * 3).toFixed(1) + ') scale(' + (1 - e * .55).toFixed(2) + ')');
        el.style.opacity = String(o);
      });
      packets.forEach(function (el, i) {
        var k = +el.getAttribute('data-co-packet'), off = (i % 2) * .65, run = t - (flip[k] - .6) - off, o = 0;
        if (run >= 0 && t < RESET) {
          var f = (run % 1.3) / .6;
          if (f < 1) { var c = at(k, ease(f)); el.setAttribute('cx', c.x.toFixed(1)); el.setAttribute('cy', c.y.toFixed(1)); o = f < .1 ? f * 10 : 1; }
        }
        el.style.opacity = String(o);
      });
      [TRAIN, flip[flip.length - 1]].forEach(function (T, w) {
        var dt = (t - T) / 1.3, el = waves[w];
        if (!el) return;
        el.setAttribute('r', (60 + ease(clamp(dt)) * 190).toFixed(1));
        el.style.opacity = dt >= 0 && dt < 1 ? String(.85 * (1 - dt)) : '0';
      });
      var st = STEPS[phase === 'reset' ? 'after' : phase];
      set(stepN, 'n', st[0]); set(step, 's', st[1]); set(count, 'c', String(done));
      set(latest, 'l', last >= 0 ? names[last] + ' → ' + outs[last] : phase === 'before' ? 'Six teams. Six piles of manual work.' : 'Each team’s own work goes into the training.');
      if (bar) bar.style.transform = 'scaleX(' + (t / C).toFixed(4) + ')';
    };
  };

  // Run it only while visible; retain manual pause and the static finished state under reduced motion.
  var scene = d.querySelector('[data-co-scene]'), motionButton = d.querySelector('[data-co-motion]');
  if (scene && motionButton && !still) {
    var sceneVisible = false, scenePaused = false, sceneReady = false;
    var hubEl = scene.querySelector('[data-co-hub]'), draw = null, clock = 0, lastTs = 0, raf = 0;
    var tick = function (ts) {
      if (lastTs) clock = (clock + Math.min(.1, (ts - lastTs) / 1000)) % 11;
      lastTs = ts; draw(clock); raf = requestAnimationFrame(tick);
    };
    var syncScene = function () {
      var run = sceneReady && sceneVisible && !scenePaused && !d.hidden;
      scene.classList.toggle('is-running', run);
      if (!draw) return;
      if (run && !raf) { lastTs = 0; raf = requestAnimationFrame(tick); }
      else if (!run && raf) { cancelAnimationFrame(raf); raf = 0; }
    };
    motionButton.hidden = false;
    motionButton.addEventListener('click', function () {
      scenePaused = !scenePaused;
      motionButton.setAttribute('aria-pressed', String(scenePaused));
      motionButton.textContent = scenePaused ? 'Play motion' : 'Pause motion';
      syncScene();
    });
    d.addEventListener('visibilitychange', syncScene);
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) { sceneVisible = entries[0].isIntersecting; syncScene(); }).observe(scene);
    } else sceneVisible = true;
    whenIn(function () { if (hubEl) { draw = hubDriver(hubEl); if (draw) draw(0); } sceneReady = true; syncScene(); });
  }


  // The hero window: one team per 7.5s. Its file is read, the prompt typed, the answer streamed, the workflow kept.
  var sessionBox = function (box) {
    var teams; try { teams = JSON.parse(box.getAttribute('data-teams')); } catch (_) { return; }
    if (still || !teams || !teams.length) return;
    var q = function (s) { return box.querySelector(s); };
    var rows = [].slice.call(box.querySelectorAll('.co-box__teams li'));
    var el = { team: q('[data-co-team]'), file: q('[data-co-file]'), prompt: q('[data-co-prompt]'), out: q('[data-co-out]'), kept: q('[data-co-kept]'),
      count: q('[data-co-count]'), step: q('[data-co-step]'), bar: q('[data-co-bar]'), ring: q('.co-box__ringv') };
    var T = 7.5, t = 0, last = 0, i = -1, seen = false, raf = 0, done = 0;
    var words = { read: 'Reading the team’s own file', type: 'Writing the prompt together', out: 'AI drafts, the team checks', kept: 'Done: workflow kept' };
    var set = function (n, v) { if (n && n.textContent !== v) n.textContent = v; };
    var frame = function (now) {
      raf = 0;
      if (!seen || d.hidden) { last = 0; return; }
      t += last ? Math.min(.1, (now - last) / 1000) : 0; last = now;
      var k = Math.floor(t / T) % teams.length, p = (t % T) / T, tm = teams[k];
      if (k !== i) {
        i = k; done = Math.min(teams.length, done + (done < teams.length ? 1 : 0));
        if (k === 0) done = 1;
        set(el.team, tm[0]); set(el.file, tm[1]); set(el.kept, tm[4]);
        rows.forEach(function (r, n) { r.classList.toggle('is-on', n === k); r.classList.toggle('is-done', n < k); r.style.removeProperty('--p'); });
      }
      var step = p < .14 ? 'read' : p < .5 ? 'type' : p < .78 ? 'out' : 'kept';
      if (box.getAttribute('data-step') !== step) box.setAttribute('data-step', step);
      set(el.prompt, step === 'read' ? '' : tm[2].slice(0, Math.ceil(tm[2].length * Math.min(1, (p - .14) / .3))));
      set(el.out, step === 'out' || step === 'kept' ? tm[3].slice(0, Math.ceil(tm[3].length * Math.min(1, (p - .5) / .2))) : '');
      if (step === 'kept') rows[k].classList.add('is-done');
      var n = k + (step === 'kept' ? 1 : 0);
      set(el.count, String(n));
      el.ring.style.setProperty('--r', (n / teams.length).toFixed(3));
      rows[k].style.setProperty('--p', p.toFixed(3));
      set(el.step, words[step]);
      el.bar.style.transform = 'scaleX(' + p.toFixed(4) + ')';
      raf = requestAnimationFrame(frame);
    };
    var go = function () { if (!raf && seen && !d.hidden) raf = requestAnimationFrame(frame); };
    if ('IntersectionObserver' in window) new IntersectionObserver(function (es) { seen = es[0].isIntersecting; go(); }).observe(box); else seen = true;
    d.addEventListener('visibilitychange', go);
    whenIn(go);
  };


  // Hero card: the pointer (or a finger) drags the training line; leaving hands it back to the sweep.
  var compare = function (box) {
    var card = box.querySelector('.co-ba__card');
    if (!card) return;
    var move = function (e) {
      var r = card.getBoundingClientRect();
      card.classList.add('is-scrub');
      card.style.setProperty('--co-x', Math.max(2, Math.min(98, (e.clientX - r.left) / r.width * 100)).toFixed(1) + '%');
    };
    card.addEventListener('pointermove', function (e) { if (e.pointerType === 'mouse' || e.buttons) move(e); });
    card.addEventListener('pointerdown', move);
    card.addEventListener('pointerleave', function () { card.classList.remove('is-scrub'); card.style.removeProperty('--co-x'); });
  };

  // Departments: the names become tabs. A link to a department anywhere on the page (the hero's teams) opens its panel.
  var depts = function (box) {
    var list = box.querySelector('[role="tablist"]'), btns = list ? [].slice.call(list.querySelectorAll('[role="tab"]')) : [];
    var panels = btns.map(function (b) { return d.getElementById(b.getAttribute('aria-controls')); });
    if (!btns.length || panels.indexOf(null) >= 0) return;
    list.hidden = false;
    panels.forEach(function (p, i) { p.setAttribute('role', 'tabpanel'); p.setAttribute('aria-labelledby', btns[i].id); p.tabIndex = 0; });
    var current = -1;
    var show = function (i, focus) {
      if (i !== current) {
        current = i;
        btns.forEach(function (b, k) {
          var on = k === i;
          b.setAttribute('aria-selected', on ? 'true' : 'false');
          b.tabIndex = on ? 0 : -1;
          panels[k].hidden = !on;
          panels[k].classList.toggle('is-in', on);
        });
      }
      if (focus) btns[i].focus();
    };
    btns.forEach(function (b, i) { b.addEventListener('click', function () { show(i); }); });
    list.addEventListener('keydown', function (e) {
      var n = btns.length, step = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[e.key], i;
      if (step) i = (current + step + n) % n; else if (e.key === 'Home') i = 0; else if (e.key === 'End') i = n - 1; else return;
      e.preventDefault(); show(i, true);
    });
    var open = function (hash) {
      for (var i = 0; i < panels.length; i++) if ('#' + panels[i].id === hash) { show(i); (box.closest('section') || box).scrollIntoView({ behavior: still ? 'auto' : 'smooth', block: 'start' }); return true; }
      return false;
    };
    d.addEventListener('click', function (e) {
      var a = e.target.closest && e.target.closest('a[href^="#dept-"]');
      if (a && open(a.getAttribute('href'))) { e.preventDefault(); history.replaceState(null, '', a.getAttribute('href')); }
    });
    addEventListener('hashchange', function () { open(location.hash); });
    if (!open(location.hash)) show(0);
  };

  // Programmes: the last panel pointed at, focused or tapped stays open.
  var progs = function (list) {
    var items = [].slice.call(list.children);
    var open = function (li) { items.forEach(function (x) { x.classList.toggle('is-on', x === li); }); };
    items.forEach(function (li) {
      li.addEventListener('pointerenter', function (e) { if (e.pointerType === 'mouse') open(li); });
      li.addEventListener('focusin', function () { open(li); });
      li.addEventListener('click', function () { open(li); });
    });
    list.classList.add('is-live');
  };

  // The trained count: its hundred people light up once, in order, when it comes into view.
  var dots = function (field) {
    if (still || !('IntersectionObserver' in window)) return;
    field.classList.add('is-armed');
    var io = new IntersectionObserver(function (es) {
      if (es[0].isIntersecting) { field.classList.add('is-seen'); io.disconnect(); }
    }, { threshold: .4 });
    io.observe(field);
  };

  var start = function () {
    d.querySelectorAll('[data-co-box]').forEach(sessionBox);
    d.querySelectorAll('[data-co-ba]').forEach(compare);
    d.querySelectorAll('[data-co-dept]').forEach(depts);
    d.querySelectorAll('[data-co-progs]').forEach(progs);
    d.querySelectorAll('[data-co-dots]').forEach(dots);
  };
  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', start); else start();
})();
