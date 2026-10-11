(function () {
  const form = document.getElementById('repairForm');
  if (!form) return;
  const f = form.elements, SC = 0.10, VAT = 0.12;
  const money = n => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const box = document.getElementById('breakdown'), errBox = document.getElementById('formErrors');
  const boxes = () => [...form.querySelectorAll('input[name="repair_service_id[]"]')];
  WGCalendar({ id: 'calendar', hiddenId: 'repair_date', labelId: 'dateLabel', unavailable: window.UNAVAILABLE });

  function refresh() {
    const sel = boxes().filter(b => b.checked);
    form.querySelectorAll('.repair-option').forEach(l => l.classList.toggle('selected', l.querySelector('input').checked));
    const sub = sel.reduce((s, b) => s + parseFloat(b.dataset.price), 0), sc = sub * SC, vat = (sub + sc) * VAT;
    box.innerHTML = (sel.map(b => '<div><span>' + b.dataset.name + '</span><span>' + money(parseFloat(b.dataset.price)) + '</span></div>').join('') || '<div><span>Choose 1 to 2 repair services</span><span></span></div>') +
      '<div><span>Subtotal</span><span>' + money(sub) + '</span></div><div><span>Service charge (10%)</span><span>' + money(sc) + '</span></div>' +
      '<div><span>VAT (12%)</span><span>' + money(vat) + '</span></div><div class="total"><span>Total</span><span>' + money(sub + sc + vat) + '</span></div>';
  }
  form.addEventListener('change', e => {
    if (e.target.name === 'repair_service_id[]' && boxes().filter(b => b.checked).length > 2) {
      e.target.checked = false;
      errBox.innerHTML = 'You can choose up to 2 repair services per booking. Untick one first.';
      errBox.style.display = 'block';
    }
    refresh();
  });
  form.addEventListener('submit', e => {
    const miss = [];
    if (!boxes().some(b => b.checked)) miss.push('Step 1: choose at least 1 repair service (up to 2).');
    if (!f.camera_brand.value.trim()) miss.push('Step 2: enter the camera brand.');
    if (!f.camera_model.value.trim()) miss.push('Step 2: enter the camera model.');
    if (!f.serial_number.value.trim()) miss.push('Step 2: enter the serial number (type "N/A" if you can\'t find it).');
    if (!f.repair_date.value) miss.push('Step 3: pick an available repair date from the calendar.');
    if (f.concerns.value.trim().length < 5) miss.push('Step 4: describe the problem or your concerns.');
    if (miss.length) {
      e.preventDefault();
      errBox.innerHTML = '<strong>You can\'t proceed to payment yet.</strong> Please complete the following:<ul class="list">' + miss.map(x => '<li>' + x + '</li>').join('') + '</ul>';
      errBox.style.display = 'block';
      errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
  refresh();
})();