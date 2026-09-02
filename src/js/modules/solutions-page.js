export function initSolutionsPage() {
  const hero = document.querySelector('.solutions-page__hero');
  const tabList = document.querySelector('[data-solutions-tabs] .wp-block-tab-list');
  const header = document.querySelector('.site-header');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let parallaxFrame = 0;
  let stickyStart = 0;
  let tabsSticky = false;

  document.querySelectorAll('.wp-block-accordion-heading__toggle-title br').forEach((breakElement) => {
    breakElement.replaceWith(document.createTextNode(' '));
  });

  function measureStickyTabs() {
    if (!tabList || !header) return;
    tabList.classList.remove('wp-block-tab-list--sticky');
    stickyStart = tabList.getBoundingClientRect().top + scrollY - header.getBoundingClientRect().bottom;
    document.documentElement.style.setProperty('--solutions-tabs-top', `${header.getBoundingClientRect().bottom}px`);
    tabsSticky = false;
    syncStickyTabs();
  }

  function syncStickyTabs() {
    if (!tabList || !header) return;
    const shouldStick = scrollY >= stickyStart;
    document.documentElement.style.setProperty('--solutions-tabs-top', `${header.getBoundingClientRect().bottom}px`);
    if (shouldStick === tabsSticky) return;
    tabsSticky = shouldStick;
    tabList.classList.toggle('wp-block-tab-list--sticky', tabsSticky);
  }

  function updateParallax() {
    parallaxFrame = 0;
    if (!hero || reducedMotion.matches) {
      hero?.style.removeProperty('--solutions-parallax-y');
      return;
    }

    const maximum = window.innerWidth < 768 ? 48 : 80;
    const offset = Math.min(maximum, Math.max(0, window.scrollY * 0.18));
    hero.style.setProperty('--solutions-parallax-y', `${offset}px`);
  }

  function requestParallaxUpdate() {
    if (!parallaxFrame) parallaxFrame = requestAnimationFrame(updateParallax);
  }

  if (hero) {
    addEventListener('scroll', requestParallaxUpdate, { passive: true });
    addEventListener('resize', requestParallaxUpdate);
    reducedMotion.addEventListener('change', requestParallaxUpdate);
    updateParallax();
  }

  addEventListener('scroll', syncStickyTabs, { passive: true });
  addEventListener('resize', measureStickyTabs);
  requestAnimationFrame(measureStickyTabs);

  document.querySelectorAll('[data-solutions-tabs]').forEach((root) => {
    const page = root.closest('[data-solutions-base-url]');
    const studyPanels = [...page.querySelectorAll('[data-solutions-studies-panel]')];
    const tabs = [...root.querySelectorAll('[data-solutions-tab]')];
    const panels = tabs.map((tab) => document.getElementById(tab.getAttribute('aria-controls')));

    function select(index, updateURL = false, focus = false) {
      tabs.forEach((tab, tabIndex) => {
        const selected = tabIndex === index;
        tab.setAttribute('aria-selected', String(selected));
        tab.tabIndex = selected ? 0 : -1;
        if (panels[tabIndex]) panels[tabIndex].hidden = !selected;
      });

      studyPanels.forEach((section) => {
        section.hidden = section.dataset.solutionsStudiesPanel !== tabs[index].dataset.solutionsTab;
        if (!section.hidden) requestAnimationFrame(() => section.solutionsCarouselSync?.());
      });

      if (focus) tabs[index].focus({ preventScroll: true });
      if (updateURL) {
        const baseURL = page?.dataset.solutionsBaseUrl || new URL('/solutions/', location.origin).href;
        history.pushState(null, '', new URL(`${tabs[index].dataset.solutionsTab}/`, baseURL));
      }
    }

    tabs.forEach((tab, index) => {
      tab.addEventListener('click', () => select(index, true));
      tab.addEventListener('keydown', (event) => {
        const targets = {
          ArrowRight: (index + 1) % tabs.length,
          ArrowLeft: (index - 1 + tabs.length) % tabs.length,
          Home: 0,
          End: tabs.length - 1,
        };

        if (!(event.key in targets)) return;
        event.preventDefault();
        select(targets[event.key], true, true);
      });
    });

    function fromURL() {
      const pathParts = location.pathname.split('/').filter(Boolean);
      const isBaseRoute = pathParts.at(-1) === 'solutions';
      const slug = isBaseRoute ? 'applications' : pathParts.at(-1);

      if (isBaseRoute) {
        const baseURL = page?.dataset.solutionsBaseUrl || new URL('/solutions/', location.origin).href;
        history.replaceState(null, '', new URL('applications/', baseURL));
      }

      select(Math.max(0, tabs.findIndex((tab) => tab.dataset.solutionsTab === slug)));
    }

    window.addEventListener('popstate', fromURL);
    fromURL();
  });

  document.querySelectorAll('[data-solutions-studies-panel]').forEach((section) => {
    const track = section.querySelector('.solutions-page__track');
    const cards = [...track.children];
    const previous = section.querySelector('[data-solutions-previous]');
    const next = section.querySelector('[data-solutions-next]');
    const position = section.querySelector('[data-solutions-position]');
    const controls = section.querySelector('.solutions-page__controls');

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
      track.scrollBy({ left: direction * step, behavior: reducedMotion.matches ? 'instant' : 'smooth' });
    }

    section.solutionsCarouselSync = sync;
    previous.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
    track.addEventListener('scroll', sync, { passive: true });
    new ResizeObserver(sync).observe(track);
    sync();
  });
}
