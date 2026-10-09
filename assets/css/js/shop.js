const search = document.getElementById('search');
const category = document.getElementById('category');
const cards = document.querySelectorAll('.product');
const none = document.getElementById('noResults');
const form = document.getElementById('shopForm');

function filter() {
  const q = search.value.trim().toLowerCase();
  const c = category.value;
  let shown = 0;
  cards.forEach(card => {
    const ok = card.dataset.name.includes(q) && (!c || card.dataset.cat === c);
    card.style.display = ok ? '' : 'none';
    if (ok) shown++;
  });
  none.style.display = shown ? 'none' : 'block';
}
search.addEventListener('input', filter);
category.addEventListener('change', filter);

if (form) {
  const q = form.elements.q;
  let t;
  q.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => form.submit(), 600); });
  form.elements.cat.addEventListener('change', () => form.submit());
  form.elements.sort.addEventListener('change', () => form.submit());
  if (q.value) { q.focus(); q.setSelectionRange(q.value.length, q.value.length); }
}