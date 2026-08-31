/** Horizontal hover cards on desktop; tap-to-toggle vertical accordion on mobile. */
export function initHomepageSolutions() {
  document.querySelectorAll('[data-solutions]').forEach((section) => {
    const cards = [...section.querySelectorAll('[data-solution-card]')];
    const mobile = window.matchMedia('(max-width: 47.99rem)');
    let active = 0;

    const track = section.querySelector('.homepage-solutions__cards');
    const measureHeight = () => {
      if (mobile.matches || !track) {
        track?.style.removeProperty('--solutions-height');
        return;
      }
      // Measure every expanded state off-screen, at this exact container width.
      const probe = track.cloneNode(true);
      probe.removeAttribute('style');
      Object.assign(probe.style, {
        position: 'absolute', visibility: 'hidden', pointerEvents: 'none',
        width: `${track.getBoundingClientRect().width}px`, height: 'auto',
        top: '0', left: '0',
      });
      probe.inert = true;
      probe.setAttribute('aria-hidden', 'true');
      probe.querySelectorAll('[id]').forEach((element) => element.removeAttribute('id'));
      const probeCards = [...probe.querySelectorAll('[data-solution-card]')];
      probeCards.forEach((card) => {
        card.style.height = 'auto';
        card.style.transition = 'none';
      });
      track.parentElement.append(probe);
      let height = 300;
      probeCards.forEach((_, expandedIndex) => {
        probeCards.forEach((card, index) => {
          card.classList.toggle('homepage-solutions__card--active', index === expandedIndex);
          card.querySelector('.homepage-solutions__details').hidden = index !== expandedIndex;
        });
        height = Math.max(height, probe.getBoundingClientRect().height);
      });
      probe.remove();
      track.style.setProperty('--solutions-height', `${Math.ceil(height)}px`);
    };


    const render = () => {
      cards.forEach((card, index) => {
        const open = index === active;
        card.classList.toggle('homepage-solutions__card--active', open);
        card.querySelector('button').setAttribute('aria-expanded', String(open));
        card.querySelector('.homepage-solutions__details').hidden = !open;
        card.querySelector('.homepage-solutions__indicator').textContent = open ? '−' : '+';
      });
    };

    cards.forEach((card, index) => {
      const trigger = card.querySelector('button');
      card.addEventListener('pointerenter', (event) => {
        if (!mobile.matches && event.pointerType === 'mouse') {
          active = index;
          render();
        }
      });
      trigger.addEventListener('focus', () => {
        if (!mobile.matches) { active = index; render(); }
      });
      trigger.addEventListener('click', () => {
        active = mobile.matches && active === index ? -1 : index;
        render();
      });
      trigger.addEventListener('keydown', (event) => {
        const nextKey = mobile.matches ? 'ArrowDown' : 'ArrowRight';
        const previousKey = mobile.matches ? 'ArrowUp' : 'ArrowLeft';
        let next = index;
        if (event.key === nextKey) next = (index + 1) % cards.length;
        else if (event.key === previousKey) next = (index - 1 + cards.length) % cards.length;
        else if (event.key === 'Home') next = 0;
        else if (event.key === 'End') next = cards.length - 1;
        else return;
        event.preventDefault();
        cards[next].querySelector('button').focus();
      });
    });
    mobile.addEventListener('change', () => {
      if (!mobile.matches && active < 0) active = 0;
      render();
      measureHeight();
    });
    section.classList.add('homepage-solutions--ready');
    render();
    measureHeight();
    let measuredWidth = track?.getBoundingClientRect().width;
    const resizeObserver = new ResizeObserver(() => {
      const width = track?.getBoundingClientRect().width;
      if (width !== measuredWidth) {
        measuredWidth = width;
        measureHeight();
      }
    });
    if (track) resizeObserver.observe(track);
    document.fonts?.ready.then(measureHeight);
  });
}
