document.addEventListener('click', (e) => {
  const btn = e.target.closest('.alert-close');
  if (!btn) return;

  const alert = btn.closest('.alert');
  alert.style.transition = 'opacity 0.2s, transform 0.2s';
  alert.style.opacity    = '0';
  alert.style.transform  = 'translateX(8px)';

  setTimeout(() => alert.remove(), 200);
});