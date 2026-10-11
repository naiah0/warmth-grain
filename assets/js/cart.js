(function () {
  const rows = document.querySelectorAll('.cart-row'), box = document.getElementById('cartBreakdown');
  const money = n => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  function recalc() {
    let sub = 0;
    rows.forEach(row => {
      const qty = Math.max(1, parseInt(row.querySelector('.qty').value, 10) || 1);
      const line = qty * parseFloat(row.dataset.price);
      row.querySelector('.sub').textContent = money(line); sub += line;
    });
    const sc = sub * 0.10, vat = (sub + sc) * 0.12;
    if (box) box.innerHTML = '<div><span>Subtotal</span><span>' + money(sub) + '</span></div><div><span>Service charge (10%)</span><span>' + money(sc) + '</span></div>' +
      '<div><span>VAT (12%)</span><span>' + money(vat) + '</span></div><div class="total"><span>Total</span><span>' + money(sub + sc + vat) + '</span></div>';
  }
  rows.forEach(r => r.querySelector('.qty').addEventListener('input', recalc));
  const ful = document.getElementById('fulfillment'), addrBox = document.getElementById('addressBox');
  if (ful) ful.addEventListener('change', () => { addrBox.style.display = ful.value === 'Delivery' ? 'block' : 'none'; });

  const form = document.getElementById('cartForm'), err = document.getElementById('cartErrors');
  if (form) form.addEventListener('submit', e => {
    if (!e.submitter || e.submitter.name !== 'go') return;   // only check when pressing Proceed to Payment
    const f = form.elements, miss = [];
    if (!f.contact_name.value.trim()) miss.push('Enter your full name.');
    if (!/^09\d{9}$/.test(f.contact_phone.value.trim())) miss.push('Enter a valid phone number (09XXXXXXXXX).');
    if (f.fulfillment.value === 'Delivery' && !f.delivery_address.value.trim()) miss.push('Enter your delivery address.');
    if (miss.length) {
      e.preventDefault();
      err.innerHTML = '<strong>You can\'t proceed to payment yet.</strong> Please complete the following:<ul class="list">' + miss.map(x => '<li>' + x + '</li>').join('') + '</ul>';
      err.style.display = 'block'; err.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
  recalc();
})();