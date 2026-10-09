(function () {
  const form = document.getElementById('repairForm');
  if (!form) return;
  const f = form.elements;
  const errBox = document.getElementById('repairErrors');
  const info = document.getElementById('repairInfo');
  const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  function refresh() {
    const o = f.repair_service_id.options[f.repair_service_id.selectedIndex];
    if (!o.value) { info.textContent = 'Select a repair service to see what is included.'; return; }
    const items = (o.dataset.incl || '').split('|').filter(Boolean).map(i => '<li>' + esc(i) + '</li>').join('');
    info.innerHTML = '<strong>' + esc(o.text) + '</strong><br>' + esc(o.dataset.desc || '') +
      '<ul>' + items + '</ul><small>Starting at ' + esc(o.dataset.price) + ' · ' + esc(o.dataset.turn || 'TBA') + '. Final cost is set after diagnosis.</small>';
  }
  f.repair_service_id.addEventListener('change', refresh);
  document.querySelectorAll('[data-repair]').forEach(b => b.addEventListener('click', () => {
    f.repair_service_id.value = b.dataset.repair; refresh();
  }));

  form.addEventListener('submit', e => {
    const errs = [];
    if (!f.repair_service_id.value) errs.push('Please select a repair service.');
    if (!f.camera_brand.value.trim()) errs.push('Camera brand is required.');
    if (!f.camera_model.value.trim()) errs.push('Camera model is required.');
    if (!f.issue.value) errs.push('Select the main problem.');
    if (!f.dropoff_date.value || f.dropoff_date.value < f.dropoff_date.min) errs.push('Choose a drop-off date from tomorrow onwards.');
    if (errs.length) {
      e.preventDefault();
      errBox.innerHTML = errs.map(x => '<div>' + esc(x) + '</div>').join('');
      errBox.style.display = 'block';
      errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }
    errBox.style.display = 'none';
    if (!confirm('Submit this repair request?')) e.preventDefault();
  });

  if (window.PRESELECT) f.repair_service_id.value = String(window.PRESELECT);
  refresh();
  if (window.OPEN_REPAIR) openModal('repairModal');
})();