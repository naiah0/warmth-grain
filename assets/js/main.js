// Navigation: desktop = icon rail, mobile = drawer (always in sync with window size)
const mq = window.matchMedia('(max-width: 900px)');
function syncNav() {
  if (document.body.classList.contains('admin-body')) return;
  if (mq.matches) document.body.classList.add('nav-closed');
  else document.body.classList.toggle('nav-closed', localStorage.getItem('wgNavClosed') === '1');
}
if (mq.addEventListener) mq.addEventListener('change', syncNav);
syncNav();
document.querySelectorAll('.nav-toggle').forEach(b => b.addEventListener('click', () => {
  document.body.classList.toggle('nav-closed');
  if (!mq.matches) localStorage.setItem('wgNavClosed', document.body.classList.contains('nav-closed') ? '1' : '0');
}));
const am = document.getElementById('adminMenuBtn');
if (am) am.addEventListener('click', () => document.getElementById('adminLinks').classList.toggle('open'));

// Home slideshow
const slides = document.querySelectorAll('.hero-slide');
if (slides.length > 1) {
  let i = 0;
  setInterval(() => { slides[i].classList.remove('active'); i = (i + 1) % slides.length; slides[i].classList.add('active'); }, 5000);
}

// Flash messages
document.querySelectorAll('.flash').forEach(a => {
  const hide = () => { a.style.transition = 'opacity .5s'; a.style.opacity = '0'; setTimeout(() => a.remove(), 500); };
  setTimeout(hide, 6000); a.addEventListener('click', hide);
});

// Small pop-up message
function toast(msg, isError) {
  let t = document.getElementById('wgToast');
  if (!t) { t = document.createElement('div'); t.id = 'wgToast'; t.className = 'toast'; document.body.appendChild(t); }
  t.textContent = msg; t.classList.toggle('err', !!isError); t.classList.add('show');
  clearTimeout(t._h); t._h = setTimeout(() => t.classList.remove('show'), 2200);
}

// Add to cart without leaving the page or jumping to the top
document.addEventListener('submit', e => {
  const f = e.target.closest('form[data-ajax-cart]');
  if (!f) return;
  e.preventDefault();
  fetch(f.action, { method: 'POST', body: new FormData(f), headers: { 'X-Requested-With': 'fetch' } })
    .then(r => r.json())
    .then(d => {
      toast(d.message, !d.ok);
      if (d.count !== undefined) document.querySelectorAll('.cart-link .cart-badge').forEach(b => b.textContent = d.count);
    })
    .catch(() => f.submit());
});

// Confirmation dialogs
document.addEventListener('click', e => {
  const el = e.target.closest('[data-confirm]');
  if (el && !confirm(el.dataset.confirm)) e.preventDefault();
});

// Show/hide password
document.querySelectorAll('[data-toggle-password]').forEach(btn => btn.addEventListener('click', () => {
  const i = document.getElementById(btn.dataset.togglePassword);
  i.type = i.type === 'password' ? 'text' : 'password';
  btn.textContent = i.type === 'password' ? 'Show' : 'Hide';
}));

// Quantity +/- controls
document.addEventListener('click', e => {
  const b = e.target.closest('.qty-control [data-step]');
  if (!b) return;
  const input = b.parentElement.querySelector('input');
  const min = parseInt(input.min) || 1, max = parseInt(input.max) || 999;
  input.value = Math.min(max, Math.max(min, (parseInt(input.value) || min) + parseInt(b.dataset.step)));
  input.dispatchEvent(new Event('input', { bubbles: true }));
});

// Clickable cards
document.addEventListener('click', e => {
  const c = e.target.closest('[data-href]');
  if (c && !e.target.closest('button, a, form, input, select')) location.href = c.dataset.href;
});

// Tabs
document.querySelectorAll('[data-tabs]').forEach(box => {
  const open = name => {
    box.querySelectorAll('.tab').forEach(t => t.classList.toggle('active', t.dataset.tab === name));
    box.querySelectorAll('.tab-panel').forEach(p => p.classList.toggle('active', p.dataset.panel === name));
  };
  box.querySelectorAll('.tab').forEach(t => t.addEventListener('click', () => open(t.dataset.tab)));
  const h = location.hash.replace('#', '');
  open(box.querySelector('.tab[data-tab="' + h + '"]') ? h : box.querySelector('.tab').dataset.tab);
});

// Print only the QR code
document.querySelectorAll('[data-print-qr]').forEach(b => b.addEventListener('click', () => {
  document.body.classList.add('print-qr');
  window.print();
  setTimeout(() => document.body.classList.remove('print-qr'), 500);
}));