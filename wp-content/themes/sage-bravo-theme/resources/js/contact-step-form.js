/**
 * Contact Form 7 step form ("Contact us Form").
 *
 * Markup: the CF7 form template (cf7-forms/contact-us-form.html) - a [data-cfs]
 * wrapper holding [data-cfs-step] panels, each with one or more [data-cfs-page].
 *
 * - Desktop/tablet: one step open at a time, "Next" validates the whole step.
 *   Completed steps can be clicked to reopen them.
 * - Mobile: one page at a time (steps 02/03 have two pages each) with a
 *   progress bar; "Next" validates the current page.
 * - CF7 still validates everything on submit; if it reports an invalid field,
 *   the form jumps back to the step/page holding it.
 */

const MOBILE = window.matchMedia('(max-width: 767px)');

const MESSAGES = {
  required: 'Please fill out this field.',
  email: 'Please enter an email address.',
  number: 'Please enter a number.',
  phone: 'Please enter a valid phone number (8-12 digits, numbers only).',
};

// Phone: digits only, 8-12 of them (checked again server-side)
const PHONE_PATTERN = /^\d{8,12}$/;

export default function initContactStepForm() {
  document.querySelectorAll('[data-cfs]').forEach(setup);
}

function setup(root) {
  const form = root.closest('form');
  const wpcf7 = root.closest('.wpcf7');
  const steps = [...root.querySelectorAll('[data-cfs-step]')];

  if (!form || !steps.length) {
    return;
  }

  const pagesOf = (step) => [...step.querySelectorAll('[data-cfs-page]')];
  const totalScreens = steps.reduce((sum, step) => sum + pagesOf(step).length, 0) + 1; // + thank you

  const state = { step: 0, page: 0, reached: 0, complete: false };

  // ---------------------------------------------------------------- render
  function render() {
    let screensBefore = 0;

    steps.forEach((step, index) => {
      const pages = pagesOf(step);
      const isActive = index === state.step;
      const isDone = index < state.reached && !state.complete;
      const tab = step.querySelector('.cfs-step__tab');

      step.classList.toggle('is-active', isActive);
      step.classList.toggle('is-done', isDone);
      step.classList.toggle('is-last-page', state.page === pages.length - 1);

      pages.forEach((page, p) => page.classList.toggle('is-current', isActive && p === state.page));

      // Completed (closed) steps act as buttons on desktop
      if (isDone && !isActive && !MOBILE.matches) {
        tab.setAttribute('role', 'button');
        tab.tabIndex = 0;
      } else {
        tab.removeAttribute('role');
        tab.tabIndex = -1;
      }

      const progress = state.complete ? 100 : ((screensBefore + state.page + 1) / totalScreens) * 100;
      if (isActive) {
        step.querySelector('.cfs-step__progress')?.style.setProperty('--cfs-progress', `${progress}%`);
      }

      screensBefore += pages.length;
    });
  }

  function goTo(stepIndex, pageIndex = 0, { focus = true } = {}) {
    state.step = stepIndex;
    state.page = pageIndex;
    state.reached = Math.max(state.reached, stepIndex);
    render();

    if (focus) {
      const tab = steps[stepIndex].querySelector('.cfs-step__tab');
      tab.focus({ preventScroll: true });

      if (root.getBoundingClientRect().top < 0) {
        root.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
      }
    }
  }

  // The part of the form "Next"/"Submit" validates
  function currentScope() {
    const step = steps[state.step];
    return MOBILE.matches ? pagesOf(step)[state.page] : step;
  }

  function next() {
    const scope = currentScope();
    if (!validate(scope)) {
      return;
    }

    const pages = pagesOf(steps[state.step]);
    if (MOBILE.matches && state.page < pages.length - 1) {
      goTo(state.step, state.page + 1);
    } else if (state.step < steps.length - 1) {
      goTo(state.step + 1, 0);
    }
  }

  // ---------------------------------------------------------------- events
  root.querySelectorAll('[data-cfs-next]').forEach((button) => button.addEventListener('click', next));

  steps.forEach((step, index) => {
    const tab = step.querySelector('.cfs-step__tab');

    step.addEventListener('click', (event) => {
      if (step.classList.contains('is-done') && !step.classList.contains('is-active') && !event.target.closest('.cfs-step__body')) {
        goTo(index);
      }
    });

    tab.addEventListener('keydown', (event) => {
      if (tab.getAttribute('role') === 'button' && (event.key === 'Enter' || event.key === ' ')) {
        event.preventDefault();
        goTo(index);
      }
    });
  });

  // Enter in a field moves to the next step instead of submitting early
  form.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter' || !event.target.matches('input:not([type="checkbox"])')) {
      return;
    }

    const isFinalScreen = state.step === steps.length - 1 && (!MOBILE.matches || state.page === pagesOf(steps[state.step]).length - 1);
    if (!isFinalScreen) {
      event.preventDefault();
      next();
    }
  });

  // Check the final step/page before CF7 sends the form
  root.querySelectorAll('.wpcf7-submit').forEach((button) => {
    button.addEventListener('click', (event) => {
      if (!validate(currentScope())) {
        event.preventDefault();
      }
    });
  });

  // Clear a field's error as soon as it's edited
  form.addEventListener('input', (event) => clearError(event.target));
  form.addEventListener('change', (event) => clearError(event.target));

  // CF7 server-side validation failed: open the step/page with the first error
  wpcf7?.addEventListener('wpcf7invalid', () => {
    const invalid = root.querySelector('.wpcf7-not-valid');
    const step = invalid?.closest('[data-cfs-step]');
    if (!step) {
      return;
    }

    const page = invalid.closest('[data-cfs-page]');
    goTo(steps.indexOf(step), Math.max(0, pagesOf(step).indexOf(page)));
  });

  // Sent: show the thank-you panel in the last step
  wpcf7?.addEventListener('wpcf7mailsent', () => {
    const last = steps[steps.length - 1];
    const title = last.querySelector('[data-cfs-done-title]');

    state.complete = true;
    root.classList.add('is-complete');
    if (title) {
      title.textContent = title.dataset.cfsDoneTitle;
    }
    last.querySelector('[data-cfs-thanks]')?.removeAttribute('hidden');
    root.querySelectorAll('.cfs-select').forEach(syncEmpty);

    goTo(steps.length - 1);
  });

  MOBILE.addEventListener('change', () => {
    // Desktop shows whole steps, so restart the step on the first page
    state.page = 0;
    render();
  });

  // ---------------------------------------------------------------- fields
  root.querySelectorAll('input[type="number"]').forEach(addNumberSpinner);

  // Phone accepts digits only - strip anything else as it's typed/pasted
  root.querySelectorAll('input[type="tel"]').forEach((input) => {
    input.setAttribute('inputmode', 'numeric');
    input.addEventListener('input', () => {
      const digits = input.value.replace(/\D/g, '').slice(0, 12);
      if (digits !== input.value) {
        input.value = digits;
      }
    });
  });

  // Hide the plan select's placeholder on desktop while nothing is chosen
  root.querySelectorAll('.cfs-select').forEach((select) => {
    syncEmpty(select);
    select.addEventListener('change', () => syncEmpty(select));
  });
  form.addEventListener('reset', () => setTimeout(() => root.querySelectorAll('.cfs-select').forEach(syncEmpty)));

  render();
}

function syncEmpty(select) {
  select.classList.toggle('is-empty', !select.value);
}

// Up/down chevrons next to number inputs (native spinners are hidden in CSS)
function addNumberSpinner(input) {
  const spin = document.createElement('span');
  spin.className = 'cfs-number-spin';

  [['up', 'Increase', () => input.stepUp()], ['down', 'Decrease', () => input.stepDown()]].forEach(([dir, label, step]) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `cfs-number-spin__btn cfs-number-spin__btn--${dir}`;
    button.tabIndex = -1; // arrow keys already step the input
    // "Increase How many properties do you have?" (the * and the invisible
    // word joiner that keeps "?*" together are dropped)
    const fieldLabel = (input.labels?.[0]?.textContent || '').replace(/[*⁠]/g, '').trim();
    button.setAttribute('aria-label', `${label} ${fieldLabel}`.trim());
    button.setAttribute('data-event', `Contact form - ${label} ${fieldLabel}`.trim());
    button.addEventListener('click', () => {
      if (dir === 'down' && !input.value) {
        return;
      }
      step();
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
    spin.appendChild(button);
  });

  input.insertAdjacentElement('afterend', spin);
}

// ---------------------------------------------------------------- validation
function validate(scope) {
  let firstInvalid = null;

  const fail = (wrap, control, message) => {
    showError(wrap, control, message);
    firstInvalid ??= control.matches('input, select, textarea') ? control : control.querySelector('input');
  };

  scope.querySelectorAll('.wpcf7-form-control-wrap').forEach((wrap) => {
    const control = wrap.querySelector('.wpcf7-form-control');
    if (!control || control.closest('[data-cfs-require-one]')) {
      return;
    }

    clearError(control);
    const required = control.classList.contains('wpcf7-validates-as-required');

    if (control.classList.contains('wpcf7-checkbox')) {
      if (required && !control.querySelector('input:checked')) {
        fail(wrap, control, MESSAGES.required);
      }
      return;
    }

    const value = control.value.trim();
    if (required && !value) {
      fail(wrap, control, MESSAGES.required);
    } else if (value && control.type === 'email' && !control.checkValidity()) {
      fail(wrap, control, MESSAGES.email);
    } else if (value && control.type === 'number' && !control.checkValidity()) {
      fail(wrap, control, MESSAGES.number);
    } else if (value && control.type === 'tel' && !PHONE_PATTERN.test(value)) {
      fail(wrap, control, MESSAGES.phone);
    }
  });

  // "Where are your properties located?" - a ticked box or a chosen region
  scope.querySelectorAll('[data-cfs-require-one]').forEach((group) => {
    const wrap = group.querySelector('.wpcf7-form-control-wrap');
    const control = wrap.querySelector('.wpcf7-form-control');
    clearError(control);

    const hasValue = group.querySelector('input:checked') || [...group.querySelectorAll('select')].some((select) => select.value);
    if (!hasValue) {
      fail(wrap, control, MESSAGES.required);
    }
  });

  firstInvalid?.focus();
  return !firstInvalid;
}

function showError(wrap, control, message) {
  control.classList.add('wpcf7-not-valid');
  control.setAttribute('aria-invalid', 'true');

  const tip = document.createElement('span');
  tip.className = 'wpcf7-not-valid-tip';
  tip.setAttribute('aria-hidden', 'true');
  tip.textContent = message;
  wrap.appendChild(tip);
}

function clearError(target) {
  const group = target.closest?.('[data-cfs-require-one]');
  const wrap = group?.querySelector('.wpcf7-form-control-wrap') ?? target.closest?.('.wpcf7-form-control-wrap');
  if (!wrap) {
    return;
  }

  wrap.querySelectorAll('.wpcf7-not-valid-tip').forEach((tip) => tip.remove());
  wrap.querySelectorAll('.wpcf7-not-valid').forEach((control) => {
    control.classList.remove('wpcf7-not-valid');
    control.setAttribute('aria-invalid', 'false');
  });
}
