/**
 * Tablet/mobile: the article list is a native scroll-snap row (touch/swipe
 * works out of the box); the arrows scroll it by one card. On desktop the
 * list is a static 3-column grid and the arrows are hidden in CSS.
 *
 * @param {ParentNode} root Where to look for sections (the document, or a block preview in the editor)
 */
export default function initLatestArticleListSection(root = document) {
  root.querySelectorAll('.js-lals').forEach((section) => {
    const track = section.querySelector('.js-lals-track');
    const prev = section.querySelector('.js-lals-prev');
    const next = section.querySelector('.js-lals-next');

    if (!track || !prev || !next || section.dataset.lalsInit) return;
    section.dataset.lalsInit = 'true';

    // Disable an arrow once the row can't scroll further that way
    const update = () => {
      const max = track.scrollWidth - track.clientWidth;
      prev.disabled = track.scrollLeft <= 1;
      next.disabled = track.scrollLeft >= max - 1;
    };

    const scrollByCard = (direction) => {
      const card = track.firstElementChild;
      const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
      const distance = card ? card.getBoundingClientRect().width + gap : track.clientWidth;
      const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      track.scrollBy({ left: direction * distance, behavior: reduceMotion ? 'auto' : 'smooth' });
    };

    prev.addEventListener('click', () => scrollByCard(-1));
    next.addEventListener('click', () => scrollByCard(1));
    track.addEventListener('scroll', update, { passive: true });

    if ('ResizeObserver' in window) {
      new ResizeObserver(update).observe(track);
    } else {
      window.addEventListener('resize', update);
    }

    update();
  });
}
