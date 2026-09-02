const wait = (duration) => new Promise((resolve) => window.setTimeout(resolve, duration));

export const initCareersTyping = () => {
  const host = document.querySelector('[data-careers-typing]');
  const output = host?.querySelector('[data-careers-typing-text]');

  if (!host || !output || host.dataset.initialized === 'true') return;

  let lines = [];

  try {
    lines = JSON.parse(host.dataset.lines || '[]');
  } catch (error) {
    lines = [];
  }

  lines = lines.filter((line) => typeof line === 'string' && line.length);

  if (!lines.length) return;

  host.dataset.initialized = 'true';

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    output.textContent = lines[0];
    return;
  }

  const typeLine = async (line) => {
    for (let index = 1; index <= line.length; index += 1) {
      output.textContent = line.slice(0, index);
      await wait(42);
    }
  };

  const eraseLine = async () => {
    while (output.textContent.length) {
      output.textContent = output.textContent.slice(0, -1);
      await wait(15);
    }
  };

  const run = async () => {
    let index = 0;

    while (document.documentElement.contains(host)) {
      await typeLine(lines[index]);
      await wait(1000);
      await eraseLine();
      await wait(180);
      index = (index + 1) % lines.length;
    }
  };

  run();
};
