export function initAboutStory() {
  document.querySelectorAll('[data-about-story]').forEach((story) => {
    const slides = [...story.querySelectorAll('[data-story-slide]')];
    const years = [...story.querySelectorAll('[data-story-year]')];
    if (!slides.length) return;
    let active = 0;

    const select = (index, focus = false) => {
      active = (index + slides.length) % slides.length;
      slides.forEach((slide, i) => { slide.hidden = i !== active; });
      years.forEach((year, i) => year.setAttribute('aria-pressed', String(i === active)));
      years[active]?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
      if (focus) years[active]?.focus({ preventScroll: true });
    };

    years.forEach((year, index) => year.addEventListener('click', () => select(index)));
    story.querySelector('[data-story-prev]')?.addEventListener('click', () => select(active - 1));
    story.querySelector('[data-story-next]')?.addEventListener('click', () => select(active + 1));
  });
}
