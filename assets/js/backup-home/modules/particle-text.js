const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
const easeOutCubic = (value) => 1 - Math.pow(1 - value, 3);
const easeInOutCubic = (value) =>
  value < 0.5 ? 4 * value * value * value : 1 - Math.pow(-2 * value + 2, 3) / 2;

const FADE_IN_DURATION = 800;
const GATHER_DURATION = 3000;
const GATHER_STAGGER = 1040;
const FORMED_HOLD_DURATION = 2000;
const DISPERSE_DURATION = 1100;

const seededValue = (index, cycle, multiplier, offset) => {
  let value = Math.imul(index + 1, multiplier) ^ Math.imul(cycle + 1, offset);

  value ^= value >>> 16;
  value = Math.imul(value, 0x7feb352d);
  value ^= value >>> 15;
  value = Math.imul(value, 0x846ca68b);
  value ^= value >>> 16;

  return (value >>> 0) / 4294967296;
};

export const initParticleText = () => {
  const hero = document.querySelector('.particle-hero[data-particle-text]');

  if (!hero) {
    return;
  }

  const canvas = hero.querySelector('.particle-hero__canvas');
  const textContainer = hero.querySelector('.particle-hero__text');
  const context = canvas?.getContext('2d');
  const text = hero.dataset.particleText;

  if (!canvas || !textContainer || !context || !text) {
    return;
  }

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const sampleCanvas = document.createElement('canvas');
  const sampleContext = sampleCanvas.getContext('2d', { willReadFrequently: true });

  if (!sampleContext) {
    return;
  }

  let width = 0;
  let height = 0;
  let particles = [];
  let animationFrame = 0;
  let phase = reduceMotion ? 'formed' : 'fade-in';
  let phaseStartedAt = performance.now();
  let cycle = 0;
  let contentRevealed = false;

  const revealContent = () => {
    if (!contentRevealed) {
      hero.classList.add('particle-hero--ready');
      contentRevealed = true;
    }
  };

  const getScatterPosition = (index) => ({
    x: seededValue(index, cycle, 3571, 1013) * width,
    y: seededValue(index, cycle, 2371, 1877) * height,
  });

  const setParticleStartPositions = () => {
    particles.forEach((particle) => {
      particle.startX = particle.x;
      particle.startY = particle.y;
    });
  };

  const startGather = (timestamp) => {
    setParticleStartPositions();
    phase = 'gather';
    phaseStartedAt = timestamp;
  };

  const startDisperse = (timestamp) => {
    cycle += 1;
    setParticleStartPositions();

    particles.forEach((particle, index) => {
      const horizontalOffset =
        (seededValue(index, cycle, 3571, 1013) * 2 - 1) * width * 0.1;
      const verticalOffset =
        (seededValue(index, cycle, 2371, 1877) * 2 - 1) * height * 0.1;

      particle.scatterX = clamp(particle.targetX + horizontalOffset, 0, width);
      particle.scatterY = clamp(particle.targetY + verticalOffset, 0, height);
    });

    phase = 'disperse';
    phaseStartedAt = timestamp;
  };

  const buildParticles = () => {
    const heroBounds = hero.getBoundingClientRect();
    const textBounds = textContainer.getBoundingClientRect();
    const isMobile = width < 700;
    const animatedText = isMobile ? 'Tech' : text;
    const fontSize = isMobile
      ? clamp((textBounds.width / 7.15) * 2, 144, 220)
      : clamp(textBounds.width / 7.15, 72, 210);
    const padding = Math.ceil(fontSize * 0.18);
    const font = `700 ${fontSize}px Geist, Arial, sans-serif`;

    sampleContext.font = font;
    const metrics = sampleContext.measureText(animatedText);
    const ascent = Math.ceil(metrics.actualBoundingBoxAscent || fontSize * 0.75);
    const descent = Math.ceil(metrics.actualBoundingBoxDescent || fontSize * 0.2);

    sampleCanvas.width = Math.ceil(metrics.width) + padding * 2;
    sampleCanvas.height = ascent + descent + padding * 2;
    sampleContext.clearRect(0, 0, sampleCanvas.width, sampleCanvas.height);
    sampleContext.font = font;
    sampleContext.fillStyle = '#ffffff';
    sampleContext.textBaseline = 'alphabetic';
    sampleContext.fillText(animatedText, padding, padding + ascent);

    const imageData = sampleContext.getImageData(
      0,
      0,
      sampleCanvas.width,
      sampleCanvas.height
    ).data;
    const density = width < 700 ? 6 : 7;
    const targets = [];
    const textLeft = textBounds.left - heroBounds.left;
    const textTop = textBounds.top - heroBounds.top;
    const verticalOffset = clamp(textBounds.height * 0.08, 24, 50);
    const originX = textLeft + (textBounds.width - sampleCanvas.width) / 2;
    const originY = textTop + (textBounds.height - sampleCanvas.height) / 2 - verticalOffset;
    const baselineInText =
      (textBounds.height - sampleCanvas.height) / 2 + padding + ascent - verticalOffset;

    hero.style.setProperty(
      '--particle-hero-baseline-tail',
      `${textBounds.height - baselineInText}px`
    );

    for (let y = 0; y < sampleCanvas.height; y += density) {
      for (let x = 0; x < sampleCanvas.width; x += density) {
        const alpha = imageData[(y * sampleCanvas.width + x) * 4 + 3];

        if (alpha > 128) {
          targets.push({ x: originX + x, y: originY + y });
        }
      }
    }

    const step = Math.max(1, Math.ceil(targets.length / 3400));

    particles = targets.filter((_, index) => index % step === 0).map((target, index) => {
      const scatter = getScatterPosition(index);

      return {
        x: reduceMotion ? target.x : scatter.x,
        y: reduceMotion ? target.y : scatter.y,
        startX: scatter.x,
        startY: scatter.y,
        scatterX: scatter.x,
        scatterY: scatter.y,
        targetX: target.x,
        targetY: target.y,
        delaySeed: seededValue(index, 0, 2081, 719),
        glyph: index % 2 === 0 ? '0' : '1',
      };
    });
  };

  const resize = () => {
    const bounds = hero.getBoundingClientRect();
    const pixelRatio = Math.min(window.devicePixelRatio || 1, 2);

    width = bounds.width;
    height = bounds.height;
    canvas.width = Math.round(width * pixelRatio);
    canvas.height = Math.round(height * pixelRatio);
    canvas.style.width = `${width}px`;
    canvas.style.height = `${height}px`;
    context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
    buildParticles();

    if (reduceMotion) {
      revealContent();
    } else {
      phase = 'fade-in';
      phaseStartedAt = performance.now();
    }
  };

  const draw = (timestamp) => {
    context.clearRect(0, 0, width, height);
    context.font = `600 ${width < 700 ? 7 : 9}px Geist, Arial, sans-serif`;
    context.textAlign = 'center';
    context.textBaseline = 'middle';

    const heroStyles = window.getComputedStyle(hero);
    const zeroColor = heroStyles.getPropertyValue('--particle-zero-color').trim() || '#172033';
    const oneColor = heroStyles.getPropertyValue('--particle-one-color').trim() || '#737b89';

    let phaseComplete = true;
    const particleOpacity =
      phase === 'fade-in'
        ? clamp((timestamp - phaseStartedAt) / FADE_IN_DURATION, 0, 1)
        : 1;

    particles.forEach((particle) => {
      if (phase === 'fade-in') {
        // Hold the scattered binary still until it is fully visible.
      } else if (phase === 'gather') {
        const delay = particle.delaySeed * GATHER_STAGGER;
        const progress = clamp((timestamp - phaseStartedAt - delay) / GATHER_DURATION, 0, 1);
        const eased = easeInOutCubic(progress);
        particle.x = particle.startX + (particle.targetX - particle.startX) * eased;
        particle.y = particle.startY + (particle.targetY - particle.startY) * eased;
        phaseComplete = phaseComplete && progress === 1;
      } else if (phase === 'disperse') {
        const progress = clamp((timestamp - phaseStartedAt) / DISPERSE_DURATION, 0, 1);
        const eased = easeOutCubic(progress);
        particle.x = particle.startX + (particle.scatterX - particle.startX) * eased;
        particle.y = particle.startY + (particle.scatterY - particle.startY) * eased;
        phaseComplete = phaseComplete && progress === 1;
      } else {
        const idleX = reduceMotion ? 0 : Math.sin(timestamp * 0.0015 + particle.delaySeed * 8) * 0.55;
        const idleY = reduceMotion ? 0 : Math.cos(timestamp * 0.0013 + particle.delaySeed * 7) * 0.45;
        particle.x = particle.targetX + idleX;
        particle.y = particle.targetY + idleY;
      }

      context.globalAlpha = particleOpacity;
      context.fillStyle = particle.glyph === '0' ? zeroColor : oneColor;
      context.fillText(particle.glyph, particle.x, particle.y);
    });

    context.globalAlpha = 1;

    if (!reduceMotion) {
      if (phase === 'fade-in' && timestamp - phaseStartedAt >= FADE_IN_DURATION) {
        startGather(timestamp);
      } else if (phase === 'gather' && phaseComplete) {
        phase = 'formed';
        phaseStartedAt = timestamp;
        revealContent();
      } else if (phase === 'formed' && timestamp - phaseStartedAt >= FORMED_HOLD_DURATION) {
        startDisperse(timestamp);
      } else if (phase === 'disperse' && phaseComplete) {
        startGather(timestamp);
      }

      animationFrame = window.requestAnimationFrame(draw);
    }
  };

  const resizeObserver = new ResizeObserver(resize);
  resizeObserver.observe(hero);

  resize();

  if (reduceMotion) {
    draw(performance.now());
  } else {
    animationFrame = window.requestAnimationFrame(draw);
  }

  window.addEventListener(
    'pagehide',
    () => {
      resizeObserver.disconnect();
      window.cancelAnimationFrame(animationFrame);
    },
    { once: true }
  );
};
