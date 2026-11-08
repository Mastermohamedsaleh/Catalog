/* =========================================================
   CatalogPro UI Prototype – minimal vanilla JS (no deps besides Bootstrap)
   In Laravel: replace the mock handlers with real form posts, and
   trigger showToast() from session('success') / session('error').
   ========================================================= */
(function () {
  'use strict';

  /* ---------- Top toast alerts ---------- */
  var TYPES = {
    success: { icon: 'bi-check-circle-fill', color: '#16a34a' },
    error:   { icon: 'bi-x-circle-fill',     color: '#dc2626' },
    warning: { icon: 'bi-exclamation-triangle-fill', color: '#d97706' },
    info:    { icon: 'bi-info-circle-fill',  color: '#4f46e5' }
  };
  window.showToast = function (message, type, ms) {
    type = TYPES[type] ? type : 'success';
    var box = document.getElementById('toastTop');
    if (!box) { box = document.createElement('div'); box.id = 'toastTop'; document.body.appendChild(box); }
    var t = TYPES[type], el = document.createElement('div');
    el.className = 'toast-top d-flex align-items-center gap-3 p-3';
    el.style.setProperty('--t', t.color);
    el.setAttribute('role', 'alert');
    el.innerHTML = '<i class="bi ' + t.icon + ' ti"></i><div class="flex-grow-1 small fw-semibold"></div>' +
                   '<button type="button" class="btn-close btn-sm" aria-label="Close"></button>';
    el.children[1].textContent = message;               // textContent = XSS-safe
    var close = function () { el.classList.add('hiding'); setTimeout(function () { el.remove(); }, 300); };
    el.querySelector('.btn-close').addEventListener('click', close);
    box.appendChild(el);
    setTimeout(close, ms || 3500);
  };


  /* ---------- Placeholder artwork: SVG data URIs (no network, no relative paths) ---------- */
  var PAL = { g1:['#6366f1','#8b5cf6'], g2:['#0ea5e9','#2563eb'], g3:['#10b981','#059669'],
              g4:['#f59e0b','#ea580c'], g5:['#ec4899','#db2777'], g6:['#14b8a6','#0891b2'] };
  function artUri(k, n) {
    var c = PAL[k] || PAL.g1, r = (n * 37) % 60;
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" preserveAspectRatio="xMidYMid slice">' +
      '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="' + c[0] + '"/><stop offset="1" stop-color="' + c[1] + '"/></linearGradient></defs>' +
      '<rect width="400" height="300" fill="url(#g)"/>' +
      '<circle cx="' + (320 - r) + '" cy="' + (40 + r) + '" r="130" fill="#fff" opacity=".10"/>' +
      '<circle cx="' + (60 + r) + '" cy="290" r="110" fill="#fff" opacity=".08"/>' +
      '<circle cx="200" cy="150" r="70" fill="#fff" opacity=".07"/></svg>';
    return 'url("data:image/svg+xml,' + encodeURIComponent(svg) + '")';
  }
  var artCount = 0;
  window.applyArt = function (el) {
    var m = /\bg[1-6]\b/.exec(el.className); if (!m) return;
    el.style.backgroundImage = artUri(m[0], el.dataset.n || (el.dataset.n = ++artCount));
    el.style.backgroundSize = 'cover'; el.style.backgroundPosition = 'center';
  };

  document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.ph, .thumb').forEach(window.applyArt);

    /* ---------- Sidebar toggle (mobile) ---------- */
    document.querySelectorAll('[data-sidebar-toggle]').forEach(function (b) {
      b.addEventListener('click', function () { document.body.classList.toggle('sidebar-open'); });
    });

    /* ---------- Show/hide password ---------- */
    document.querySelectorAll('.toggle-pass').forEach(function (b) {
      b.addEventListener('click', function () {
        var i = b.parentElement.querySelector('input');
        var show = i.type === 'password';
        i.type = show ? 'text' : 'password';
        b.firstElementChild.className = 'bi ' + (show ? 'bi-eye-slash' : 'bi-eye');
      });
    });

    /* ---------- Form validation + mock submit ----------
       <form class="needs-validation" data-success="Msg" data-redirect="x.html" data-modal="#id"> */
    document.querySelectorAll('form.needs-validation').forEach(function (form) {
      form.setAttribute('novalidate', '');
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var pw = form.querySelector('[name=password]'), pc = form.querySelector('[name=password_confirmation]');
        if (pw && pc) pc.setCustomValidity(pw.value === pc.value ? '' : 'Passwords do not match');
        form.classList.add('was-validated');
        if (!form.checkValidity()) { window.showToast('Please fix the highlighted fields.', 'error'); return; }
        window.showToast(form.dataset.success || 'Saved successfully', 'success');
        if (form.dataset.modal) { var m = bootstrap.Modal.getInstance(document.querySelector(form.dataset.modal)); m && m.hide(); }
        if (form.dataset.redirect) setTimeout(function () { location.href = form.dataset.redirect; }, 900);
        else { form.reset(); form.classList.remove('was-validated'); form.querySelectorAll('.preview-grid').forEach(function (p) { p.innerHTML = ''; }); }
      });
      var pc2 = form.querySelector('[name=password_confirmation]'), pw2 = form.querySelector('[name=password]');
      if (pc2 && pw2) {
        var sync = function () { pc2.setCustomValidity(pw2.value === pc2.value ? '' : 'Passwords do not match'); };
        pc2.addEventListener('input', sync); pw2.addEventListener('input', sync);
      }
    });

    /* ---------- File input previews ----------
       <input type="file" data-preview="#target"> */
    document.querySelectorAll('input[type=file][data-preview]').forEach(function (inp) {
      inp.addEventListener('change', function () {
        var out = document.querySelector(inp.dataset.preview); out.innerHTML = '';
        Array.prototype.forEach.call(inp.files, function (f) {
          if (f.type.indexOf('image/') === 0) {
            var img = new Image(); img.alt = f.name; img.src = URL.createObjectURL(f);
            img.onload = function () { URL.revokeObjectURL(img.src); };
            out.appendChild(img);
          } else {
            var c = document.createElement('span'); c.className = 'file-chip';
            c.innerHTML = '<i class="bi bi-paperclip"></i> '; c.appendChild(document.createTextNode(f.name)); out.appendChild(c);
          }
        });
      });
    });

    /* ---------- Live table filter ----------  <input data-filter="#tableId"> */
    document.querySelectorAll('[data-filter]').forEach(function (inp) {
      inp.addEventListener('input', function () {
        var q = inp.value.toLowerCase();
        var s = inp.dataset.filter;   // works for table rows and for grids of .col cards
        document.querySelectorAll(s + ' tbody tr, ' + s + ' > .col').forEach(function (el) {
          el.hidden = el.textContent.toLowerCase().indexOf(q) === -1;
        });
      });
    });

    /* ---------- Delete row (mock) ----------  <button data-delete="Category"> */
    document.addEventListener('click', function (e) {
      var b = e.target.closest('[data-delete]'); if (!b) return;
      if (window.confirm('Delete this ' + b.dataset.delete.toLowerCase() + '?')) {
        b.closest('tr').remove();
        window.showToast(b.dataset.delete + ' deleted successfully', 'success');
      }
    });

    /* ---------- Edit buttons just open the modal with a toast hint (mock) ---------- */

    /* ---------- Product gallery thumbs ---------- */
    document.querySelectorAll('.gallery-thumb').forEach(function (t) {
      t.addEventListener('click', function () {
        document.querySelectorAll('.gallery-thumb').forEach(function (x) { x.classList.remove('active'); });
        t.classList.add('active');
        var main = document.getElementById('galleryMain');
        main.className = 'gallery-main ph ' + t.dataset.g; window.applyArt(main);
        main.firstElementChild.className = 'bi ' + t.dataset.icon;
      });
    });

    /* ---------- "Download Details" – generates a text file client-side (mock) ----------
       In Laravel: point the href to a route that returns Storage::download(...) */
    document.querySelectorAll('[data-download]').forEach(function (b) {
      b.addEventListener('click', function () {
        var blob = new Blob([b.dataset.download.replace(/\\n/g, '\n')], { type: 'text/plain' });
        var a = document.createElement('a'); a.href = URL.createObjectURL(blob);
        a.download = (b.dataset.filename || 'details') + '.txt'; document.body.appendChild(a); a.click(); a.remove();
        URL.revokeObjectURL(a.href);
        window.showToast('Download started', 'info');
      });
    });

    /* ---------- Logout (mock) ---------- */
    document.querySelectorAll('[data-logout]').forEach(function (b) {
      b.addEventListener('click', function (e) { e.preventDefault(); window.showToast('Signed out successfully', 'info'); setTimeout(function () { location.href = 'login.html'; }, 800); });
    });
  });
})();
