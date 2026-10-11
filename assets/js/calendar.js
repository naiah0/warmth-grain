window.WGCalendar = function (o) {
  const cal = document.getElementById(o.id), hidden = document.getElementById(o.hiddenId), label = document.getElementById(o.labelId);
  if (!cal || !hidden) return;
  const unavail = new Set(o.unavailable || []);
  const today = new Date(); today.setHours(0, 0, 0, 0);
  const maxDate = new Date(today); maxDate.setDate(maxDate.getDate() + 130);
  let view = new Date(today.getFullYear(), today.getMonth(), 1);
  const iso = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

  function render() {
    const y = view.getFullYear(), m = view.getMonth();
    let h = '<div class="cal-head"><button type="button" data-nav="-1">&lsaquo;</button><strong>' +
      view.toLocaleString('en-US', { month: 'long', year: 'numeric' }) + '</strong><button type="button" data-nav="1">&rsaquo;</button></div><div class="cal-grid">';
    ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(d => h += '<span class="cal-dow">' + d + '</span>');
    for (let i = 0; i < new Date(y, m, 1).getDay(); i++) h += '<span></span>';
    for (let d = 1; d <= new Date(y, m + 1, 0).getDate(); d++) {
      const dt = new Date(y, m, d), key = iso(dt);
      const off = dt <= today || dt > maxDate || unavail.has(key);
      h += '<button type="button" class="cal-day' + (hidden.value === key ? ' sel' : '') + '" data-date="' + key + '"' + (off ? ' disabled' : '') + '>' + d + '</button>';
    }
    cal.innerHTML = h + '</div>';
  }
  cal.addEventListener('click', e => {
    const nav = e.target.closest('[data-nav]'), day = e.target.closest('[data-date]');
    if (nav) {
      const next = new Date(view.getFullYear(), view.getMonth() + parseInt(nav.dataset.nav), 1);
      if (next >= new Date(today.getFullYear(), today.getMonth(), 1) && next <= maxDate) { view = next; render(); }
    } else if (day && !day.disabled) {
      hidden.value = day.dataset.date;
      if (label) label.textContent = new Date(day.dataset.date + 'T00:00').toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
      hidden.dispatchEvent(new Event('change', { bubbles: true }));
      render();
    }
  });
  render();
};