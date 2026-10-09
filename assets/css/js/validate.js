document.querySelectorAll('form[data-validate]').forEach(form => {

  function showError(field, msg) {
    clearError(field);
    field.style.borderColor = '#a33';
    const p = document.createElement('small');
    p.className = 'field-error';
    p.style.cssText = 'color:#a33;display:block;margin-top:.2rem';
    p.textContent = msg;
    field.insertAdjacentElement('afterend', p);
  }

  function clearError(field) {
    field.style.borderColor = '';
    const next = field.nextElementSibling;
    if (next && next.classList.contains('field-error')) next.remove();
  }

  function check(field) {
    const v = field.value.trim();
    const label = (form.querySelector(`label[for="${field.id}"]`)?.textContent || field.name).trim();

    if (field.required && v === '') return `${label} is required.`;
    if (v === '') return '';

    if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v))
      return 'Enter a valid email address.';

    if (field.dataset.phone !== undefined && !/^09\d{9}$/.test(v))
      return 'Phone must look like 09XXXXXXXXX.';

    if (field.minLength > 0 && v.length < field.minLength)
      return `${label} must be at least ${field.minLength} characters.`;

    if (field.type === 'number') {
      const n = parseFloat(v);
      if (field.min !== '' && n < parseFloat(field.min)) return `${label} cannot be less than ${field.min}.`;
      if (field.max !== '' && n > parseFloat(field.max)) return `${label} cannot be more than ${field.max}.`;
    }

    if (field.dataset.match) {
      const other = form.querySelector(`[name="${field.dataset.match}"]`);
      if (other && other.value !== field.value) return 'Passwords do not match.';
    }
    return '';
  }

  const fields = form.querySelectorAll('input:not([type=hidden]):not([type=checkbox]), select, textarea');

  fields.forEach(f => {
    f.addEventListener('blur', () => {
      const msg = check(f);
      msg ? showError(f, msg) : clearError(f);
    });
    f.addEventListener('input', () => clearError(f));
  });

  form.addEventListener('submit', (e) => {
    let firstBad = null;
    fields.forEach(f => {
      if (f.offsetParent === null || f.disabled) return;   // skip hidden/disabled
      const msg = check(f);
      if (msg) { showError(f, msg); firstBad = firstBad || f; }
      else clearError(f);
    });
    if (firstBad) { e.preventDefault(); firstBad.focus(); }
  });
});