/**
 * Compiled NuWare JavaScript entry point.
 * Source: src/js/main.js
 */

document.documentElement.classList.add('has-js');

if (document.querySelector('.case-library__filters')) {
  import('./modules/case-library.js?v=1').then(({ initCaseLibrary }) => initCaseLibrary());
}

import('./modules/contact-overlay.js?v=2').then(({ initContactOverlay }) => initContactOverlay());

if (document.querySelector('[data-application-overlay]')) {
  import('./modules/application-overlay.js?v=1').then(({ initApplicationOverlay }) => initApplicationOverlay());
}

if (document.querySelector('[data-ai-hero]')) {
  import('./modules/ai-page.js?v=7').then(({ initAiPage }) => initAiPage());
}

if (document.querySelector('[data-solutions-tabs]')) {
  import('./modules/solutions-page.js?v=6').then(({ initSolutionsPage }) => initSolutionsPage());
}

if (document.querySelector('[data-careers-typing]')) {
  import('./modules/careers-typing.js?v=1').then(({ initCareersTyping }) => initCareersTyping());
}

if (document.querySelector('[data-careers-positions]')) {
  import('./modules/careers-positions.js?v=1').then(({ initCareersPositions }) => initCareersPositions());
}

import('./modules/hero-answer.js?v=1').then(({ initHeroAnswer }) => initHeroAnswer());

import('./modules/homepage-solutions.js?v=2').then(({ initHomepageSolutions }) => {
  initHomepageSolutions();
});

import('./modules/theme-toggle.js?v=2').then(({ initThemeToggle }) => {
  initThemeToggle();
});

import('./modules/hero-binary-title.js').then(({ initHeroBinaryTitle }) => {
  initHeroBinaryTitle();
});

if (document.querySelector('[data-hero-particles]')) {
  import('./modules/hero-particles.js?v=3').then(({ initHeroParticles }) => initHeroParticles());
} else {
  import('./modules/particle-text.js?v=6').then(({ initParticleText }) => initParticleText());
}

import('./modules/ai-carousel.js?v=35').then(({ initAiCarousel }) => {
  initAiCarousel();
});

import('./modules/stat-counter.js?v=2').then(({ initStatCounters }) => {
  initStatCounters();
});

import('./modules/our-worlds-carousel.js?v=5').then(({ initOurWorldsCarousels }) => {
  initOurWorldsCarousels();
});

const siteHeader = document.querySelector('.site-header');
const menuToggle = document.querySelector('.site-header__toggle');

if (siteHeader && menuToggle) {
  menuToggle.addEventListener('click', () => {
    const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';

    menuToggle.setAttribute('aria-expanded', String(!isOpen));
    siteHeader.classList.toggle('site-header--menu-open', !isOpen);
  });
}

const mobileMenuQuery = window.matchMedia('(max-width: 47.99rem)');
const dropdownLabels = document.querySelectorAll(
  '.site-header__menu .menu-item--dropdown-label.menu-item-has-children > a'
);

dropdownLabels.forEach((dropdownLabel) => {
  const menuItem = dropdownLabel.parentElement;

  dropdownLabel.setAttribute('aria-haspopup', 'true');
  dropdownLabel.setAttribute('aria-expanded', 'false');

  dropdownLabel.addEventListener('click', (event) => {
    event.preventDefault();

    if (!mobileMenuQuery.matches || !menuItem) {
      return;
    }

    if (!menuItem.classList.contains('menu-item--submenu-open')) {
      dropdownLabels.forEach((otherLabel) => {
        const otherItem = otherLabel.parentElement;

        if (otherItem && otherItem !== menuItem) {
          otherItem.classList.remove('menu-item--submenu-open');
          otherLabel.setAttribute('aria-expanded', 'false');
        }
      });
    }

    const isOpen = menuItem.classList.toggle('menu-item--submenu-open');
    dropdownLabel.setAttribute('aria-expanded', String(isOpen));
  });
});
