/* Artificia admin: shell behaviours, no dependencies. Later screens extend this file.
 *   Drawer         #menu-btn opens #rail under 900px; Escape or the scrim closes it.
 *   Progress bar   #progress runs on internal link clicks and form submits.
 *   Busy submit    A submitting button shows "Saving…" and disables; [data-busy="Text"] changes the text; [data-no-busy] on the form opts out.
 *   Click again    [data-confirm="Click again to delete"] on a button or link: the first click arms it for 4 s, the second goes through.
 *   Dirty guard    form[data-dirty-guard]: beforeunload warning after an edit, Cmd/Ctrl+S submits, [data-dirty-note] shown while dirty,
 *                  [data-needs-saved="<form id>"] buttons/links are disabled while dirty; form[data-dirty] starts dirty (a re-shown unsaved form).
 *   Toasts         .toast elements from the server are timed (data-ttl); Admin.toast(msg, 'ok'|'error') adds one; Escape dismisses all.
 *   Page editor    #page-form (views/page-edit.php): tabs, section cards (add from /admin/section-form, move, remove, collapse),
 *                  list items (add from <template data-item-tpl>, remove), Quill on textarea[data-richtext], live counters
 *                  [data-count="min-max"], Google snippet preview. New page form: [data-slug-to] fills the address from the title.
 * Public API: window.Admin = { toast(msg, kind), setDirty(form, bool) }.
 */
(function () {
  'use strict';
  var doc = document, body = doc.body;

  /* ---- Toasts ---- */
  var toasts = doc.getElementById('toasts');
  function arm(t) {
    var ttl = parseInt(t.getAttribute('data-ttl') || '3000', 10);
    var timer = setTimeout(function () { t.remove(); }, ttl);
    var x = t.querySelector('.toast__x');
    if (x) x.addEventListener('click', function () { clearTimeout(timer); t.remove(); });
  }
  function toast(msg, kind) {
    if (!toasts) return;
    var error = kind === 'error';
    var t = doc.createElement('div');
    t.className = 'toast toast--' + (error ? 'error' : 'ok');
    t.setAttribute('role', error ? 'alert' : 'status');
    t.setAttribute('data-ttl', error ? '6000' : '3000');
    var m = doc.createElement('span'); m.className = 'toast__msg'; m.textContent = msg;
    var x = doc.createElement('button'); x.type = 'button'; x.className = 'toast__x'; x.setAttribute('aria-label', 'Dismiss'); x.textContent = '×';
    t.appendChild(m); t.appendChild(x); toasts.appendChild(t); arm(t);
  }
  if (toasts) toasts.querySelectorAll('.toast').forEach(arm);

  /* ---- Drawer ---- */
  var rail = doc.getElementById('rail'), menu = doc.getElementById('menu-btn'), scrim = doc.getElementById('scrim');
  function drawer(open) {
    if (!rail || !menu) return;
    body.classList.toggle('rail-open', open);
    menu.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (scrim) scrim.hidden = !open;
    if (open) setTimeout(function () { (rail.querySelector('a') || rail).focus(); }, 30); // after visibility flips
    else if (doc.activeElement && rail.contains(doc.activeElement)) menu.focus();
  }
  if (menu) menu.addEventListener('click', function () { drawer(!body.classList.contains('rail-open')); });
  if (scrim) scrim.addEventListener('click', function () { drawer(false); });

  /* ---- Click again (registered before the progress bar so a first click does not start it) ---- */
  doc.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('[data-confirm]');
    if (!b) return;
    if (b.classList.contains('is-armed')) { clearTimeout(b._t); return; }
    e.preventDefault();
    b._label = b.textContent;
    b.textContent = b.getAttribute('data-confirm') || 'Click again to delete';
    b.classList.add('is-armed');
    b._t = setTimeout(function () { b.textContent = b._label; b.classList.remove('is-armed'); }, 4000);
  });
  // A link marked aria-disabled (Preview while unsaved) must not open from the keyboard either.
  doc.addEventListener('click', function (e) { if (e.target.closest && e.target.closest('a[aria-disabled="true"]')) e.preventDefault(); });

  /* ---- Progress bar ---- */
  var bar = doc.getElementById('progress');
  function progress() { if (bar) { bar.classList.remove('is-on'); void bar.offsetWidth; bar.classList.add('is-on'); } }
  doc.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    if (a.target && a.target !== '_self' || a.hasAttribute('data-no-progress') || a.hasAttribute('download')) return;
    if (a.origin !== location.origin || (a.pathname === location.pathname && a.search === location.search && a.hash)) return;
    progress();
  });
  window.addEventListener('pageshow', function (e) {
    if (bar) bar.classList.remove('is-on');
    if (e.persisted) doc.querySelectorAll('button[data-saved-label]').forEach(function (b) { b.textContent = b.getAttribute('data-saved-label'); b.disabled = false; });
  });

  /* ---- Dirty guard ---- */
  var dirty = new Set();
  function setDirty(form, on) {
    if (on) dirty.add(form); else dirty.delete(form);
    form.querySelectorAll('[data-dirty-note]').forEach(function (n) { n.hidden = !on; });
    doc.querySelectorAll('[data-dirty-note][data-for="' + form.id + '"]').forEach(function (n) { n.hidden = !on; });
    doc.querySelectorAll('[data-needs-saved="' + form.id + '"]').forEach(function (b) {
      if (b.tagName !== 'A') { b.disabled = on; return; }
      if (on) { b.setAttribute('aria-disabled', 'true'); b.tabIndex = -1; } else { b.removeAttribute('aria-disabled'); b.removeAttribute('tabindex'); }
    });
  }
  function guarded(e) { var f = e.target.form || (e.target.closest && e.target.closest('form')); return f && f.hasAttribute('data-dirty-guard') && !e.target.hasAttribute('data-dirty-ignore') ? f : null; }
  ['input', 'change'].forEach(function (ev) { doc.addEventListener(ev, function (e) { var f = guarded(e); if (f) setDirty(f, true); }); });
  doc.addEventListener('reset', function (e) { if (e.target.hasAttribute && e.target.hasAttribute('data-dirty-guard')) setDirty(e.target, false); });
  window.addEventListener('beforeunload', function (e) { if (dirty.size) { e.preventDefault(); e.returnValue = ''; } });
  doc.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      if (body.classList.contains('rail-open')) drawer(false);
      if (toasts) toasts.querySelectorAll('.toast').forEach(function (t) { t.remove(); });
    }
    if ((e.metaKey || e.ctrlKey) && !e.altKey && e.key.toLowerCase() === 's') {
      var f = (doc.activeElement && doc.activeElement.form && doc.activeElement.form.hasAttribute('data-dirty-guard') && doc.activeElement.form) || doc.querySelector('form[data-dirty-guard]');
      if (f) { e.preventDefault(); if (f.requestSubmit) f.requestSubmit(); else f.submit(); }
    }
  });

  /* ---- Form submit: leave-guard off, progress on, busy label ----
   * On window, so it runs after every document- and form-level submit listener and sees their preventDefault(). */
  window.addEventListener('submit', function (e) {
    var f = e.target;
    if (e.defaultPrevented) return;
    if (f.hasAttribute('data-dirty-guard')) setDirty(f, false);
    progress();
    if (f.hasAttribute('data-no-busy')) return;
    var b = e.submitter || f.querySelector('button[type=submit]');
    if (!b) return;
    setTimeout(function () { // after the browser has read the button's own name/value
      b.setAttribute('data-saved-label', b.textContent);
      b.textContent = b.getAttribute('data-busy') || 'Saving…';
      b.disabled = true;
    }, 0);
  });

  /* ---- New page form: the address follows the title until it is typed in ---- */
  var slugSrc = doc.querySelector('[data-slug-to]');
  if (slugSrc) {
    var slugOut = doc.querySelector(slugSrc.getAttribute('data-slug-to')), typed = slugOut.value !== '';
    slugOut.addEventListener('input', function () { typed = slugOut.value !== ''; });
    slugSrc.addEventListener('input', function () {
      if (!typed) slugOut.value = slugSrc.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    });
  }

  /* ---- Page editor ---- */
  // On DOMContentLoaded: by then the deferred quill.js and media.js after this file have run.
  doc.addEventListener('DOMContentLoaded', function () {
    var form = doc.getElementById('page-form');
    if (!form) return;
    if (form.hasAttribute('data-dirty')) setDirty(form, true);
    var list = doc.getElementById('sections'), empty = doc.getElementById('sections-empty'), seq = 1000; // seq: fresh indexes above any the server used
    var plain = function (s) { return s.replace(/\*([^*\n]+)\*/g, '$1').trim(); };
    var len = function (s) { return Array.from(s).length; };

    function rich(root) {
      if (!window.Quill) return; // the plain textarea still works
      root.querySelectorAll('textarea[data-richtext]').forEach(function (ta) {
        if ('_quill' in ta) return; // mounted, or failed once
        var wrap = doc.createElement('div'), box = doc.createElement('div'), label = doc.getElementById(ta.id + '-l'), q;
        box.innerHTML = ta.value; // sanitized on the server when it was saved
        wrap.appendChild(box); // Quill puts its toolbar beside box, inside wrap, so a failure removes both
        ta.parentNode.insertBefore(wrap, ta);
        try {
          q = new Quill(box, {
            theme: 'snow', formats: ['header', 'bold', 'italic', 'link', 'list'],
            modules: { toolbar: [[{ header: [2, 3, false] }], ['bold', 'italic', 'link'], [{ list: 'ordered' }, { list: 'bullet' }], ['clean']] }
          });
        } catch (err) { // keep the plain textarea and let the rest of the editor set up
          wrap.remove();
          ta._quill = null;
          if (window.console) console.error(err);
          return;
        }
        ta._quill = q;
        ta.hidden = true;
        q.root.setAttribute('aria-labelledby', ta.id + '-l');
        q.root.setAttribute('role', 'textbox');
        q.root.setAttribute('aria-multiline', 'true');
        if (label) label.addEventListener('click', function () { q.focus(); });
        q.on('text-change', function (d, old, source) {
          ta.value = q.getLength() > 1 ? q.getSemanticHTML().replace(/&nbsp;/g, ' ') : ''; // 2.0.3 writes every space as &nbsp;
          if (source === 'user') ta.dispatchEvent(new Event('input', { bubbles: true }));
        });
      });
    }

    function renumber() {
      var cards = [].slice.call(list.children);
      cards.forEach(function (c, n) {
        c.querySelector('[data-num]').textContent = n + 1;
        c.querySelector('[data-sec-move="up"]').disabled = n === 0;
        c.querySelector('[data-sec-move="down"]').disabled = n === cards.length - 1;
        c.querySelectorAll('[data-items]').forEach(function (items) {
          [].forEach.call(items.children, function (it, k) { it.querySelector('[data-num]').textContent = k + 1; });
        });
      });
      doc.querySelector('[data-sec-count]').textContent = cards.length;
      empty.hidden = cards.length > 0;
    }
    function changed() { renumber(); setDirty(form, true); }
    function focusFirst(el) { var f = el.querySelector('input:not([type=hidden]), select, textarea:not([hidden]), .ql-editor'); if (f) f.focus(); }

    // On document, after the click-again handler: a first click on Remove only arms it (defaultPrevented).
    doc.addEventListener('click', function (e) {
      var b = e.target.closest && e.target.closest('button');
      if (!b || e.defaultPrevented || !form.contains(b)) return;
      var card = b.closest('[data-section]');
      if (b.hasAttribute('data-sec-toggle')) {
        var open = b.getAttribute('aria-expanded') !== 'true';
        b.setAttribute('aria-expanded', open ? 'true' : 'false');
        doc.getElementById(b.getAttribute('aria-controls')).hidden = !open;
      } else if (b.hasAttribute('data-sec-move')) {
        var up = b.getAttribute('data-sec-move') === 'up', sib = up ? card.previousElementSibling : card.nextElementSibling;
        if (!sib) return;
        list.insertBefore(card, up ? sib : sib.nextElementSibling);
        changed();
        (b.disabled ? card.querySelector('[data-sec-move]:not(:disabled)') : b).focus(); // moving the node drops focus
      } else if (b.hasAttribute('data-sec-remove')) {
        var next = card.nextElementSibling || card.previousElementSibling;
        card.remove();
        changed();
        (next ? next.querySelector('[data-sec-toggle]') : doc.getElementById('add-type')).focus();
      } else if (b.hasAttribute('data-item-add')) {
        var field = b.closest('[data-list]'), holder = doc.createElement('div');
        holder.innerHTML = field.querySelector('template[data-item-tpl]').innerHTML.replace(/__j__/g, String(seq++));
        var item = holder.firstElementChild;
        field.querySelector('[data-items]').appendChild(item);
        rich(item);
        changed();
        focusFirst(item);
      } else if (b.hasAttribute('data-item-remove')) {
        var lf = b.closest('[data-list]');
        b.closest('[data-item]').remove();
        changed();
        lf.querySelector('[data-item-add]').focus();
      }
    });

    var addBtn = doc.getElementById('add-section'), addType = doc.getElementById('add-type');
    addBtn.addEventListener('click', function () {
      if (!addType.value) { addType.focus(); return; }
      addBtn.disabled = true;
      fetch('/admin/section-form?type=' + encodeURIComponent(addType.value) + '&i=' + (seq++), { headers: { Accept: 'text/html' } })
        .then(function (r) { if (!r.ok || r.redirected) throw new Error(r.status); return r.text(); }) // redirected = logged out
        .then(function (html) {
          list.insertAdjacentHTML('beforeend', html);
          var card = list.lastElementChild;
          rich(card);
          changed();
          addType.value = '';
          card.scrollIntoView({ block: 'start' });
          focusFirst(card);
        })
        .catch(function () { toast('The section could not be added. Reload the page (you may need to log in again) and try again.', 'error'); })
        .then(function () { addBtn.disabled = false; });
    });

    // Tabs: the server picks the first one (errors first); the choice is posted so a save comes back to it.
    var tabs = [].slice.call(form.querySelectorAll('[data-tab]')), tabInput = form.querySelector('input[name=tab]');
    function show(t) {
      tabs.forEach(function (x) {
        var on = x === t;
        x.setAttribute('aria-selected', on ? 'true' : 'false');
        x.tabIndex = on ? 0 : -1;
        doc.getElementById(x.getAttribute('aria-controls')).hidden = !on;
      });
      tabInput.value = t.getAttribute('data-tab');
    }
    tabs.forEach(function (t, n) {
      t.addEventListener('click', function () { show(t); });
      t.addEventListener('keydown', function (e) {
        var d = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
        if (d) { var to = tabs[(n + d + tabs.length) % tabs.length]; show(to); to.focus(); }
      });
    });

    // Counters "n/max": amber under the minimum, warn over the maximum.
    form.querySelectorAll('[data-count]').forEach(function (inp) {
      var lim = inp.getAttribute('data-count').split('-'), c = doc.createElement('p');
      c.className = 'counter';
      inp.insertAdjacentElement('afterend', c);
      function update() {
        var n = len(plain(inp.value));
        c.textContent = n + '/' + lim[1];
        c.classList.toggle('is-under', n < +lim[0]);
        c.classList.toggle('is-over', n > +lim[1]);
      }
      inp.addEventListener('input', update);
      update();
    });

    // Placeholders show what the page uses when a field is empty; the snippet shows what Google would.
    var title = doc.getElementById('f-title'), slug = doc.getElementById('f-slug'), mt = doc.getElementById('f-meta_title'),
      md = doc.getElementById('f-meta_description'), h1 = doc.getElementById('f-h1'), snip = form.querySelector('[data-snippet]');
    function cut(s, n) { var a = Array.from(s); return a.length > n ? a.slice(0, n - 1).join('') + '…' : s; }
    function snippet() {
      h1.placeholder = title.value;
      mt.placeholder = plain(title.value);
      var segs = (snip.getAttribute('data-prefix') + slug.value.trim()).split('/').filter(Boolean);
      if (segs.join('/') === 'home') segs = [];
      snip.querySelector('[data-snip-url]').textContent = [snip.getAttribute('data-host')].concat(segs).join(' › ');
      snip.querySelector('[data-snip-title]').textContent = cut(plain(mt.value) || plain(title.value) || 'Untitled', 60);
      var d = plain(md.value) || md.getAttribute('data-fallback');
      snip.querySelector('[data-snip-desc]').textContent = d ? cut(d, 160) : 'No description yet: Google will pick some text from the page.';
    }
    form.addEventListener('input', function (e) { if ([title, slug, mt, md].indexOf(e.target) > -1) snippet(); });

    rich(form);
    renumber();
    snippet();
  });

  window.Admin = { toast: toast, setDirty: setDirty };
})();
