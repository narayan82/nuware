/**
 * Compiled NuWare JavaScript entry point.
 * Source: src/js/main.js
 */

document.documentElement.classList.add('has-js');

import('./modules/homepage-solutions.js?v=2').then(({ initHomepageSolutions }) => {
  initHomepageSolutions();
});

import('./modules/theme-toggle.js').then(({ initThemeToggle }) => {
  initThemeToggle();
});

import('./modules/hero-binary-title.js').then(({ initHeroBinaryTitle }) => {
  initHeroBinaryTitle();
});

import('./modules/particle-text.js?v=6').then(({ initParticleText }) => {
  initParticleText();
});

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
