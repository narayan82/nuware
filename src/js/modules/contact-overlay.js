export function initContactOverlay() {
  const dialog = document.querySelector('[data-contact-overlay]');
  if (!dialog || typeof dialog.showModal !== 'function') return;
  const triggers = document.querySelectorAll(
    '.site-header .menu-item--contact > a, .site-footer__cta, .hero-answer__cta'
  );
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  let returnFocus, closeTimer, openFrame, closing = false;

  const close = () => {
    if (!dialog.open || closing) return;
    closing = true;
    cancelAnimationFrame(openFrame);
    dialog.classList.remove('contact-overlay--open');
    closeTimer = window.setTimeout(() => dialog.close(), reduced.matches ? 0 : 300);
  };

  triggers.forEach(trigger => {
    trigger.setAttribute('aria-haspopup', 'dialog');
    trigger.setAttribute('aria-controls', dialog.id);
    trigger.addEventListener('click', event => {
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      event.preventDefault();
      if (dialog.open) return;
      returnFocus = trigger;
      closing = false;
      dialog.showModal();
      dialog.scrollTop = 0;
      document.documentElement.classList.add('contact-overlay-open');
      dialog.getBoundingClientRect();
      openFrame = requestAnimationFrame(() => dialog.classList.add('contact-overlay--open'));
    });
  });

  dialog.querySelector('[data-contact-close]').addEventListener('click', close);
  dialog.addEventListener('cancel', event => {
    event.preventDefault();
    close();
  });
  dialog.addEventListener('click', event => {
    const rect = dialog.getBoundingClientRect();
    if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right ||
        event.clientY < rect.top || event.clientY > rect.bottom)) close();
  });
  dialog.addEventListener('close', () => {
    clearTimeout(closeTimer);
    cancelAnimationFrame(openFrame);
    closing = false;
    dialog.classList.remove('contact-overlay--open');
    document.documentElement.classList.remove('contact-overlay-open');
    if (returnFocus?.isConnected) returnFocus.focus({ preventScroll: true });
  });
}
