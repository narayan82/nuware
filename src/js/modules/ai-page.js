// AI-page presets are independent from the homepage carousel.
import { initAiPageParticles } from './ai-page-particles.js?v=3';

export function initAiPage() {
  const hero = document.querySelector('[data-ai-hero]');
  if (!hero) return;
  const page = hero.closest('[data-ai-base-url]');
  const particles = initAiPageParticles(hero);
  const tabList = document.querySelector('[data-ai-tabs] .wp-block-tab-list');
  const header = document.querySelector('.site-header');
  let stickyStart = 0;
  let sticky = false;

  function measureStickyTabs() {
    if (!tabList || !header) return;
    const wasSticky = sticky;
    hero.classList.remove('ai-page__hero--tabs-sticky');
    tabList.classList.remove('wp-block-tab-list--sticky');
    const headerRect = header.getBoundingClientRect();
    const listHeight = tabList.getBoundingClientRect().height;
    const particleHeight = hero.getBoundingClientRect().height;
    stickyStart = hero.getBoundingClientRect().bottom + scrollY - headerRect.bottom - listHeight;
    document.documentElement.style.setProperty('--ai-tabs-top', `${headerRect.bottom}px`);
    document.documentElement.style.setProperty('--ai-tabs-height', `${listHeight}px`);
    document.documentElement.style.setProperty('--ai-particles-height', `${particleHeight}px`);
    sticky = false;
    if (wasSticky || scrollY >= stickyStart) syncStickyTabs();
  }

  function syncStickyTabs() {
    if (!tabList || !header) return;
    const shouldStick = scrollY >= stickyStart;
    if (shouldStick === sticky) {
      if (shouldStick) document.documentElement.style.setProperty('--ai-tabs-top', `${header.getBoundingClientRect().bottom}px`);
      return;
    }
    sticky = shouldStick;
    hero.classList.toggle('ai-page__hero--tabs-sticky', sticky);
    tabList.classList.toggle('wp-block-tab-list--sticky', sticky);
    document.documentElement.style.setProperty('--ai-tabs-top', `${header.getBoundingClientRect().bottom}px`);
  }

  addEventListener('scroll', syncStickyTabs, { passive: true });
  addEventListener('resize', measureStickyTabs);
  requestAnimationFrame(measureStickyTabs);

  document.querySelectorAll('[data-ai-tabs]').forEach((root) => {
    const tabs = [...root.querySelectorAll('[data-ai-tab]')];
    const panels = tabs.map((tab) => document.getElementById(tab.getAttribute('aria-controls')));
    let mobileSelect;
    function select(index, writeURL = false, focus = false) {
      particles.setTab(index);
      tabs.forEach((tab, i) => {
        tab.setAttribute('aria-selected', String(i === index));
        tab.tabIndex = i === index ? 0 : -1;
        if (panels[i]) panels[i].hidden = i !== index;
      });
      if (mobileSelect) mobileSelect.value = String(index);
      if (focus) tabs[index].focus({ preventScroll: true });
      if (writeURL) {
        const baseURL = page?.dataset.aiBaseUrl || new URL('/ai/', location.origin).href;
        history.pushState(null, '', new URL(`${tabs[index].dataset.aiTab}/`, baseURL));
      }
    }
    function fromURL() {
      const pathParts = location.pathname.split('/').filter(Boolean);
      const isBaseRoute = pathParts.at(-1) === 'ai';
      const slug = isBaseRoute ? 'advisory' : pathParts.at(-1);
      if (isBaseRoute) {
        const baseURL = page?.dataset.aiBaseUrl || new URL('/ai/', location.origin).href;
        history.replaceState(null, '', new URL('advisory/', baseURL));
      }
      select(Math.max(0, tabs.findIndex((tab) => tab.dataset.aiTab === slug)));
    }

    const mobileSelectWrap = document.createElement('div');
    mobileSelectWrap.className = 'ai-page__mobile-tab-select';
    mobileSelect = document.createElement('select');
    mobileSelect.setAttribute('aria-label', 'Choose an AI topic');
    tabs.forEach((tab, index) => {
      const option = document.createElement('option');
      option.value = String(index);
      option.textContent = tab.textContent.trim();
      mobileSelect.append(option);
    });
    mobileSelect.addEventListener('change', () => select(Number(mobileSelect.value), true));
    mobileSelectWrap.append(mobileSelect);
    root.before(mobileSelectWrap);

    tabs.forEach((tab, index) => {
      tab.addEventListener('click', () => select(index, true));
      tab.addEventListener('keydown', (event) => {
        const targets = { ArrowRight: (index + 1) % tabs.length, ArrowLeft: (index - 1 + tabs.length) % tabs.length, Home: 0, End: tabs.length - 1 };
        if (!(event.key in targets)) return;
        event.preventDefault();
        select(targets[event.key], true, true);
      });
    });
    window.addEventListener('popstate', fromURL);
    fromURL();
  });

  const section = document.querySelector('[data-ai-studies]');
  if (!section) return;
  const track = section.querySelector('.ai-page__track');
  const cards = [...track.children];
  const previous = section.querySelector('[data-ai-previous]');
  const next = section.querySelector('[data-ai-next]');
  const position = section.querySelector('[data-ai-position]');
  const controls = section.querySelector('.ai-page__controls');
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  function sync() {
    const overflow = track.scrollWidth - track.clientWidth;
    controls.hidden = overflow < 2;
    previous.disabled = track.scrollLeft <= 2;
    next.disabled = track.scrollLeft >= overflow - 2;
    const visible = cards.map((card, i) => ({ i, rect: card.getBoundingClientRect() }))
      .filter(({ rect }) => rect.right > track.getBoundingClientRect().left + 4 && rect.left < track.getBoundingClientRect().right - 4);
    if (visible.length) position.textContent = `${visible[0].i + 1}–${visible.at(-1).i + 1} of ${cards.length}`;
  }
  function move(direction) {
    const step = cards.length > 1 ? cards[1].offsetLeft - cards[0].offsetLeft : track.clientWidth;
    track.scrollBy({ left: direction * step, behavior: reduced.matches ? 'instant' : 'smooth' });
  }
  previous.addEventListener('click', () => move(-1));
  next.addEventListener('click', () => move(1));
  track.addEventListener('scroll', sync, { passive: true });
  new ResizeObserver(sync).observe(track);
  sync();
}
