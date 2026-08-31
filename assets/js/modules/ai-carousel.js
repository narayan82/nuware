import { initAiParticles } from './ai-particles.js?v=9';

export const initAiCarousel = () => {
  document.querySelectorAll('[data-ai-carousel]').forEach((carousel) => {
    const slides = Array.from(carousel.querySelectorAll('.ai-carousel__slide'));
    const dots = Array.from(carousel.querySelectorAll('[data-ai-carousel-dot]'));
    const nextButtons = Array.from(carousel.querySelectorAll('[data-ai-carousel-next]'));

    if (!slides.length || slides.length !== dots.length) {
      return;
    }

    let activeIndex = 0;
    const background = initAiParticles(carousel);

    const showSlide = (nextIndex) => {
      activeIndex = (nextIndex + slides.length) % slides.length;
      background.setSlide(activeIndex);

      slides.forEach((slide, index) => {
        const isActive = index === activeIndex;

        slide.hidden = !isActive;
        slide.setAttribute('aria-hidden', String(!isActive));
        dots[index].classList.toggle('ai-carousel__dot--active', isActive);
        dots[index].setAttribute('aria-selected', String(isActive));
        dots[index].tabIndex = isActive ? 0 : -1;
      });
    };

    dots.forEach((dot, index) => {
      dot.addEventListener('click', () => showSlide(index));

      dot.addEventListener('keydown', (event) => {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
          return;
        }

        event.preventDefault();
        const direction = event.key === 'ArrowRight' ? 1 : -1;
        const nextIndex = (index + direction + dots.length) % dots.length;

        showSlide(nextIndex);
        dots[nextIndex].focus();
      });
    });

    nextButtons.forEach((button) => {
      button.addEventListener('click', () => showSlide(activeIndex + 1));
    });

    showSlide(0);
  });
};
