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
    var src = b.getAttribute('data-src') || '';
    if (src.indexOf('https://') !== 0) return; // the server allowlists hosts; never load anything else
    var f = d.createElement('iframe');
    f.src = src;
    f.title = b.getAttribute('data-title') || 'Map';
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
    f.setAttribute('tabindex', '-1');
    f.focus();
  });

  // The WhatsApp button waits until the hero (and its chooser) has scrolled away.
  var fab = d.querySelector('.wa-float'), hero = d.querySelector('.hero');
  if (fab && hero && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (es) { fab.classList.toggle('is-away', es[0].isIntersecting); }).observe(hero);
  }

  // Attribution: the first visit's utm_*, gclid and fbclid are kept in the atk cookie for 30 days.
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
      if (typeof window.gtag === 'function') window.gtag('event', 'generate_lead', { form_type: type });
      if (typeof window.fbq === 'function') window.fbq('track', 'Lead', { content_category: type }, { eventID: res.event_id });
    };
    var fail = function (res) {
      btn.disabled = false;
      form.classList.remove('is-busy');
      if (res.errors) {
        var firstBad = null, other = [];
        Object.keys(res.errors).forEach(function (k) {
          var el = field(k) || field(k + '[]'), err = el && d.getElementById(el.id + '-err');
          if (!err) { other.push(res.errors[k]); return; }
          err.textContent = res.errors[k];
          el.setAttribute('aria-invalid', 'true');
          firstBad = firstBad || el;
        });
        status(other.length ? other.join(' ') : 'Please check the highlighted fields.', true);
        if (firstBad) firstBad.focus();
        return;
      }
      var wa = typeof res.whatsapp === 'string' && res.whatsapp.indexOf('https://wa.me/') === 0 ? res.whatsapp : form.getAttribute('data-wa');
      status(res.message || 'Your enquiry could not be sent just now. Please send it to us on WhatsApp instead.', true, wa);
    };
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (form.classList.contains('is-busy')) return;
      ATK.forEach(function (k) { set(k, atk[k]); });
      set('page_url', location.origin + location.pathname);
      set('referrer', d.referrer);
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

  // Sketches further down the page draw themselves when they arrive.
  d.querySelectorAll('.sec:not(.hero) .draw').forEach(function (svg) {
    var lines = svg.querySelectorAll('.ln');
    if (lines.length) gsap.fromTo(lines, { strokeDasharray: 1, strokeDashoffset: 1 }, { strokeDashoffset: 0, duration: 0.7, ease: 'power2.inOut', stagger: 0.06, scrollTrigger: once(svg, 'top 80%') });
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
