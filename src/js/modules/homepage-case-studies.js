export function initHomepageCaseStudies() {
  const section = document.querySelector('[data-homepage-case-studies]');
  if (!section) return;

  const track = section.querySelector('.homepage-case-studies__track');
  const cards = [...track.children];
  const previous = section.querySelector('[data-home-case-previous]');
  const next = section.querySelector('[data-home-case-next]');
  const position = section.querySelector('[data-home-case-position]');
  const controls = section.querySelector('.homepage-case-studies__controls');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  function sync() {
    const overflow = track.scrollWidth - track.clientWidth;
    controls.hidden = overflow < 2;
    previous.disabled = track.scrollLeft <= 2;
    next.disabled = track.scrollLeft >= overflow - 2;

    const bounds = track.getBoundingClientRect();
    const visible = cards
      .map((card, index) => ({ index, rect: card.getBoundingClientRect() }))
      .filter(({ rect }) => rect.right > bounds.left + 4 && rect.left < bounds.right - 4);

    if (visible.length) position.textContent = `${visible[0].index + 1}–${visible.at(-1).index + 1} of ${cards.length}`;
  }

  function move(direction) {
    const step = cards.length > 1 ? cards[1].offsetLeft - cards[0].offsetLeft : track.clientWidth;
    track.scrollBy({ left: direction * step, behavior: reducedMotion.matches ? 'auto' : 'smooth' });
  }

  previous.addEventListener('click', () => move(-1));
  next.addEventListener('click', () => move(1));
  track.addEventListener('scroll', sync, { passive: true });
  new ResizeObserver(sync).observe(track);
  sync();
}
