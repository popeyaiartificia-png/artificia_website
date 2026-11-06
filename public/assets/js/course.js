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
