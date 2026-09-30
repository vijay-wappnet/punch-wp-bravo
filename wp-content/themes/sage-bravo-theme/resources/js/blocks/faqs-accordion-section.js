// Scroll-after-open: wait for the 300ms open/close transition, then scroll
const SCROLL_DELAY = 350;
const SCROLL_OFFSET = 400;

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
  if (!answer) return;

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

      accordion.querySelectorAll('.faqs-accordion-section__item.is-open').forEach((openItem) => {
        if (openItem !== item) setOpen(openItem, false);
      });

      setOpen(item, willOpen);

      // Same as the old site: once the answer has opened, smooth-scroll the
      // item to SCROLL_OFFSET px from the top of the viewport
      if (willOpen) {
        setTimeout(() => {
          window.scrollTo({
            top: item.getBoundingClientRect().top + window.pageYOffset - SCROLL_OFFSET,
            behavior: 'smooth',
          });
        }, SCROLL_DELAY);
      }
    });
  });
}
