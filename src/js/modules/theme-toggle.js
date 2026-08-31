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

  const localHour = new Date().getHours();
  applyTheme(localHour >= 19 || localHour < 7 ? 'dark' : 'light');

  toggle.addEventListener('click', () => {
    const theme = root.classList.contains('theme-dark') ? 'light' : 'dark';

    applyTheme(theme);

    // Manual selection lasts for this page; the next load follows local time.
  });
};
