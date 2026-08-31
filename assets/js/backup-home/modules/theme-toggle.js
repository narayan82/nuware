const STORAGE_KEY = 'nuware-color-theme';

export const initThemeToggle = () => {
  const toggle = document.querySelector('.site-header__theme-toggle');

  if (!toggle) {
    return;
  }

  const root = document.documentElement;

  const applyTheme = (theme) => {
    const isDark = theme === 'dark';

    root.classList.toggle('theme-dark', isDark);
    toggle.setAttribute('aria-pressed', String(isDark));
    toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
  };

  let savedTheme = null;

  try {
    savedTheme = window.localStorage.getItem(STORAGE_KEY);
  } catch (error) {
    savedTheme = null;
  }

  applyTheme(savedTheme === 'dark' ? 'dark' : 'light');

  toggle.addEventListener('click', () => {
    const theme = root.classList.contains('theme-dark') ? 'light' : 'dark';

    applyTheme(theme);

    try {
      window.localStorage.setItem(STORAGE_KEY, theme);
    } catch (error) {
      // The selected theme still applies for this page when storage is unavailable.
    }
  });
};
