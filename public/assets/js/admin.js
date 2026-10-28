/* Artificia admin: shell behaviours, no dependencies. Later screens extend this file.
 *   Drawer         #menu-btn opens #rail under 900px; Escape or the scrim closes it.
 *   Progress bar   #progress runs on internal link clicks and form submits.
 *   Busy submit    A submitting button shows "Saving…" and disables; [data-busy="Text"] changes the text; [data-no-busy] on the form opts out.
 *   Click again    [data-confirm="Click again to delete"] on a button or link: the first click arms it for 4 s, the second goes through.
 *   Dirty guard    form[data-dirty-guard]: beforeunload warning after an edit, Cmd/Ctrl+S submits, [data-dirty-note] shown while dirty.
 *   Toasts         .toast elements from the server are timed (data-ttl); Admin.toast(msg, 'ok'|'error') adds one; Escape dismisses all.
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

  /* ---- Form submit: leave-guard off, progress on, busy label ---- */
  doc.addEventListener('submit', function (e) {
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

  window.Admin = { toast: toast, setDirty: setDirty };
})();
