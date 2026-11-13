/* Course selection, a quick transformation and a living light trail. */
(function () {
  'use strict';
  document.querySelectorAll('[data-course-track]').forEach(function (link) {
    link.addEventListener('click', function () {
      var select = document.querySelector('.course-page select[name="track"]');
      if (select) {
        select.value = link.dataset.courseTrack;
        select.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  });
  var hero = document.querySelector('[data-masterclass]');
  if (!hero) return;
  var root = document.documentElement;
  var trace = hero.querySelector('.learning-trace');
  var fill = hero.querySelector('.learning-fill');
  var marker = hero.querySelector('.learning-marker');
  var energy = Array.from(hero.querySelectorAll('.learning-energy-halo, .learning-energy-core'));
  var sheen = hero.querySelector('.learning-energy-sheen');
  var sparks = Array.from(hero.querySelectorAll('.learning-spark'));
  var controls = hero.querySelector('[data-journey-controls]');
  var replay = hero.querySelector('[data-journey-replay]');
  var pause = hero.querySelector('[data-journey-pause]');
  var pauseLabel = hero.querySelector('[data-journey-pause-label]');
  if (!trace || !fill || !marker || !replay || !pause || !controls) return;
  var length = trace.getTotalLength();
  var motion = matchMedia('(prefers-reduced-motion: reduce)');
  var elapsed = 0, last = 0, frame = 0, visible = false, finished = false, paused = false;
  var pulseTime = 0, lastPaint = 0, duration = 1800, pulseDuration = 3200;
  function paintEnergy(progress) {
    energy.forEach(function (path) { path.style.strokeDashoffset = String(1 - progress + .08); });
    sparks.forEach(function (spark, i) {
      var position = progress - i * .028;
      spark.style.opacity = position >= 0 && position <= .985 ? String(1 - i * .25) : '0';
      var point = trace.getPointAtLength(length * Math.max(0, Math.min(1, position)));
      if (i === 1 && sheen) { sheen.setAttribute('cx', String(point.x)); sheen.setAttribute('cy', String(point.y)); }
      spark.setAttribute('cx', String(point.x));
      spark.setAttribute('cy', String(point.y - 2 - i * 1.5));
    });
  }
  function paint(progress) {
    fill.style.strokeDashoffset = String(1 - progress);
    trace.style.strokeDashoffset = String(1 - progress);
    var point = trace.getPointAtLength(length * progress);
    marker.setAttribute('cx', String(point.x));
    marker.setAttribute('cy', String(point.y));
    hero.dataset.journeyPhase = progress === 1 ? 'after' : (progress === 0 ? 'before' : 'travel');
  }
  function ready() { return root.classList.contains('is-in') || !root.classList.contains('motion'); }
  function canRun() { return visible && !document.hidden && ready() && !motion.matches && !paused; }
  function tick(now) {
    frame = 0;
    if (!canRun()) return;
    var delta = now - last;
    last = now;
    if (!finished) {
      elapsed += delta;
      var t = Math.min(1, elapsed / duration);
      var progress = t * t * (3 - 2 * t);
      paint(progress);
      paintEnergy(progress);
      if (t === 1) { finished = true; replay.disabled = false; pulseTime = 0; }
    } else {
      pulseTime += delta;
      // Limit decorative updates to 30fps once the quick transformation is complete.
      if (now - lastPaint >= 1000 / 30) {
        paintEnergy((pulseTime % pulseDuration) / pulseDuration);
        lastPaint = now;
      }
    }
    frame = requestAnimationFrame(tick);
  }
  function sync() {
    controls.hidden = motion.matches;
    if (motion.matches) {
      finished = true;
      paint(1);
      replay.disabled = false;
    }
    var running = canRun();
    hero.classList.toggle('is-journey-paused', !running);
    pause.setAttribute('aria-pressed', String(paused));
    pause.setAttribute('aria-label', paused ? 'Resume path animation' : 'Pause path animation');
    pauseLabel.textContent = paused ? 'Play' : 'Pause';
    if (running && !frame) { last = performance.now(); frame = requestAnimationFrame(tick); }
    else if (!running && frame) { cancelAnimationFrame(frame); frame = 0; }
  }
  replay.addEventListener('click', function () {
    elapsed = 0;
    pulseTime = 0;
    finished = false;
    paused = false;
    replay.disabled = true;
    paint(0);
    paintEnergy(0);
    sync();
  });
  pause.addEventListener('click', function () { paused = !paused; sync(); });
  document.addEventListener('visibilitychange', sync);
  motion.addEventListener('change', sync);
  new MutationObserver(sync).observe(root, { attributes: true, attributeFilter: ['class'] });
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      visible = entries[0].isIntersecting;
      sync();
    }).observe(hero);
  } else visible = true;
  hero.classList.add('has-learning-motion');
  paint(motion.matches ? 1 : 0);
  paintEnergy(0);
  replay.disabled = !motion.matches;
  sync();
}());

/* Two tool rows: seamless native scrolling, without oversized composited tracks. */
(function () {
  'use strict';
  var section = document.querySelector('.tools--course-toolkit');
  if (!section) return;
  var motion = matchMedia('(prefers-reduced-motion: reduce)');
  var rows = Array.from(section.querySelectorAll('[data-toolkit-row]')).map(function (viewport, index) {
    var group = viewport.querySelector('[data-toolkit-group]');
    var copy = group.cloneNode(true);
    copy.removeAttribute('data-toolkit-group');
    copy.setAttribute('data-toolkit-copy', '');
    copy.setAttribute('aria-hidden', 'true');
    copy.inert = true;
    // A repeated SVG may contain a local gradient or clip ID. Keep every copy unique.
    var ids = {};
    copy.querySelectorAll('[id]').forEach(function (node) {
      var previous = node.id;
      ids[previous] = 'toolkit-copy-' + index + '-' + previous;
      node.id = ids[previous];
    });
    copy.querySelectorAll('*').forEach(function (node) {
      Array.from(node.attributes).forEach(function (attribute) {
        if (attribute.name === 'id') return;
        var value = attribute.value;
        Object.keys(ids).forEach(function (id) {
          value = value.split('url(#' + id + ')').join('url(#' + ids[id] + ')');
          if (value === '#' + id) value = '#' + ids[id];
        });
        if (value !== attribute.value) node.setAttribute(attribute.name, value);
      });
    });
    group.parentElement.appendChild(copy);
    return { viewport: viewport, group: group, direction: Number(viewport.dataset.direction), width: 0, position: 0, hovered: false, interacting: false, keyboardFocus: false };
  });
  var visible = false, frame = 0, last = 0, speed = 58, browseUntil = 0, browseTimer = 0;
  function held() {
    return performance.now() < browseUntil || rows.some(function (row) {
      return row.hovered || row.interacting || row.keyboardFocus || row.viewport.matches(':focus-visible');
    });
  }
  function canRun() { return visible && !document.hidden && !motion.matches && !held(); }
  function tick(now) {
    frame = 0;
    if (!canRun()) { sync(); return; }
    if (now - last >= 1000 / 30) {
      var delta = Math.min(now - last, 80) / 1000;
      last = now;
      rows.forEach(function (row) {
        if (!row.width) return;
        row.position = ((row.position + row.direction * speed * delta) % row.width + row.width) % row.width;
        row.viewport.scrollLeft = row.position;
      });
    }
    frame = requestAnimationFrame(tick);
  }
  function sync() {
    section.classList.toggle('has-toolkit-motion', !motion.matches);
    var running = canRun();
    section.classList.toggle('is-toolkit-paused', !running);
    if (running && !frame) {
      rows.forEach(function (row) { if (row.width) row.position = row.viewport.scrollLeft % row.width; });
      last = performance.now();
      frame = requestAnimationFrame(tick);
    } else if (!running && frame) { cancelAnimationFrame(frame); frame = 0; }
  }
  function measure() {
    speed = parseFloat(getComputedStyle(section).getPropertyValue('--toolkit-speed')) || 58;
    rows.forEach(function (row) {
      var width = row.group.getBoundingClientRect().width;
      if (!width || width === row.width) return;
      var progress = row.width ? row.viewport.scrollLeft / row.width : (row.direction < 0 && !motion.matches ? 1 : 0);
      row.width = width;
      row.position = progress * width;
      row.viewport.scrollLeft = row.position;
    });
    sync();
  }
  rows.forEach(function (row) {
    row.viewport.addEventListener('pointerenter', function (event) { if (event.pointerType === 'mouse') { row.hovered = true; sync(); } });
    row.viewport.addEventListener('pointerleave', function (event) { if (event.pointerType === 'mouse') { row.hovered = false; sync(); } });
    row.viewport.addEventListener('pointerdown', function () { row.interacting = true; row.keyboardFocus = false; sync(); });
    row.viewport.addEventListener('wheel', function (event) {
      if (!event.deltaX && !event.shiftKey) return;
      browseUntil = performance.now() + 1200;
      clearTimeout(browseTimer);
      browseTimer = setTimeout(sync, 1250);
      sync();
    }, { passive: true });
    row.viewport.addEventListener('focus', sync);
    row.viewport.addEventListener('blur', function () { row.keyboardFocus = false; setTimeout(sync, 0); });
    row.viewport.addEventListener('keydown', function (event) {
      if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
      event.preventDefault();
      row.keyboardFocus = true;
      sync();
      var distance = Math.max(150, row.viewport.clientWidth * .35);
      if (event.key === 'Home') row.viewport.scrollLeft = 0;
      else if (event.key === 'End') {
        var end = row.group.lastElementChild.getBoundingClientRect().right;
        var edge = row.viewport.getBoundingClientRect().right;
        var padding = parseFloat(getComputedStyle(row.viewport).paddingRight) || 0;
        row.viewport.scrollLeft = Math.max(0, row.viewport.scrollLeft + end - edge + padding);
      }
      else row.viewport.scrollLeft += event.key === 'ArrowRight' ? distance : -distance;
    });
  });
  function releaseGesture() { rows.forEach(function (row) { row.interacting = false; }); sync(); }
  document.addEventListener('pointerup', releaseGesture);
  document.addEventListener('pointercancel', releaseGesture);
  window.addEventListener('blur', function () {
    rows.forEach(function (row) { row.interacting = false; row.hovered = false; });
    sync();
  });
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) rows.forEach(function (row) { row.interacting = false; });
    sync();
  });
  motion.addEventListener('change', function () { sync(); measure(); });
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) { visible = entries[0].isIntersecting; sync(); }).observe(section);
  } else visible = true;
  if ('ResizeObserver' in window) {
    var resize = new ResizeObserver(measure);
    rows.forEach(function (row) { resize.observe(row.group); });
  }
  window.addEventListener('resize', measure);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(measure);
  section.classList.toggle('has-toolkit-motion', !motion.matches);
  measure();
}());

/* Native project carousel; previews build once on arrival, then stay complete. */
(function () {
  'use strict';
  var section = document.querySelector('[data-course-projects]');
  if (!section) return;
  var cards = Array.from(section.querySelectorAll('[data-course-project]'));
  var motion = matchMedia('(prefers-reduced-motion: reduce)');
  var track = section.querySelector('[data-project-track]');
  var controls = section.querySelector('[data-project-controls]');
  var previous = section.querySelector('[data-project-prev]');
  var next = section.querySelector('[data-project-next]');
  var pages = section.querySelector('[data-project-pages]');
  var status = section.querySelector('[data-project-status]');
  var positions = [], perPage = 2, current = 0, scrollTimer;
  function pad(number) { return String(number).padStart(2, '0'); }
  function update() {
    if (!positions.length) return;
    current = positions.reduce(function (best, value, index) {
      return Math.abs(value - track.scrollLeft) < Math.abs(positions[best] - track.scrollLeft) ? index : best;
    }, 0);
    previous.setAttribute('aria-disabled', String(current === 0));
    next.setAttribute('aria-disabled', String(current === positions.length - 1));
    Array.from(pages.children).forEach(function (button, index) {
      if (index === current) button.setAttribute('aria-current', 'true');
      else button.removeAttribute('aria-current');
    });
    var first = current * perPage + 1;
    var last = Math.min(first + perPage - 1, cards.length);
    var label = pad(first) + (last > first ? '\u2013' + pad(last) : '') + ' / ' + pad(cards.length);
    if (status.textContent !== label) status.textContent = label;
  }
  function goTo(index) {
    index = Math.max(0, Math.min(index, positions.length - 1));
    track.scrollTo({ left: positions[index], behavior: motion.matches ? 'auto' : 'smooth' });
  }
  function measure() {
    var anchor = current * perPage;
    perPage = parseInt(getComputedStyle(track).getPropertyValue('--project-per-page'), 10) || 1;
    var total = Math.ceil(cards.length / perPage);
    var bounds = track.getBoundingClientRect();
    var max = track.scrollWidth - track.clientWidth;
    positions = Array.from({ length: total }, function (_, index) {
      return Math.max(0, Math.min(max, cards[index * perPage].getBoundingClientRect().left - bounds.left + track.scrollLeft));
    });
    if (pages.children.length !== total) {
      pages.replaceChildren();
      positions.forEach(function (_, index) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'course-projects__page';
        var first = index * perPage + 1;
        var last = Math.min(first + perPage - 1, cards.length);
        button.setAttribute('aria-label', 'Show project' + (last > first ? 's ' + first + ' to ' + last : ' ' + first));
        button.setAttribute('aria-controls', track.id);
        button.addEventListener('click', function () { goTo(index); });
        pages.appendChild(button);
      });
    }
    controls.hidden = total <= 1;
    track.scrollLeft = positions[Math.min(Math.floor(anchor / perPage), total - 1)] || 0;
    update();
  }
  if (track && controls && cards.length) {
    previous.addEventListener('click', function () { if (current > 0) goTo(current - 1); });
    next.addEventListener('click', function () { if (current < positions.length - 1) goTo(current + 1); });
    track.addEventListener('keydown', function (event) {
      if (event.target !== track || event.altKey || event.ctrlKey || event.metaKey) return;
      if (event.key === 'ArrowRight') goTo(current + 1);
      else if (event.key === 'ArrowLeft') goTo(current - 1);
      else if (event.key === 'Home') goTo(0);
      else if (event.key === 'End') goTo(positions.length - 1);
      else return;
      event.preventDefault();
    });
    track.addEventListener('scroll', function () {
      clearTimeout(scrollTimer);
      scrollTimer = setTimeout(update, 120);
    }, { passive: true });
    if ('ResizeObserver' in window) new ResizeObserver(measure).observe(track);
    else window.addEventListener('resize', measure);
    section.classList.add('has-project-carousel');
    measure();
  }
  function sync() {
    section.classList.toggle('has-project-motion', !motion.matches);
    section.classList.toggle('is-projects-paused', document.hidden);
  }
  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        entry.target.classList.toggle('is-project-offscreen', !entry.isIntersecting);
        if (entry.isIntersecting && entry.intersectionRatio >= .2) entry.target.classList.add('is-project-visible');
      });
    }, { threshold: .2 });
    cards.forEach(function (card) { observer.observe(card); });
  } else cards.forEach(function (card) { card.classList.add('is-project-visible'); });
  motion.addEventListener('change', sync);
  document.addEventListener('visibilitychange', sync);
  sync();
}());

/* About hero: one team, three lines of work. A comet runs the orbit Build → Train → Teach; at each station the dot
   hologram in the middle swirls apart and re-forms as that line of work (an AI chip, a team, a graduation cap).
   1.2 s a station, so all three have shown inside three seconds. The pointer scatters the dots. Reduced motion: the chip, still. */
(function () {
  'use strict';
  var box = document.querySelector('.jv--map'), cv = box && box.querySelector('.jv-morph'), ctx = cv && cv.getContext('2d');
  if (!ctx) return;
  var d = document, root = d.documentElement, nodes = box.querySelectorAll('.jv-node');
  var still = matchMedia('(prefers-reduced-motion: reduce)').matches;
  var TAU = Math.PI * 2, STEP = 1.2, LEG = .45, MOVE = .6, DUST = 240;
  var ANG = [-Math.PI / 2, Math.PI / 6, Math.PI * 5 / 6]; // stations: Build top, Train lower right, Teach lower left (as in journey-visual.php)
  var font = getComputedStyle(d.querySelector('.masterclass-title') || d.body).fontFamily;
  var W, H, dpr, cx, cy, rx, ry, S, p, shapes = [], N = 0, P, spr = [], pad;
  var px = -1e4, py = -1e4, lit = -1;

  // Each shape is drawn in a 200-unit box (lime, with white accents), then read back as a dot grid.
  var gap = function (g, draw, w) { g.save(); g.globalCompositeOperation = 'destination-out'; g.lineWidth = w; draw(true); g.restore(); };
  var head = function (g, x, y, r) { g.beginPath(); g.arc(x, y, r, 0, TAU); g.fill(); };
  var body = function (g, x, top, base, w) { g.beginPath(); g.roundRect(x - w, top, w * 2, base - top, [w * .8, w * .8, 0, 0]); g.fill(); };
  var DRAW = [
    function chip(g) {
      g.fillStyle = g.strokeStyle = '#d2ff00';
      g.lineWidth = 12;
      g.beginPath(); g.roundRect(-58, -58, 116, 116, 16); g.stroke();
      [-38, -13, 13, 38].forEach(function (o) { g.fillRect(o - 4.5, -94, 9, 28); g.fillRect(o - 4.5, 66, 9, 28); g.fillRect(-94, o - 4.5, 28, 9); g.fillRect(66, o - 4.5, 28, 9); });
      g.fillStyle = '#fff';
      g.font = '800 70px ' + font; g.textAlign = 'center'; g.textBaseline = 'middle';
      g.fillText('AI', 0, 5);
    },
    function team(g) { // three people side by side, the middle one (white) a step forward
      g.fillStyle = '#d2ff00';
      [-72, 72].forEach(function (x) { head(g, x, -22, 16); body(g, x, 4, 50, 28); });
      g.fillStyle = '#fff';
      head(g, 0, -36, 21); body(g, 0, -4, 62, 36);
    },
    function cap(g) {
      g.fillStyle = g.strokeStyle = '#d2ff00';
      g.beginPath(); g.moveTo(-54, -14); g.lineTo(-54, 32); g.quadraticCurveTo(0, 64, 54, 32); g.lineTo(54, -14); g.fill();
      var board = function (stroke) { g.beginPath(); g.moveTo(0, -72); g.lineTo(100, -32); g.lineTo(0, 8); g.lineTo(-100, -32); g.closePath(); stroke ? (g.stroke(), g.fill()) : g.fill(); };
      gap(g, board, 14);
      board();
      g.fillStyle = g.strokeStyle = '#fff';
      g.lineWidth = 9; g.lineJoin = 'round';
      g.beginPath(); g.moveTo(0, -32); g.lineTo(76, -24); g.lineTo(76, 34); g.stroke();
      head(g, 0, -32, 8);
      g.beginPath(); g.roundRect(66, 30, 20, 34, 5); g.fill();
    }
  ];
  var sample = function (draw) {
    var c = d.createElement('canvas'), n = Math.ceil(S), g, data, out = [], x, y, i;
    c.width = c.height = n;
    g = c.getContext('2d', { willReadFrequently: true });
    g.translate(n / 2, n / 2); g.scale(n / 200, n / 200);
    draw(g);
    data = g.getImageData(0, 0, n, n).data;
    for (y = p / 2; y < n; y += p) for (x = p / 2; x < n; x += p) {
      i = ((y | 0) * n + (x | 0)) * 4;
      if (data[i + 3] > 140) out.push([x - n / 2, y - n / 2, data[i + 2] > 128 ? 1 : 0]);
    }
    for (i = out.length - 1; i > 0; i--) { x = Math.random() * (i + 1) | 0; y = out[i]; out[i] = out[x]; out[x] = y; } // any dot may go anywhere: the swirl reads as one burst
    return out;
  };
  // A glowing dot per tone: lime, white, and the dimmer orbit dust.
  var sprite = function (rgb, core, glow) {
    var z = Math.ceil(p * 2.6 * dpr), c = d.createElement('canvas'), g = c.getContext('2d'), gr;
    c.width = c.height = z;
    gr = g.createRadialGradient(z / 2, z / 2, 0, z / 2, z / 2, z / 2);
    gr.addColorStop(0, 'rgba(' + rgb + ',1)'); gr.addColorStop(core, 'rgba(' + rgb + ',1)');
    gr.addColorStop(core + .06, 'rgba(' + rgb + ',' + glow + ')'); gr.addColorStop(1, 'rgba(' + rgb + ',0)');
    g.fillStyle = gr; g.fillRect(0, 0, z, z);
    return c;
  };

  var setup = function () {
    W = cv.clientWidth; H = cv.clientHeight;
    if (!W || !H) return false;
    dpr = Math.min(devicePixelRatio || 1, 2);
    cv.width = Math.round(W * dpr); cv.height = Math.round(H * dpr);
    cx = W / 2; cy = H / 2; rx = W * .36; ry = H * .4;
    S = H * .6; p = Math.max(4, S / 40);
    shapes = DRAW.map(sample);
    var n = Math.max.apply(null, shapes.map(function (s) { return s.length; })) + DUST, i;
    if (n !== N) {
      N = n;
      P = { a: new Float32Array(N), j: new Float32Array(N), dl: new Float32Array(N), ph: new Float32Array(N), ox: new Float32Array(N), oy: new Float32Array(N), vx: new Float32Array(N), vy: new Float32Array(N) };
      for (i = 0; i < N; i++) { P.a[i] = Math.random() * TAU; P.j[i] = .9 + Math.random() * .22; P.dl[i] = Math.random() * .2; P.ph[i] = Math.random() * TAU; }
    }
    spr = [sprite('210,255,0', .2, .3), sprite('255,255,255', .2, .3), sprite('210,255,0', .12, .12)];
    pad = ctx.createRadialGradient(0, 0, 0, 0, 0, 1);
    pad.addColorStop(0, 'rgba(210,255,0,.35)'); pad.addColorStop(1, 'rgba(210,255,0,0)');
    return true;
  };

  var ease = function (u) { u = u < 0 ? 0 : u > 1 ? 1 : u; return u < .5 ? 4 * u * u * u : 1 - Math.pow(-2 * u + 2, 3) / 2; };
  var onRing = function (a, k) { return [cx + rx * k * Math.cos(a), cy + ry * k * Math.sin(a)]; };
  // Where dot i rests in shape s at time t: its place in the shape, or (shape -1, or a spare dot) drifting on the orbit.
  var home = function (s, i, t, out) {
    var pt = s >= 0 && shapes[s][i];
    if (pt) { out[0] = cx + pt[0]; out[1] = cy - H * .02 + pt[1]; out[2] = pt[2]; return; }
    var a = P.a[i] + t * .12;
    out[0] = cx + rx * P.j[i] * Math.cos(a); out[1] = cy + ry * P.j[i] * Math.sin(a); out[2] = 2;
  };

  var A = [0, 0, 0], B = [0, 0, 0];
  var frame = function (t) {
    var k = Math.floor(t / STEP), ts = t - k * STEP, s = k % 3, from = k ? (k - 1) % 3 : -1, prev = (s + 2) % 3;
    var i, e, x, y, th, dx, dy, dd, dist, f, sz, tone, al, c, sn, q;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.globalCompositeOperation = 'destination-out'; // fade, leaving short trails behind fast dots
    ctx.globalAlpha = 1; ctx.fillStyle = 'rgba(0,0,0,.5)'; ctx.fillRect(0, 0, W, H);
    ctx.globalCompositeOperation = 'source-over';

    // The orbit: front half brighter than the back, a turning dashed ring outside it, dial ticks inside.
    ctx.lineWidth = 1;
    ctx.strokeStyle = 'rgba(210,255,0,.13)'; ctx.beginPath(); ctx.ellipse(cx, cy, rx, ry, 0, Math.PI, TAU); ctx.stroke();
    ctx.strokeStyle = 'rgba(210,255,0,.3)'; ctx.beginPath(); ctx.ellipse(cx, cy, rx, ry, 0, 0, Math.PI); ctx.stroke();
    ctx.setLineDash([2, 8]); ctx.lineDashOffset = -t * 16; ctx.strokeStyle = 'rgba(210,255,0,.2)';
    ctx.beginPath(); ctx.ellipse(cx, cy, rx * 1.07, ry * 1.09, 0, 0, TAU); ctx.stroke(); ctx.setLineDash([]);
    ctx.strokeStyle = 'rgba(210,255,0,.22)'; ctx.beginPath();
    for (i = 0; i < 96; i++) { th = i / 96 * TAU + t * .06; c = Math.cos(th); sn = Math.sin(th); f = i % 8 ? .97 : .93; ctx.moveTo(cx + rx * .99 * c, cy + ry * .99 * sn); ctx.lineTo(cx + rx * f * c, cy + ry * f * sn); }
    ctx.stroke();
    // The projector pad under the hologram.
    ctx.save(); ctx.translate(cx, cy + S * .5); ctx.scale(S * .55, S * .08); ctx.fillStyle = pad; ctx.beginPath(); ctx.arc(0, 0, 1, 0, TAU); ctx.fill(); ctx.restore();

    // The dots: from the last shape to this one, swirling round the centre and flung outwards on the way.
    var scan = ts > MOVE + .1 && ts < MOVE + .55 ? cy - H * .02 - S / 2 + (ts - MOVE - .1) / .45 * S : -1e4;
    for (i = 0; i < N; i++) {
      home(from, i, t, A); home(s, i, t, B);
      e = ease((ts - P.dl[i]) / (MOVE - .2));
      x = A[0] + (B[0] - A[0]) * e; y = A[1] + (B[1] - A[1]) * e;
      if (e > 0 && e < 1 && (A[2] < 2 || B[2] < 2)) { // spare dots stay on the orbit
        q = Math.sin(Math.PI * e); th = (k % 2 ? -1 : 1) * 1.2 * q;
        dx = (x - cx) * (1 + .3 * q); dy = (y - cy) * (1 + .3 * q);
        c = Math.cos(th); sn = Math.sin(th);
        x = cx + dx * c - dy * sn * (rx / ry); y = cy + dx * sn * (ry / rx) + dy * c;
      }
      // The pointer pushes dots aside; they spring home.
      dx = x - px; dy = y - py; dd = dx * dx + dy * dy;
      if (dd < 5000) { dist = Math.sqrt(dd) || 1; f = (1 - dist / 70.7) * 2.6; P.vx[i] += dx / dist * f; P.vy[i] += dy / dist * f; }
      P.vx[i] = (P.vx[i] - P.ox[i] * .07) * .84; P.vy[i] = (P.vy[i] - P.oy[i] * .07) * .84;
      P.ox[i] += P.vx[i]; P.oy[i] += P.vy[i];
      x += P.ox[i]; y += P.oy[i];
      tone = e < .5 ? A[2] : B[2];
      al = tone === 2 ? .55 : .72 + .28 * Math.sin(t * 4 + P.ph[i]);
      if (tone !== 2 && Math.abs(y - scan) < p * 1.2) { tone = 1; al = 1; } // the build scan passes over the finished shape
      sz = p * 2.6 * (tone === 2 ? .7 : 1);
      ctx.globalAlpha = al;
      ctx.drawImage(spr[tone], x - sz / 2, y - sz / 2, sz, sz);
    }
    ctx.globalAlpha = 1;
    if (scan > -1e4) {
      var g = ctx.createLinearGradient(cx - S * .6, 0, cx + S * .6, 0);
      g.addColorStop(0, 'rgba(210,255,0,0)'); g.addColorStop(.5, 'rgba(240,255,170,.7)'); g.addColorStop(1, 'rgba(210,255,0,0)');
      ctx.fillStyle = g; ctx.fillRect(cx - S * .6, scan - .75, S * 1.2, 1.5);
    }

    // The comet: one leg of the orbit per station, its tail longest at full speed; a ring breaks out where it lands.
    var u = Math.min(1, ts / LEG), a = ANG[prev] + TAU / 3 * ease(u), tail = .08 + 1.1 * Math.sin(Math.PI * u), pt, pt2;
    ctx.lineCap = 'round';
    for (i = 0; i < 18; i++) {
      pt = onRing(a - tail * i / 18, 1); pt2 = onRing(a - tail * (i + 1) / 18, 1);
      ctx.strokeStyle = 'rgba(210,255,0,' + (1 - i / 18) * .9 + ')'; ctx.lineWidth = 3.2 * (1 - i / 18) + .4;
      ctx.beginPath(); ctx.moveTo(pt[0], pt[1]); ctx.lineTo(pt2[0], pt2[1]); ctx.stroke();
    }
    pt = onRing(a, 1);
    ctx.drawImage(spr[1], pt[0] - p * 2.2, pt[1] - p * 2.2, p * 4.4, p * 4.4);
    if (ts > LEG && ts < LEG + .55) {
      q = (ts - LEG) / .55; pt = onRing(ANG[s], 1);
      ctx.strokeStyle = 'rgba(210,255,0,' + (1 - q) + ')'; ctx.lineWidth = 2 * (1 - q) + .5;
      ctx.beginPath(); ctx.ellipse(pt[0], pt[1], 12 + q * 70, 8 + q * 46, 0, 0, TAU); ctx.stroke();
    }
    // The station's label lights as the comet reaches it.
    var on = ts >= LEG * .85 ? s : k ? prev : -1;
    if (on !== lit) { if (lit >= 0) nodes[lit].classList.remove('is-on'); if (on >= 0) nodes[on].classList.add('is-on'); lit = on; }
  };

  // One clock that only runs while the hero is on screen and the tab is showing.
  var t = 0, last = 0, raf = 0, seen = false, started = false;
  var tick = function (now) { t += Math.min(.05, (now - last) / 1000); last = now; frame(t); raf = requestAnimationFrame(tick); };
  var go = function () {
    if (still) return;
    var run = seen && started && N && !d.hidden;
    if (run && !raf) { last = performance.now(); raf = requestAnimationFrame(tick); }
    if (!run && raf) { cancelAnimationFrame(raf); raf = 0; }
  };
  var redo = function () {
    if (!setup()) return;
    if (still) { for (var i = 0; i < 6; i++) frame(STEP - .02); } else frame(t); // still: the chip, formed, after its scan
  };
  new ResizeObserver(redo).observe(box);
  if (d.fonts) d.fonts.ready.then(redo);
  new IntersectionObserver(function (en) { seen = en[0].isIntersecting; go(); }).observe(box);
  d.addEventListener('visibilitychange', go);
  var hero = box.closest('section') || box;
  hero.addEventListener('pointermove', function (ev) { var r = cv.getBoundingClientRect(); px = ev.clientX - r.left; py = ev.clientY - r.top; });
  hero.addEventListener('pointerleave', function () { px = py = -1e4; });
  var start = function () { started = true; go(); };
  if (root.classList.contains('is-in')) start();
  else new MutationObserver(function (m, mo) { if (root.classList.contains('is-in')) { mo.disconnect(); start(); } }).observe(root, { attributes: true, attributeFilter: ['class'] });
}());
