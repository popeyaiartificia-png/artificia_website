/* Solutions page: friction pulled into flow in the hero, the solution deck and the outcome lines that straighten. Everything still works, and reads, without it. */
(function () {
  'use strict';
  var d = document, root = d.documentElement;
  var still = matchMedia('(prefers-reduced-motion: reduce)').matches;
  var clamp = function (v, a, b) { return v < a ? a : v > b ? b : v; };
  var smooth = function (e0, e1, x) { var t = clamp((x - e0) / (e1 - e0), 0, 1); return t * t * (3 - 2 * t); };
  var pad = function (n) { return (n < 10 ? '0' : '') + n; };
  // Once the loader has gone, so the first moments are seen.
  var whenIn = function (fn) {
    if (root.classList.contains('is-in')) return fn();
    var mo = new MutationObserver(function () { if (root.classList.contains('is-in')) { mo.disconnect(); fn(); } });
    mo.observe(root, { attributes: true, attributeFilter: ['class'] });
  };

  // The hero: each friction is a bundle of strands coiled around its label. They pass through the core, unwind and leave
  // as one clean lane to the solution that fixes it. The pointer combs the coils straight; a spotlight tours the pairs.
  var flow = function (box) {
    var stage = box.querySelector('.sol-flow__stage'), canvas = stage && stage.querySelector('canvas');
    var ctx = canvas && canvas.getContext('2d'), core = stage && stage.querySelector('.sol-flow__core');
    var froms = [].slice.call(box.querySelectorAll('[data-from]')), lanes = [].slice.call(box.querySelectorAll('[data-lane]'));
    var N = lanes.length;
    if (!ctx || !core || !N) return;
    var wideQ = matchMedia('(min-width: 900px)');
    var seed = 7, rnd = function () { seed = seed * 16807 % 2147483647; return seed / 2147483647; };
    var W = 0, H = 0, gx = 0, gy = 0, x0 = -40, bundles = [], grad, gradOn;
    var t = 0, last = 0, raf = 0, seen = true, started = still, reveal = still ? 1 : 0;
    var hover = -1, tour = -1, tourAt = 2.2, marked = [];
    var ptr = { x: -1e4, y: -1e4, tx: -1e4, ty: -1e4, on: 0, ton: 0 };
    var pt = [0, 0];

    // A soft neon dot, drawn once and stamped for every pulse.
    var sprite = d.createElement('canvas'), sg = sprite.getContext('2d');
    sprite.width = sprite.height = 48;
    var rg = sg.createRadialGradient(24, 24, 0, 24, 24, 24);
    rg.addColorStop(0, 'rgba(246,255,200,1)'); rg.addColorStop(.22, 'rgba(210,255,0,.85)'); rg.addColorStop(1, 'rgba(210,255,0,0)');
    sg.fillStyle = rg; sg.fillRect(0, 0, 48, 48);

    var layout = function () {
      var r = stage.getBoundingClientRect(), dpr = Math.min(window.devicePixelRatio || 1, 2);
      W = r.width; H = r.height;
      if (!W || !H) return;
      canvas.width = Math.round(W * dpr); canvas.height = Math.round(H * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      var c = core.getBoundingClientRect(), wide = wideQ.matches, K = wide ? 6 : 4;
      gx = c.left + c.width / 2 - r.left; gy = c.top + c.height / 2 - r.top;
      seed = 7;
      bundles = lanes.map(function (a, i) {
        var b = a.getBoundingClientRect(), from = froms[i], ax, ay, ly, ex, strands = [], pulses = [], j;
        if (wide) { ly = b.top + b.height / 2 - r.top; ex = b.left - r.left - 8; }
        else { ly = H * (i + .5) / N; ex = W + 40; }
        if (from && from.offsetWidth) { var q = from.getBoundingClientRect(); ax = q.left + q.width / 2 - r.left; ay = q.top + q.height / 2 - r.top; }
        else { ax = gx * (.2 + .5 * ((i * .37) % 1)); ay = H * (.14 + .72 * ((i * .61) % 1)); }
        for (j = 0; j < K; j++) {
          strands.push({ sy: clamp(ay + (rnd() - .5) * H * .75, 0, H), ph: rnd() * 6.283, loops: 5 + rnd() * 4,
            amp: (wide ? 11 : 7) + rnd() * (wide ? 19 : 9), spin: (.45 + rnd() * .6) * (rnd() < .5 ? -1 : 1), off: j - (K - 1) / 2 });
        }
        for (j = 0; j < 3; j++) pulses.push({ s: rnd(), j: (j * 2) % K });
        return { ly: ly, ex: Math.max(ex, gx + 60), ax: ax, ay: ay, strands: strands, pulses: pulses, glow: 0 };
      });
      var g = clamp(gx / W, .08, .92);
      grad = ctx.createLinearGradient(0, 0, W, 0);
      grad.addColorStop(0, 'rgba(150,158,128,0)'); grad.addColorStop(.05, 'rgba(150,158,128,.3)');
      grad.addColorStop(g - .07, 'rgba(165,174,140,.42)'); grad.addColorStop(g + .05, 'rgba(210,255,0,.42)'); grad.addColorStop(1, 'rgba(210,255,0,.62)');
      gradOn = ctx.createLinearGradient(0, 0, W, 0);
      gradOn.addColorStop(0, 'rgba(232,238,214,0)'); gradOn.addColorStop(.05, 'rgba(232,238,214,.75)');
      gradOn.addColorStop(g - .07, 'rgba(238,244,220,.9)'); gradOn.addColorStop(g + .04, 'rgba(210,255,0,1)'); gradOn.addColorStop(1, 'rgba(222,255,80,1)');
      draw();
    };

    // Where strand `st` of bundle B is at u (0 = off the left edge, 1 = the solution's lane).
    var point = function (B, st, u) {
      var gu = (gx - x0) / (B.ex - x0), x, y;
      if (u <= gu) {
        var v = u / gu, m = 1 - v;
        // From the strand's start, through its friction label, to the core.
        var p2y = gy + st.off * 1.6, cx = 2 * B.ax - (x0 + gx) / 2, cy = 2 * B.ay - (st.sy + p2y) / 2;
        x = m * m * x0 + 2 * m * v * cx + v * v * gx;
        y = m * m * st.sy + 2 * m * v * cy + v * v * p2y;
        // The friction: a coil, widest around the label, unwound to nothing at the core.
        var env = Math.pow(Math.sin(Math.PI * Math.min(1, v * 1.12)), .7) * (1 - smooth(.6, .97, v)) * st.amp;
        if (ptr.on > .01) {
          var dx = x - ptr.x, dy = y - ptr.y;
          env *= 1 - .94 * ptr.on * Math.exp(-(dx * dx + dy * dy) / 15000);
        }
        var a = st.loops * 6.283 * v + st.ph + t * st.spin;
        x += env * Math.cos(a) * 1.15; y += env * Math.sin(a);
      } else {
        var w = (u - gu) / (1 - gu), from = gy + st.off * 1.6, to = B.ly + st.off * .8;
        x = gx + w * (B.ex - gx);
        y = from + (to - from) * smooth(0, .68, w);
      }
      pt[0] = x; pt[1] = y;
    };

    var trace = function (B, st) {
      var gu = (gx - x0) / (B.ex - x0), k, u;
      for (k = 0; k <= 150; k++) {
        u = gu * k / 150;
        if (u > reveal) return;
        point(B, st, u);
        if (k) ctx.lineTo(pt[0], pt[1]); else ctx.moveTo(pt[0], pt[1]);
      }
      for (k = 1; k <= 28; k++) {
        u = gu + (1 - gu) * k / 28;
        if (u > reveal) return;
        point(B, st, u); ctx.lineTo(pt[0], pt[1]);
      }
    };

    var draw = function () {
      if (!W) return;
      ctx.clearRect(0, 0, W, H);
      ctx.lineCap = 'round'; ctx.lineJoin = 'round';
      var lit = 0;
      bundles.forEach(function (B) { lit = Math.max(lit, B.glow); });
      bundles.forEach(function (B) {
        var on = B.glow;
        ctx.globalAlpha = (1 - on) * (1 - lit * .55);
        if (ctx.globalAlpha > .01) {
          ctx.strokeStyle = grad; ctx.lineWidth = 1;
          ctx.beginPath(); B.strands.forEach(function (st) { trace(B, st); }); ctx.stroke();
        }
        if (on > .01) {
          ctx.globalAlpha = on; ctx.strokeStyle = gradOn; ctx.lineWidth = 1.5;
          ctx.beginPath(); B.strands.forEach(function (st) { trace(B, st); }); ctx.stroke();
        }
      });
      if (reveal >= 1 && !still) {
        ctx.globalCompositeOperation = 'lighter';
        bundles.forEach(function (B) {
          var gu = (gx - x0) / (B.ex - x0);
          B.pulses.forEach(function (p) {
            point(B, B.strands[p.j], p.s);
            var past = p.s > gu, size = (past ? 15 : 9) * (1 + B.glow * .45);
            ctx.globalAlpha = (past ? .9 : .28) * (.55 + B.glow * .45);
            ctx.drawImage(sprite, pt[0] - size / 2, pt[1] - size / 2, size, size);
          });
        });
        ctx.globalCompositeOperation = 'source-over';
      }
      ctx.globalAlpha = 1;
    };

    var mark = function (i, on) {
      if (marked[i] === on) return;
      marked[i] = on;
      if (froms[i]) froms[i].classList.toggle('is-on', on);
      lanes[i].parentNode.classList.toggle('is-on', on);
    };
    var hit = function (i) {
      if (!wideQ.matches) return; // on phones the lanes run off the edge, away from the list
      var li = lanes[i].parentNode;
      li.classList.add('is-hit');
      setTimeout(function () { li.classList.remove('is-hit'); }, 360);
    };
    var focusIndex = function () { return hover >= 0 ? hover : tour; };

    var tick = function (now) {
      raf = 0;
      if (!seen || d.hidden || !started) { last = 0; return; }
      var dt = last ? Math.min(.05, (now - last) / 1000) : .016;
      last = now; t += dt;
      if (reveal < 1) reveal = Math.min(1, reveal + dt / 1.8);
      ptr.x += (ptr.tx - ptr.x) * .2; ptr.y += (ptr.ty - ptr.y) * .2; ptr.on += (ptr.ton - ptr.on) * .07;
      if (hover < 0 && reveal >= 1) {
        tourAt += dt;
        if (tourAt > 3.2) { tourAt = 0; tour = (tour + 1) % N; }
      }
      var f = focusIndex();
      bundles.forEach(function (B, i) {
        B.glow += ((i === f ? 1 : 0) - B.glow) * Math.min(1, dt * 4.5);
        mark(i, B.glow > .5);
        if (reveal < 1) return;
        var gu = (gx - x0) / (B.ex - x0);
        B.pulses.forEach(function (p) {
          p.s += dt * (p.s < gu ? .05 : .3) * (1 + B.glow * .7);
          if (p.s >= 1) { p.s -= 1; hit(i); }
        });
      });
      draw();
      schedule();
    };
    var schedule = function () { if (!raf && !still) raf = requestAnimationFrame(tick); };
    // Without motion the picture holds still; pointing at a pair still lights it.
    var settle = function () {
      if (!still) return;
      var f = focusIndex();
      bundles.forEach(function (B, i) { B.glow = i === f ? 1 : 0; mark(i, i === f); });
      draw();
    };
    var setHover = function (i) { hover = i; if (i < 0) tourAt = 0; settle(); };

    lanes.forEach(function (a, i) {
      a.addEventListener('pointerenter', function () { setHover(i); });
      a.addEventListener('pointerleave', function () { setHover(-1); });
      a.addEventListener('focus', function () { setHover(i); });
      a.addEventListener('blur', function () { setHover(-1); });
    });
    froms.forEach(function (li, i) {
      li.addEventListener('pointerenter', function () { setHover(i); });
      li.addEventListener('pointerleave', function () { setHover(-1); });
    });
    box.addEventListener('pointermove', function (e) {
      var r = stage.getBoundingClientRect();
      ptr.tx = e.clientX - r.left; ptr.ty = e.clientY - r.top;
      if (ptr.on < .01) { ptr.x = ptr.tx; ptr.y = ptr.ty; }
      ptr.ton = e.pointerType === 'touch' ? 0 : 1;
    }, { passive: true });
    box.addEventListener('pointerleave', function () { ptr.ton = 0; });

    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (es) { seen = es[0].isIntersecting; schedule(); }).observe(box);
    }
    d.addEventListener('visibilitychange', schedule);
    if ('ResizeObserver' in window) new ResizeObserver(layout).observe(stage); else addEventListener('resize', layout);
    if (d.fonts && d.fonts.ready) d.fonts.ready.then(layout);
    layout();
    if (!still) whenIn(function () { started = true; schedule(); });
  };

  // The deck and the outcome lines read the scroll position, once per frame. (Section joins are site.js's fluid floods.)
  var scroll = function () {
    var cards = [].slice.call(d.querySelectorAll('[data-sol-card]'));
    var names = [].slice.call(d.querySelectorAll('[data-sol-name]'));
    var num = d.querySelector('[data-sol-num]'), meter = d.querySelector('[data-sol-meter]');
    var rows = [].slice.call(d.querySelectorAll('[data-sol-row]'));
    var deckQ = matchMedia('(min-width: 1100px) and (min-height: 700px)');
    var tops = [], current = -1, queued = false;
    var measure = function () {
      tops = cards.map(function (li) { return parseFloat(getComputedStyle(li).top) || 0; });
    };
    var update = function () {
      queued = false;
      var vh = innerHeight, deck = deckQ.matches, active = 0;
      cards.forEach(function (li, i) {
        var r = li.getBoundingClientRect(), card = li.firstElementChild;
        if (r.top < vh * .5) active = i;
        if (deck && !still && i < cards.length - 1) {
          var next = cards[i + 1].getBoundingClientRect().top;
          var q = clamp(1 - (next - tops[i + 1]) / Math.max(1, card.offsetHeight), 0, 1);
          card.style.setProperty('--sc', (1 - q * .06).toFixed(4));
          card.style.setProperty('--br', (1 - q * .55).toFixed(3));
        } else if (card.style.length) {
          card.style.removeProperty('--sc'); card.style.removeProperty('--br');
        }
      });
      if (cards.length && active !== current) {
        current = active;
        names.forEach(function (n, i) { n.classList.toggle('is-on', i === active); });
        cards.forEach(function (c, i) { c.classList.toggle('is-on', i === active); });
        if (num) {
          num.textContent = pad(active + 1);
          if (!still) { num.classList.remove('is-flip'); void num.offsetWidth; num.classList.add('is-flip'); }
        }
      }
      if (meter && cards.length) {
        var list = cards[0].parentNode.getBoundingClientRect();
        meter.style.setProperty('--p', clamp((vh * .5 - list.top) / Math.max(1, list.height - vh * .3), 0, 1).toFixed(3));
      }
      rows.forEach(function (row) {
        var p = still ? 1 : clamp((vh * .9 - row.getBoundingClientRect().top) / (vh * .4), 0, 1);
        row.style.setProperty('--p', p.toFixed(3));
      });
    };
    var queue = function () { if (!queued) { queued = true; requestAnimationFrame(update); } };
    addEventListener('scroll', queue, { passive: true });
    addEventListener('resize', function () { measure(); queue(); });
    measure(); update();

    // Each card plays its list once, the first time it is properly in view.
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (es) {
        es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-seen'); io.unobserve(e.target); } });
      }, { threshold: .35 });
      cards.forEach(function (c) { io.observe(c); });
    } else cards.forEach(function (c) { c.classList.add('is-seen'); });

    // The browser frame straightens once the system is in view.
    var shot = d.querySelector('.solutions-page .cs__shot');
    if (shot && 'IntersectionObserver' in window && !still) {
      var so = new IntersectionObserver(function (es) {
        if (es[0].isIntersecting) { setTimeout(function () { shot.classList.add('is-flat'); }, 500); so.disconnect(); }
      }, { threshold: .6 });
      so.observe(shot);
    }
  };

  var start = function () {
    d.querySelectorAll('[data-sol-flow]').forEach(flow);
    scroll();
  };
  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', start); else start();
})();
