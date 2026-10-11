(function () {
  const form = document.getElementById('bookingForm');
  if (!form) return;
  const f = form.elements, SC = 0.10, VAT = 0.12;
  const money = n => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const price = s => parseFloat(((s.options[s.selectedIndex] || {}).dataset || {}).price || 0);
  const label = s => s.options[s.selectedIndex].text.replace(/\s+—.*$/, '');
  const box = document.getElementById('breakdown'), errBox = document.getElementById('formErrors');
  const scanHint = document.getElementById('scanHint'), printHint = document.getElementById('printHint');
  let busy = false;

  WGCalendar({ id: 'calendar', hiddenId: 'dropoff_date', labelId: 'dateLabel', unavailable: window.UNAVAILABLE });

  // Sequential steps: developing -> (output) -> scanning -> printing
  function gate() {
    const dev = !!f.developing_id.value, o = f.output_option.value;
    f.output_option.disabled = !dev;
    const needScan = dev && o !== 'Prints only';
    f.scanning_id.disabled = !needScan;
    if (!needScan) f.scanning_id.value = '';
    const needPrint = dev && o !== 'Digital only' && (o === 'Prints only' || !!f.scanning_id.value);
    f.printing_id.disabled = !needPrint;
    if (!needPrint) f.printing_id.value = '';
    scanHint.textContent = !dev ? 'Locked: choose a developing process first.' : (o === 'Prints only' ? 'Not needed for prints only.' : 'Choose the scan quality you want.');
    printHint.textContent = !dev ? 'Locked: choose a developing process first.' : (o === 'Digital only' ? 'Not needed for digital only.' : (!needPrint ? 'Locked: choose a scanning option first.' : 'Choose your print size or type.'));
  }
  function refresh() {
    const n = parseInt(f.roll_count.value, 10) || 0;
    let html = '', per = 0;
    [['developing_id', 'Developing'], ['scanning_id', 'Scanning'], ['printing_id', 'Printing']].forEach(([k, l]) => {
      const s = f[k];
      if (!s.disabled && s.value) { const p = price(s); per += p; html += '<div><span>' + l + ': ' + label(s) + ' &times; ' + n + '</span><span>' + money(p * n) + '</span></div>'; }
    });
    const sub = per * n, sc = sub * SC, vat = (sub + sc) * VAT;
    box.innerHTML = (html || '<div><span>Choose your processing options</span><span></span></div>') +
      '<div><span>Subtotal</span><span>' + money(sub) + '</span></div><div><span>Service charge (10%)</span><span>' + money(sc) + '</span></div>' +
      '<div><span>VAT (12%)</span><span>' + money(vat) + '</span></div><div class="total"><span>Total</span><span>' + money(sub + sc + vat) + '</span></div>';
  }
  function applyPackage() {
    const o = f.package_id.options[f.package_id.selectedIndex];
    if (!o.value) return;
    busy = true;
    f.developing_id.value = o.dataset.dev; gate();
    f.output_option.value = o.dataset.output; gate();
    f.scanning_id.value = o.dataset.scan || ''; gate();
    f.printing_id.value = o.dataset.print || ''; gate();
    busy = false; refresh();
  }
  f.package_id.addEventListener('change', () => { applyPackage(); refresh(); });
  ['developing_id', 'output_option', 'scanning_id', 'printing_id'].forEach(k => f[k].addEventListener('change', () => {
    if (!busy) f.package_id.value = '';
    gate(); refresh();
  }));
  f.roll_count.addEventListener('input', refresh);

  form.addEventListener('submit', e => {
    const miss = [], o = f.output_option.value;
    if (!f.developing_id.value) miss.push('Step 1: choose a developing process.');
    else {
      if (o !== 'Prints only' && !f.scanning_id.value) miss.push('Step 1: choose a scanning option.');
      if (o !== 'Digital only' && !f.printing_id.value) miss.push('Step 1: choose a printing option.');
    }
    const n = parseInt(f.roll_count.value, 10);
    if (!(n >= 1 && n <= 50)) miss.push('Step 2: number of rolls must be between 1 and 50.');
    if (!f.dropoff_date.value) miss.push('Step 3: pick an available drop-off date from the calendar.');
    if (!f.time_slot.value) miss.push('Step 3: choose a drop-off time slot.');
    if (!f.notes.value.trim()) miss.push('Step 4: fill in the notes (type "None" if you have no special instructions).');
    if (miss.length) {
      e.preventDefault();
      errBox.innerHTML = '<strong>You can\'t proceed to payment yet.</strong> Please complete the following:<ul class="list">' + miss.map(x => '<li>' + x + '</li>').join('') + '</ul>';
      errBox.style.display = 'block';
      errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
  gate(); refresh();
  if (f.package_id.value) applyPackage();
})();