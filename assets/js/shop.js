const form = document.getElementById('shopForm');
if (form) {
  const q = form.elements.q;
  let t;
  q.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => form.submit(), 700); });
  ['sort', 'stock'].forEach(n => form.elements[n].addEventListener('change', () => form.submit()));
  if (q.value) { q.focus(); q.setSelectionRange(q.value.length, q.value.length); }
}