/** Update server-rendered results without interrupting typing or moving focus. */
export function initCaseLibrary() {
  const form = document.querySelector('.case-library__filters');
  const results = document.querySelector('[data-case-library-results]');
  if (!form || !results) return;

  const search = form.querySelector('[type="search"]');
  let timer;
  let controller;
  let revision = 0;
  let composing = false;

  async function update(version) {
    const url = new URL(form.action);
    new FormData(form).forEach((value, key) => {
      if (value) url.searchParams.set(key, value);
    });
    controller = new AbortController();
    results.setAttribute('aria-busy', 'true');
    try {
      const response = await fetch(url, { signal: controller.signal });
      if (!response.ok) throw new Error('Unable to load case studies');
      const html = new DOMParser().parseFromString(await response.text(), 'text/html');
      const next = html.querySelector('[data-case-library-results]');
      if (!next) throw new Error('Missing results');
      if (version !== revision) return;
      results.replaceChildren(...next.childNodes);
      window.history.replaceState(null, '', url);
    } catch (error) {
      if (error.name !== 'AbortError' && version === revision) {
        // Preserve working filters even if asynchronous updates are unavailable.
        window.location.assign(url);
      }
    } finally {
      if (version === revision) results.removeAttribute('aria-busy');
    }
  }

  function schedule(delay = 0) {
    clearTimeout(timer);
    controller?.abort();
    const version = ++revision;
    results.removeAttribute('aria-busy');
    if (!composing) timer = setTimeout(() => update(version), delay);
  }

  search.addEventListener('input', () => schedule(300));
  search.addEventListener('compositionstart', () => { composing = true; schedule(); });
  search.addEventListener('compositionend', () => { composing = false; schedule(300); });
  form.querySelectorAll('select').forEach((select) => {
    select.addEventListener('change', () => schedule());
  });
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    schedule();
  });
}
