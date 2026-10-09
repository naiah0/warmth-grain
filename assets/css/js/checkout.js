const ful = document.getElementById('fulfillment');
const box = document.getElementById('addressBox');
const addr = document.getElementById('delivery_address');
const form = document.getElementById('checkoutForm');
ful.addEventListener('change', () => {
  const d = ful.value === 'Delivery';
  box.style.display = d ? 'block' : 'none';
  addr.required = d;
});
form.addEventListener('submit', e => { if (!confirm('Place this order?')) e.preventDefault(); });