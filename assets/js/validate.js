document.querySelectorAll('form[data-validate]').forEach(form => {
  const anchorOf = f => f.parentElement.classList.contains('pw') ? f.parentElement : f;
  function clearError(field) {
    field.style.borderColor = '';
    const n = anchorOf(field).nextElementSibling;
    if (n && n.classList.contains('field-error')) n.remove();
  }
  function showError(field, msg) {
    clearError(field);
    field.style.borderColor = '#a33';
    const p = document.createElement('small');
    p.className = 'field-error'; p.textContent = msg;
    anchorOf(field).insertAdjacentElement('afterend', p);
  }
  function check(field) {
    const v = field.value.trim();
    const label = (form.querySelector('label[for="' + field.id + '"]')?.textContent || field.name).trim();
    if (field.required && v === '') return label + ' is required.';
    if (v === '') return '';
    if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return 'Enter a valid email address.';
    if (field.dataset.phone !== undefined && !/^09\d{9}$/.test(v)) return 'Phone must look like 09XXXXXXXXX.';
    if (field.minLength > 0 && v.length < field.minLength) return 'Must be at least ' + field.minLength + ' characters.';
    if (field.type === 'number') {
      const n = parseFloat(v);
      if (field.min !== '' && n < parseFloat(field.min)) return 'Cannot be less than ' + field.min + '.';
      if (field.max !== '' && n > parseFloat(field.max)) return 'Cannot be more than ' + field.max + '.';
    }
    if (field.dataset.match) {
      const other = form.querySelector('[name="' + field.dataset.match + '"]');
      if (other && other.value !== field.value) return 'Passwords do not match.';
    }
    return '';
  }
  const fields = form.querySelectorAll('input:not([type=hidden]):not([type=checkbox]), select, textarea');
  fields.forEach(f => {
    f.addEventListener('blur', () => { const m = check(f); m ? showError(f, m) : clearError(f); });
    f.addEventListener('input', () => clearError(f));
  });
  form.addEventListener('submit', e => {
    let bad = null;
    fields.forEach(f => {
      if (f.offsetParent === null || f.disabled) return;
      const m = check(f);
      if (m) { showError(f, m); bad = bad || f; } else clearError(f);
    });
    if (bad) { e.preventDefault(); bad.focus(); }
  });
});