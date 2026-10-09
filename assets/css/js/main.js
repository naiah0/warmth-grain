// Mobile menu
const toggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
if (toggle && sidebar) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));

// Flash messages fade out
document.querySelectorAll('.alert').forEach(a => setTimeout(() => {
  a.style.transition = 'opacity .5s'; a.style.opacity = '0'; setTimeout(() => a.remove(), 500);
}, 6000));

// Confirmation dialogs: data-confirm="message"
document.addEventListener('click', e => {
  const el = e.target.closest('[data-confirm]');
  if (el && !confirm(el.dataset.confirm)) e.preventDefault();
});

// Modals
function openModal(id) { const m = document.getElementById(id); if (m) { m.classList.add('open'); document.body.classList.add('modal-open'); } }
function closeModal(m) { m.classList.remove('open'); document.body.classList.remove('modal-open'); }
document.addEventListener('click', e => {
  const o = e.target.closest('[data-modal-open]');
  if (o) { e.preventDefault(); openModal(o.dataset.modalOpen); return; }
  if (e.target.closest('[data-modal-close]') || e.target.classList.contains('modal')) {
    const m = e.target.closest('.modal'); if (m) closeModal(m);
  }
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.modal.open').forEach(closeModal); });

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