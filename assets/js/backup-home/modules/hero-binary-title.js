/**
 * Scrambles a fixed-width binary string into Techn010gy, then loops 010 <-> olo.
 */
const wait = (duration) => new Promise((resolve) => {
  window.setTimeout(resolve, duration);
});

function render(sequence, characters) {
  sequence.textContent = characters.join('');
}

async function scramble(sequence, characters) {
  const iterations = 12;

  for (let iteration = 0; iteration < iterations; iteration += 1) {
    for (let index = 0; index < characters.length; index += 1) {
      characters[index] = Math.random() > 0.5 ? '1' : '0';
    }

    render(sequence, characters);
    await wait(45);
  }
}

async function replaceSequentially(sequence, characters, target, indexes = target.keys()) {
  for (const index of indexes) {
    characters[index] = target[index];
    render(sequence, characters);
    await wait(70);
  }
}

export async function initHeroBinaryTitle() {
  const hero = document.querySelector('.home-hero');
  const sequence = document.querySelector('.home-hero__sequence');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!hero || !sequence) {
    return;
  }

  const binary = [...'0110101011'];
  const hybrid = [...'Techn010gy'];
  const technology = [...'Technology'];
  const middleIndexes = [5, 6, 7];
  const characters = [...binary];

  if (reduceMotion) {
    render(sequence, technology);
    hero.classList.add('home-hero--subtitle-visible', 'home-hero--content-visible');
    return;
  }

  await scramble(sequence, characters);
  await replaceSequentially(sequence, characters, hybrid);

  hero.classList.add('home-hero--subtitle-visible');
  await wait(400);
  hero.classList.add('home-hero--content-visible');

  while (sequence.isConnected) {
    await wait(500);
    await replaceSequentially(sequence, characters, technology, middleIndexes);
    await wait(700);
    await replaceSequentially(sequence, characters, hybrid, middleIndexes);
  }
}
