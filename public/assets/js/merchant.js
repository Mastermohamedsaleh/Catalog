/* =========================================================
   Merchant pages (orders + wallet) – mock logic, no dependencies.
   Orders are kept in localStorage so a new order shows up in the wallet page.
   In Laravel: delete this file's data layer and render from the database.
   IMPORTANT: profit, status and is_paid_to_wallet must be set SERVER-SIDE
   (profit = (selling_price - product.price) * stock, status = 'pending',
   is_paid_to_wallet = false). The browser values below are for preview only.
   ========================================================= */
(function () {
  'use strict';
  var CUR = 'EGP', KEY = 'cp_orders_v1';

  /* ----- mock products (cost price = what the merchant pays) ----- */
  var PRODUCTS = [
    { id: 1, name: 'UltraBook Pro 14',  price: 21000, stock: 8,  icon: 'bi-laptop',     g: 'g1' },
    { id: 2, name: 'Studio Headphones', price: 3800,  stock: 25, icon: 'bi-headphones', g: 'g4' },
    { id: 3, name: 'Fit Watch S2',      price: 2700,  stock: 40, icon: 'bi-watch',      g: 'g3' },
    { id: 4, name: 'Mirrorless X100',   price: 13500, stock: 5,  icon: 'bi-camera',     g: 'g5' },
    { id: 5, name: 'Nova Phone 12',     price: 12000, stock: 12, icon: 'bi-phone',      g: 'g2' }
  ];

  /* ----- mock orders ----- */
  function seed(id, pid, name, phone, addr, sell, qty, status, paid, date) {
    var p = PRODUCTS.filter(function (x) { return x.id === pid; })[0];
    return { id: id, product_id: pid, customer_name: name, customer_phone: phone, customer_address: addr,
             product_price: p.price, selling_price: sell, stock: qty, profit: (sell - p.price) * qty,
             status: status, is_paid_to_wallet: paid, date: date };
  }
  var SEED = [
    seed(1, 2, 'Mohamed Ali',   '01012345678', '12 Tahrir St, Dokki, Giza',        4500, 2, 'delivered', true,  '2026-10-20'),
    seed(2, 3, 'Sara Hassan',   '01123456789', '5 Nile Corniche, Maadi, Cairo',    3200, 1, 'delivered', true,  '2026-10-24'),
    seed(3, 5, 'Omar Khaled',   '01234567890', '88 El-Horreya Rd, Alexandria',    13500, 1, 'delivered', false, '2026-10-30'),
    seed(4, 1, 'Nour Ibrahim',  '01098765432', '23 Abbas El-Akkad, Nasr City',    23500, 1, 'shipped',   false, '2026-11-02'),
    seed(5, 4, 'Hana Mostafa',  '01555555555', '7 El-Gomhoria St, Mansoura',      15000, 1, 'confirmed', false, '2026-11-05'),
    seed(6, 2, 'Youssef Adel',  '01011112222', '31 Salah Salem, Heliopolis',       4300, 3, 'pending',   false, '2026-11-07'),
    seed(7, 3, 'Laila Samir',   '01122223333', '9 Faisal St, Giza',                3000, 1, 'returned',  false, '2026-10-26')
  ];

  function load() { try { var r = localStorage.getItem(KEY); if (r) return JSON.parse(r); } catch (e) {} return SEED.slice(); }
  function save(o) { try { localStorage.setItem(KEY, JSON.stringify(o)); } catch (e) {} }
  var orders = load();

  /* ----- helpers ----- */
  var STATUS = {
    pending:   ['Pending',   'badge-soft-warning'],
    confirmed: ['Confirmed', 'badge-soft-primary'],
    shipped:   ['Shipped',   'bg-info-subtle text-info-emphasis'],
    delivered: ['Delivered', 'badge-soft-success'],
    returned:  ['Returned',  'badge-soft-danger'],
    cancelled: ['Cancelled', 'bg-secondary-subtle text-secondary-emphasis']
  };
  function $(id) { return document.getElementById(id); }
  function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function money(n) { return Number(n).toLocaleString('en-US') + ' ' + CUR; }
  function prod(id) { return PRODUCTS.filter(function (p) { return p.id === Number(id); })[0]; }
  function badge(s) { var x = STATUS[s] || STATUS.pending; return '<span class="badge ' + x[1] + '">' + x[0] + '</span>'; }
  function walletBadge(o) {
    if (o.is_paid_to_wallet) return '<span class="badge badge-soft-success"><i class="bi bi-check2-circle me-1"></i>Credited</span>';
    if (o.status === 'returned' || o.status === 'cancelled') return '<span class="badge bg-secondary-subtle text-secondary-emphasis">No profit</span>';
    return '<span class="badge badge-soft-warning">Not yet</span>';
  }

  /* ----- wallet maths ----- */
  function totals() {
    var t = { available: 0, pending: 0, delivered: 0, lost: 0 };
    orders.forEach(function (o) {
      if (o.status === 'returned' || o.status === 'cancelled') { t.lost++; return; }
      if (o.status === 'delivered') t.delivered++;
      if (o.is_paid_to_wallet) t.available += o.profit; else t.pending += o.profit;
    });
    return t;
  }

  document.addEventListener('DOMContentLoaded', function () {
    var t = totals();
    if ($('navBalance')) $('navBalance').textContent = money(t.available);

    /* ============ ORDERS PAGE ============ */
    if ($('orderForm')) {
      var f = $('orderForm'), sel = $('productId'), qty = $('qty'), cost = $('productPrice'),
          sell = $('sellingPrice'), profit = $('profit'), hint = $('stockHint');

      sel.insertAdjacentHTML('beforeend', PRODUCTS.map(function (p) {
        return '<option value="' + p.id + '">' + esc(p.name) + '</option>'; }).join(''));

      var recalc = function () {
        var p = prod(sel.value), c = p ? p.price : 0, q = Number(qty.value) || 0, s = Number(sell.value) || 0;
        cost.value = p ? c : '';
        hint.textContent = p ? 'Available: ' + p.stock + ' in stock' : 'Select a product first';
        qty.max = p ? p.stock : '';
        qty.setCustomValidity(p && q > p.stock ? 'Only ' + p.stock + ' in stock' : '');
        sell.min = c;
        sell.setCustomValidity(p && s < c ? 'Selling price cannot be lower than the cost price' : '');
        var pr = p && s ? (s - c) * q : 0;
        profit.value = p && s ? pr : '';
        $('profitText').textContent = p && s ? money(pr) : '—';
        $('profitText').className = 'h4 fw-bold mb-0 ' + (pr > 0 ? 'text-success' : pr < 0 ? 'text-danger' : '');
      };
      ['input', 'change'].forEach(function (ev) { f.addEventListener(ev, recalc); });
      recalc();

      var render = function () {
        var rows = orders.slice().sort(function (a, b) { return b.id - a.id; }).map(function (o) {
          var p = prod(o.product_id) || { name: 'Product #' + o.product_id, icon: 'bi-box', g: 'g1' };
          return '<tr><td class="fw-semibold">#' + o.id + '</td>' +
            '<td><div class="d-flex align-items-center gap-2"><span class="thumb ' + p.g + '" style="width:36px;height:36px;font-size:1rem"><i class="bi ' + p.icon + '"></i></span><span class="fw-semibold">' + esc(p.name) + '</span></div></td>' +
            '<td><div class="fw-semibold">' + esc(o.customer_name) + '</div><div class="small text-muted text-nowrap"><i class="bi bi-telephone"></i> ' + esc(o.customer_phone) + '</div></td>' +
            '<td class="d-none d-xxl-table-cell small text-muted" style="min-width:180px">' + esc(o.customer_address) + '</td>' +
            '<td class="text-nowrap d-none d-xxl-table-cell">' + money(o.product_price) + '</td><td class="text-nowrap">' + money(o.selling_price) + '</td><td>' + o.stock + '</td>' +
            '<td class="text-nowrap fw-semibold ' + (o.profit >= 0 ? 'text-success' : 'text-danger') + '">' + money(o.profit) + '</td>' +
            '<td>' + badge(o.status) + '</td><td>' + walletBadge(o) + '</td></tr>';
        }).join('');
        $('ordersBody').innerHTML = rows;
        $('ordersCount').textContent = orders.length;
      };
      render();

      f.addEventListener('submit', function (e) {
        e.preventDefault(); recalc();
        f.classList.add('was-validated');
        if (!f.checkValidity()) { window.showToast('Please fix the highlighted fields.', 'error'); return; }
        var p = prod(sel.value), q = Number(qty.value), s = Number(sell.value);
        orders.push({
          id: orders.reduce(function (m, o) { return Math.max(m, o.id); }, 0) + 1,
          product_id: p.id, customer_name: $('customerName').value.trim(), customer_phone: $('customerPhone').value.trim(),
          customer_address: $('customerAddress').value.trim(), product_price: p.price, selling_price: s, stock: q,
          profit: (s - p.price) * q, status: 'pending', is_paid_to_wallet: false, date: new Date().toISOString().slice(0, 10)
        });
        save(orders); render();
        f.reset(); f.classList.remove('was-validated'); recalc();
        if ($('navBalance')) $('navBalance').textContent = money(totals().available);
        window.showToast('Order sent to admin successfully', 'success');
      });
    }

    /* ============ WALLET PAGE ============ */
    if ($('walletBody')) {
      $('wAvail').textContent = money(t.available);
      $('wPending').textContent = money(t.pending);
      $('wDelivered').textContent = t.delivered;
      $('wLost').textContent = t.lost;
      $('walletBody').innerHTML = orders.slice().sort(function (a, b) { return b.id - a.id; }).map(function (o) {
        var p = prod(o.product_id) || { name: 'Product #' + o.product_id };
        var cls = o.status === 'returned' || o.status === 'cancelled' ? 'text-muted text-decoration-line-through' : (o.is_paid_to_wallet ? 'text-success' : 'text-warning-emphasis');
        return '<tr><td class="text-muted small text-nowrap">' + esc(o.date || '') + '</td><td class="fw-semibold">#' + o.id + '</td><td>' + esc(p.name) +
               '</td><td class="d-none d-md-table-cell">' + esc(o.customer_name) + '</td><td>' + badge(o.status) + '</td><td>' + walletBadge(o) +
               '</td><td class="text-end fw-semibold text-nowrap ' + cls + '">+ ' + money(o.profit) + '</td></tr>';
      }).join('');
    }
  });
})();
