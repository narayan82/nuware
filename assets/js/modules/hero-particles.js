import { HERO_SHAPES } from './hero-particle-shapes.js';

// Hero-only controls. These never change the AI carousel's particles.js instance.
const SETTINGS = { fade: 800, gather: 3000, stagger: 1040, drift: 7, maxParticles: 3400 };
const HOVER = { radius: 100, distance: 24, response: 10 };
const clamp = (n, min, max) => Math.max(min, Math.min(n, max));
const ease = (t) => t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;

export async function initHeroParticles() {
  const hero = document.querySelector('[data-hero-particles]');
  if (!hero || typeof window.particlesJS !== 'function') return;
  const host = hero.querySelector('.particle-hero__canvas');
  const textBox = hero.querySelector('.particle-hero__text');
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  const test = document.createElement('canvas').getContext('2d');
  await document.fonts.ready;
  window.particlesJS(host.id, {
    particles: {
      number: { value: 0, density: { enable: false } },
      shape: { type: 'circle' }, line_linked: { enable: false },
      move: { enable: false }, size: { value: 1 }, opacity: { value: 1 },
    },
    interactivity: { events: { onhover: { enable: false }, onclick: { enable: false }, resize: false } },
    retina_detect: false,
  });
  const engine = window.pJSDom.find(entry => entry.pJS.canvas.el.parentElement === host)?.pJS;
  if (!engine) return;
  const canvas = engine.canvas.el;
  const ctx = engine.canvas.ctx;
  let width, height, glyphSize, paths, elapsed = 0, last = performance.now();
  let visible = true, formed = reduced.matches, colors = [], disposed = false;
  const pointer = { active: false, x: 0, y: 0 };
  const clearPointer = () => { pointer.active = false; };
  const trackPointer = (event) => {
    if (event.pointerType !== 'mouse' || reduced.matches) {
      clearPointer();
      return;
    }
    const rect = canvas.getBoundingClientRect();
    if (!rect.width || !rect.height) return;
    pointer.x = (event.clientX - rect.left) * width / rect.width;
    pointer.y = (event.clientY - rect.top) * height / rect.height;
    pointer.active = true;
  };
  hero.addEventListener('pointermove', trackPointer, { passive: true });
  hero.addEventListener('pointerleave', clearPointer);
  hero.addEventListener('pointercancel', clearPointer);
  window.addEventListener('blur', clearPointer);

  const updateColors = () => {
    const styles = getComputedStyle(hero);
    colors = [
      styles.getPropertyValue('--particle-zero-color').trim() || '#172033',
      styles.getPropertyValue('--particle-one-color').trim() || '#737b89',
    ];
  };
  // Check the glyph's small bounding box, not just its centre, against the SVG path.
  const inside = (path, x, y) => {
    const dx = glyphSize * 0.28, dy = glyphSize * 0.38;
    return test.isPointInPath(path, x, y) &&
      test.isPointInPath(path, x - dx, y - dy) && test.isPointInPath(path, x + dx, y - dy) &&
      test.isPointInPath(path, x - dx, y + dy) && test.isPointInPath(path, x + dx, y + dy);
  };

  const build = () => {
    const rect = hero.getBoundingClientRect();
    const box = textBox.getBoundingClientRect();
    width = rect.width; height = rect.height;
    const ratio = Math.min(devicePixelRatio || 1, 2);
    canvas.width = Math.round(width * ratio); canvas.height = Math.round(height * ratio);
    engine.canvas.w = width; engine.canvas.h = height;
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    const mobile = width < 700;
    const word = mobile ? 'Tech' : 'Technology';
    const shape = HERO_SHAPES[mobile ? 'tech' : 'technology'];
    // Preserve the former hero's exact sizing, baseline and content spacing formula.
    const fontSize = mobile ? clamp(box.width / 7.15 * 2, 144, 220) : clamp(box.width / 7.15, 72, 210);
    test.font = `700 ${fontSize}px Geist, Arial, sans-serif`;
    const metrics = test.measureText(word);
    const ascent = Math.ceil(metrics.actualBoundingBoxAscent || fontSize * 0.75);
    const descent = Math.ceil(metrics.actualBoundingBoxDescent || fontSize * 0.2);
    const padding = Math.ceil(fontSize * 0.18);
    const sampleHeight = ascent + descent + padding * 2;
    const offset = clamp(box.height * 0.08, 24, 50);
    const left = box.left - rect.left + (box.width - metrics.width) / 2;
    const top = box.top - rect.top + (box.height - sampleHeight) / 2 + padding - offset;
    const baseline = (box.height - sampleHeight) / 2 + padding + ascent - offset;
    hero.style.setProperty('--particle-hero-baseline-tail', `${box.height - baseline}px`);
    const matrix = new DOMMatrix([metrics.width / shape.viewBox[2], 0, 0, (ascent + descent) / shape.viewBox[3], left, top]);
    paths = shape.paths.map(d => {
      const path = new Path2D(); path.addPath(new Path2D(d), matrix); return path;
    });
    glyphSize = mobile ? 7 : 9;
    const gap = mobile ? 4.5 : 5;
    const targets = [];
    for (let y = top; y <= top + ascent + descent; y += gap) {
      for (let x = left; x <= left + metrics.width; x += gap) {
        const letter = paths.findIndex(path => inside(path, x, y));
        if (letter >= 0) targets.push({ x, y, letter });
      }
    }
    const stride = Math.max(1, Math.ceil(targets.length / SETTINGS.maxParticles));
    engine.particles.array = targets.filter((_, i) => i % stride === 0).map((target, index) => {
      const particle = new engine.fn.particle({ value: '#172033' }, 1);
      const angle = Math.random() * Math.PI * 2;
      Object.assign(particle, {
        x: formed ? target.x : Math.random() * width,
        y: formed ? target.y : Math.random() * height,
        targetX: target.x, targetY: target.y, letter: target.letter,
        vx: Math.cos(angle) * SETTINGS.drift, vy: Math.sin(angle) * SETTINGS.drift,
        digit: index % 2, delay: Math.random() * SETTINGS.stagger,
        hoverX: 0, hoverY: 0,
      });
      particle.startX = particle.x; particle.startY = particle.y;
      particle.driftX = particle.vx; particle.driftY = particle.vy;
      particle.draw = () => {
        ctx.globalAlpha = reduced.matches ? 1 : clamp(elapsed / SETTINGS.fade, 0, 1);
        ctx.fillStyle = colors[particle.digit];
        ctx.fillText(String(particle.digit), particle.x, particle.y);
      };
      return particle;
    });
    updateColors();
    if (formed || reduced.matches) hero.classList.add('particle-hero--ready');
  };

  engine.fn.particlesUpdate = () => {
    const now = performance.now();
    const dt = engine.particles.move.enable ? Math.min(40, now - last) : 0;
    last = now;
    elapsed += dt;
    if (reduced.matches) {
      formed = true;
      hero.classList.add('particle-hero--ready');
    }
    const particles = engine.particles.array;
    for (const p of particles) {
      const path = paths[p.letter];
      // Keep targets moving even during gathering: early arrivals never stop.
      if (!reduced.matches && dt > 0) {
        const dx = p.vx * dt / 1000, dy = p.vy * dt / 1000;
        if (!inside(path, p.targetX + dx, p.targetY)) p.vx *= -1;
        if (!inside(path, p.targetX, p.targetY + dy)) p.vy *= -1;
        const x = p.targetX + p.vx * dt / 1000, y = p.targetY + p.vy * dt / 1000;
        if (inside(path, x, y)) { p.targetX = x; p.targetY = y; }
        else { p.vx *= -1; p.vy *= -1; }
      }
      if (!formed) {
        const progress = clamp((elapsed - SETTINGS.fade - p.delay) / SETTINGS.gather, 0, 1);
        const blend = ease(progress);
        const drift = elapsed / 1000;
        p.x = (p.startX + p.driftX * drift) * (1 - blend) + p.targetX * blend;
        p.y = (p.startY + p.driftY * drift) * (1 - blend) + p.targetY * blend;
      } else {
        p.x = p.targetX; p.y = p.targetY;
      }
      // Displace only the rendered position; moving letter targets stay intact.
      let hoverX = 0, hoverY = 0;
      if (pointer.active && !reduced.matches) {
        const dx = p.x - pointer.x, dy = p.y - pointer.y;
        const distance = Math.hypot(dx, dy);
        if (distance < HOVER.radius) {
          const force = HOVER.distance * Math.pow(1 - distance / HOVER.radius, 2);
          const angle = distance > 0.01 ? Math.atan2(dy, dx) : p.delay;
          hoverX = Math.cos(angle) * force;
          hoverY = Math.sin(angle) * force;
        }
      }
      const response = reduced.matches ? 1 : 1 - Math.exp(-HOVER.response * dt / 1000);
      p.hoverX += (hoverX - p.hoverX) * response;
      p.hoverY += (hoverY - p.hoverY) * response;
      p.x += p.hoverX;
      p.y += p.hoverY;
    }
    if (!formed && elapsed >= SETTINGS.fade + SETTINGS.gather + SETTINGS.stagger) {
      formed = true;
      hero.classList.add('particle-hero--ready');
    }
    // Sparse abstract connections fade away as the binary resolves into the word.
    const lineAlpha = 0.1 * clamp(elapsed / SETTINGS.fade, 0, 1) *
      (1 - clamp((elapsed - SETTINGS.fade) / SETTINGS.gather, 0, 1));
    if (!formed && lineAlpha > 0) {
      const grid = new Map();
      ctx.strokeStyle = colors[1]; ctx.globalAlpha = lineAlpha; ctx.lineWidth = 0.5;
      particles.forEach(p => {
        const key = `${Math.floor(p.x / 70)},${Math.floor(p.y / 70)}`;
        const cell = grid.get(key) || [];
        for (const other of cell.slice(-2)) {
          ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(other.x, other.y); ctx.stroke();
        }
        cell.push(p); grid.set(key, cell);
      });
    }
    ctx.font = `600 ${glyphSize}px Geist, Arial, sans-serif`;
    ctx.textAlign = 'center'; ctx.textBaseline = 'middle'; ctx.globalAlpha = 1;
  };

  const resume = () => {
    if (disposed) return;
    cancelAnimationFrame(engine.fn.drawAnimFrame);
    last = performance.now();
    engine.particles.move.enable = visible && !document.hidden && !reduced.matches;
    engine.fn.vendors.draw();
  };
  build();
  const resize = new ResizeObserver(() => {
    const rect = hero.getBoundingClientRect();
    if (rect.width !== width || rect.height !== height) { build(); resume(); }
  });
  resize.observe(hero);
  const theme = new MutationObserver(() => { updateColors(); if (reduced.matches) resume(); });
  theme.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
  const observer = new IntersectionObserver(([entry]) => { visible = entry.isIntersecting; resume(); });
  observer.observe(hero);
  reduced.addEventListener('change', resume);
  document.addEventListener('visibilitychange', resume);
  window.addEventListener('pagehide', event => {
    cancelAnimationFrame(engine.fn.drawAnimFrame);
    clearPointer();
    if (!event.persisted) {
      disposed = true; resize.disconnect(); theme.disconnect(); observer.disconnect();
      hero.removeEventListener('pointermove', trackPointer);
      hero.removeEventListener('pointerleave', clearPointer);
      hero.removeEventListener('pointercancel', clearPointer);
      window.removeEventListener('blur', clearPointer);
    }
  });
  window.addEventListener('pageshow', resume);
  resume();
}
