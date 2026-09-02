export function initCareersPositions() {
  const drawer = document.querySelector('[data-position-drawer]');
  const content = drawer?.querySelector('[data-position-content]');
  const closeButton = drawer?.querySelector('[data-position-close]');

  if (!drawer || !content || !closeButton || typeof drawer.showModal !== 'function') return;

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let returnFocus = null;
  let closeTimer = 0;

  const closeDrawer = () => {
    if (!drawer.open) return;
    drawer.classList.remove('position-drawer--open');
    window.clearTimeout(closeTimer);
    closeTimer = window.setTimeout(() => drawer.close(), reducedMotion.matches ? 0 : 300);
  };

  document.querySelectorAll('[data-position-open]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
      const template = document.getElementById(trigger.dataset.positionTemplate);
      if (!(template instanceof HTMLTemplateElement)) return;

      returnFocus = trigger;
      content.replaceChildren(template.content.cloneNode(true));
      const title = content.querySelector('[data-position-title]');
      if (title) title.id = 'position-drawer-title';

      drawer.showModal();
      drawer.scrollTop = 0;
      document.documentElement.classList.add('position-drawer-open');
      drawer.getBoundingClientRect();
      requestAnimationFrame(() => drawer.classList.add('position-drawer--open'));

      content.querySelector('[data-application-trigger]')?.addEventListener('click', (event) => {
        event.preventDefault();
        closeDrawer();
        window.setTimeout(
          () => window.dispatchEvent(new CustomEvent('nuware:open-application')),
          reducedMotion.matches ? 0 : 310
        );
      });
    });
  });

  closeButton.addEventListener('click', closeDrawer);
  drawer.addEventListener('cancel', (event) => {
    event.preventDefault();
    closeDrawer();
  });
  drawer.addEventListener('click', (event) => {
    const rect = drawer.getBoundingClientRect();
    if (event.target === drawer && event.clientX < rect.left) closeDrawer();
  });
  drawer.addEventListener('close', () => {
    window.clearTimeout(closeTimer);
    drawer.classList.remove('position-drawer--open');
    document.documentElement.classList.remove('position-drawer-open');
    content.replaceChildren();
    returnFocus?.focus({ preventScroll: true });
  });
}
