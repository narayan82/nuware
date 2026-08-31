/**
 * AI carousel background — edit these three presets to customise each slide.
 * count/mobileCount: dots; size: maximum radius in px; speed: drift speed.
 * opacity/lineOpacity: 0–1; lineDistance: connection reach in px; lineWidth: px.
 */
export const AI_PARTICLE_PRESETS = [
  // SLIDE 1 — Everything starts with data: sparse, freely drifting network.
  { count: 275, mobileCount: 35, size: 4, speed: 0.6, opacity: 1.0, lineDistance: 140, lineOpacity: 20.0, lineWidth: 5 },
  // SLIDE 2 — Connected intelligence: closer, more interconnected network.
  { count: 100, mobileCount: 45, size: 1.8, speed: 0.45, opacity: 0.55, lineDistance: 175, lineOpacity: 0.2, lineWidth: 0.5 },
  // SLIDE 3 — Same total particle budget as slide 2, shared across four globes.
  { count: 375, mobileCount: 145, size: 2, speed: 2.8, opacity: 0.6, lineDistance: 140, lineOpacity: 0.26, lineWidth: 0.5 },
];

// Slide 2 globe: x/y are fractions of the canvas; radius is a fraction of width.
export const AI_GLOBE = {
  transitionDuration: 1800, // Milliseconds to gather into the globe.
  desktop: { x: 0.70, y: 0.5, radius: 0.21 },
  mobile: { x: 0.5, y: 0.5, radius: 0.43 },
  outlineOpacity: 0,
  gridOpacity: 0,
};

// Shared light/dark colours. Use six-digit hex values.
export const AI_PARTICLE_COLOURS = {
  light: { dots: '#172033', lines: '#000000' },
  dark: { dots: '#f7f8fb', lines: '#94a3b8' },
};

const rgb = (hex) => ({
  r: parseInt(hex.slice(1, 3), 16),
  g: parseInt(hex.slice(3, 5), 16),
  b: parseInt(hex.slice(5, 7), 16),
});

export const initAiParticles = (carousel) => {
  const host = carousel.querySelector('[data-ai-particles]');
  if (!host || typeof window.particlesJS !== 'function') return { setSlide() {} };

  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  const mobile = window.matchMedia('(max-width: 47.99rem)');
  let active = 0;
  let inView = true;
  const palette = () => AI_PARTICLE_COLOURS[document.documentElement.classList.contains('theme-dark') ? 'dark' : 'light'];
  const first = AI_PARTICLE_PRESETS[0];
  window.particlesJS(host.id, {
    particles: {
      number: { value: mobile.matches ? first.mobileCount : first.count, density: { enable: false } },
      color: { value: palette().dots },
      shape: { type: 'circle', stroke: { width: 0 } },
      opacity: { value: first.opacity, random: false, anim: { enable: false } },
      size: { value: first.size, random: true, anim: { enable: false } },
      line_linked: { enable: true, distance: first.lineDistance, color: palette().lines, opacity: first.lineOpacity, width: first.lineWidth },
      move: { enable: !reduced.matches, speed: first.speed, direction: 'none', random: true, straight: false, out_mode: 'out', bounce: false, attract: { enable: false } },
    },
    interactivity: {
      detect_on: 'canvas',
      events: { onhover: { enable: false }, onclick: { enable: false }, resize: true },
    },
    retina_detect: true,
  });
  const instance = window.pJSDom.find((entry) => entry.pJS.canvas.el.parentElement === host)?.pJS;
  if (!instance) return { setSlide() {} };

  // Slide 2: circular collision boundary, not a CSS mask.
  let globeBounds;
  let gathering;
  const getGlobe = () => {
    const { w, h } = instance.canvas;
    // The mobile spacer reserves room above pagination without limiting slide 1.
    const band = mobile.matches ? carousel.querySelector('[data-ai-globe-space]') : null;
    const bandRect = band?.getBoundingClientRect();
    const canvasRect = instance.canvas.el.getBoundingClientRect();
    const scaleY = canvasRect.height ? h / canvasRect.height : 1;
    const centreY = bandRect ? (bandRect.top - canvasRect.top + bandRect.height / 2) * scaleY : h / 2;
    const bandRadius = bandRect ? Math.max(0, bandRect.height / 2 - 12) * scaleY : h * 0.4;
    if (active === 2) {
      const globes = Array.from({ length: 4 }, (_, index) => {
        // Four equal diameters span the full canvas, touching side by side.
        const radius = bandRect ? Math.min(w / 8, bandRadius) : w / 8;
        return {
          x: (index + 0.5) * w / 4,
          y: centreY,
          radius,
        };
      });
      return { w, h, globes };
    }
    const layout = mobile.matches ? AI_GLOBE.mobile : AI_GLOBE.desktop;
    const radius = Math.min(w * layout.radius, bandRadius);
    return { w, h, globes: [{ x: w * layout.x, y: bandRect ? centreY : h * layout.y, radius }] };
  };
  const seedGlobe = () => {
    gathering = undefined;
    globeBounds = getGlobe();
    instance.particles.array.forEach((particle, index) => {
      const globe = globeBounds.globes[index % globeBounds.globes.length];
      const angle = Math.random() * Math.PI * 2;
      const radius = Math.sqrt(Math.random()) * Math.max(0, globe.radius - particle.radius);
      particle.x = globe.x + Math.cos(angle) * radius;
      particle.y = globe.y + Math.sin(angle) * radius;
    });
  };
  const gatherGlobe = (previousCount, previousSpeed) => {
    globeBounds = getGlobe();
    gathering = {
      elapsed: 0, lastFrame: performance.now(),
      points: new Map(instance.particles.array.map((particle, index) => {
        const globe = globeBounds.globes[index % globeBounds.globes.length];
        const angle = Math.random() * Math.PI * 2;
        const radius = Math.sqrt(Math.random()) * Math.max(0, globe.radius - particle.radius);
        return [particle, {
          x: globe.x + Math.cos(angle) * radius,
          y: globe.y + Math.sin(angle) * radius,
          startX: particle.x, startY: particle.y,
          driftX: particle.vx * previousSpeed / 2,
          driftY: particle.vy * previousSpeed / 2,
          added: index >= previousCount,
        }];
      })),
    };
  };
  const originalUpdate = instance.fn.particlesUpdate;
  instance.fn.particlesUpdate = () => {
    if (active === 0) { originalUpdate(); return; }
    if (!globeBounds || globeBounds.w !== instance.canvas.w || globeBounds.h !== instance.canvas.h) seedGlobe();
    const particles = instance.particles.array;
    const now = performance.now();
    if (gathering) {
      if (instance.particles.move.enable) gathering.elapsed += Math.min(64, now - gathering.lastFrame);
      gathering.lastFrame = now;
    }
    const progress = gathering ? (reduced.matches ? 1 : Math.min(1, gathering.elapsed / Math.max(1, AI_GLOBE.transitionDuration))) : 1;
    const ease = progress * progress * (3 - 2 * progress);
    particles.forEach((particle, index) => {
      const { x, y, radius } = globeBounds.globes[index % globeBounds.globes.length];
      const target = gathering?.points.get(particle);
      const point = target || particle;
      if (instance.particles.move.enable) {
        const speed = instance.particles.move.speed / 2;
        point.x += particle.vx * speed;
        point.y += particle.vy * speed;
      }
      const dx = point.x - x;
      const dy = point.y - y;
      const distance = Math.hypot(dx, dy);
      const limit = Math.max(0, radius - particle.radius);
      if (distance > limit && distance > 0) {
        const nx = dx / distance;
        const ny = dy / distance;
        point.x = x + nx * limit;
        point.y = y + ny * limit;
        // Reflect only outward velocity, keeping the original speed.
        const outward = particle.vx * nx + particle.vy * ny;
        if (outward > 0) {
          particle.vx -= 2 * outward * nx;
          particle.vy -= 2 * outward * ny;
        }
      }
      if (target) {
        // Blend ongoing drift into moving globe targets, rather than teleporting.
        const frames = gathering.elapsed / (1000 / 60);
        const startX = target.startX + target.driftX * frames;
        const startY = target.startY + target.driftY * frames;
        particle.x = startX + (target.x - startX) * ease;
        particle.y = startY + (target.y - startY) * ease;
        if (target.added) particle.opacity = instance.particles.opacity.value * ease;
      }
    });
    if (progress === 1) gathering = undefined;
    globeBounds.globes.forEach(({ x, y, radius }) => {
    const ctx = instance.canvas.ctx;
    ctx.save();
    ctx.strokeStyle = palette().lines;
    ctx.lineWidth = 0.6 * instance.canvas.pxratio;
    ctx.globalAlpha = AI_GLOBE.outlineOpacity;
    ctx.beginPath();
    ctx.arc(x, y, radius, 0, Math.PI * 2);
    ctx.stroke();
    // Fine meridians/equator give the circular network a globe silhouette.
    ctx.globalAlpha = AI_GLOBE.gridOpacity;
    [0.38, 0.72].forEach((scale) => {
      ctx.beginPath();
      ctx.ellipse(x, y, radius * scale, radius, 0, 0, Math.PI * 2);
      ctx.stroke();
    });
    ctx.beginPath();
    ctx.ellipse(x, y, radius, radius * 0.3, 0, 0, Math.PI * 2);
    ctx.stroke();
    ctx.restore();
    });
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        if (i % globeBounds.globes.length === j % globeBounds.globes.length) {
          instance.fn.interact.linkParticles(particles[i], particles[j]);
        }
      }
    }
  };

  const syncMotion = () => {
    if (gathering) gathering.lastFrame = performance.now();
    window.cancelAnimationFrame(instance.fn.drawAnimFrame);
    instance.particles.move.enable = inView && !document.hidden && !reduced.matches;
    instance.fn.vendors.draw();
  };

  const setSlide = (index) => {
    const previous = active;
    const previousCount = instance.particles.array.length;
    const previousSpeed = instance.particles.move.speed;
    active = index % AI_PARTICLE_PRESETS.length;
    const preset = AI_PARTICLE_PRESETS[active];
    const colours = palette();
    const ratio = instance.canvas.pxratio;
    const particles = instance.particles;
    particles.number.value = mobile.matches ? preset.mobileCount : preset.count;
    particles.color.value = colours.dots;
    particles.opacity.value = preset.opacity;
    particles.size.value = preset.size * ratio;
    particles.move.speed = preset.speed * ratio;
    Object.assign(particles.line_linked, {
      distance: preset.lineDistance * ratio, width: preset.lineWidth * ratio,
      opacity: preset.lineOpacity, color: colours.lines, color_rgb_line: rgb(colours.lines),
    });
    // Update the existing simulation instead of resetting particle positions.
    while (particles.array.length < particles.number.value) {
      particles.array.push(new instance.fn.particle(particles.color, preset.opacity));
    }
    particles.array.length = particles.number.value;
    particles.array.forEach((particle) => {
      particle.color = { value: colours.dots, rgb: rgb(colours.dots) };
      particle.opacity = preset.opacity;
      particle.radius = (0.4 + Math.random() * 0.6) * particles.size.value;
    });
    if (active !== 0) {
      if (previous !== active || !globeBounds || previousCount !== particles.array.length) {
        if (reduced.matches) seedGlobe();
        else gatherGlobe(previousCount, previousSpeed);
      }
    } else if (previous !== 0) {
      gathering = undefined;
      // Restore full-canvas movement for the other slides.
      particles.array.forEach((particle) => {
        particle.x = Math.random() * instance.canvas.w;
        particle.y = Math.random() * instance.canvas.h;
      });
      globeBounds = undefined;
    }
    syncMotion();
  };

  const themeObserver = new MutationObserver(() => setSlide(active));
  themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
  mobile.addEventListener('change', () => setSlide(active));
  reduced.addEventListener('change', syncMotion);
  document.addEventListener('visibilitychange', syncMotion);
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(([entry]) => {
      inView = entry.isIntersecting;
      syncMotion();
    });
    observer.observe(carousel);
  }
  return { setSlide };
};
