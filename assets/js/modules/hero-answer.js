export function initHeroAnswer() {
  const form = document.querySelector('[data-hero-answer-form]');
  const dialog = document.querySelector('[data-hero-answer]');
  const config = window.nuwareAi;
  if (!form || !dialog || typeof dialog.showModal !== 'function' || !config?.endpoint || !config?.nonce) return;

  const input = form.querySelector('.particle-hero__input');
  const submitButton = form.querySelector('.particle-hero__submit');
  const closeButton = dialog.querySelector('[data-hero-answer-close]');
  const liveRegion = dialog.querySelector('[data-hero-answer-live]');
  const status = dialog.querySelector('[data-hero-answer-status]');
  const question = dialog.querySelector('[data-hero-answer-question]');
  const answer = dialog.querySelector('[data-hero-answer-text]');
  const remaining = dialog.querySelector('[data-hero-answer-remaining]');
  const limit = dialog.querySelector('[data-hero-answer-limit]');
  if (!input || !submitButton || !closeButton || !liveRegion || !status || !question || !answer || !remaining || !limit) return;

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let submitting = false;
  let exhausted = false;
  let returnFocus = null;
  let closeTimer = null;

  const setFormDisabled = (disabled) => {
    input.disabled = disabled;
    submitButton.disabled = disabled;
  };

  const setLoading = (loading) => {
    submitting = loading;
    liveRegion.setAttribute('aria-busy', loading ? 'true' : 'false');
    closeButton.disabled = loading;
    setFormDisabled(loading || exhausted);
  };

  const resetContent = () => {
    status.hidden = true;
    status.textContent = '';
    status.classList.remove('hero-answer__status--error');
    question.hidden = true;
    question.textContent = '';
    answer.hidden = true;
    answer.textContent = '';
    remaining.hidden = true;
    remaining.textContent = '';
    limit.hidden = true;
  };

  const openDialog = () => {
    window.clearTimeout(closeTimer);
    if (!dialog.open) {
      returnFocus = document.activeElement;
      dialog.showModal();
      document.documentElement.classList.add('hero-answer-open');
    }
    requestAnimationFrame(() => dialog.classList.add('hero-answer--open'));
  };

  const closeDialog = () => {
    if (!dialog.open || submitting) return;
    dialog.classList.remove('hero-answer--open');
    document.documentElement.classList.remove('hero-answer-open');

    const finishClose = () => {
      if (dialog.open) dialog.close();
      if (returnFocus?.isConnected && !returnFocus.disabled) returnFocus.focus({ preventScroll: true });
      returnFocus = null;
    };

    if (reduceMotion) {
      finishClose();
      return;
    }
    closeTimer = window.setTimeout(finishClose, 300);
  };

  const showLimit = () => {
    exhausted = true;
    setLoading(false);
    status.hidden = true;
    remaining.hidden = true;
    limit.hidden = false;
    setFormDisabled(true);
    openDialog();
  };

  const showError = () => {
    resetContent();
    status.textContent = 'Something went wrong. Please try again.';
    status.classList.add('hero-answer__status--error');
    status.hidden = false;
    setLoading(false);
    openDialog();
  };

  closeButton.addEventListener('click', closeDialog);
  dialog.addEventListener('cancel', (event) => {
    event.preventDefault();
    closeDialog();
  });
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) closeDialog();
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (submitting || exhausted) return;

    const submittedQuestion = input.value.trim();
    if (!submittedQuestion) {
      input.focus();
      return;
    }

    resetContent();
    status.textContent = 'Finding an answer…';
    status.hidden = false;
    setLoading(true);
    openDialog();

    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), 65000);

    try {
      const response = await fetch(config.endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': config.nonce,
        },
        body: JSON.stringify({ question: submittedQuestion }),
        signal: controller.signal,
      });
      const data = await response.json().catch(() => null);

      if (response.status === 429) {
        resetContent();
        showLimit();
        return;
      }
      if (!response.ok || !data?.answer) throw new Error('NuWare AI request failed');

      resetContent();
      question.textContent = submittedQuestion;
      question.hidden = false;
      answer.textContent = data.answer;
      answer.hidden = false;
      input.value = '';

      const questionsRemaining = Number(data.questions_remaining);
      if (questionsRemaining <= 0) {
        showLimit();
      } else {
        remaining.textContent = `${questionsRemaining} question${questionsRemaining === 1 ? '' : 's'} remaining`;
        remaining.hidden = false;
        setLoading(false);
      }
    } catch (error) {
      if (!exhausted) showError();
    } finally {
      window.clearTimeout(timeout);
    }
  });
}
