/** AI PAGE ONLY — each preset corresponds to a Gutenberg tab, in order.
 * Opacity values: 0–1. Sizes/line distances: CSS pixels. Duration: milliseconds.
 * formation: 'network', 'globe', 'globes', or 'ring'.
 * x/y: centre as fractions of the canvas. radius: fraction of its shorter side.
 */
export const AI_TAB_PARTICLES = [
  // Advisory: full-width abstract network (homepage slide 1 appearance).
  { formation: 'network', count: 275, mobileCount: 175, size: 2, speed: 1.8, opacity: 0.6, lineDistance: 140, lineOpacity: 0.56, lineWidth: 0.6, duration: 2800 },
  // AI Incubation: denser abstract network.
  { formation: 'network', count: 575, mobileCount: 175, size: 2, speed: 0.8, opacity: 0.6, lineDistance: 140, lineOpacity: 0.26, lineWidth: 0.6, duration: 1800 },
  // AI Infra Security: abstract network.
  { formation: 'network', count: 275, mobileCount: 175, size: 2, speed: 0.8, opacity: 0.6, lineDistance: 140, lineOpacity: 0.26, lineWidth: 0.6, duration: 1800 },
  // AI Strategy Value: denser abstract network.
  { formation: 'network', count: 575, mobileCount: 175, size: 2, speed: 0.8, opacity: 0.6, lineDistance: 140, lineOpacity: 0.26, lineWidth: 0.6, duration: 1800 },
];

export const AI_TAB_COLOURS = {
  light: { dots: '#172033', lines: '#737b89' },
  dark: { dots: '#f7f8fb', lines: '#94a3b8' },
};

export function initAiPageParticles(hero) {
  const host = hero.querySelector('[data-ai-particles]');
  if (!host || typeof window.particlesJS !== 'function') return { setTab() {} };
  const mobile = matchMedia('(max-width: 47.99rem)');
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  const palette = () => AI_TAB_COLOURS.dark;
  const rgb = (hex) => ({ r: parseInt(hex.slice(1, 3), 16), g: parseInt(hex.slice(3, 5), 16), b: parseInt(hex.slice(5, 7), 16) });
  const first = AI_TAB_PARTICLES[0];
  window.particlesJS(host.id, {
    particles: {
      number: { value: mobile.matches ? first.mobileCount : first.count, density: { enable: false } },
      color: { value: palette().dots }, shape: { type: 'circle' },
      opacity: { value: first.opacity, random: false }, size: { value: first.size, random: true },
      line_linked: { enable: true, distance: first.lineDistance, color: palette().lines, opacity: first.lineOpacity, width: first.lineWidth },
      move: { enable: !reduced.matches, speed: first.speed, random: true, out_mode: 'out' },
    },
    interactivity: { events: { onhover: { enable: false }, onclick: { enable: false }, resize: true } },
    retina_detect: true,
  });
  const pjs = window.pJSDom.find((entry) => entry.pJS.canvas.el.parentElement === host)?.pJS;
  if (!pjs) return { setTab() {} };
  let active = 0, transition, inView = true;
  let lastTime = performance.now();
  let dimensions = `${pjs.canvas.w},${pjs.canvas.h}`;
  let targets = new Map();
  let currentCount = pjs.particles.array.length;
  const preset = () => AI_TAB_PARTICLES[active];
  function bounds(index) {
    const { w, h } = pjs.canvas;
    const config = preset();
    if (config.formation === 'network') return null;
    const count = config.formation === 'globes' ? Math.max(1, config.globes || 4) : 1;
    const radius = Math.min(w / count * 0.45, Math.min(w, h) * (config.radius ?? 0.4));
    return { x: count > 1 ? (index % count + 0.5) * w / count : w * (mobile.matches ? 0.5 : config.x ?? 0.5), y: h * (config.y ?? 0.46), radius };
  }
  function destination(index) {
    const globe = bounds(index);
    if (!globe) return { x: Math.random() * pjs.canvas.w, y: Math.random() * pjs.canvas.h };
    const inner = preset().formation === 'ring' ? 1 - (preset().ringThickness ?? 0.25) : 0;
    const radius = globe.radius * Math.sqrt(inner * inner + Math.random() * (1 - inner * inner));
    const angle = Math.random() * Math.PI * 2;
    return { x: globe.x + Math.cos(angle) * radius, y: globe.y + Math.sin(angle) * radius };
  }
  function updateColours() {
    const colours = palette();
    pjs.particles.color.value = colours.dots;
    pjs.particles.array.forEach((particle) => { particle.color = { value: colours.dots, rgb: rgb(colours.dots) }; });
    Object.assign(pjs.particles.line_linked, { color: colours.lines, color_rgb_line: rgb(colours.lines) });
  }
  function configure(animate = true) {
    const config = preset(), ratio = pjs.canvas.pxratio;
    currentCount = Math.max(0, Math.round(mobile.matches ? config.mobileCount : config.count));
    pjs.particles.number.value = currentCount;
    while (pjs.particles.array.length < currentCount) {
      pjs.particles.array.push(new pjs.fn.particle(pjs.particles.color, 0));
    }
    targets = new Map(pjs.particles.array.map((particle, i) => [particle, {
      ...destination(i), startX: particle.x, startY: particle.y,
      startOpacity: particle.opacity, opacity: i < currentCount ? config.opacity : 0,
      startRadius: particle.radius, radius: (0.4 + Math.random() * 0.6) * config.size * ratio,
    }]));
    transition = { elapsed: animate && !reduced.matches ? 0 : config.duration, fromSpeed: pjs.particles.move.speed, fromDistance: pjs.particles.line_linked.distance, fromWidth: pjs.particles.line_linked.width, fromOpacity: pjs.particles.line_linked.opacity };
    updateColours();
    dimensions = `${pjs.canvas.w},${pjs.canvas.h}`;
  }
  function drift(point, particle, index, step) {
    point.x += particle.vx * step;
    point.y += particle.vy * step;
    const globe = bounds(index);
    if (!globe) {
      point.x = (point.x + pjs.canvas.w) % pjs.canvas.w;
      point.y = (point.y + pjs.canvas.h) % pjs.canvas.h;
      return;
    }
    const dx = point.x - globe.x, dy = point.y - globe.y;
    const distance = Math.hypot(dx, dy) || 1;
    const outer = Math.max(1, globe.radius - particle.radius);
    const inner = preset().formation === 'ring' ? outer * (1 - (preset().ringThickness ?? 0.25)) : 0;
    if (distance > outer || distance < inner) {
      const nx = dx / distance, ny = dy / distance, limit = distance > outer ? outer : inner;
      point.x = globe.x + nx * limit; point.y = globe.y + ny * limit;
      const outward = particle.vx * nx + particle.vy * ny;
      if ((distance > outer && outward > 0) || (distance < inner && outward < 0)) {
        particle.vx -= 2 * outward * nx; particle.vy -= 2 * outward * ny;
      }
    }
  }
  pjs.fn.particlesUpdate = () => {
    if (dimensions !== `${pjs.canvas.w},${pjs.canvas.h}`) configure(false);
    const now = performance.now(), delta = Math.min(64, now - lastTime); lastTime = now;
    const config = preset(), ratio = pjs.canvas.pxratio;
    if (transition && pjs.particles.move.enable) transition.elapsed += delta;
    const progress = transition ? Math.min(1, transition.elapsed / Math.max(1, config.duration)) : 1;
    const ease = progress * progress * (3 - 2 * progress);
    const blend = (from, to) => from + (to - from) * ease;
    if (transition) {
      pjs.particles.move.speed = blend(transition.fromSpeed, config.speed * ratio);
      Object.assign(pjs.particles.line_linked, { distance: blend(transition.fromDistance, config.lineDistance * ratio), width: blend(transition.fromWidth, config.lineWidth * ratio), opacity: blend(transition.fromOpacity, config.lineOpacity) });
    }
    pjs.particles.array.forEach((particle, i) => {
      const target = transition ? targets.get(particle) : null;
      const point = target || particle;
      if (pjs.particles.move.enable) drift(point, particle, i, pjs.particles.move.speed / 2 * delta / (1000 / 60));
      if (target) {
        particle.x = blend(target.startX, target.x); particle.y = blend(target.startY, target.y);
        particle.opacity = blend(target.startOpacity, target.opacity); particle.radius = blend(target.startRadius, target.radius);
      }
    });
    if (transition && progress === 1) { transition = null; targets.clear(); pjs.particles.array.length = currentCount; }
    const particles = pjs.particles.array;
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        if (!transition && config.formation === 'globes' && i % config.globes !== j % config.globes) continue;
        if (particles[i].opacity > 0.01 && particles[j].opacity > 0.01) pjs.fn.interact.linkParticles(particles[i], particles[j]);
      }
    }
  };
  function sync() {
    cancelAnimationFrame(pjs.fn.drawAnimFrame);
    lastTime = performance.now();
    pjs.particles.move.enable = inView && !document.hidden && !reduced.matches;
    if (reduced.matches && transition) transition.elapsed = preset().duration;
    pjs.fn.vendors.draw();
  }
  new MutationObserver(() => { updateColours(); sync(); }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
  mobile.addEventListener('change', () => { configure(false); sync(); });
  reduced.addEventListener('change', sync);
  document.addEventListener('visibilitychange', sync);
  new IntersectionObserver(([entry]) => { inView = entry.isIntersecting; sync(); }).observe(hero);
  return {
    refresh() {
      pjs.canvas.w = pjs.canvas.el.offsetWidth * pjs.canvas.pxratio;
      pjs.canvas.h = pjs.canvas.el.offsetHeight * pjs.canvas.pxratio;
      pjs.canvas.el.width = pjs.canvas.w;
      pjs.canvas.el.height = pjs.canvas.h;
      configure(false);
      sync();
    },
    setTab(index) {
    const next = Math.max(0, Math.min(AI_TAB_PARTICLES.length - 1, index));
    if (next === active) return;
    active = next; configure(); sync();
    },
  };
}
