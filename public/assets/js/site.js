/* Artificia AI site script: menu, click-to-load embeds, curriculum ruler, motion. */
(function () {
  'use strict';
  var d = document, root = d.documentElement;

  // Each About principle keeps its neon glow until it is clicked again.
  d.querySelectorAll('.ab-note__toggle').forEach(function (button) {
    button.addEventListener('click', function () {
      var glowing = button.getAttribute('aria-pressed') !== 'true';
      button.setAttribute('aria-pressed', String(glowing));
      button.closest('.ab-note').classList.toggle('is-glowing', glowing);
    });
  });

  // Let the 700ms logo assembly finish, then leave as soon as the page is ready (2.2s safety cap).
  var loader = d.querySelector('.loader');
  var enter = function () { root.classList.add('is-in'); };
  if (loader && root.classList.contains('js') && !root.classList.contains('al') && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var done = 0, gone = false, safetyTimer;
    loader.classList.add('is-active');
    var leave = function () {
      if (gone) return;
      gone = true;
      clearTimeout(safetyTimer);
      loader.classList.add('is-out');
      setTimeout(enter, 120);
      setTimeout(function () { loader.remove(); }, 550);
    };
    var step = function () { done++; if (done === 4) leave(); };
    (d.fonts && d.fonts.ready ? d.fonts.ready : Promise.resolve()).then(step, step);
    var assemble = function () {
      requestAnimationFrame(function () {
        if (gone) return;
        var animation = loader.querySelector('.loader__piece:last-child').getAnimations()[0];
        if (animation) animation.finished.then(step, step); else step();
      });
    };
    var logo = d.querySelector('.brand img'); // reuse the preloaded logo instead of requesting another copy
    if (logo && !logo.complete) { logo.addEventListener('load', assemble); logo.addEventListener('error', step); }
    else if (logo && logo.naturalWidth) assemble(); else step();
    var first = d.querySelector('main img:not([loading="lazy"])'); // a lazy image below the fold would hold the loader to its timeout
    if (first && !first.complete) { first.addEventListener('load', step); first.addEventListener('error', step); } else step();
    if (d.readyState === 'complete') step(); else addEventListener('load', step);
    // Start the cap at the first paint, so page setup cannot consume the logo's animation time.
    requestAnimationFrame(function () { if (!gone) safetyTimer = setTimeout(leave, 2200); });
  } else {
    if (loader) loader.remove();
    enter();
  }

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

  // Videos load nothing until the visitor asks for them.
  d.addEventListener('click', function (e) {
    var b = e.target.closest('.vid[data-src]');
    if (!b) return;
    var src = b.getAttribute('data-src') || '';
    if (src.indexOf('https://') !== 0) return; // the server allowlists hosts; never load anything else
    var f = d.createElement('iframe');
    f.src = src;
    f.title = b.getAttribute('data-title') || 'Video';
    f.allow = 'autoplay; fullscreen';
    f.setAttribute('allowfullscreen', '');
    var w = d.createElement('div');
    w.className = 'vid is-playing';
    w.appendChild(f);
    b.replaceWith(w);
    f.setAttribute('tabindex', '-1');
    f.focus();
  });

  // Testimonial cards keep working links without JavaScript; enhanced playback opens one player on demand.
  var reviewDialog, reviewOpener, reviewOverflow;
  d.addEventListener('click', function (e) {
    var card = e.target.closest('[data-testimonial-fan] .voices__surface');
    if (!card || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button !== 0 || !window.HTMLDialogElement) return;
    e.preventDefault();
    if (!reviewDialog) {
      reviewDialog = d.createElement('dialog');
      reviewDialog.className = 'review-player';
      reviewDialog.setAttribute('aria-labelledby', 'review-player-title');
      reviewDialog.innerHTML = '<div class="review-player__head"><div><h3 id="review-player-title"></h3><p></p></div><button class="review-player__close" type="button" aria-label="Close testimonial">&times;</button></div><div class="review-player__body"></div>';
      d.body.appendChild(reviewDialog);
      reviewDialog.querySelector('button').addEventListener('click', function () { reviewDialog.close(); });
      reviewDialog.addEventListener('click', function (event) {
        var r = reviewDialog.getBoundingClientRect();
        if (event.target === reviewDialog && (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom)) reviewDialog.close();
      });
      reviewDialog.addEventListener('close', function () {
        reviewDialog.querySelector('.review-player__body').replaceChildren();
        d.body.style.overflow = reviewOverflow;
        if (reviewOpener && reviewOpener.isConnected) reviewOpener.focus({ preventScroll: true });
      });
    }
    reviewOpener = card;
    reviewDialog.querySelector('h3').textContent = card.dataset.reviewName;
    reviewDialog.querySelector('.review-player__head p').textContent = card.dataset.reviewRole;
    var body = reviewDialog.querySelector('.review-player__body');
    body.replaceChildren();
    if (card.dataset.reviewSrc) {
      var frame = d.createElement('iframe');
      frame.src = card.dataset.reviewSrc;
      frame.title = 'Testimonial from ' + card.dataset.reviewName;
      frame.allow = 'autoplay; fullscreen'; frame.allowFullscreen = true;
      var fallback = d.createElement('a');
      fallback.className = 'review-player__link'; fallback.href = card.href;
      fallback.target = '_blank'; fallback.rel = 'noopener'; fallback.textContent = 'Open video in a new tab';
      body.append(frame, fallback);
    } else {
      var quote = d.createElement('blockquote');
      quote.textContent = card.dataset.reviewQuote; body.appendChild(quote);
    }
    if (card.dataset.reviewNote) {
      var note = d.createElement('p');
      note.className = 'review-player__photo-note';
      note.textContent = card.dataset.reviewNote;
      body.appendChild(note);
    }
    reviewOverflow = d.body.style.overflow;
    d.body.style.overflow = 'hidden';
    reviewDialog.showModal();
  });

  // The WhatsApp button waits until the hero (and its chooser) has scrolled away.
  // It also steps aside over the footer, which carries its own WhatsApp link.
  var fab = d.querySelector('.wa-float'), fabAway = [d.querySelector('.hero, .course-hero, .sol-hero'), d.querySelector('[data-foot]')].filter(Boolean);
  if (fab && fabAway.length && 'IntersectionObserver' in window) {
    var fabIo = new IntersectionObserver(function (es) {
      es.forEach(function (e) { e.target.inSight = e.isIntersecting; });
      fab.classList.toggle('is-away', fabAway.some(function (el) { return el.inSight; }));
    });
    fabAway.forEach(function (el) { fabIo.observe(el); });
  }

  // Attribution: the first visit's utm_*, gclid, fbclid and outside referrer are kept in the atk cookie for 30 days.
  var ATK = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid'];
  var cookie = function (name) {
    var m = d.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    try { return m ? decodeURIComponent(m[1]) : ''; } catch (e) { return ''; }
  };
  var atk = {};
  try { atk = JSON.parse(cookie('atk') || '{}') || {}; } catch (e) { atk = {}; }
  if (!cookie('atk')) {
    var qs = new URLSearchParams(location.search), first = {}, seen = false;
    ATK.forEach(function (k) { var v = qs.get(k); if (v) { first[k] = v.slice(0, 255); seen = true; } });
    try { if (d.referrer && new URL(d.referrer).host !== location.host) { first.referrer = d.referrer.slice(0, 500); seen = true; } } catch (e) {}
    if (seen) {
      atk = first; // used even if the browser refuses the cookie
      d.cookie = 'atk=' + encodeURIComponent(JSON.stringify(first)) + '; max-age=2592000; path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    }
  }

  // Lead forms: sent with fetch; the thank-you shows only when the server says the lead is saved (ok:true).
  d.querySelectorAll('form.lead-form').forEach(function (form) {
    var btn = form.querySelector('button[type=submit]'), st = form.querySelector('.lead-form__status');
    var log = form.querySelector('.logline'), type = form.getAttribute('data-form-type') || '';
    var field = function (name) { var el = form.elements.namedItem(name); return el && el.length !== undefined && !el.tagName ? el[0] : el; };
    var set = function (name, v) { var el = field(name); if (el) el.value = v || ''; };
    var status = function (msg, isError, wa) {
      st.textContent = msg;
      st.classList.toggle('is-error', !!isError);
      if (wa) {
        var a = d.createElement('a');
        a.href = wa;
        a.target = '_blank';
        a.rel = 'noopener noreferrer';
        a.textContent = 'Message us on WhatsApp';
        st.appendChild(d.createTextNode(' '));
        st.appendChild(a);
      }
    };
    var done = function (res) {
      var nameEl = field('name'), first = nameEl ? nameEl.value.trim().split(/\s+/)[0] : '';
      log.querySelector('[data-thanks]').textContent = 'Thanks' + (first ? ', ' + first : '') + '. We’ll message you on WhatsApp soon.';
      log.querySelector('.logline__time').textContent = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
      st.textContent = '';
      log.hidden = false;
      form.classList.add('is-done');
      log.focus();
      if (!res.event_id) return; // spam gets the thank-you but no tracking event
      if (typeof window.gtag === 'function') window.gtag('event', 'generate_lead', { form_type: type });
      if (typeof window.fbq === 'function') window.fbq('track', 'Lead', { content_category: type }, { eventID: res.event_id });
    };
    var fail = function (res) {
      btn.disabled = false;
      form.classList.remove('is-busy');
      var wa = typeof res.whatsapp === 'string' && res.whatsapp.indexOf('https://wa.me/') === 0 ? res.whatsapp : form.getAttribute('data-wa');
      if (res.errors) {
        var firstBad = null, other = [];
        Object.keys(res.errors).forEach(function (k) {
          var el = field(k) || field(k + '[]'), err = el && d.getElementById(el.id + '-err');
          if (!err) { other.push(res.errors[k]); return; }
          err.textContent = res.errors[k];
          el.setAttribute('aria-invalid', 'true');
          firstBad = firstBad || el;
        });
        status(other.length ? other.join(' ') : 'Please check the highlighted fields.', true, wa);
        if (firstBad) firstBad.focus();
        return;
      }
      status(res.message || 'Your enquiry could not be sent just now. Please send it to us on WhatsApp instead.', true, wa);
    };
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (form.classList.contains('is-busy')) return;
      ATK.forEach(function (k) { set(k, atk[k]); });
      set('page_url', location.origin + location.pathname);
      set('referrer', atk.referrer || d.referrer);
      set('fbp', cookie('_fbp'));
      set('fbc', cookie('_fbc'));
      form.querySelectorAll('.field__err').forEach(function (p) { p.textContent = ''; });
      form.querySelectorAll('[aria-invalid]').forEach(function (el) { el.removeAttribute('aria-invalid'); });
      btn.disabled = true;
      form.classList.add('is-busy');
      status('Sending…');
      fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { return r.json().catch(function () { return {}; }); })
        .then(function (res) { if (res && res.ok === true) done(res); else fail(res || {}); })
        .catch(function () { fail({}); });
    });
  });

  // Careers: a role card picks its role in the application form on the way down to it.
  d.querySelectorAll('[data-pick-role]').forEach(function (link) {
    link.addEventListener('click', function () {
      var select = d.querySelector('#enquire select[name="role"]');
      if (select) select.value = link.getAttribute('data-pick-role');
    });
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

  // The hero lesson: pick a likely next word and the sentence grows, the way a language model writes.
  d.querySelectorAll('[data-predict]').forEach(function (box) {
    var tree;
    try { tree = JSON.parse(box.querySelector('script[type="application/json"]').textContent); } catch (e) { return; }
    var said = box.querySelector('.predict__said'), opts = box.querySelector('.predict__opts');
    var note = box.querySelector('.predict__note'), again = box.querySelector('.predict__again'), hint = note.textContent;
    var render = function (list) {
      opts.textContent = '';
      list.forEach(function (o) {
        var li = d.createElement('li'), b = d.createElement('button');
        b.type = 'button';
        b.style.setProperty('--p', o.p / 100);
        ['predict__w', 'predict__bar', 'predict__p'].forEach(function (c) { var s = d.createElement('span'); s.className = c; b.appendChild(s); });
        b.children[0].textContent = o.w;
        b.children[1].setAttribute('aria-hidden', 'true');
        b.children[2].textContent = o.p + '%';
        b.addEventListener('click', function () { pick(o); });
        li.appendChild(b);
        opts.appendChild(li);
      });
      opts.hidden = false;
      opts.classList.add('is-new'); // the bars grow from nothing on the next frame
      requestAnimationFrame(function () { requestAnimationFrame(function () { opts.classList.remove('is-new'); }); });
    };
    var pick = function (o) {
      var m = d.createElement('mark');
      m.className = 'predict__new';
      m.textContent = o.w;
      said.appendChild(d.createTextNode(' '));
      said.appendChild(m);
      if (o.next) { render(o.next); opts.querySelector('button').focus({ preventScroll: true }); return; }
      opts.hidden = true;
      note.textContent = note.getAttribute('data-end');
      again.hidden = false;
      again.focus({ preventScroll: true });
    };
    again.addEventListener('click', function () {
      said.textContent = tree.start;
      note.textContent = hint;
      again.hidden = true;
      render(tree.next);
      opts.querySelector('button').focus({ preventScroll: true });
    });
    render(tree.next);
  });

  // Glossary: the first time an AI word appears on a page, it explains itself in plain English.
  var GLOSS = [
    ['voice agents?', 'Voice agent', 'An AI that talks on a phone call. It listens, understands and answers out loud, in a natural voice.'],
    ['RAG', 'RAG', 'Retrieval-augmented generation. Before it answers, the AI looks up the right pages in your own documents, then answers from them.'],
    ['MCP', 'MCP', 'Model Context Protocol. A standard plug that lets an AI model use other apps and data, a bit like USB for AI.'],
    ['n8n', 'n8n', 'A visual automation tool. You join apps together as boxes and arrows, and the workflow then runs on its own.'],
    ['Claude Code', 'Claude Code', 'Anthropic’s coding assistant. You describe what you want in plain words and it writes, runs and fixes the code.'],
    ['NotebookLM', 'NotebookLM', 'Google’s AI notebook. Add your notes or PDFs and it answers questions from those sources only.'],
    ['GoHighLevel', 'GoHighLevel', 'A sales and marketing platform: leads, follow-ups, WhatsApp, email and calendars in one place.'],
    ['multi-agent systems|agents?', 'Agent', 'An AI that does more than answer. It plans steps and uses tools, like search, email or a browser, to finish a task.'],
    ['local (?:LLMs|models)', 'Local model', 'An AI model that runs on your own computer instead of in the cloud, so your data stays with you.'],
    ['LLMs?', 'LLM', 'Large language model: the kind of AI behind ChatGPT, Claude and Gemini. It writes by predicting the next word.'],
    ['prompting|prompts?', 'Prompt', 'The instruction you give an AI. Most of getting a good answer is asking clearly.'],
    ['capstone', 'Capstone', 'A final project of your own that pulls together what you learned on the course.'],
    ['Demo Day', 'Demo Day', 'Day 30 of the masterclass, when you present your own project to mentors and classmates.'],
    ['Git', 'Git', 'A tool that saves every version of your code, so you can undo mistakes and work with others.']
  ];
  var scope = d.querySelectorAll('main .lede, main .prose, main .index__item p, main .case p, main .step p, main .mod__topics, main .qa__a, main .flow__node p');
  if (scope.length) {
    var defs = d.createElement('div'), tip = d.createElement('div'), shown = null;
    defs.hidden = true;
    tip.className = 'gloss';
    tip.setAttribute('aria-hidden', 'true');
    tip.innerHTML = '<b></b><span></span>';
    d.body.appendChild(defs);
    d.body.appendChild(tip);
    var textNodes = function () {
      var list = [];
      scope.forEach(function (el) {
        var w = d.createTreeWalker(el, NodeFilter.SHOW_TEXT, { acceptNode: function (n) { return n.parentNode.closest('a, button, .term-w, .predict__opts') ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT; } });
        while (w.nextNode()) list.push(w.currentNode);
      });
      return list;
    };
    GLOSS.forEach(function (g, i) {
      var re = new RegExp('\\b(?:' + g[0] + ')\\b([.,;:!?)]*)'), nodes = textNodes();
      for (var k = 0; k < nodes.length; k++) {
        var m = re.exec(nodes[k].data);
        if (!m) continue;
        var word = nodes[k].splitText(m.index);
        word.splitText(m[0].length);
        // A button never breaks inside, so any punctuation after it rides along instead of wrapping onto a line of its own.
        var b = d.createElement('button'), def = d.createElement('span'), keep = d.createElement('span');
        keep.className = 'term-w';
        keep.appendChild(b);
        if (m[1]) keep.appendChild(d.createTextNode(m[1]));
        b.type = 'button';
        b.className = 'term';
        b.textContent = m[0].slice(0, m[0].length - m[1].length);
        b.setAttribute('aria-describedby', 'gl-' + i);
        b.setAttribute('aria-expanded', 'false');
        b.dataset.t = g[1];
        def.id = 'gl-' + i;
        def.textContent = g[2];
        defs.appendChild(def);
        word.parentNode.replaceChild(keep, word);
        break; // first mention only
      }
    });
    var hide = function () {
      if (!shown) return;
      shown.setAttribute('aria-expanded', 'false');
      tip.classList.remove('is-on');
      shown = null;
    };
    var show = function (b) {
      if (shown === b) return;
      hide();
      shown = b;
      b.setAttribute('aria-expanded', 'true');
      tip.firstChild.textContent = b.dataset.t;
      tip.lastChild.textContent = d.getElementById(b.getAttribute('aria-describedby')).textContent;
      var r = b.getBoundingClientRect(), w = tip.offsetWidth, h = tip.offsetHeight;
      var y = r.top - h - 10 < 8 ? r.bottom + 10 : r.top - h - 10;
      var x = Math.max(12, Math.min(r.left + r.width / 2 - w / 2, innerWidth - w - 12));
      tip.style.left = x + 'px';
      tip.style.top = y + 'px';
      tip.classList.add('is-on');
    };
    d.addEventListener('mouseover', function (e) { var b = e.target.closest('.term'); if (b) show(b); else if (shown && !shown.contains(d.activeElement)) hide(); });
    d.addEventListener('focusin', function (e) { var b = e.target.closest('.term'); if (b) show(b); else hide(); });
    d.addEventListener('click', function (e) { var b = e.target.closest('.term'); if (b) show(b); else hide(); });
    d.addEventListener('keydown', function (e) { if (e.key === 'Escape') hide(); });
    addEventListener('scroll', hide, { passive: true });
  }

  // Value noise and a two-octave fbm, shared by the terrain and both reveal passes.
  var NOISE = [
    'float hash(vec2 p) { return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453); }',
    'float noise(vec2 p) { vec2 i = floor(p), f = fract(p); f = f * f * (3. - 2. * f);',
    '  return mix(mix(hash(i), hash(i + vec2(1., 0.)), f.x), mix(hash(i + vec2(0., 1.)), hash(i + vec2(1., 1.)), f.x), f.y); }',
    'float fbm(vec2 p) { return noise(p) * .65 + noise(p * 2.3 + 5.2) * .35; }'
  ].join('\n');
  var still = matchMedia('(prefers-reduced-motion: reduce)').matches;
  // A topographic ground on a canvas: the stage's, and the sections' under it. `from` is the element whose top meets the
  // stage's bottom; each canvas shifts the field by its distance from there, so the moving contours run on without a seam.
  // The flood at the foot of Before AI / With AI: the dark rises over the same terrain, low ground first, so its edge is a moving contour
  // with a clean dark edge. uRise is how far it has climbed, in pixels.
  var FLOOD = [
    '  float s = clamp(uRes.x / 1536., .6, 1.2), y = gl_FragCoord.y, x = gl_FragCoord.x / uRes.x * 2. - 1.;',
    '  float amp = mix(FLOOD_AMP, FLOOD_AMPMAX, smoothstep(30., 320., uRise)) * clamp(uRes.x / 1536., .8, 1.2) * (FLOOD_CALM + (1. - FLOOD_CALM) * x * x);',
    '  float rise = uRise - FLOOD_DIP * s * (1. - x * x) * smoothstep(0., 200., uRise);', // calm and late in the middle, where the copy sits; livelier over the figures
    '  float swell = (FLOOD_SWELL) * s;', // keeps moving when the page is still
    '  float e = y - rise + swell * FLOOD_FADE + (v - 4.5) * amp * smoothstep(0., FLOOD_NEAR * s, y);',
    '  float aa = max(fwidth(e), 1e-3), o = 1. - smoothstep(-aa, aa, e);',
    '  vec3 c = mix(FLOOD_BASE, FLOOD_LINE, a) * o;',
    '  gl_FragColor = vec4(c, o);'
  ].join('\n');
  // The homepage's floods keep their calm tuning; `fluid` floods (the section joins) swell wider and faster and show the
  // contour wobble from the start, so even a low flood reads as liquid. `fluid` is a strength: 1 full, lower for a calmer edge.
  var floodShader = function (flood) {
    var k = +flood.fluid || 0, f = function (n) { return (n * k).toFixed(1); }, sp = (.5 + .5 * k).toFixed(2);
    var shader = FLOOD.replace('FLOOD_BASE', flood.base || 'vec3(.067, .082, .059)').replace('FLOOD_LINE', flood.line || 'vec3(.47, .5, .36)');
    // Contact's short join and the footer's neon use broad, slow swells on the shared map, with no small surface ripples.
    if (flood.profile === 'contact' || flood.profile === 'calm') return shader
      .replace('FLOOD_AMPMAX', '18.').replace('FLOOD_AMP', '12.').replace(/FLOOD_CALM/g, '.9')
      .replace('FLOOD_NEAR', '180.').replace('FLOOD_FADE', '1.').replace('FLOOD_DIP', '0.')
      .replace('FLOOD_SWELL', 'sin(x * 2.6 + uTime * .45) * 8. + sin(x * 5.1 - uTime * .3) * 3.');
    return shader.replace('FLOOD_AMPMAX', k ? f(86) : '120.').replace('FLOOD_AMP', k ? f(46) : '34.').replace(/FLOOD_CALM/g, k ? '.72' : '.4').replace('FLOOD_NEAR', k ? '140.' : '90.')
      .replace('FLOOD_FADE', k ? '1.' : 'smoothstep(0., 40., y)')
      .replace('FLOOD_DIP', k ? '24.' : '80.')
      .replace('FLOOD_SWELL', k
        ? ('sin(gl_FragCoord.x / 115. + uTime * .95) * ' + f(30) + ' + sin(gl_FragCoord.x / 57. - uTime * .8) * ' + f(14) + ' + sin(gl_FragCoord.x / 31. + uTime * 1.6) * ' + f(6)).replace(/uTime/g, 'uTime * ' + sp)
        : 'sin(x * 2.6 + uTime * .55) * 9. + sin(x * 6.1 - uTime * .4) * 4.');
  };
  // Chromium discards the oldest canvas when a page opens too many WebGL contexts. Keep headroom for the robot and
  // gallery, and release inner-page section contexts once they leave the nearby viewport.
  var topoContexts = 0, topoContextLimit = 12;
  var topoField = function (topo, from, flood) {
    var solutionTerrain = topo.closest('.solutions__terrain');
    var releaseLive = null, fallbackResize = false;
    var managed = !flood && !!topo.closest('.course-page, .solutions-page') && 'IntersectionObserver' in window;
    var freshCanvas = function () {
      var next = topo.cloneNode(false);
      next.flood = topo.flood; next.weave = topo.weave;
      topo.terrainLive = false;
      topo.replaceWith(next); topo = next;
    };
    // Contour lines of a smooth noise field (marching squares), drawn once per size, like a survey map.
    var drawTopo = function () {
      var dpr = Math.min(window.devicePixelRatio || 1, 2), w = topo.clientWidth, h = topo.clientHeight;
      topo.width = Math.round(w * dpr);
      topo.height = Math.round(h * dpr);
      var x = topo.getContext('2d'), seed = 11, G = 8, lat = [];
      if (!x) return;
      var rnd = function () { seed = seed * 16807 % 2147483647; return seed / 2147483647; };
      for (var i = 0; i < G * G; i++) lat.push(rnd());
      var at = function (a, b) { return lat[(a % G + G) % G + ((b % G + G) % G) * G]; };
      var noise = function (u, v) {
        var i = Math.floor(u), j = Math.floor(v), fu = u - i, fv = v - j;
        fu = fu * fu * (3 - 2 * fu); fv = fv * fv * (3 - 2 * fv);
        return at(i, j) * (1 - fu) * (1 - fv) + at(i + 1, j) * fu * (1 - fv) + at(i, j + 1) * (1 - fu) * fv + at(i + 1, j + 1) * fu * fv;
      };
      var c = 12, cols = Math.ceil(w / c) + 1, rows = Math.ceil(h / c) + 1, v = new Float32Array(cols * rows), q, r;
      for (r = 0; r < rows; r++) for (q = 0; q < cols; q++) v[q + r * cols] = noise(q * c / 380, r * c / 380) + 0.4 * noise(q * c / 170 + 5.3, r * c / 170 + 2.1);
      x.setTransform(dpr, 0, 0, dpr, 0, 0);
      x.clearRect(0, 0, w, h);
      x.strokeStyle = 'rgba(120, 128, 92, .22)'; // reads on the white and, later, on the olive
      x.lineWidth = 1;
      x.beginPath();
      for (var L = 0.2; L < 1.35; L += 0.105) {
        for (r = 0; r < rows - 1; r++) for (q = 0; q < cols - 1; q++) {
          var a = v[q + r * cols], b = v[q + 1 + r * cols], e = v[q + 1 + (r + 1) * cols], f = v[q + (r + 1) * cols], p = [];
          if ((a < L) !== (b < L)) p.push(q * c + c * (L - a) / (b - a), r * c);
          if ((b < L) !== (e < L)) p.push((q + 1) * c, r * c + c * (L - b) / (e - b));
          if ((f < L) !== (e < L)) p.push(q * c + c * (L - f) / (e - f), (r + 1) * c);
          if ((a < L) !== (f < L)) p.push(q * c, r * c + c * (L - a) / (f - a));
          for (var k = 0; k + 3 < p.length; k += 4) { x.moveTo(p[k], p[k + 1]); x.lineTo(p[k + 2], p[k + 3]); }
        }
      }
      x.stroke();
      if (solutionTerrain) solutionTerrain.classList.add('has-topo');
    };
    var fallback = function () {
      topo.terrainLive = false;
      if (flood) {
        topo.style.display = 'none';
        topo.parentNode.classList.remove('is-live');
        return;
      }
      // A canvas which has held WebGL cannot acquire a 2D context. Replace it before drawing the static map.
      freshCanvas();
      drawTopo();
      if (!fallbackResize) {
        fallbackResize = true;
        var rt;
        addEventListener('resize', function () { clearTimeout(rt); rt = setTimeout(function () { if (!topo.terrainLive) drawTopo(); }, 150); });
      }
    };
    // The living terrain: the same contour lines, drawn on the GPU from a slowly warping field, so the ground drifts and
    // folds like the reference's. 30 fps is plenty for something this slow; it pauses whenever its canvas is off screen.
    var liveTopo = function () {
      if (topoContexts >= topoContextLimit) return false;
      var tg = topo.getContext('webgl', { alpha: true, premultipliedAlpha: true, antialias: false });
      if (!tg) return false;
      topoContexts++;
      var terrainLost = false, raf = 0, observer;
      var release = function () {
        if (terrainLost) return;
        terrainLost = true; topoContexts--; topo.terrainLive = false;
        cancelAnimationFrame(raf);
        if (observer) observer.disconnect();
        var extension = tg.getExtension('WEBGL_lose_context');
        if (extension && !tg.isContextLost()) extension.loseContext();
        releaseLive = null;
      };
      if (!tg.getExtension('OES_standard_derivatives')) { release(); return false; }
      var sh = function (type, src) { var s = tg.createShader(type); tg.shaderSource(s, src); tg.compileShader(s); return s; };
      var tp = tg.createProgram();
      tg.attachShader(tp, sh(tg.VERTEX_SHADER, 'attribute vec2 p; void main() { gl_Position = vec4(p, 0., 1.); }'));
      tg.attachShader(tp, sh(tg.FRAGMENT_SHADER, [
        '#extension GL_OES_standard_derivatives : enable',
        'precision highp float;',
        'uniform vec2 uRes, uOff; uniform float uTime, uRise;',
        NOISE,
        'void main() {',
        '  vec2 p = (gl_FragCoord.xy + uOff) / 360.; float t = uTime * .05;',
        '  vec2 w = vec2(noise(p * .6 + vec2(t, 1.7)), noise(p * .6 + vec2(4.3, -t))) - .5;', // a slow warp folds the terrain
        '  float v = fbm(p + w * 1.1 + vec2(t * .5, t * .3)) * ' + (topo.weave ? '27.' : '9.') + ';',
        '  float dl = abs(fract(v - .5) - .5) / max(fwidth(v), 1e-4);', // distance to the nearest contour, in pixels
        '  float a = (1. - smoothstep(.35, 1.35, dl)) * .19;',
        flood ? floodShader(flood)
          : topo.weave ? '  float k = a * 2.1; gl_FragColor = vec4(vec3(174., 213., 52.) / 255. * k, k);' : '  gl_FragColor = vec4(vec3(.47, .5, .36) * a, a);',
        '}'
      ].join('\n')));
      tg.bindAttribLocation(tp, 0, 'p');
      tg.linkProgram(tp);
      if (!tg.getProgramParameter(tp, tg.LINK_STATUS)) { release(); return false; }
      tg.useProgram(tp);
      tg.bindBuffer(tg.ARRAY_BUFFER, tg.createBuffer());
      tg.bufferData(tg.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), tg.STATIC_DRAW);
      tg.enableVertexAttribArray(0);
      tg.vertexAttribPointer(0, 2, tg.FLOAT, false, 0, 0);
      var uR = tg.getUniformLocation(tp, 'uRes'), uT = tg.getUniformLocation(tp, 'uTime'), uO = tg.getUniformLocation(tp, 'uOff'), uL = tg.getUniformLocation(tp, 'uRise'), seen = true, last = 0, was = 0;
      topo.addEventListener('webglcontextlost', function () {
        if (terrainLost) return; // An off-screen section was deliberately released.
        release();
        if (solutionTerrain) solutionTerrain.classList.remove('has-topo');
        fallback();
      }, { once: true });
      // A screen of margin, so the lines are already moving when the section arrives.
      if (!managed && 'IntersectionObserver' in window) {
        observer = new IntersectionObserver(function (es) { seen = es[0].isIntersecting; }, { rootMargin: '100% 0px' });
        observer.observe(topo);
      }
      var loop = function (now) {
        if (terrainLost) return;
        raf = requestAnimationFrame(loop);
        // A flood on the move draws every frame; the rest of the time 30 fps is plenty.
        if (!seen || d.hidden || now - last < (flood && flood.rise !== was ? 16 : 33)) return;
        last = now;
        var w = topo.clientWidth, h = topo.clientHeight;
        if (topo.width !== w || topo.height !== h) { topo.width = w; topo.height = h; tg.viewport(0, 0, w, h); }
        tg.uniform2f(uR, w, h);
        var box = topo.getBoundingClientRect();
        tg.uniform2f(uO, box.left, from ? from.getBoundingClientRect().top - box.bottom : 0);
        topo.terrainTime = now / 1000;
        tg.uniform1f(uT, topo.terrainTime);
        if (flood) { tg.uniform1f(uL, flood.rise); was = flood.rise; }
        tg.drawArrays(tg.TRIANGLES, 0, 3);
        if (solutionTerrain && !solutionTerrain.classList.contains('has-topo')) solutionTerrain.classList.add('has-topo');
      };
      releaseLive = release;
      raf = requestAnimationFrame(loop);
      return true;
    };
    // The footer and the fluid joins add no GPU work on phones, where WebGL contexts are scarce: a resting arc and a drawn map,
    // as under reduced motion.
    var lean = !!topo.closest('.foot, .flood-join') && !matchMedia(topo.closest('.foot') ? '(min-width: 1100px)' : '(min-width: 900px)').matches;
    // Without motion or WebGL the flood stays what the CSS draws: a resting arc.
    if (flood) {
      if (!still && !lean) {
        if (liveTopo()) topo.parentNode.classList.add('is-live');
        else fallback();
      }
      return;
    }
    if (topo.weave && lean) { topo.style.display = 'none'; return; }
    if (!still && !lean && managed) {
      var holder = topo.parentNode, started = false;
      var nearby = function (seen) {
        if (seen && !started) {
          started = true;
          topo.terrainLive = liveTopo();
          if (!topo.terrainLive) fallback();
        } else if (!seen && releaseLive) {
          releaseLive(); freshCanvas(); started = false;
        }
      };
      var box = holder.getBoundingClientRect();
      nearby(box.bottom >= -innerHeight && box.top <= innerHeight * 2);
      new IntersectionObserver(function (es) { nearby(es[0].isIntersecting); }, { rootMargin: innerHeight + 'px 0px' }).observe(holder);
      return;
    }
    topo.terrainLive = !still && !lean && liveTopo();
    if (!topo.terrainLive) fallback();
  };
  // Viewport-sized contour ground. CSS and the SVG remain usable without WebGL.
  var createWorkTerrain = function (section, canvas) {
    var surface = section.closest('.workscape-pair') || section;
    var gl, program, buffer, vertex, fragment, uniforms, observer, raf = 0;
    var dead = false, lost = false, ready = false, seen = false, painted = false, last = 0, progress = -1;
    var ground = [0, 0, 0], line = [0, 0, 0];
    var pointer = { x: .5, y: .5, tx: .5, ty: .5, vx: 0, vy: 0, dx: 0, dy: 0, power: 0, force: 0, entered: false };
    var boundary = surface.previousElementSibling;
    var priorTerrain = boundary && boundary.matches('.tools--flow, .statement') ? boundary.querySelector('.topo-bg') : null;
    var startGround = [40, 44, 32], endGround = [244, 244, 237];
    var startLine = [54, 59, 37], endLine = [212, 215, 202];
    var clamp = function (v, a, b) { return Math.max(a, Math.min(b, v)); };
    var setProgress = function (value) {
      if (dead) return;
      var p = clamp(Number(value) || 0, 0, 1);
      if (p === progress) return;
      progress = p;
      var bg = [], stroke = [];
      for (var i = 0; i < 3; i++) {
        var b = startGround[i] + (endGround[i] - startGround[i]) * p;
        var l = startLine[i] + (endLine[i] - startLine[i]) * p;
        ground[i] = b / 255; line[i] = l / 255;
        bg.push(Math.round(b)); stroke.push(Math.round(l));
      }
      section.style.setProperty('--work-ground', 'rgb(' + bg.join(',') + ')');
      surface.style.setProperty('--work-ground', 'rgb(' + bg.join(',') + ')');
      section.style.setProperty('--work-line', 'rgb(' + stroke.join(',') + ')');
      section.classList.toggle('is-light', p > .42);
    };
    var stop = function () { if (raf) cancelAnimationFrame(raf); raf = 0; last = 0; };
    var schedule = function () {
      if (!dead && !lost && ready && seen && !d.hidden && !raf) raf = requestAnimationFrame(frame);
    };
    var updateSeen = function () {
      var r = surface.getBoundingClientRect();
      seen = r.bottom > 0 && r.top < innerHeight && r.right > 0 && r.left < innerWidth;
      if (seen && !d.hidden) schedule(); else stop();
    };
    var visibility = function () { if (d.hidden) stop(); else updateSeen(); };
    var move = function (event) {
      if (!seen || dead) return;
      var r = canvas.getBoundingClientRect();
      if (!r.width || !r.height) return;
      var x = clamp((event.clientX - r.left) / r.width, 0, 1);
      var y = 1 - clamp((event.clientY - r.top) / r.height, 0, 1);
      if (!pointer.entered) { pointer.x = pointer.tx = x; pointer.y = pointer.ty = y; pointer.entered = true; }
      var dx = x - pointer.tx, dy = y - pointer.ty;
      pointer.tx = x; pointer.ty = y;
      pointer.dx = clamp(dx * 16, -1, 1); pointer.dy = clamp(dy * 16, -1, 1);
      pointer.force = Math.max(pointer.force, clamp(Math.sqrt(dx * dx + dy * dy) * 24, 0, 1));
    };
    var leave = function () { pointer.force = 0; pointer.dx = pointer.dy = 0; pointer.entered = false; };
    var contextLost = function () {
      lost = true; ready = false; stop();
      section.classList.remove('has-live-terrain', 'can-live-terrain');
    };
    var release = function () {
      if (!gl) return;
      try {
        if (buffer) gl.deleteBuffer(buffer);
        if (program) gl.deleteProgram(program);
        if (vertex) gl.deleteShader(vertex);
        if (fragment) gl.deleteShader(fragment);
      } catch (_) {}
      buffer = program = vertex = fragment = null;
    };
    var frame = function (now) {
      raf = 0;
      if (dead || lost || !ready || !seen || d.hidden) return;
      if (last && now - last < 1000 / 30) { schedule(); return; }
      var dt = last ? Math.min((now - last) / 1000, .1) : 1 / 30;
      last = now;
      var ratio = Math.min(window.devicePixelRatio || 1, 1);
      var w = Math.round(canvas.clientWidth * ratio), h = Math.round(canvas.clientHeight * ratio);
      if (!w || !h) { schedule(); return; }
      try {
        if (canvas.width !== w || canvas.height !== h) {
          canvas.width = w; canvas.height = h; gl.viewport(0, 0, w, h);
        }
        var ease = 1 - Math.exp(-dt * 7), fade = Math.exp(-dt * 3);
        pointer.x += (pointer.tx - pointer.x) * ease; pointer.y += (pointer.ty - pointer.y) * ease;
        pointer.vx += (pointer.dx - pointer.vx) * ease; pointer.vy += (pointer.dy - pointer.vy) * ease;
        pointer.power += (pointer.force - pointer.power) * ease;
        pointer.force *= fade; pointer.dx *= fade; pointer.dy *= fade;
        gl.useProgram(program);
        gl.uniform2f(uniforms.res, w, h);
        gl.uniform2f(uniforms.css, canvas.clientWidth, canvas.clientHeight);
        var bounds = canvas.getBoundingClientRect(), sectionBounds = section.getBoundingClientRect();
        var anchor = afterStage || section;
        gl.uniform3f(uniforms.join, anchor.getBoundingClientRect().top, bounds.bottom, sectionBounds.top);
        gl.uniform1f(uniforms.joinEnabled, priorTerrain ? 1 : 0);
        gl.uniform1f(uniforms.sharedTime, priorTerrain && priorTerrain.terrainTime || now / 1000);
        gl.uniform1f(uniforms.time, now / 1000);
        gl.uniform3f(uniforms.ground, ground[0], ground[1], ground[2]);
        gl.uniform3f(uniforms.line, line[0], line[1], line[2]);
        gl.uniform2f(uniforms.pointer, pointer.x, pointer.y);
        gl.uniform2f(uniforms.velocity, pointer.vx, pointer.vy);
        gl.uniform1f(uniforms.power, pointer.power);
        gl.drawArrays(gl.TRIANGLES, 0, 3);
        if (!painted) {
          if (gl.getError() !== gl.NO_ERROR) throw new Error('Terrain draw unavailable');
          painted = true; section.classList.add('has-live-terrain');
        }
      } catch (_) {
        ready = false; stop(); release(); section.classList.remove('has-live-terrain', 'can-live-terrain');
        return;
      }
      schedule();
    };
    var destroy = function () {
      if (dead) return;
      dead = true; ready = false; stop();
      if (observer) observer.disconnect();
      section.removeEventListener('pointermove', move); section.removeEventListener('pointerleave', leave);
      d.removeEventListener('visibilitychange', visibility);
      window.removeEventListener('scroll', updateSeen); window.removeEventListener('resize', updateSeen);
      canvas.removeEventListener('webglcontextlost', contextLost);
      release();
      section.classList.remove('has-live-terrain', 'can-live-terrain', 'is-light');
      section.style.removeProperty('--work-ground'); section.style.removeProperty('--work-line');
      if (surface !== section) surface.style.removeProperty('--work-ground');
    };
    var api = { setProgress: setProgress, destroy: destroy };
    setProgress(0);
    try {
      if (priorTerrain && !priorTerrain.terrainLive) return api;
      gl = canvas.getContext('webgl', { alpha: false, antialias: false, depth: false, stencil: false, powerPreference: 'low-power' });
      if (!gl || !gl.getExtension('OES_standard_derivatives')) return api;
      var compile = function (type, source) {
        var shader = gl.createShader(type);
        if (!shader) throw new Error('Terrain shader unavailable');
        gl.shaderSource(shader, source); gl.compileShader(shader);
        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) { gl.deleteShader(shader); throw new Error('Terrain shader unavailable'); }
        return shader;
      };
      vertex = compile(gl.VERTEX_SHADER, 'attribute vec2 p; void main() { gl_Position = vec4(p, 0., 1.); }');
      fragment = compile(gl.FRAGMENT_SHADER, [
        '#extension GL_OES_standard_derivatives : enable',
        'precision highp float;',
        'uniform vec2 uRes, uCSS, uPointer, uVelocity; uniform vec3 uJoin;',
        'uniform float uTime, uSharedTime, uPower, uJoinEnabled;',
        'uniform vec3 uGround, uLine;',
        NOISE,
        'void main() {',
        '  vec2 uv = gl_FragCoord.xy / uRes;',
        '  vec2 p = gl_FragCoord.xy / max(uRes.y, 1.) * 2.2;',
        '  float t = uTime * .1;',
        '  vec2 warp = vec2(noise(p * .45 + vec2(t * .25, 1.7)), noise(p * .45 + vec2(4.3, -t * .2))) - .5;',
        '  vec2 delta = uv - uPointer; delta.x *= uRes.x / uRes.y;',
        '  float nearPointer = exp(-dot(delta, delta) * 8.);',
        '  p += warp * 1.1 + uVelocity * nearPointer * .15 * uPower;',
        '  vec2 pixel = gl_FragCoord.xy * uCSS / uRes;',
        '  float depth = uJoin.y - pixel.y - uJoin.z;',
        '  float blend = uJoinEnabled > .5 ? smoothstep(0., 420., depth) : 1.;',
        // Align the means by an integer contour period, so morphing density does not add a horizontal band.
        '  float field = fbm(p + vec2(t * .08, -t * .04)) * 3. + 3.;',
        '  if (blend < 1.) {',
        '    vec2 q = (pixel + vec2(0., uJoin.x - uJoin.y)) / 360.;',
        '    float clock = uSharedTime * .05;',
        '    vec2 bend = vec2(noise(q * .6 + vec2(clock, 1.7)), noise(q * .6 + vec2(4.3, -clock))) - .5;',
        '    float shared = fbm(q + bend * 1.1 + vec2(clock * .5, clock * .3)) * 9.;',
        '    field = mix(shared, field, blend);',
        '  }',
        '  float dist = abs(fract(field - .5) - .5) / max(fwidth(field), mix(.0001, .00001, blend));',
        '  float ink = 1. - smoothstep(mix(.35, .25, blend), mix(1.35, 1.25, blend), dist);',
        '  vec3 joinGround = vec3(35., 39., 25.) / 255.;',
        '  vec3 joinLine = mix(joinGround, vec3(.47, .5, .36), .19);',
        '  vec3 ground = mix(joinGround, uGround, blend), line = mix(joinLine, uLine, blend);',
        '  gl_FragColor = vec4(mix(ground, line, ink), 1.);',
        '}'
      ].join('\n'));
      program = gl.createProgram();
      if (!program) throw new Error('Terrain program unavailable');
      gl.attachShader(program, vertex); gl.attachShader(program, fragment);
      gl.bindAttribLocation(program, 0, 'p'); gl.linkProgram(program);
      if (!gl.getProgramParameter(program, gl.LINK_STATUS)) throw new Error('Terrain program unavailable');
      buffer = gl.createBuffer();
      if (!buffer) throw new Error('Terrain buffer unavailable');
      gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
      gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);
      gl.enableVertexAttribArray(0); gl.vertexAttribPointer(0, 2, gl.FLOAT, false, 0, 0);
      uniforms = {};
      [['res', 'uRes'], ['css', 'uCSS'], ['join', 'uJoin'], ['joinEnabled', 'uJoinEnabled'], ['sharedTime', 'uSharedTime'], ['time', 'uTime'], ['ground', 'uGround'], ['line', 'uLine'], ['pointer', 'uPointer'], ['velocity', 'uVelocity'], ['power', 'uPower']].forEach(function (pair) {
        uniforms[pair[0]] = gl.getUniformLocation(program, pair[1]);
      });
      ready = true; section.classList.add('can-live-terrain');
      canvas.addEventListener('webglcontextlost', contextLost);
      section.addEventListener('pointermove', move, { passive: true }); section.addEventListener('pointerleave', leave);
      d.addEventListener('visibilitychange', visibility);
      if ('IntersectionObserver' in window) {
        observer = new IntersectionObserver(function (entries) {
          seen = entries[0].isIntersecting;
          if (seen && !d.hidden) schedule(); else stop();
        });
        observer.observe(surface);
      } else {
        window.addEventListener('scroll', updateSeen, { passive: true }); window.addEventListener('resize', updateSeen);
      }
      updateSeen();
    } catch (_) {
      ready = false; stop(); release(); section.classList.remove('has-live-terrain', 'can-live-terrain');
    }
    return api;
  };

  // Carry the homepage's contour ground through every masterclass section, and on into the homepage and Solutions enquiries (and About's hero, principles, founder and visit, and the legal pages' text).
  d.querySelectorAll('.course-page > section, .hero--stage ~ .lead--stage, .solutions-page > .lead--stage,.about-page > :is(.ab-notes, .team, .contact), .about-page:not(.contact-page) > .journey-hero, .journey-hero--plain ~ .sec--rich_text').forEach(function (section) {
    if (section.querySelector('.topo-bg')) return;
    var terrain = d.createElement('canvas');
    terrain.className = 'topo-bg';
    terrain.setAttribute('aria-hidden', 'true');
    section.prepend(terrain);
  });
  // Contact: Vadodara's local time in the direct-lines board (hidden without JS).
  d.querySelectorAll('[data-clock]').forEach(function (el) {
    var tick = function () { el.textContent = new Date().toLocaleTimeString('en-US', { timeZone: 'Asia/Kolkata', hour: 'numeric', minute: '2-digit' }) + ' IST'; };
    tick(); el.hidden = false; setInterval(tick, 15000);
  });
  // Fluid joins: a section whose CSS sets --flood rises over the one before it the way the homepage's flood does, its ground
  // climbing the contour map with a moving edge. --flood is `auto` (the section's own background colour) or an "r g b" triple.
  d.querySelectorAll('main section').forEach(function (section) {
    var want = getComputedStyle(section).getPropertyValue('--flood').trim();
    if (!want || want === 'none' || !section.previousElementSibling || section.parentElement.closest('section')) return;
    var rgb = (want === 'auto' ? getComputedStyle(section).backgroundColor : want).match(/[\d.]+/g);
    if (!rgb || rgb.length < 3 || rgb[3] === '0') return;
    rgb = rgb.slice(0, 3);
    var join = d.createElement('div'), canvas = d.createElement('canvas');
    join.className = 'flood-join';
    join.setAttribute('aria-hidden', 'true');
    join.style.setProperty('--flood-c', 'rgb(' + rgb.join(',') + ')');
    canvas.className = 'topo-bg';
    var fluid = section.closest('.solutions-page') ? .45 : 1;
    if (section.closest('.corporate-page') && section.matches('.statement, .co-kit')) fluid = .25;
    canvas.flood = { rise: 70, fluid: fluid, base: 'vec3(' + rgb.map(function (n) { return (n / 255).toFixed(3); }).join(', ') + ')' };
    if (section.matches('.contact-page > .ab-work, .careers-page > .lead--careers')) {
      canvas.flood.profile = 'contact';
      join.classList.add('flood-join--contact');
    }
    join.appendChild(canvas);
    section.classList.add('has-flood');
    section.prepend(join);
    if (canvas.flood.profile === 'contact') canvas.flood.rise = Math.min(56, join.offsetHeight * .6);
  });
  var afterStage = (d.querySelector('.hero--stage') || {}).nextElementSibling;
  d.querySelectorAll('.topo-bg').forEach(function (c) {
    // The shared gallery canvas also draws behind the comparison; keep only its later dark flood.
    if (c.parentNode.matches('.versus') && c.closest('.workscape-pair')) return;
    // Its contour lines at Solutions' 65% terrain strength, so the flood and the section under it read as one ground.
    if (c.parentNode.matches('.versus__dusk')) c.flood = { rise: 30, line: 'vec3(.329, .354, .255)' };
    if (c.parentNode.matches('.foot__rise')) c.flood = { rise: 30, base: 'vec3(174., 213., 52.) / 255.', line: 'vec3(.16, .2, 0.)', profile: 'calm' };
    topoField(c, c.closest('.course-page, .solutions-page') || afterStage || c.parentNode, c.flood); // inner pages: one map under every section
  });

  // The footer masthead clears over the panel; its generated robot remains one coherent cutout.
  d.querySelectorAll('[data-foot]').forEach(function (foot) {
    var bar = d.querySelector('[data-top]');
    if (bar) {
      var clear = function () { bar.classList.toggle('is-over-foot', innerWidth >= 1100 && innerHeight >= 620 && foot.getBoundingClientRect().top < 32); };
      addEventListener('scroll', clear, { passive: true });
      addEventListener('resize', clear);
      clear();
    }
  });

  // The masthead floats over a dark hero and turns solid once the hero has gone (on phones, as soon as the page moves).
  (function () {
    var bar = d.querySelector('[data-top]'), hero = d.querySelector('[data-stage], .sol-hero, .course-hero');
    if (!bar || !hero) return;
    var solid = function () { bar.classList.toggle('is-solid', innerWidth < 900 ? scrollY > 40 : hero.getBoundingClientRect().bottom < 64); };
    addEventListener('scroll', solid, { passive: true });
    solid();
  })();

  // The stage hero: a topographic ground, then the robot redrawn in WebGL with liquid neon blobs that reveal a second layer.
  d.querySelectorAll('[data-stage]').forEach(function (stage) {
    topoField(stage.querySelector('.stage__topo'), null);

    if (still) return; // the photograph alone, standing still
    var cv = stage.querySelector('.stage__gl'), img = stage.querySelector('.stage__robot');
    var g = cv.getContext('webgl', { alpha: true, antialias: false, premultipliedAlpha: true });
    if (!g) return;
    // Pass 1, the ink: a mask at half size that persists between frames. Each frame it drifts a little, softens and fades,
    // then takes the stroke the pointer made since the last frame as one capsule, so even a fast swipe is a single unbroken ribbon.
    var INK_FS = [
      'precision highp float;',
      'uniform sampler2D uPrev; uniform vec2 uRes, uA, uB; uniform float uR, uOn, uTime, uK, uS;',
      NOISE,
      'void main() {',
      '  vec2 p = gl_FragCoord.xy, uv = p / uRes, o = 1. / uRes;',
      '  vec2 q = uv - (vec2(noise(uv * 4. + uTime * .2), noise(uv * 4. + 9.1 - uTime * .2)) - .5) * 1.6 * o;',
      '  float v = texture2D(uPrev, q).r * .4 + (texture2D(uPrev, q + vec2(o.x, 0.)).r + texture2D(uPrev, q - vec2(o.x, 0.)).r',
      '    + texture2D(uPrev, q + vec2(0., o.y)).r + texture2D(uPrev, q - vec2(0., o.y)).r) * .15;',
      '  v = max(v * uK - .0016 * uS, 0.);', // fades by time, not frames; the constant keeps 8-bit values from stalling above zero
      '  vec2 pa = p - uA, ba = uB - uA;',
      '  float d = length(pa - ba * clamp(dot(pa, ba) / max(dot(ba, ba), 1e-3), 0., 1.));',
      '  v = max(v, (1. - smoothstep(uR * .3, uR, d)) * uOn);',
      '  gl_FragColor = vec4(v, v, v, 1.);',
      '}'
    ].join('\n');
    // Pass 2, the picture: the robot keyed off its black backdrop, and wherever the ink lies the hidden layer instead.
    // Over the robot that is its x-ray in neon (or the helmet image); over the black it is a faint olive tone, so the shape
    // carries on across the background without a cut, the way the reference's liquid sits on its white page.
    var FS = [
      'precision highp float;',
      'uniform sampler2D uBase, uHelm, uInk; uniform vec2 uRes; uniform vec4 uRect, uHRect; uniform float uHas, uTime, uScan; uniform vec3 uBg, uThink;',
      'const vec3 LIME = vec3(174., 213., 52.) / 255., INK = vec3(.05, .055, .035), TONE = vec3(.068, .08, .036);',
      NOISE,
      'float luma(vec3 c) { return dot(c, vec3(.299, .587, .114)); }',
      'float inside(vec2 uv) { return step(0., uv.x) * step(uv.x, 1.) * step(0., uv.y) * step(uv.y, 1.); }',
      'float edge(sampler2D t, vec2 uv, vec2 px) {',
      '  float l = luma(texture2D(t, uv - vec2(px.x, 0.)).rgb), r = luma(texture2D(t, uv + vec2(px.x, 0.)).rgb);',
      '  float u = luma(texture2D(t, uv - vec2(0., px.y)).rgb), b = luma(texture2D(t, uv + vec2(0., px.y)).rgb);',
      '  return length(vec2(r - l, b - u));',
      '}',
      // A thought crossing the crown, s from 0 to 1: its head sweeps from the left ear (angle 3.08) over the top to the right (.05).
      'float comet(float s, float ang) {',
      '  float d = ang - mix(3.08, .05, s);', // d > 0 is the tail
      '  return step(0., s) * (d > 0. ? exp(-d * 7.) : exp(-d * d * 300.)) * smoothstep(0., .06, s) * smoothstep(1., .94, s);',
      '}',
      'void main() {',
      '  vec2 p = vec2(gl_FragCoord.x, uRes.y - gl_FragCoord.y);',
      // The liquid's outline: the ink level, its edge eaten by slow noise so it reads as poured, never as a circle.
      '  float v = texture2D(uInk, gl_FragCoord.xy / uRes).r;',
      '  float k = v + (fbm(p / 85. + uTime * .1) - .5) * .4 + (noise(p / 16.) - .5) * .16 * (1. - v);', // fine erosion grows as the ink thins
      '  float m = smoothstep(.455, .47, k);',
      // A hairline at the edge, only where the edge is fresh and steep: a fading pool erodes, it never glows.
      '  vec2 iu = gl_FragCoord.xy / uRes, io = 4. / uRes;',
      '  float gd = length(vec2(texture2D(uInk, iu + vec2(io.x, 0.)).r - texture2D(uInk, iu - vec2(io.x, 0.)).r, texture2D(uInk, iu + vec2(0., io.y)).r - texture2D(uInk, iu - vec2(0., io.y)).r));',
      '  float band = smoothstep(.43, .452, k) * (1. - smoothstep(.458, .48, k)) * smoothstep(.015, .045, gd);',
      // The robot, keyed off its own black backdrop, with a solid core over the head and neck so the dark mechanics stay whole.
      '  vec2 uv = (p - uRect.xy) / uRect.zw;',
      '  vec3 base = texture2D(uBase, clamp(uv, 0., 1.)).rgb;',
      '  float core = 1. - smoothstep(.85, 1., length((uv - vec2(.5, .42)) / vec2(.2, .3)));',
      '  float ka = inside(uv) * max(smoothstep(.03, .09, distance(base, uBg)), core) * smoothstep(0., .12, uv.x) * smoothstep(1., .88, uv.x) * smoothstep(1., .9, uv.y);',
      '  float e = edge(uBase, uv, 1.5 / uRect.zw) * inside(uv);',
      '  vec2 hv = (p - uHRect.xy) / uHRect.zw;',
      '  vec3 helm = texture2D(uHelm, clamp(hv, 0., 1.)).rgb;',
      '  float ha = inside(hv) * smoothstep(.04, .16, max(helm.r, max(helm.g, helm.b)));',
      '  vec3 xray = INK + LIME * (clamp(e * 2.6, 0., 1.) + pow(luma(base), 2.4) * .15);',
      '  float solid = uHas > .5 ? ha : ka;',
      '  vec3 lay = mix(TONE, uHas > .5 ? helm : xray, solid);',
      '  vec3 c = mix(base * ka, lay, m); float a = mix(ka, 1., m);', // premultiplied
      // The scan: once a cycle a neon line passes down the robot (uScan, in its own height) and leaves a short x-ray wake.
      '  float sd = uv.y - uScan, on = step(-.5, uScan);',
      '  float ks = ka * smoothstep(.03, .12, distance(base, uBg));', // the robot's own surfaces only, never the backdrop inside its box
      '  float wake = ks * (1. - m) * on * step(sd, 0.) * exp(min(sd, 0.) * 11.);', // min() keeps exp finite while the scan is parked
      '  c = mix(c, xray * ka, wake * .8);',
      '  c = mix(c, LIME, ks * on * (exp(-sd * sd * 45000.) * .8 + exp(-sd * sd * 2500.) * .05));',
      // The thinking: each piece of work that lands in the left ear crosses the head to the right ear, where its result leaves,
      // as a comet lighting only the head's own seams. hq is the crown in photo heights from its centre between the ears.
      '  vec2 hq = (uv - vec2(.4975, .333)) * vec2(.807, 1.);',
      '  float ang = atan(-hq.y, hq.x), hr = length(hq);',
      '  float th = (comet(uThink.x, ang) + comet(uThink.y, ang) + comet(uThink.z, ang)) * smoothstep(.02, .05, hr) * smoothstep(.16, .145, hr);',
      '  c = mix(c, LIME, clamp(e * 2.6, 0., 1.) * min(th, 1.) * ks * (1. - m) * .9);',
      // The hairline: clear over the robot, only a whisper over the black.
      '  float lip = band * mix(.14, .55, solid);',
      '  c = mix(c, LIME, lip); a = mix(a, 1., lip);',
      // At rest, a faint wireframe of the helmet layer sits over the face.
      '  float w = uHas > .5 ? clamp(edge(uHelm, hv, 1.5 / uHRect.zw) * inside(hv) * 2.6, 0., 1.) * (1. - m) * .22 : 0.;',
      '  c = mix(c, INK, w); a = mix(a, 1., w);',
      '  gl_FragColor = vec4(c, a);',
      '}'
    ].join('\n');
    var shader = function (type, src) { var s = g.createShader(type); g.shaderSource(s, src); g.compileShader(s); return s; };
    var program = function (fs, names) {
      var p = g.createProgram();
      g.attachShader(p, shader(g.VERTEX_SHADER, 'attribute vec2 p; void main() { gl_Position = vec4(p, 0., 1.); }'));
      g.attachShader(p, shader(g.FRAGMENT_SHADER, fs));
      g.bindAttribLocation(p, 0, 'p');
      g.linkProgram(p);
      if (!g.getProgramParameter(p, g.LINK_STATUS)) return null;
      var u = { p: p };
      names.forEach(function (n) { u[n] = g.getUniformLocation(p, n); });
      return u;
    };
    var U = program(FS, ['uBase', 'uHelm', 'uInk', 'uRes', 'uRect', 'uHRect', 'uHas', 'uTime', 'uScan', 'uBg', 'uThink']);
    var I = program(INK_FS, ['uPrev', 'uRes', 'uA', 'uB', 'uR', 'uOn', 'uTime', 'uK', 'uS']);
    if (!U || !I) return;
    g.bindBuffer(g.ARRAY_BUFFER, g.createBuffer());
    g.bufferData(g.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), g.STATIC_DRAW);
    g.enableVertexAttribArray(0);
    g.vertexAttribPointer(0, 2, g.FLOAT, false, 0, 0);
    var texture = function (unit, source, w, h) {
      g.activeTexture(g.TEXTURE0 + unit);
      var tx = g.createTexture();
      g.bindTexture(g.TEXTURE_2D, tx);
      [g.TEXTURE_WRAP_S, g.TEXTURE_WRAP_T].forEach(function (k) { g.texParameteri(g.TEXTURE_2D, k, g.CLAMP_TO_EDGE); });
      g.texParameteri(g.TEXTURE_2D, g.TEXTURE_MIN_FILTER, g.LINEAR);
      g.texParameteri(g.TEXTURE_2D, g.TEXTURE_MAG_FILTER, g.LINEAR);
      if (source) g.texImage2D(g.TEXTURE_2D, 0, g.RGBA, g.RGBA, g.UNSIGNED_BYTE, source);
      else g.texImage2D(g.TEXTURE_2D, 0, g.RGBA, w || 1, h || 1, 0, g.RGBA, g.UNSIGNED_BYTE, null);
      return tx;
    };
    texture(1, null); // an empty helmet until one loads
    g.useProgram(U.p);
    g.uniform1i(U.uBase, 0);
    g.uniform1i(U.uHelm, 1);
    g.uniform1i(U.uInk, 2);
    g.uniform1f(U.uHas, 0);
    g.uniform3f(U.uBg, 10 / 255, 11 / 255, 7 / 255);
    g.useProgram(I.p);
    g.uniform1i(I.uPrev, 2);
    var helmSrc = cv.getAttribute('data-reveal');
    if (helmSrc) {
      var helm = new Image();
      helm.onload = function () { texture(1, helm); g.useProgram(U.p); g.uniform1f(U.uHas, 1); };
      helm.src = helmSrc;
    }
    // Two half-size ink buffers, read from one and written to the other each frame.
    var inks = [], fbos = [], MW = 0, MH = 0, src = 0;
    var makeInk = function () {
      for (var i = 0; i < 2; i++) {
        if (inks[i]) { g.deleteTexture(inks[i]); g.deleteFramebuffer(fbos[i]); }
        inks[i] = texture(2, null, MW, MH);
        fbos[i] = g.createFramebuffer();
        g.bindFramebuffer(g.FRAMEBUFFER, fbos[i]);
        g.framebufferTexture2D(g.FRAMEBUFFER, g.COLOR_ATTACHMENT0, g.TEXTURE_2D, inks[i], 0);
        g.clearColor(0, 0, 0, 1);
        g.clear(g.COLOR_BUFFER_BIT);
      }
      g.bindFramebuffer(g.FRAMEBUFFER, null);
    };

    var dpr = Math.min(window.devicePixelRatio || 1, 1.5), aim = null, pen = null, lastMove = -1e9, wander = null;
    stage.addEventListener('pointermove', function (e) {
      var r = cv.getBoundingClientRect();
      aim = { x: (e.clientX - r.left) * dpr, y: (e.clientY - r.top) * dpr };
      if (!pen) pen = { x: aim.x, y: aim.y };
      lastMove = performance.now();
    }, { passive: true });
    stage.addEventListener('pointerleave', function () { aim = pen = null; });
    // The helmet sits over the face: centred, as wide as the head plus a little, its visor on the robot's eye line.
    var HELM = { width: 0.66, eye: 0.41, visor: 0.415 };
    // One clock for the automation loop and the scan, as fractions of the cycle: three inputs travel in, the robot scans, three outputs travel out.
    // The report lands by .55 so its chart holds for the last ~3 s.
    var CYCLE = 7000, flowT0 = 1e12, SCAN = [.26, .4];
    var IN = [[.03, .16], [.08, .21], [.13, .26]], OUT = [[.36, .45], [.41, .5], [.46, .55]];
    // The reference's helmet chin guard, retold: a neon livery band runs round the neck and a soft light sweeps behind. They are 2D
    // canvases either side of the WebGL robot: the sweep behind it, the band's front half over it (no back half). They rise in once the page has
    // entered, then bob and lean a little toward the pointer. Positions are fractions of the photograph's box.
    var rigB = stage.querySelector('.stage__rig--back'), rigF = stage.querySelector('.stage__rig--front');
    var bx = rigB && rigB.getContext('2d'), fx = rigF && rigF.getContext('2d');
    var LIVERY = 'ARTIFICIA  ✦  AI AUTOMATION  ✦  AGENTIC WORKFLOWS  ✦  BUSINESS SOFTWARE  ✦  CORPORATE AI TRAINING  ✦  ';
    var BAND = { y: .6, r: .2, h: .027 }, TILT = .24; // TILT: how far we look down on the band, in radians
    var CAP = { y: .345, rx: .15, ry: .14, tilt: .38, base: 4 }; // the neural cap over the skull: centre (at the temples) and radii in photo heights, base latitude in degrees
    var rigIn = 0, spin = 0, glyphs = [], glyphFor = '';
    // Rebuild the band lettering with the real site font after a slow font download.
    if (d.fonts) d.fonts.ready.then(function () { glyphFor = ''; });
    var drawRig = function (now, t, ir, cr) {
      if (!bx || !fx) return;
      var rd = Math.min(window.devicePixelRatio || 1, 1.5), cw = cr.width, ch = cr.height, Wp = Math.round(cw * rd), Hp = Math.round(ch * rd);
      [rigB, rigF].forEach(function (c) { if (c.width !== Wp || c.height !== Hp) { c.width = Wp; c.height = Hp; } });
      [bx, fx].forEach(function (c) { c.setTransform(1, 0, 0, 1, 0, 0); c.globalAlpha = 1; c.clearRect(0, 0, Wp, Hp); c.setTransform(rd, 0, 0, rd, 0, 0); });
      if (!rigIn && root.classList.contains('is-in')) rigIn = now + 450;
      if (!rigIn || now < rigIn) return;
      var en = Math.min((now - rigIn) / 1700, 1), al = 1 - Math.pow(1 - en, 3);
      var ox = ir.left - cr.left, oy = ir.top - cr.top, w = ir.width, h = ir.height, cx = ox + w / 2;
      var ca = Math.cos(TILT), sa = Math.sin(TILT);
      spin += ((aim ? (aim.x / dpr / cw - .5) * .9 : 0) - spin) * .05;

      // The light sweep, behind everything: a wide soft ribbon from the lower left, up behind the neck.
      var by = oy + h * BAND.y + (1 - al) * h * .06 + Math.sin(t * 1.1 + 2) * h * .003, top = [], bot = [];
      for (var s = 0; s <= 24; s++) {
        var u = s / 24, sm = u * u * (3 - 2 * u), x = -.05 * cw + u * 1.1 * cw;
        var yc = by + h * .13 + (h * -.22) * sm + Math.sin(u * 3.2 + t * .45) * h * .018, th = h * .07 * Math.pow(Math.sin(Math.PI * u), .7) * (1 + .25 * Math.sin(t * .6 + u * 4));
        top.push([x, yc - th / 2]);
        bot.unshift([x, yc + th / 2]);
      }
      var gr = bx.createLinearGradient(0, 0, cw, 0);
      gr.addColorStop(0, 'rgba(200, 230, 80, 0)');
      gr.addColorStop(.3, 'rgba(200, 230, 80, .065)');
      gr.addColorStop(.75, 'rgba(200, 230, 80, .04)');
      gr.addColorStop(1, 'rgba(200, 230, 80, 0)');
      bx.globalAlpha = al;
      bx.fillStyle = gr;
      bx.beginPath();
      top.concat(bot).forEach(function (p, i) { if (i) bx.lineTo(p[0], p[1]); else bx.moveTo(p[0], p[1]); });
      bx.fill();

      var i;
      // The livery band: a neon cylinder round the neck, its text running right to left across the front.
      var BR = w * BAND.r, bh = h * BAND.h;
      var bp = function (thv, yy) { var Z = BR * Math.cos(thv); return [cx + BR * Math.sin(thv), by - yy * ca + Z * sa, Z]; };
      // Only the front half is drawn. Everything on it fades out toward the sides (cosθ → 0), so the band wraps out of sight with no hard ends.
      var edge = function (co) { var f = Math.min(co / .6, 1); return f * f * (3 - 2 * f); };
      // A colour across x (x = cx + BR·sinθ), optionally lit like a cylinder: one gradient, so no seams.
      var shade = function (r, g, b, a, lit) {
        var gr = fx.createLinearGradient(cx - BR, 0, cx + BR, 0);
        for (var k = 0; k <= 32; k++) {
          var sx = k / 16 - 1, co = Math.sqrt(1 - sx * sx), l = lit ? .42 + .58 * co : 1;
          gr.addColorStop(k / 32, 'rgba(' + Math.round(r * l) + ',' + Math.round(g * l) + ',' + Math.round(b * l) + ',' + (a * edge(co)).toFixed(3) + ')');
        }
        return gr;
      };
      var arc = function (yy, style, lw) {
        fx.beginPath();
        for (var n = -24; n <= 24; n++) { var q = bp(n / 48 * Math.PI, yy); if (n === -24) fx.moveTo(q[0], q[1]); else fx.lineTo(q[0], q[1]); }
        fx.strokeStyle = style; fx.lineWidth = lw; fx.lineCap = 'butt'; fx.lineJoin = 'round'; fx.stroke();
      };
      fx.globalAlpha = al;
      arc(0, shade(174, 213, 52, .07), bh * 1.9); // a halo spilling onto the neck
      fx.fillStyle = shade(174, 213, 52, 1, true);
      fx.beginPath();
      for (i = -24; i <= 24; i++) { var q0 = bp(i / 48 * Math.PI, bh / 2); if (i === -24) fx.moveTo(q0[0], q0[1]); else fx.lineTo(q0[0], q0[1]); }
      for (i = 24; i >= -24; i--) { var q1 = bp(i / 48 * Math.PI, -bh / 2); fx.lineTo(q1[0], q1[1]); }
      fx.closePath();
      fx.fill();
      arc(bh / 2, shade(242, 255, 184, .9), 1.1);
      fx.font = '800 ' + Math.max(8, Math.round(bh * .46)) + 'px "Artificia Sans", system-ui, sans-serif';
      if ('fontStretch' in fx) fx.fontStretch = 'expanded';
      var key = fx.font + '|' + Math.round(BR);
      if (glyphFor !== key) {
        // Lay the livery round the full circle, repeated to fit with spacing stretched a touch so it closes seamlessly.
        glyphFor = key;
        glyphs = [];
        var ws = LIVERY.split('').map(function (chr) { return fx.measureText(chr).width + bh * .05; }), tot = ws.reduce(function (x, y) { return x + y; }, 0);
        var reps = Math.max(1, Math.round(2 * Math.PI * BR / tot)), per = 2 * Math.PI / (reps * tot), acc = 0;
        for (var r2 = 0; r2 < reps; r2++) LIVERY.split('').forEach(function (chr, n) { glyphs.push([chr, (acc + ws[n] / 2) * per]); acc += ws[n]; });
      }
      var brot = -t * .3 - spin * .5;
      fx.textAlign = 'center';
      fx.textBaseline = 'middle';
      fx.fillStyle = '#10120A';
      glyphs.forEach(function (gl) {
        var thv = gl[1] + brot, cs = Math.cos(thv);
        if (cs < .1 || gl[0] === ' ') return;
        var q = bp(thv, 0);
        fx.globalAlpha = al * (.3 + .7 * cs) * edge(cs);
        fx.save();
        fx.translate(q[0], q[1]);
        fx.rotate(Math.atan2(-BR * Math.sin(thv) * sa, BR * cs));
        fx.scale(cs, 1);
        fx.fillText(gl[0], 0, bh * .03);
        fx.restore();
      });

      // The neural cap, balancing the band: a holographic wireframe of latitudes and meridians wrapping the skull, curving the
      // other way to the band (we look up at it), turning slowly, with signals running up the meridians to the crown.
      // Front surface only, fading at the silhouette; the back is hidden by the head. Lines are lime over a faint ink stroke so they read on the white shell.
      var RX = h * CAP.rx, RY = h * CAP.ry, sy = oy + h * CAP.y + (1 - al) * h * .03, ct = Math.cos(CAP.tilt), st = Math.sin(CAP.tilt);
      var lw = Math.max(1, h * .0011), lrot = t * .12 + spin * .4, D = Math.PI / 180;
      var sp = function (la, lo) {
        var y = Math.sin(la), z = Math.cos(la) * Math.cos(lo);
        return [cx + RX * Math.cos(la) * Math.sin(lo), sy - RY * (y * ct + z * st), z * ct - y * st];
      };
      var vis = function (z) { var f = Math.min(Math.max(z / .45, 0), 1); return f * f * (3 - 2 * f); };
      var line = function (pts, a) {
        for (var n = 1; n < pts.length; n++) {
          var p0 = pts[n - 1], p1 = pts[n], v = vis((p0[2] + p1[2]) / 2) * a;
          if (v < .01) continue;
          fx.globalAlpha = al * v;
          fx.beginPath(); fx.moveTo(p0[0], p0[1]); fx.lineTo(p1[0], p1[1]);
          fx.strokeStyle = 'rgba(16, 18, 10, .3)'; fx.lineWidth = lw * 3; fx.stroke();
          fx.strokeStyle = '#AED534'; fx.lineWidth = lw; fx.stroke();
        }
      };
      var n2, pts;
      [[CAP.base, .95], [30, .5], [52, .45], [72, .35]].forEach(function (L) {
        for (pts = [], n2 = 0; n2 <= 48; n2++) pts.push(sp(L[0] * D, (n2 / 48 * 2 - 1) * Math.PI));
        line(pts, L[1]);
      });
      for (var m = 0; m < 14; m++) {
        for (pts = [], n2 = 0; n2 <= 12; n2++) pts.push(sp((CAP.base + (78 - CAP.base) * n2 / 12) * D, m / 14 * 2 * Math.PI + lrot));
        line(pts, .4);
        var nd = pts[0];
        fx.globalAlpha = al * vis(nd[2]); fx.fillStyle = '#AED534';
        fx.beginPath(); fx.arc(nd[0], nd[1], lw * 1.8, 0, 2 * Math.PI); fx.fill();
      }
      // Signals: each climbs a meridian from the base ring to the crown in 1.6 s, then picks another.
      for (var k = 0; k < 4; k++) {
        var ph = t / 1.6 + k / 4, cyc2 = Math.floor(ph), pr = ph - cyc2, lo = ((cyc2 * 5 + k * 3) % 14) / 14 * 2 * Math.PI + lrot;
        for (var j = 8; j >= 0; j--) {
          var q = sp((CAP.base + (78 - CAP.base) * Math.max(pr - j * .025, 0)) * D, lo), a = vis(q[2]) * (1 - j / 9) * Math.sin(Math.PI * pr);
          if (a < .01) continue;
          fx.globalAlpha = al * a; fx.fillStyle = j ? '#AED534' : '#f4ffd0';
          fx.beginPath(); fx.arc(q[0], q[1], lw * (j ? 2 - j * .15 : 3), 0, 2 * Math.PI); fx.fill();
        }
      }
      bx.globalAlpha = fx.globalAlpha = 1;
    };
    var on = false, raf = 0, t0 = performance.now(), lastF = 0;
    var frame = function (now) {
      raf = requestAnimationFrame(frame);
      // The page enters mid-way into the first cycle, its inputs already on the wires, so the loop is busy from the first moment.
      if (flowT0 === 1e12 && root.classList.contains('is-in')) { flowT0 = now - CYCLE * .12; anims.forEach(function (a) { a.startTime = flowT0; }); }
      var W = Math.round(cv.clientWidth * dpr), H = Math.round(cv.clientHeight * dpr);
      if (cv.width !== W || cv.height !== H) { cv.width = W; cv.height = H; }
      if (MW !== Math.ceil(W / 2) || MH !== Math.ceil(H / 2)) { MW = Math.ceil(W / 2); MH = Math.ceil(H / 2); makeInk(); }
      var ir = img.getBoundingClientRect(), cr = cv.getBoundingClientRect();
      var rx = (ir.left - cr.left) * dpr, ry = (ir.top - cr.top) * dpr, rw = ir.width * dpr, rh = ir.height * dpr;
      var t = (now - t0) / 1000, R = Math.min(rw, rh) * 0.15;
      // A slow wanderer over the face, always tracked, painting only while nobody points: alive on load and on phones.
      var wt = t * 0.5, prevW = wander;
      wander = { x: rx + rw * (0.5 + 0.12 * Math.sin(wt) + 0.035 * Math.sin(wt * 2.7)), y: ry + rh * (0.4 + 0.09 * Math.sin(wt * 1.31 + 1.2)) };
      var from, to, rad;
      if (aim && pen && now - lastMove < 2200) {
        // The liquid follows a beat behind the cursor; a quick flick spreads it wider.
        from = { x: pen.x, y: pen.y };
        pen.x += (aim.x - pen.x) * 0.28;
        pen.y += (aim.y - pen.y) * 0.28;
        to = pen;
        rad = R * (1 + Math.min(Math.hypot(to.x - from.x, to.y - from.y) / R, 0.5));
      } else {
        from = prevW || wander;
        to = wander;
        // The wanderer lifts while the scan passes, so only one neon event plays at a time.
        var wc = now > flowT0 ? ((now - flowT0) % CYCLE) / CYCLE : -1;
        rad = wc > SCAN[0] - .2 && wc < SCAN[1] + .04 ? 0 : R * 0.75;
      }
      // Ink pass, into the other buffer (mask pixels, y up).
      g.bindFramebuffer(g.FRAMEBUFFER, fbos[1 - src]);
      g.viewport(0, 0, MW, MH);
      g.useProgram(I.p);
      g.activeTexture(g.TEXTURE2);
      g.bindTexture(g.TEXTURE_2D, inks[src]);
      g.uniform2f(I.uRes, MW, MH);
      g.uniform2f(I.uA, from.x / 2, (H - from.y) / 2);
      g.uniform2f(I.uB, to.x / 2, (H - to.y) / 2);
      g.uniform1f(I.uR, rad / 2);
      g.uniform1f(I.uOn, 1);
      g.uniform1f(I.uTime, t);
      var steps = Math.min((now - (lastF || now)) / 16.67, 6) || 1; // how many 60 Hz frames this one stands for
      lastF = now;
      g.uniform1f(I.uK, Math.pow(.99, steps));
      g.uniform1f(I.uS, steps);
      g.drawArrays(g.TRIANGLES, 0, 3);
      src = 1 - src;
      // Picture pass, to the screen.
      g.bindFramebuffer(g.FRAMEBUFFER, null);
      g.viewport(0, 0, W, H);
      g.useProgram(U.p);
      g.bindTexture(g.TEXTURE_2D, inks[src]);
      var hw = rw * HELM.width;
      g.uniform2f(U.uRes, W, H);
      g.uniform4f(U.uRect, rx, ry, rw, rh);
      g.uniform4f(U.uHRect, rx + (rw - hw) / 2, ry + rh * HELM.eye - hw * HELM.visor, hw, hw);
      g.uniform1f(U.uTime, t);
      var cyc = now > flowT0 ? ((now - flowT0) % CYCLE) / CYCLE : -1, sx = (cyc - SCAN[0]) / (SCAN[1] - SCAN[0]);
      g.uniform1f(U.uScan, sx >= 0 && sx <= 1 ? -.04 + 1.1 * sx * sx * (3 - 2 * sx) : -9);
      // Each input's thought runs from the moment it lands in the left ear to the moment its result leaves the right one.
      var th = IN.map(function (n, k) { var s = (cyc - n[1]) / (OUT[k][0] - n[1]); return s >= 0 && s <= 1 ? s * s * (3 - 2 * s) : -1; });
      g.uniform3f(U.uThink, th[0], th[1], th[2]);
      g.drawArrays(g.TRIANGLES, 0, 3);
      drawRig(now, t, ir, cr);
      updateRun(cyc);
    };
    var run = function (yes) {
      if (yes === on) return;
      on = yes;
      if (on) raf = requestAnimationFrame(frame); else cancelAnimationFrame(raf);
    };
    // The automation loop: three kinds of work arrive on the left and run into the robot's temple, the robot scans,
    // and three results leave from the other temple. Wires and pulses are SVG, timed with the Web Animations API on
    // the same 7 s clock as the scan in the shader, so the story reads in order: in, think, out.
    var flows = stage.querySelector('.stage__flows'), wires = flows && flows.querySelector('.stage__wires'), anims = [];
    var runEl = flows && flows.querySelector('.run'), runRows = runEl ? runEl.querySelectorAll('.run__row') : [], runKey = '';
    // The panel's rows follow the same clock as the pulses: waiting, running while a pulse travels to the row, done once it lands.
    var updateRun = function (cyc) {
      if (!runEl || !stage.classList.contains('is-flow')) return;
      var st = [], n = 0;
      for (var k = 0; k < runRows.length; k++) { st.push(cyc < OUT[k][0] ? 'wait' : cyc < OUT[k][1] ? 'run' : 'done'); if (st[k] === 'done') n++; }
      var key = st.join() + (n === runRows.length);
      if (key !== runKey) {
        runKey = key;
        runRows.forEach(function (r, k) { r.setAttribute('data-state', st[k]); });
        runEl.classList.toggle('is-report', n === runRows.length);
        runEl.querySelector('.run__step b').textContent = n;
      }
      runEl.querySelector('.run__bar i').style.transform = 'scaleX(' + Math.max(0, cyc) + ')';
    };
    var NS = 'http://www.w3.org/2000/svg';
    var svgEl = function (tag, attrs) { var n = d.createElementNS(NS, tag); for (var k in attrs) n.setAttribute(k, attrs[k]); wires.appendChild(n); return n; };
    var loop = function (node, frames) { var a = node.animate(frames, { duration: CYCLE, iterations: Infinity }); a.startTime = flowT0; anims.push(a); };
    var travel = function (path, a, b) {
      loop(path, [
        { offset: 0, strokeDashoffset: 9, opacity: 0 },
        { offset: a, strokeDashoffset: 9, opacity: 1, easing: 'cubic-bezier(.55, 0, .25, 1)' },
        { offset: b, strokeDashoffset: -100, opacity: 1 },
        { offset: b + .002, strokeDashoffset: -100, opacity: 0 },
        { offset: 1, strokeDashoffset: -100, opacity: 0 }
      ]);
    };
    var glow = function (node, at) { loop(node, [{ offset: 0, opacity: 0 }, { offset: at - .01, opacity: 0 }, { offset: at, opacity: 1 }, { offset: at + .09, opacity: 0 }, { offset: 1, opacity: 0 }]); };
    var ping = function (x, y, at) {
      var ring = svgEl('circle', { class: 'ring', cx: x, cy: y, r: 5 });
      loop(ring, [{ offset: 0, opacity: 0, transform: 'scale(1)' }, { offset: at, opacity: .9, transform: 'scale(1)' }, { offset: at + .08, opacity: 0, transform: 'scale(3.4)' }, { offset: 1, opacity: 0, transform: 'scale(3.4)' }]);
    };
    var curve = function (a, b) { var dx = (b.x - a.x) * .5; return 'M' + a.x + ' ' + a.y + 'C' + (a.x + dx) + ' ' + a.y + ' ' + (b.x - dx) + ' ' + b.y + ' ' + b.x + ' ' + b.y; };
    var layoutFlows = function () {
      anims.forEach(function (a) { a.cancel(); });
      anims = [];
      if (!flows || !Element.prototype.animate) return;
      wires.textContent = '';
      var sr = stage.getBoundingClientRect(), ir = img.getBoundingClientRect(), cp = stage.querySelector('.stage__copy').getBoundingClientRect();
      var W = sr.width, H = sr.height, pad = cp.left - sr.left;
      var R = { x: ir.left - sr.left, y: ir.top - sr.top, w: ir.width, h: ir.height };
      var top = 104, bottom = cp.top - sr.top - 54;
      // Only where there is room above the copy and beside the head; phones keep the scan and the liquid.
      var room = bottom - top, row = room < 150; // a short window: the inputs sit side by side above the copy instead of in a column
      if (W < 1180 || room < 34) { stage.classList.remove('is-flow'); return; }
      stage.classList.add('is-flow');
      var pl = { x: R.x + R.w * .34, y: R.y + R.h * .345 }, pr = { x: R.x + R.w * .66, y: R.y + R.h * .345 };
      var ins = flows.querySelectorAll('.chip--in');
      var rowX = pad;
      ins.forEach(function (c, k) {
        var x = pad + [0, .07, .025][k] * W, y = top + Math.min(room / 2, 64) * k; // a compact group, however tall the room
        if (row) { x = rowX; rowX += c.offsetWidth + 26; y = top + Math.max(0, Math.min(room - 34, 28)) * [0, 1, .4][k]; }
        c.style.left = x + 'px';
        c.style.top = y + 'px';
        var a = { x: x + c.offsetWidth + 6, y: y + c.offsetHeight / 2 }, path = curve(a, pl);
        svgEl('path', { class: 'wire', d: path });
        travel(svgEl('path', { class: 'pulse', d: path, pathLength: 100 }), IN[k][0], IN[k][1]);
        glow(c.querySelector('.chip__glow'), IN[k][0] + .01);
        ping(pl.x, pl.y, IN[k][1]);
      });
      // The live-run panel sits on the right edge, level with the robot's eyes and clear of the doors below it.
      var dr = stage.querySelector('.stage__doors'), dTop = dr ? dr.getBoundingClientRect().top - sr.top : H;
      var pw = runEl.offsetWidth, ph = runEl.offsetHeight, px = W - pad - pw;
      var py = Math.max(96, Math.min(pr.y - ph * .32, dTop - 32 - ph));
      runEl.style.left = px + 'px';
      runEl.style.top = py + 'px';
      runRows.forEach(function (rw, k) {
        var rr = rw.getBoundingClientRect(), b = { x: px - 6, y: rr.top - sr.top + rr.height / 2 }, path = curve(pr, b);
        svgEl('path', { class: 'wire', d: path });
        travel(svgEl('path', { class: 'pulse', d: path, pathLength: 100 }), OUT[k][0], OUT[k][1]);
        ping(pr.x, pr.y, OUT[k][0] + .005);
        svgEl('circle', { class: 'port', cx: b.x, cy: b.y, r: 2 });
      });
      [pl, pr].forEach(function (p) { svgEl('circle', { class: 'port', cx: p.x, cy: p.y, r: 2.5 }); });
    };
    var start = function () {
      texture(0, img);
      stage.classList.add('is-gl');
      layoutFlows();
      var lt;
      addEventListener('resize', function () { clearTimeout(lt); lt = setTimeout(layoutFlows, 200); });
      if ('IntersectionObserver' in window) new IntersectionObserver(function (es) { run(es[0].isIntersecting && !d.hidden); }).observe(stage);
      else run(true);
      d.addEventListener('visibilitychange', function () { run(!d.hidden); });
    };
    if (img.complete && img.naturalWidth) start(); else img.addEventListener('load', start);
  });

  // Crossed-off list: each line is set to the full column, like a poster, its first and last letters on the edges.
  // A title too long to stay readable on one line keeps the CSS size and wraps, one stroke per line.
  d.querySelectorAll('.tasks').forEach(function (list) {
    var seen = 0, pen = d.createElement('canvas').getContext('2d');
    var fit = function () {
      list.querySelectorAll('.task__t').forEach(function (t) {
        var word = t.firstElementChild, text = word.textContent.trim().toUpperCase();
        t.style.cssText = 'font-size:100px;white-space:nowrap';
        pen.font = '820 100px "Artificia Sans"';
        var first = pen.measureText(text[0]), last = pen.measureText(text[text.length - 1]);
        var lead = -first.actualBoundingBoxLeft, tail = last.width - last.actualBoundingBoxRight + parseFloat(getComputedStyle(t).letterSpacing); // CSS spacing also follows the last letter
        var px = 100 * t.clientWidth / (word.getBoundingClientRect().width - lead - tail);
        t.style.cssText = px < 22 ? '' : 'font-size:' + Math.min(px, innerWidth * 0.14).toFixed(2) + 'px;white-space:nowrap;margin-left:' + (-lead * px / 100).toFixed(1) + 'px';
      });
    };
    fit();
    d.fonts.ready.then(function () { fit(); if (window.ScrollTrigger) window.ScrollTrigger.refresh(); });
    new ResizeObserver(function () { if (list.clientWidth !== seen) { seen = list.clientWidth; fit(); } }).observe(list);
  });

  // Motion runs only when the head script promised it (no reduced-motion preference) and GSAP loaded.
  var gsap = window.gsap, ST = window.ScrollTrigger;
  if (!root.classList.contains('motion') || !gsap || !ST) { root.classList.remove('motion'); return; }
  window.__motion = true;
  gsap.registerPlugin(ST);
  // Expanding the founder moves every following section; refresh their scroll positions so floods stay clear of the text.
  d.querySelectorAll('.about-page .person__more').forEach(function (details) {
    details.addEventListener('toggle', function () {
      requestAnimationFrame(function () { ST.refresh(); });
    });
  });
  var out = 'expo.out';
  var once = function (el, start) { return { trigger: el, start: start || 'top 85%', once: true }; };
  var lit = function (head) { head.classList.add('is-lit'); };

  // The authored moment: the heading is highlighted, the hero sketch draws itself, then the doors settle in.
  d.querySelectorAll('.hero').forEach(function (hero) {
    var tl = gsap.timeline({ delay: 0.15 });
    hero.querySelectorAll('.sec__head').forEach(function (h) { tl.call(lit, [h], 0.25); });
    var lines = hero.querySelectorAll('.draw .ln');
    if (lines.length) tl.to(lines, { strokeDashoffset: 0, duration: 0.8, ease: 'power2.inOut', stagger: 0.09 }, 0);
    var doors = hero.querySelectorAll('.doors > li');
    if (doors.length) tl.to(doors, { opacity: 1, y: 0, duration: 0.7, ease: out, stagger: 0.08 }, 0.2);
  });

  // The stage leaves the way landonorris.com's does: pinned, the robot shrinks into a card while the headline runs past behind it.
  d.querySelectorAll('[data-stage]').forEach(function (st) {
    if (!matchMedia('(min-width: 900px)').matches) return;
    var rows = st.querySelectorAll('.stage__row');
    var bar = d.querySelector('[data-top]');
    // Once the white has turned olive, the masthead goes back to its light logo, as Lando's wordmark does.
    var tl = gsap.timeline({ scrollTrigger: { trigger: st, start: 'top top', end: '+=120%', scrub: 0.6, pin: true,
      onUpdate: function (self) { if (bar) bar.classList.toggle('on-dark', self.progress > 0.3); } } });
    // Only the wrapper fades here; the intro animates the items inside it, so the two never fight over the same element.
    tl.fromTo(st.querySelector('.stage__ui'), { opacity: 1, y: 0 }, { opacity: 0, y: -24, duration: 0.25 }, 0)
      .fromTo(st.querySelector('.stage__card'), { clipPath: 'inset(0% 0% 0% 0%)', backgroundColor: 'rgba(10, 11, 7, 0)' },
        { clipPath: 'inset(14% 26% 14% 26%)', backgroundColor: 'rgba(10, 11, 7, 1)', duration: 0.6, ease: 'power2.inOut' }, 0)
      .to(st.querySelector('.stage__robot'), { scale: 0.86, duration: 0.6, ease: 'power2.inOut' }, 0)
      .to(st, { backgroundColor: '#232719', duration: 0.6 }, 0)
      .to(st.querySelector('.stage__marquee'), { opacity: 1, duration: 0.25 }, 0.15);
    if (rows[0]) tl.fromTo(rows[0], { xPercent: 0 }, { xPercent: -28, duration: 1, ease: 'none' }, 0);
    if (rows[1]) tl.fromTo(rows[1], { xPercent: -28 }, { xPercent: 0, duration: 1, ease: 'none' }, 0);
  });

  // Open gallery: the desktop composition travels sideways with normal page scrolling.
  // Without motion or on smaller screens, every visual stays in the document flow.
  d.querySelectorAll('[data-work-gallery]').forEach(function (section) {
    if (!section.querySelector('.workscape__item')) return;
    gsap.matchMedia().add('(prefers-reduced-motion: no-preference)', function () {
      var terrain = createWorkTerrain(section, (section.closest('.workscape-pair') || section).querySelector('.workscape__canvas'));
      // The measured reference uses GSAP's default power1.out for the background's color value.
      var paint = function (self) { terrain.setProgress(1 - Math.pow(1 - self.progress, 2)); };
      var sceneScroll = function () {
        var viewport = section.querySelector('.workscape__viewport');
        return ST.getAll().find(function (trigger) { return trigger.trigger === viewport && trigger.vars.pin; });
      };
      var ground = ST.create({ trigger: section,
        start: function () { var scene = sceneScroll(); return scene ? scene.start : 'top top'; },
        end: function () { var scene = sceneScroll(); return scene ? scene.end : 'bottom bottom'; },
        onUpdate: paint, onRefresh: paint, invalidateOnRefresh: true, refreshPriority: -1 });
      paint(ground);
      return function () { ground.kill(); terrain.destroy(); };
    });
    gsap.matchMedia().add('(min-width: 1100px) and (min-height: 620px) and (prefers-reduced-motion: no-preference)', function () {
      var viewport = section.querySelector('.workscape__viewport'), track = section.querySelector('.workscape__track');
      // Three project visuals, automation, and one combined voice/chat service card.
      if (section.querySelectorAll('.workscape__item').length !== 5 || section.querySelectorAll('.workscape__item--mobile, .workscape__item--ghl').length !== 1 || section.querySelectorAll('.workscape__item--workflow, .workscape__item--support').length !== 1 || ['website', 'conversations', 'assistants'].some(function (kind) {
        return section.querySelectorAll('.workscape__item--' + kind).length !== 1;
      })) return;
      var prev = section.querySelector('[data-work-prev]'), next = section.querySelector('[data-work-next]');
      section.classList.add('is-horizontal');
      var review = section.querySelector('.workscape__thought--review'), website = section.querySelector('.workscape__item--website');
      if (track.scrollWidth <= viewport.clientWidth || (review && review.getBoundingClientRect().bottom > website.getBoundingClientRect().top - 24)) {
        section.classList.remove('is-horizontal');
        return;
      }
      var distance = function () { return Math.max(0, track.scrollWidth - viewport.clientWidth); };
      var controls = function (p) { prev.disabled = p < 0.001; next.disabled = p > 0.999; };
      var travel = gsap.to(track, { x: function () { return -distance(); }, ease: 'none',
        scrollTrigger: { trigger: viewport, start: 'top 84px', end: function () { return '+=' + Math.max(distance(), innerHeight * .8); },
          pin: true, scrub: 1, invalidateOnRefresh: true,
          onUpdate: function (self) { controls(self.progress); } } });
      var reveals = [];
      track.querySelectorAll('.workscape__item').forEach(function (card, i) {
        var picture = card.querySelector('.workscape__picture'), label = card.querySelector('.workscape__caption > span');
        // Four rem of relative travel, with the frame intact so website text and UI controls stay legible.
        gsap.fromTo(picture, { x: -32 }, { x: 32, ease: 'none',
          scrollTrigger: { trigger: card, containerAnimation: travel, start: 'left right', end: 'right left', scrub: true } });
        if (!label || !label.textContent.trim()) return;
        var original = label.innerHTML, bar = d.createElement('i');
        bar.className = 'workscape__label-wipe'; bar.setAttribute('aria-hidden', 'true'); label.appendChild(bar);
        gsap.set(label, { clipPath: 'inset(0 100% 0 0)' });
        var wipe = gsap.timeline({ paused: true })
          .to(label, { clipPath: 'inset(0 0% 0 0)', duration: .6, ease: 'power2.out' }, 0)
          .fromTo(bar, { scaleX: 1 }, { scaleX: 0, duration: .6, ease: 'power2.inOut' }, .3);
        var trigger = ST.create(card.offsetLeft < viewport.clientWidth * .95 ? { trigger: card, start: 'top 90%', once: true, onEnter: function () { wipe.play(); } }
          : { trigger: card, containerAnimation: travel, start: 'left 95%', once: true, onEnter: function () { wipe.play(); } });
        card.workReveal = wipe;
        reveals.push(function () { trigger.kill(); wipe.kill(); label.innerHTML = original; label.style.clipPath = ''; delete card.workReveal; });
      });
      var go = function (progress, immediate) {
        var st = travel.scrollTrigger, p = Math.max(0, Math.min(1, progress));
        window.scrollTo({ top: st.start + (st.end - st.start) * p, behavior: immediate ? 'instant' : 'smooth' });
        if (immediate) { st.update(); travel.progress(p); }
      };
      var back = function () { go(travel.scrollTrigger.progress - viewport.clientWidth * .7 / Math.max(distance(), 1)); };
      var forward = function () { go(travel.scrollTrigger.progress + viewport.clientWidth * .7 / Math.max(distance(), 1)); };
      var focus = function (event) {
        var card = event.target.closest('.workscape__item');
        if (!card) return;
        if (card.workReveal) card.workReveal.progress(1).pause();
        var box = card.getBoundingClientRect(), bounds = viewport.getBoundingClientRect();
        if (box.left >= bounds.left && box.right <= bounds.right) return;
        var left = box.left - bounds.left - Number(gsap.getProperty(track, 'x'));
        go((left - viewport.clientWidth * .15) / Math.max(distance(), 1), true);
      };
      prev.addEventListener('click', back); next.addEventListener('click', forward);
      track.addEventListener('focusin', focus); controls(0);
      return function () {
        prev.removeEventListener('click', back); next.removeEventListener('click', forward);
        track.removeEventListener('focusin', focus); reveals.forEach(function (dispose) { dispose(); });
        section.classList.remove('is-horizontal');
      };
    });
  });

  // Statement: match the reference cascade: .15s line stagger, .6s wipes, retraction after .3s.
  // The original server-rendered heading remains available to screen readers; the animated copy is decorative.
  d.querySelectorAll('.statement__t').forEach(function (t) {
    var original = t.innerHTML, byRow = !!t.closest('.corporate-page');
    gsap.matchMedia().add('(prefers-reduced-motion: no-preference)', function () {
      var readable = d.createElement('span'), visual = d.createElement('span');
      readable.className = 'statement__readable';
      readable.innerHTML = original;
      visual.className = 'statement__visual';
      visual.setAttribute('aria-hidden', 'true');
      t.replaceChildren(readable, visual);
      var tl, observer, timer, rowTriggers = [], rowTimelines = [], revealedWords = 0, signature = '', started = false, disposed = false;
      var clearRows = function () {
        rowTriggers.forEach(function (trigger) { trigger.kill(); }); rowTriggers = [];
        rowTimelines.forEach(function (timeline) { timeline.kill(); }); rowTimelines = [];
      };
      var build = function () {
        if (disposed) return;
        if (tl) { if (tl.scrollTrigger) tl.scrollTrigger.kill(); tl.kill(); }
        clearRows();
        visual.innerHTML = original;
        var walk = d.createTreeWalker(visual, NodeFilter.SHOW_TEXT), nodes = [], n, gap = false, previousWord;
        while ((n = walk.nextNode())) nodes.push(n);
        nodes.forEach(function (node) {
          var frag = d.createDocumentFragment();
          node.nodeValue.split(/(\s+)/).forEach(function (part) {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(d.createTextNode(part)); gap = true; return; }
            if (!gap && previousWord && /^[.,!?;:]+$/.test(part)) { previousWord.textContent += part; return; }
            var word = d.createElement('span');
            word.className = 'statement__word';
            word.dataset.space = gap ? '1' : '0';
            word.textContent = part;
            previousWord = word; gap = false;
            frag.appendChild(word);
          });
          node.parentNode.replaceChild(frag, node);
        });
        var lines = [];
        visual.querySelectorAll('.statement__word').forEach(function (word) {
          var top = word.getBoundingClientRect().top, last = lines[lines.length - 1];
          if (!last || Math.abs(last.top - top) > Math.max(3, parseFloat(getComputedStyle(t).fontSize) * 0.12)) { last = { top: top, words: [] }; lines.push(last); }
          last.words.push({ text: word.textContent, em: !!word.closest('em'), space: word.dataset.space === '1' });
        });
        var rows = d.createDocumentFragment();
        lines.forEach(function (line) {
          var row = d.createElement('span'), box = d.createElement('span'), ink = d.createElement('span'), bar = d.createElement('span');
          row.className = 'statement__row'; box.className = 'statement__line';
          ink.className = 'statement__ink'; bar.className = 'statement__bar';
          line.words.forEach(function (word, i) {
            if (i && word.space) ink.appendChild(d.createTextNode(' '));
            var el = d.createElement(word.em ? 'em' : 'span');
            el.textContent = word.text;
            ink.appendChild(el);
          });
          box.append(ink, bar); row.appendChild(box); rows.appendChild(row);
        });
        visual.replaceChildren(rows);
        signature = t.clientWidth + ':' + getComputedStyle(t).fontSize;
        if (!lines.length || (started && !byRow)) { ST.refresh(); return; }
        var bars = visual.querySelectorAll('.statement__bar'), boxes = visual.querySelectorAll('.statement__line');
        // This short statement still spans most of a laptop screen. Reveal each row when it arrives, so the bars on
        // lower lines cannot finish below the fold. Batch rows arriving together to retain the reference's cascade.
        if (byRow) {
          var ends = new Map(), count = 0, pending = [];
          boxes.forEach(function (box, i) {
            count += lines[i].words.length; ends.set(box, count);
            if (count <= revealedWords) return;
            gsap.set(box, { clipPath: 'inset(0 100% 0 0)' });
            gsap.set(bars[i], { scaleX: 1, transformOrigin: 'right center' });
            pending.push(box);
          });
          rowTriggers = ST.batch(pending, { start: 'top 82%', once: true, interval: .1, onEnter: function (batch) {
            batch.forEach(function (box, i) {
              if (disposed || !visual.contains(box)) return;
              revealedWords = Math.max(revealedWords, ends.get(box));
              var wipe = gsap.timeline({ delay: i * .15 })
                .to(box, { clipPath: 'inset(0 0% 0 0)', duration: .6, ease: 'power2.out' }, 0)
                .to(box.querySelector('.statement__bar'), { scaleX: 0, duration: .6, ease: 'power2.inOut' }, .3);
              rowTimelines.push(wipe);
            });
          } });
          ST.refresh(); return;
        }
        // Measured on landonorris.com: lines cascade instead of waiting for earlier wipes to finish.
        gsap.set(boxes, { clipPath: 'inset(0 100% 0 0)' });
        gsap.set(bars, { scaleX: 1, transformOrigin: 'right center' });
        // Inner pages open with a full-height hero, so their statement arrives low and large: wait until it is well in
        // view, or the cascade finishes before most lines are on screen (user, 12 Nov). The homepage keeps the reference.
        tl = gsap.timeline({ scrollTrigger: { trigger: t, start: t.closest('.solutions-page, .course-page') ? 'top 70%' : 'top 90%', once: true },
          onStart: function () { started = true; } });
        bars.forEach(function (bar, i) {
          var at = i * 0.15;
          tl.to(boxes[i], { clipPath: 'inset(0 0% 0 0)', duration: 0.6, ease: 'power2.out' }, at)
            .to(bar, { scaleX: 0, duration: 0.6, ease: 'power2.inOut' }, at + 0.3);
        });
        ST.refresh();
      };
      var ready = d.fonts ? d.fonts.ready : Promise.resolve();
      ready.then(function () {
        if (disposed) return;
        build();
        observer = new ResizeObserver(function () {
          var next = t.clientWidth + ':' + getComputedStyle(t).fontSize;
          if (next === signature) return;
          clearTimeout(timer); timer = setTimeout(build, 120);
        });
        observer.observe(t);
      }).catch(function () { if (!disposed) t.innerHTML = original; });
      return function () {
        disposed = true; clearTimeout(timer);
        clearRows();
        if (observer) observer.disconnect();
        if (tl) { if (tl.scrollTrigger) tl.scrollTrigger.kill(); tl.kill(); }
        t.innerHTML = original;
      };
    });
  });

  // One continuous arrival: the dawn, figures and copy share a scroll clock. No second pin or empty scroll interval.
  // MatchMedia reverts inline styles and triggers when crossing the layout breakpoint.
  d.querySelectorAll('.versus').forEach(function (v) {
    var figA = v.querySelector('.versus__fig--a'), figB = v.querySelector('.versus__fig--b'), sides = v.querySelectorAll('.versus__side');
    gsap.matchMedia().add({ desktop: '(min-width: 1100px)', mobile: '(max-width: 1099px)', reduce: '(prefers-reduced-motion: reduce)' }, function (context) {
      if (context.conditions.reduce) { v.classList.remove('is-wait'); return; }
      var desktop = context.conditions.desktop;
      v.classList.add('is-wait');
      var sharedWhite = v.previousElementSibling && v.previousElementSibling.matches('.workscape');
      gsap.set(v, { backgroundColor: sharedWhite ? 'transparent' : '#232719' });
      var tl = gsap.timeline({ scrollTrigger: { trigger: v, start: 'top 90%', end: desktop ? 'top 8%' : 'top 32%', scrub: 0.35,
        onUpdate: function (self) { v.classList.toggle('is-wait', self.progress < 0.72); } } });
      if (!sharedWhite) tl.fromTo(v.querySelector('.versus__dawn'),
        { clipPath: 'ellipse(85% 0% at 50% 100%)' },
        { clipPath: 'ellipse(100% 155% at 50% 100%)', duration: 1, ease: 'none' }, 0);
      tl.from(figA, { xPercent: desktop ? -14 : -5, opacity: 0.4, duration: 0.85, ease: 'power1.out', force3D: false }, 0)
        .from(figB, { xPercent: desktop ? 14 : 5, opacity: 0.4, duration: 0.85, ease: 'power1.out', force3D: false }, 0)
        .from(v.querySelector('.versus__grid'), { opacity: 0, y: 24, duration: 0.4, ease: 'power1.out' }, 0.6);
      // The flood climbs slowly at first, so it never covers copy that is still being read, then closes over what is left.
      // The long scrub lets it keep flowing for a moment after the page stops.
      var flood = (v.querySelector('.versus__dusk canvas') || {}).flood;
      // It closes while the section's foot is still mid-screen, so no paper is left between the masthead and Solutions.
      if (flood) gsap.fromTo(flood, { rise: 30 }, { rise: function () { return innerHeight * (desktop ? 1.1 : 0.55); }, ease: 'power2.in',
        scrollTrigger: { trigger: v, start: 'bottom 100%', end: 'bottom 45%', scrub: 0.6, invalidateOnRefresh: true } });
      // Keyboard navigation must never land on a visually hidden link during the arrival.
      var revealFocus = function () {
        if (tl.progress() < 1) {
          window.scrollTo({ top: tl.scrollTrigger.end, behavior: 'instant' });
          tl.progress(1);
          v.classList.remove('is-wait');
        }
      };
      v.addEventListener('focusin', revealFocus);
      return function () { v.removeEventListener('focusin', revealFocus); v.classList.remove('is-wait'); };
    });
  });

  // One portrait-led deck first, then the stories open together as the visitor scrolls.
  // The fully open CSS layout remains the fallback on phones, short screens and reduced motion.
  d.querySelectorAll('[data-testimonial-fan]').forEach(function (section) {
    gsap.matchMedia().add('(min-width: 900px) and (min-height: 680px) and (prefers-reduced-motion: no-preference)', function () {
      var cards = section.querySelectorAll('.voices__card');
      if (cards.length < 2) return;
      var stage = section.querySelector('.voices__stage'), fan = section.querySelector('.voices__fan');
      var stamp = section.querySelector('.voices__stamp'), keyboardOpen = false;
      section.classList.add('is-sequenced');
      var reveal = gsap.timeline({ scrollTrigger: {
        trigger: stage, start: 'top top', end: function () { return '+=' + Math.round(innerHeight * .85); },
        pin: stage, scrub: .65, invalidateOnRefresh: true,
        onUpdate: function (self) { section.classList.toggle('is-open', keyboardOpen || self.progress > .65); }
      } });
      // A short opening hold gives the single stack its own moment before the fan unfolds.
      reveal.fromTo(fan, { '--fan-spread': 0 }, { '--fan-spread': 1, duration: .7, ease: 'power2.inOut', immediateRender: true }, .16)
        .to({}, { duration: .14 });
      if (stamp) reveal.to(stamp, { opacity: 0, scale: .75, rotation: 8, duration: .25, ease: 'power2.in' }, .16);
      var revealFocus = function () {
        if (keyboardOpen) return;
        keyboardOpen = true;
        // Release the scroll pin as well as the animation, so Tab can reach the following section.
        var trigger = reveal.scrollTrigger, start = trigger.start, end = trigger.end;
        trigger.kill();
        reveal.pause().progress(1);
        section.classList.add('is-open');
        ST.refresh();
        if (scrollY >= start && scrollY <= end) window.scrollTo({ top: section.getBoundingClientRect().top + scrollY, behavior: 'instant' });
      };
      section.addEventListener('focusin', revealFocus);
      return function () { section.removeEventListener('focusin', revealFocus); section.classList.remove('is-sequenced', 'is-open'); };
    });
  });

  // Solutions arrive in reading order; the links and drawings remain static without motion.
  d.querySelectorAll('[data-solutions]').forEach(function (section) {
    gsap.matchMedia().add('(prefers-reduced-motion: no-preference)', function () {
      var arrival = gsap.from(section.querySelectorAll('.solution__link'), { y: 34, opacity: 0, duration: 0.85, stagger: 0.13, ease: 'power2.out', scrollTrigger: once(section.querySelector('.solutions__track'), 'top 88%'), clearProps: 'transform,opacity' });
      var revealFocus = function () { if (arrival.progress() < 1) { arrival.progress(1); if (arrival.scrollTrigger) arrival.scrollTrigger.kill(); } };
      section.addEventListener('focusin', revealFocus);
      return function () { section.removeEventListener('focusin', revealFocus); };
    });
  });
  // Wider screens (homepage): the section holds while the six cards slide left with the scroll, then the page moves on.
  d.querySelectorAll('[data-solutions]').forEach(function (section) {
    gsap.matchMedia().add('(min-width: 900px) and (min-height: 600px) and (prefers-reduced-motion: no-preference)', function () {
      var track = section.querySelector('.solutions__track');
      section.classList.add('is-rail');
      var shift = function () { return track.scrollWidth - track.clientWidth; };
      var slide = gsap.to(track, { x: function () { return -shift(); }, ease: 'none', scrollTrigger: {
        trigger: track, start: 'center 52%', end: function () { return '+=' + shift(); },
        pin: section, scrub: 0.6, invalidateOnRefresh: true } });
      // Tabbing to a card scrolls to the frame where that card is fully in view.
      var showFocus = function (e) {
        var card = e.target.closest('.solution'), st = slide.scrollTrigger, max = shift();
        if (!card || !max) return;
        var p = Math.min(1, Math.max(0, (card.offsetLeft - track.offsetLeft + card.offsetWidth - track.clientWidth) / max));
        window.scrollTo({ top: st.start + (st.end - st.start) * p, behavior: 'instant' });
      };
      section.addEventListener('focusin', showFocus);
      return function () { section.removeEventListener('focusin', showFocus); section.classList.remove('is-rail'); };
    });
  });

  // The list crosses itself off at the reading line, always in order, however fast the visitor scrolls.
  d.querySelectorAll('.tasks').forEach(function (list) {
    var free = 0;
    list.querySelectorAll('.task').forEach(function (row) {
      row.classList.add('is-wait');
      ST.create({ trigger: row, start: 'top 82%', once: true, onEnter: function () {
        var now = performance.now(), at = Math.max(now, free);
        free = at + 320;
        setTimeout(function () { row.classList.remove('is-wait'); }, at - now);
      } });
    });
    list.addEventListener('focusin', function (e) { e.target.closest('.task').classList.remove('is-wait'); });
  });

  // Further down, each heading's highlighter is drawn as its section arrives.
  d.querySelectorAll('.sec:not(.hero) .sec__head, .pagehead .sec__head').forEach(function (h) {
    if (h.closest('.pagehead')) { setTimeout(function () { lit(h); }, 250); return; }
    ST.create({ trigger: h, start: 'top 78%', once: true, onEnter: function () { lit(h); } });
  });

  // Sketches further down the page draw themselves when they arrive.
  d.querySelectorAll('.sec:not(.hero) .draw').forEach(function (svg) {
    var lines = svg.querySelectorAll('.ln');
    if (lines.length) gsap.fromTo(lines, { strokeDasharray: 1, strokeDashoffset: 1 }, { strokeDashoffset: 0, duration: 0.7, ease: 'power2.inOut', stagger: 0.06, scrollTrigger: once(svg, 'top 80%') });
  });

  // Photographs are uncovered from the top, like a print being pulled.
  d.querySelectorAll('.sec:not(.hero) .frame, .sec:not(.hero) .index__photo').forEach(function (f) {
    gsap.fromTo(f, { clipPath: 'inset(0 0 100% 0)' }, { clipPath: 'inset(0 0 0% 0)', duration: 1.1, ease: 'power3.inOut', scrollTrigger: once(f, 'top 88%') });
  });

  d.querySelectorAll('.sec:not(.hero) [data-reveal="list"]').forEach(function (l) {
    gsap.from(l.children, { y: 16, opacity: 0, duration: 0.7, ease: out, stagger: 0.06, scrollTrigger: once(l) });
  });
  d.querySelectorAll('[data-reveal="fade"]').forEach(function (el) {
    gsap.from(el, { y: 12, opacity: 0, duration: 0.8, ease: out, scrollTrigger: once(el) });
  });

  // Outcomes: each dimension line is measured out, then its number counts up.
  d.querySelectorAll('.dim').forEach(function (dim, i) {
    var rules = dim.querySelectorAll('.dim__rule'), num = dim.querySelector('[data-count]');
    var tl = gsap.timeline({ scrollTrigger: once(dim, 'top 88%'), delay: (i % 3) * 0.08 });
    if (rules.length) tl.fromTo(rules, { scaleX: 0 }, { scaleX: 1, duration: 0.8, ease: out });
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

  // Fluid joins climb with the scroll: over the contours where WebGL runs, as a rising arc on phones. They stay low while the
  // section above is still being read and close over it only as the join reaches the top of the screen.
  d.querySelectorAll('.flood-join').forEach(function (join) {
    var trigger = { trigger: join.parentNode, start: 'top bottom', end: 'top top', scrub: 1, invalidateOnRefresh: true };
    if (join.classList.contains('is-live')) gsap.fromTo(join.firstChild.flood, { rise: function () { return join.classList.contains('flood-join--contact') ? Math.min(56, join.offsetHeight * .6) : 70; } }, { rise: function () { return join.offsetHeight * 1.4; }, ease: join.classList.contains('flood-join--contact') ? 'sine.inOut' : 'power4.in', scrollTrigger: trigger });
    else gsap.fromTo(join, { clipPath: join.classList.contains('flood-join--contact') ? 'ellipse(90% 45% at 50% 100%)' : 'ellipse(85% 7% at 50% 100%)' }, { clipPath: 'ellipse(110% 100% at 50% 100%)', ease: 'power4.in', scrollTrigger: trigger });
  });

  // The footer arrives: neon floods up over the last section, the frame closes in, the robot rises, then the line is said and the AI circled.
  d.querySelectorAll('[data-foot]').forEach(function (foot) {
    var rise = foot.querySelector('.foot__rise'), flood = (rise.querySelector('canvas') || {}).flood;
    if (flood && rise.classList.contains('is-live')) gsap.fromTo(flood, { rise: 30 }, { rise: function () { return rise.offsetHeight * 1.4; }, ease: 'power3.in',
      scrollTrigger: { trigger: foot, start: 'top bottom', end: 'top 10%', scrub: 1, invalidateOnRefresh: true } });
    gsap.matchMedia().add({ composed: '(min-width: 1100px) and (min-height: 620px)', stacked: '(max-width: 1099px), (max-height: 619px)' }, function (context) {
      var composed = context.conditions.composed, words = foot.querySelectorAll('.foot__say > span'), mark = foot.querySelector('.foot__mark path');
      if (composed) gsap.timeline({ scrollTrigger: { trigger: foot, start: 'top 88%', end: 'bottom bottom', scrub: 0.7, invalidateOnRefresh: true } })
        .fromTo(foot.querySelector('.foot__panel'), { scale: 0.93 }, { scale: 1, ease: 'power2.out', duration: 1, transformOrigin: '50% 100%' }, 0)
        .fromTo(foot.querySelector('.foot__figure'), { yPercent: 34 }, { yPercent: 0, ease: 'power2.out', duration: 1 }, 0);
      var say = gsap.timeline({ scrollTrigger: { trigger: foot, start: composed ? 'top 42%' : 'top 70%', once: true } });
      say.from(words, { yPercent: 40, opacity: 0, duration: 1, ease: out, stagger: 0.09 }, 0)
        .fromTo(mark, { strokeDashoffset: 1 }, { strokeDashoffset: 0, duration: 0.9, ease: 'power2.inOut' }, 0.45);
      if (composed) say.from(foot.querySelectorAll('.foot__col'), { opacity: 0, y: 22, duration: 0.9, ease: out, stagger: 0.12 }, 0.2);
    });
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

})();
