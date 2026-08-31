const easeInOutCubic = (value) =>
  value < 0.5 ? 4 * value * value * value : 1 - Math.pow(-2 * value + 2, 3) / 2;

export const initStatCounters = () => {
  document.querySelectorAll('[data-stat-counter]').forEach((counter) => {
    const dataElement = counter.querySelector('.stat-counter__data');
    const valueElement = counter.querySelector('[data-stat-value]');
    const suffixElement = counter.querySelector('[data-stat-suffix]');
    const descriptionElement = counter.querySelector('[data-stat-description]');
    const previousButton = counter.querySelector('[data-stat-previous]');
    const nextButton = counter.querySelector('[data-stat-next]');

    if (!dataElement || !valueElement || !suffixElement || !descriptionElement) {
      return;
    }

    let stats;

    try {
      stats = JSON.parse(dataElement.textContent);
    } catch (error) {
      return;
    }

    if (!Array.isArray(stats) || !stats.length) {
      return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let activeIndex = 0;
    let isChanging = false;

    const formatValue = (value) => String(Math.round(value)).padStart(2, '0');

    const showStat = (nextIndex) => {
      if (isChanging || stats.length < 2) {
        return;
      }

      const resolvedIndex = (nextIndex + stats.length) % stats.length;

      if (resolvedIndex === activeIndex) {
        return;
      }

      const startValue = Number(stats[activeIndex].value);
      const nextStat = stats[resolvedIndex];
      const endValue = Number(nextStat.value);
      const duration = reduceMotion ? 0 : 850;
      const startedAt = performance.now();

      isChanging = true;
      counter.classList.add('stat-counter--changing');

      const update = (timestamp) => {
        const progress = duration === 0 ? 1 : Math.min((timestamp - startedAt) / duration, 1);
        const currentValue = startValue + (endValue - startValue) * easeInOutCubic(progress);

        valueElement.textContent = formatValue(currentValue);

        if (progress < 1) {
          window.requestAnimationFrame(update);
          return;
        }

        activeIndex = resolvedIndex;
        suffixElement.textContent = nextStat.suffix || '';
        descriptionElement.textContent = nextStat.description || '';

        window.requestAnimationFrame(() => {
          counter.classList.remove('stat-counter--changing');
          isChanging = false;
        });
      };

      window.requestAnimationFrame(update);
    };

    previousButton?.addEventListener('click', () => showStat(activeIndex - 1));
    nextButton?.addEventListener('click', () => showStat(activeIndex + 1));

    counter.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowLeft' || event.key === 'ArrowDown') {
        event.preventDefault();
        showStat(activeIndex - 1);
      } else if (event.key === 'ArrowRight' || event.key === 'ArrowUp') {
        event.preventDefault();
        showStat(activeIndex + 1);
      }
    });
  });
};
