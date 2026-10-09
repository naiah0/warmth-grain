const rows = document.querySelectorAll('.cart-row');
const totalEl = document.getElementById('cartTotal');
const fmt = n => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2 });

function recalc() {
  let total = 0;
  rows.forEach(row => {
    const qty = Math.max(0, parseInt(row.querySelector('.qty').value, 10) || 0);
    const sub = qty * parseFloat(row.dataset.price);
    row.querySelector('.sub').textContent = fmt(sub);
    total += sub;
  });
  totalEl.textContent = fmt(total);
}
rows.forEach(row => row.querySelector('.qty').addEventListener('input', recalc));