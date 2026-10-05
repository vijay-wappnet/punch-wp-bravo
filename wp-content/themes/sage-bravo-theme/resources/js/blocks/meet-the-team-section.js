/**
 * Mobile arrows for the Meet The Team track: native horizontal scrolling,
 * the arrows just scroll it by one card. No autoplay.
 *
 * @param {ParentNode} root Where to look for sections (the document, or a block preview in the editor)
 */
export default function initMeetTheTeamSection(root = document) {
  root.querySelectorAll('.js-mts').forEach((section) => {
    if (section.dataset.mtsInit) return;

    const track = section.querySelector('.js-mts-track');
    const nav = section.querySelector('.js-mts-nav');
    const prev = section.querySelector('.js-mts-prev');
    const next = section.querySelector('.js-mts-next');
    if (!track || !nav || !prev || !next) return;

    section.dataset.mtsInit = 'true';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    const update = () => {
      const max = track.scrollWidth - track.clientWidth;
      nav.hidden = max <= 1;
      prev.disabled = track.scrollLeft <= 1;
      next.disabled = track.scrollLeft >= max - 1;
    };

    const scrollByCard = (direction) => {
      const card = track.querySelector('.meet-the-team-section__card');
      if (!card) return;
      const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
      track.scrollBy({
        left: direction * (card.getBoundingClientRect().width + gap),
        behavior: reducedMotion.matches ? 'auto' : 'smooth',
      });
    };

    prev.addEventListener('click', () => scrollByCard(-1));
    next.addEventListener('click', () => scrollByCard(1));
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
  });
}
