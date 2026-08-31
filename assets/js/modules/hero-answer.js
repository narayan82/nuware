export function initHeroAnswer() {
  const form = document.querySelector('[data-hero-particles] .particle-hero__form');
  const dialog = document.querySelector('[data-hero-answer]');
  if (!form || !dialog || typeof dialog.showModal !== 'function') return;

  const input = form.querySelector('.particle-hero__input');
  const question = dialog.querySelector('[data-hero-answer-question]');
  const closeButton = dialog.querySelector('[data-hero-answer-close]');
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  let returnFocus, closeTimer, openFrame;
  let closing = false;

  const close = () => {
    if (!dialog.open || closing) return;
    closing = true;
    cancelAnimationFrame(openFrame);
    dialog.classList.remove('hero-answer--open');
    closeTimer = window.setTimeout(() => dialog.close(), reduced.matches ? 0 : 300);
  };

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    if (dialog.open) return;
    closing = false;
    returnFocus = document.activeElement;
    question.textContent = input.value.trim();
    question.hidden = !question.textContent;
    dialog.showModal();
    dialog.scrollTop = 0;
    document.documentElement.classList.add('hero-answer-open');
    // Commit the offscreen state before applying the entrance transition.
    dialog.getBoundingClientRect();
    openFrame = requestAnimationFrame(() => dialog.classList.add('hero-answer--open'));
  });

  closeButton.addEventListener('click', close);
  dialog.addEventListener('cancel', (event) => {
    event.preventDefault();
    close();
  });
  dialog.addEventListener('click', (event) => {
    const rect = dialog.getBoundingClientRect();
    if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right ||
        event.clientY < rect.top || event.clientY > rect.bottom)) close();
  });
  dialog.addEventListener('close', () => {
    clearTimeout(closeTimer);
    cancelAnimationFrame(openFrame);
    closing = false;
    dialog.classList.remove('hero-answer--open');
    document.documentElement.classList.remove('hero-answer-open');
    if (returnFocus?.isConnected) returnFocus.focus({ preventScroll: true });
  });
}
