export function initApplicationOverlay() {
  const dialog = document.querySelector('[data-application-overlay]');
  const closeButton = dialog?.querySelector('[data-application-close]');

  if (!dialog || !closeButton || typeof dialog.showModal !== 'function') return;

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let closeTimer = 0;

  const open = () => {
    if (dialog.open) return;
    dialog.showModal();
    dialog.scrollTop = 0;
    document.documentElement.classList.add('contact-overlay-open');
    dialog.getBoundingClientRect();
    requestAnimationFrame(() => dialog.classList.add('contact-overlay--open'));
  };

  const close = () => {
    if (!dialog.open) return;
    dialog.classList.remove('contact-overlay--open');
    window.clearTimeout(closeTimer);
    closeTimer = window.setTimeout(() => dialog.close(), reducedMotion.matches ? 0 : 300);
  };

  window.addEventListener('nuware:open-application', open);
  closeButton.addEventListener('click', close);
  dialog.addEventListener('cancel', (event) => {
    event.preventDefault();
    close();
  });
  dialog.addEventListener('click', (event) => {
    const rect = dialog.getBoundingClientRect();
    if (event.target === dialog && event.clientX < rect.left) close();
  });
  dialog.addEventListener('close', () => {
    window.clearTimeout(closeTimer);
    dialog.classList.remove('contact-overlay--open');
    document.documentElement.classList.remove('contact-overlay-open');
  });
}
