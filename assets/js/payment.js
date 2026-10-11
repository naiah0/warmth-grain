(function () {
  const form = document.getElementById('payForm');
  if (!form) return;
  const panels = form.querySelectorAll('.qr-panel'), proof = form.elements.proof, btn = document.getElementById('paidBtn');
  const proofBox = document.getElementById('proofBox'), fileName = document.getElementById('fileName'), pick = document.getElementById('pickMsg');
  function sync() {
    const sel = form.querySelector('input[name=method]:checked'), m = sel ? sel.value : '';
    form.querySelectorAll('.pay-option').forEach(l => l.classList.toggle('selected', l.querySelector('input').checked));
    panels.forEach(p => p.style.display = p.dataset.method === m ? 'block' : 'none');
    pick.style.display = m ? 'none' : 'block';
    const cash = m === 'Cash';
    proofBox.style.display = (m && !cash) ? 'block' : 'none';
    const has = proof.files && proof.files.length > 0;
    fileName.textContent = has ? proof.files[0].name : 'No file attached yet';
    btn.disabled = !m || (!cash && !has);
  }
  form.addEventListener('change', sync);
  form.addEventListener('submit', e => {
    if (btn.disabled) { e.preventDefault(); return; }
    if (!confirm('Confirm that you have made this payment?')) e.preventDefault();
  });
  sync();
})();