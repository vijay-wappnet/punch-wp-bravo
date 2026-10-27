// Space between the fixed header and the opened question
const SCROLL_GAP = 20;
// Longest we wait for an answer's open/close transition (it's 300ms)
const TRANSITION_TIMEOUT = 700;

/**
 * Where the visible page starts: the bottom of the fixed site header (which
 * is also pushed down by the WP admin bar when logged in). Measured each
 * time, as the header is shorter on mobile.
 */
function headerOffset() {
  const header = document.getElementById('site-header');
  if (!header) return 0;
  const { bottom } = header.getBoundingClientRect();
  return Math.max(0, bottom);
}

/**
 * Resolves when the answer's max-height transition has finished (or straight
 * away if there's nothing to animate, e.g. reduced motion), with a timeout
 * so a missed transitionend can't block the scroll.
 */
function transitionDone(answer) {
  const duration = parseFloat(getComputedStyle(answer).transitionDuration) || 0;
  if (!duration) return Promise.resolve();

  return new Promise((resolve) => {
    const finish = () => {
      answer.removeEventListener('transitionend', onEnd);
      clearTimeout(timer);
      resolve();
    };
    const onEnd = (event) => {
      if (event.target === answer && event.propertyName === 'max-height') finish();
    };
    const timer = setTimeout(finish, TRANSITION_TIMEOUT);
    answer.addEventListener('transitionend', onEnd);
  });
}

/**
 * Scrolls the item to just below the fixed header. Content can still move
 * while a long smooth scroll runs (lazy images loading, animated sections),
 * so once it settles the position is checked and corrected if needed.
 */
function scrollToItem(item) {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const behavior = reduceMotion ? 'auto' : 'smooth';
  const offset = () => item.getBoundingClientRect().top - headerOffset() - SCROLL_GAP;

  window.scrollTo({ top: window.pageYOffset + offset(), behavior });

  let corrected = false;
  const correct = () => {
    if (corrected) return;
    corrected = true;
    window.removeEventListener('scrollend', correct);
    if (Math.abs(offset()) > 2) {
      window.scrollTo({ top: window.pageYOffset + offset(), behavior });
    }
  };
  // `scrollend` where supported, otherwise a timeout after the smooth scroll
  window.addEventListener('scrollend', correct, { once: true });
  setTimeout(correct, 1500);
}

/**
 * Sets one FAQ item open or closed. The answer's max-height is animated
 * (CSS) between 0 and its content height, then released to `none` once open
 * so it follows content reflow (e.g. on resize). Closed answers are `inert`
 * so the collapsed content can't be focused or read by screen readers.
 */
function setOpen(item, open) {
  const toggle = item.querySelector('.js-faqs-accordion-toggle');
  const answer = item.querySelector('.faqs-accordion-section__answer');
  const content = item.querySelector('.faqs-accordion-section__answer-content');

  item.classList.toggle('is-open', open);
  toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
  if (!answer) return Promise.resolve();

  answer.inert = !open;
  const height = `${content?.offsetHeight ?? 0}px`;

  if (open) {
    answer.style.maxHeight = height;
    answer.addEventListener('transitionend', function release(event) {
      if (event.propertyName !== 'max-height') return;
      answer.removeEventListener('transitionend', release);
      if (item.classList.contains('is-open')) answer.style.maxHeight = 'none';
    });
  } else {
    // Animating from `none` doesn't transition, so pin the current height first
    answer.style.maxHeight = height;
    void answer.offsetHeight; // force reflow
    answer.style.maxHeight = '0px';
  }

  return transitionDone(answer);
}

/**
 * Accordion: one answer open at a time; clicking the open question closes it.
 * Native <button>s already handle Enter/Space, so only `click` is needed.
 *
 * @param {ParentNode} root Where to look for accordions (the document, or a block preview in the editor)
 */
export default function initFaqsAccordionSection(root = document) {
  const accordions = root.querySelectorAll('.js-faqs-accordion');
  if (!accordions.length) return;

  accordions.forEach((accordion) => {
    if (accordion.dataset.faqsInit) return;
    accordion.dataset.faqsInit = 'true';

    accordion.addEventListener('click', (event) => {
      const toggle = event.target.closest('.js-faqs-accordion-toggle');
      if (!toggle || !accordion.contains(toggle)) return;

      const item = toggle.closest('.faqs-accordion-section__item');
      const willOpen = !item.classList.contains('is-open');

      const transitions = [];
      accordion.querySelectorAll('.faqs-accordion-section__item.is-open').forEach((openItem) => {
        if (openItem !== item) transitions.push(setOpen(openItem, false));
      });

      transitions.push(setOpen(item, willOpen));

      // Once the answer has opened AND any other item has finished closing
      // (which moves this one up), scroll the opened question to the top of
      // the viewport, just below the fixed header - so its answer is in view
      // on mobile too
      if (willOpen) {
        Promise.all(transitions).then(() => {
          if (item.classList.contains('is-open')) scrollToItem(item);
        });
      }
    });
  });
}
