/* Artificia admin media: the Media page behaviours and the reusable image picker. No dependencies; load after admin.js.
 *
 *   Media page   (#media-page) search filter, drag-and-drop upload zone, click an image for its detail dialog (edit alt).
 *   Picker       MediaPicker.open({ selected: id }) => Promise<{id, alt, thumb, url, width, height} | null>
 *                Opens a <dialog> with the /admin/media.json grid and a search box; null if cancelled.
 *   Wiring       <button type="button" data-media-pick="#f-image">Choose image</button>
 *                sets the input's value to the chosen id (and fires input/change, so the dirty guard sees it), and
 *                fills any [data-media-preview="#f-image"] <img> src/alt. Inputs: [data-media-clear="#f-image"] empties it.
 */
(function () {
  'use strict';
  var doc = document;

  function el(tag, cls, text) { var n = doc.createElement(tag); if (cls) n.className = cls; if (text != null) n.textContent = text; return n; }
  // A click on the backdrop targets the dialog itself, but so does one in the dialog's own padding: compare the position.
  function onBackdrop(dlg, e) {
    var r = dlg.getBoundingClientRect();
    return e.target === dlg && (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom);
  }

  /* ---- Picker ---- */
  var dlg, pending, seq = 0;
  function build() {
    dlg = el('dialog', 'dlg dlg--wide');
    dlg.setAttribute('aria-labelledby', 'picker-title');
    var h = el('h2', null, 'Choose an image'); h.id = 'picker-title';
    var search = el('input'); search.type = 'search'; search.placeholder = 'Find by description'; search.setAttribute('aria-label', 'Search images'); search.autocomplete = 'off';
    var more = el('a', 'btn btn--ghost btn--sm', 'Upload new image'); more.href = '/admin/media'; more.target = '_blank'; more.rel = 'noopener';
    var bar = el('div', 'filters'); bar.appendChild(search); bar.appendChild(more);
    var grid = el('ul', 'media'); grid.id = 'picker-grid';
    var status = el('p', 'hint'); status.setAttribute('aria-live', 'polite');
    var close = el('button', 'btn btn--ghost', 'Cancel'); close.type = 'button';
    dlg.appendChild(h); dlg.appendChild(bar); dlg.appendChild(status); dlg.appendChild(grid); dlg.appendChild(close);
    doc.body.appendChild(dlg);
    close.addEventListener('click', function () { dlg.close(); });
    dlg.addEventListener('click', function (e) { if (onBackdrop(dlg, e)) dlg.close(); });
    dlg.addEventListener('close', function () { var p = pending; pending = null; if (p) p(dlg._choice || null); dlg._choice = null; });
    search.addEventListener('input', function () {
      var q = search.value.trim().toLowerCase(), shown = 0;
      grid.querySelectorAll('.media__item').forEach(function (li) { var hit = li.getAttribute('data-search').indexOf(q) > -1; li.hidden = !hit; if (hit) shown++; });
      status.textContent = shown ? '' : 'No image matches that search.';
    });
    dlg._grid = grid; dlg._status = status; dlg._search = search;
  }
  function open(opts) {
    if (!dlg) build();
    if (pending) { var p = pending; pending = null; p(null); } // a second open() cancels the first
    var mine = ++seq;
    dlg._search.value = ''; dlg._choice = null; dlg._status.textContent = 'Loading…'; dlg._grid.textContent = '';
    if (!dlg.open) dlg.showModal();
    fetch('/admin/media.json', { headers: { Accept: 'application/json' } }).then(function (r) {
      if (!r.ok) throw new Error(r.status);
      return r.json();
    }).then(function (list) {
      if (mine !== seq) return; // a later open() owns the grid now
      dlg._status.textContent = list.length ? '' : 'No images yet. Upload one on the Media page first.';
      list.forEach(function (m) {
        var li = el('li', 'media__item'), b = el('button', 'media__open'), t = el('span', 'media__thumb'), img = el('img');
        li.setAttribute('data-search', (m.alt || '').toLowerCase());
        b.type = 'button'; if (opts && opts.selected === m.id) b.setAttribute('aria-pressed', 'true');
        img.src = m.thumb; img.alt = ''; img.loading = 'lazy';
        t.appendChild(img); b.appendChild(t); b.appendChild(el('span', 'media__name', m.alt || 'No description'));
        b.addEventListener('click', function () { dlg._choice = m; dlg.close(); });
        li.appendChild(b); dlg._grid.appendChild(li);
      });
    }).catch(function () { if (mine === seq) dlg._status.textContent = 'The images could not be loaded. Close this and try again.'; });
    return new Promise(function (resolve) { pending = resolve; });
  }
  window.MediaPicker = { open: open };

  doc.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('[data-media-pick]');
    var clear = e.target.closest && e.target.closest('[data-media-clear]');
    var sel = b ? b.getAttribute('data-media-pick') : clear ? clear.getAttribute('data-media-clear') : null;
    if (!sel) return;
    var input = doc.querySelector(sel);
    if (!input) return;
    function set(m) {
      input.value = m ? m.id : '';
      input.dispatchEvent(new Event('input', { bubbles: true })); input.dispatchEvent(new Event('change', { bubbles: true }));
      doc.querySelectorAll('[data-media-preview="' + sel + '"]').forEach(function (img) { img.src = m ? m.thumb : 'data:,'; img.alt = m ? m.alt : ''; img.hidden = !m; });
    }
    if (clear) return set(null);
    open({ selected: parseInt(input.value, 10) || null }).then(function (m) { if (m) set(m); });
  });

  /* ---- Media page ---- */
  var page = doc.getElementById('media-page');
  if (!page) return;

  var search = doc.getElementById('media-search'), items = page.querySelectorAll('.media__item'), none = doc.getElementById('media-none'), count = doc.getElementById('media-count');
  if (search) search.addEventListener('input', function () {
    var q = search.value.trim().toLowerCase(), shown = 0;
    items.forEach(function (li) { var hit = li.getAttribute('data-search').indexOf(q) > -1; li.hidden = !hit; if (hit) shown++; });
    none.hidden = shown > 0;
    count.textContent = q ? shown + ' of ' + items.length : '';
  });

  var zone = doc.getElementById('dropzone'), file = doc.getElementById('f-file'), label = doc.getElementById('dropzone-name'), idle = label.textContent;
  function named() { label.textContent = file.files.length ? file.files[0].name : idle; if (file.files.length) doc.getElementById('f-alt').focus(); }
  file.addEventListener('change', named);
  ['dragenter', 'dragover'].forEach(function (ev) { zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('is-over'); }); });
  ['dragleave', 'drop'].forEach(function (ev) { zone.addEventListener(ev, function () { zone.classList.remove('is-over'); }); });
  zone.addEventListener('drop', function (e) {
    e.preventDefault();
    if (e.dataTransfer.files.length) { file.files = e.dataTransfer.files; named(); }
  });
  // A file dropped outside the zone would navigate away from the page.
  ['dragover', 'drop'].forEach(function (ev) { window.addEventListener(ev, function (e) { if (!zone.contains(e.target)) e.preventDefault(); }); });

  var detail = doc.getElementById('media-detail');
  if (!detail) return;
  var f = {
    id: doc.getElementById('md-id'), alt: doc.getElementById('md-alt'), img: doc.getElementById('md-img'),
    size: doc.getElementById('md-size'), url: doc.getElementById('md-url')
  };
  function show(b, altOverride) {
    f.id.value = b.getAttribute('data-id');
    f.alt.value = altOverride != null ? altOverride : b.getAttribute('data-alt');
    f.img.src = b.getAttribute('data-thumb'); f.img.alt = b.getAttribute('data-alt');
    var kb = b.getAttribute('data-kb');
    f.size.textContent = b.getAttribute('data-w') + ' × ' + b.getAttribute('data-h') + ' px' + (kb !== '0' ? ', ' + kb + ' KB' : '');
    f.url.href = f.url.textContent = b.getAttribute('data-url');
    detail.showModal();
    f.alt.focus();
  }
  page.querySelectorAll('.media__open').forEach(function (b) { b.addEventListener('click', function () { show(b); }); });
  detail.querySelector('[data-close]').addEventListener('click', function () { detail.close(); });
  detail.addEventListener('click', function (e) { if (onBackdrop(detail, e)) detail.close(); });

  // After a rejected save the page is re-rendered: reopen the same image with the text that was typed.
  var again = page.getAttribute('data-reopen');
  if (again) {
    var b = page.querySelector('.media__open[data-id="' + again + '"]');
    if (b) show(b, f.alt.value);
  }
})();
