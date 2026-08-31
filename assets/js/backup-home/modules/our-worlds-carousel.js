export const initOurWorldsCarousels = () => {
  document.querySelectorAll('[data-our-worlds-carousel]').forEach((carousel) => {
    const cards = Array.from(carousel.querySelectorAll('[data-world-card]'));
    const track = carousel.querySelector('.our-worlds__grid');
    const dots = Array.from(carousel.querySelectorAll('[data-world-dot]'));
    const previousButton = carousel.querySelector('[data-world-previous]');
    const nextButton = carousel.querySelector('[data-world-next]');

    // Reveal once on desktop; leave the mobile carousel and reduced-motion view untouched.
    const revealQuery = window.matchMedia('(min-width: 64rem) and (prefers-reduced-motion: no-preference)');
    let revealObserver;
    let revealed = false;
    const reveal = () => {
      revealed = true;
      carousel.classList.add('our-worlds--revealed');
      revealObserver?.disconnect();
    };
    const prepareReveal = () => {
      revealObserver?.disconnect();
      carousel.classList.remove('our-worlds--reveal-ready', 'our-worlds--revealed');
      if (!revealQuery.matches || revealed || !track || !('IntersectionObserver' in window)) return;
      cards.forEach((card, index) => card.style.setProperty('--world-reveal-delay', `${index * 400}ms`));
      revealObserver = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) reveal();
      }, { threshold: 0.15 });
      carousel.classList.add('our-worlds--reveal-ready');
      revealObserver.observe(track);
    };
    carousel.addEventListener('focusin', reveal);
    revealQuery.addEventListener('change', prepareReveal);
    prepareReveal();

    if (cards.length < 2) {
      return;
    }

    let activeIndex = 0;
    const mobileQuery = window.matchMedia('(max-width: 47.99rem)');

    const showWorld = (nextIndex) => {
      activeIndex = (nextIndex + cards.length) % cards.length;
      const previousIndex = (activeIndex - 1 + cards.length) % cards.length;
      const followingIndex = (activeIndex + 1) % cards.length;

      track?.style.setProperty('--world-offset', `${cards[activeIndex].offsetLeft}px`);

      cards.forEach((card, index) => {
        const isActive = index === activeIndex;
        const link = card.querySelector('.our-worlds__link');

        card.classList.toggle('our-worlds__card--active', isActive);
        card.classList.toggle('our-worlds__card--previous', index === previousIndex);
        card.classList.toggle('our-worlds__card--next', index === followingIndex);
        if (mobileQuery.matches) {
          card.setAttribute('aria-hidden', String(!isActive));

          if (link) {
            link.tabIndex = isActive ? 0 : -1;
          }
        } else {
          card.removeAttribute('aria-hidden');
          link?.removeAttribute('tabindex');
        }

        dots[index]?.classList.toggle('our-worlds__pagination-dot--active', isActive);
      });
    };

    previousButton?.addEventListener('click', () => showWorld(activeIndex - 1));
    nextButton?.addEventListener('click', () => showWorld(activeIndex + 1));

    carousel.addEventListener('keydown', (event) => {
      if (!mobileQuery.matches) {
        return;
      }

      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        showWorld(activeIndex - 1);
      } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        showWorld(activeIndex + 1);
      }
    });

    mobileQuery.addEventListener('change', () => showWorld(activeIndex));

    const resizeObserver = new ResizeObserver(() => showWorld(activeIndex));

    resizeObserver.observe(carousel);

    window.addEventListener('pagehide', () => resizeObserver.disconnect(), { once: true });

    showWorld(0);
  });
};
