/**
 * Metrics Counter Section
 *  - Each number counts up from 0 to its value once, when the section scrolls
 *    into view (IntersectionObserver + requestAnimationFrame). The prefix and
 *    suffix stay put and the number ends on exactly the text the editor typed
 *    (thousands separators and decimals kept). The counting number is
 *    aria-hidden; screen readers get the final value from a hidden copy.
 *  - On mobile the arrow buttons move the horizontal track one card at a time
 *    and are disabled at either end.
 *
 * Every section is set up on its own, so any number of them can be on a page.
 * With reduced motion the numbers are simply left at their final values.
 *
 * @param {ParentNode} root Where to look for sections (the document, or a block preview in the editor)
 */

const DURATION = 1600; // ms

const easeOutCubic = (t) => 1 - (1 - t) ** 3;

function formatNumber(value, decimals, grouping) {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
    useGrouping: grouping,
  }).format(value);
}

function initCounters(section) {
  const values = Array.from(section.querySelectorAll('.js-mcs-value[data-target]'));
  if (!values.length) return;

  const counters = values.map((el) => ({
    el,
    target: Number(el.dataset.target),
    decimals: Number(el.dataset.decimals) || 0,
    grouping: el.dataset.grouping === 'true',
    final: el.textContent,
  })).filter((counter) => Number.isFinite(counter.target));

  if (!counters.length) return;

  // Start from zero (the markup holds the final values until now)
  counters.forEach((counter) => {
    counter.el.textContent = formatNumber(0, counter.decimals, counter.grouping);
  });

  const run = () => {
    const start = performance.now();

    const frame = (now) => {
      const progress = Math.min((now - start) / DURATION, 1);
      const eased = easeOutCubic(progress);

      counters.forEach((counter) => {
        // At the end show the editor's own text, so the formatting is exactly theirs
        counter.el.textContent = progress === 1
          ? counter.final
          : formatNumber(counter.target * eased, counter.decimals, counter.grouping);
      });

      if (progress < 1) requestAnimationFrame(frame);
    };

    requestAnimationFrame(frame);
  };

  // Once per page load: stop watching as soon as it has been triggered
  const observer = new IntersectionObserver((entries) => {
    if (!entries.some((entry) => entry.isIntersecting)) return;

    observer.disconnect();
    run();
  }, { threshold: 0.35 });

  observer.observe(section);
}

function initCarousel(section) {
  const track = section.querySelector('.js-mcs-track');
  const prev = section.querySelector('.js-mcs-prev');
  const next = section.querySelector('.js-mcs-next');
  const cards = track ? Array.from(track.querySelectorAll('.js-mcs-card')) : [];
  if (!track || !prev || !next || cards.length < 2) return;

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const behavior = reducedMotion ? 'auto' : 'smooth';

  // The card that is currently first in view
  const currentIndex = () => {
    const trackLeft = track.getBoundingClientRect().left;
    let best = 0;
    let bestDistance = Infinity;

    cards.forEach((card, index) => {
      const distance = Math.abs(card.getBoundingClientRect().left - trackLeft - parseFloat(getComputedStyle(track).paddingLeft || 0));
      if (distance < bestDistance) {
        best = index;
        bestDistance = distance;
      }
    });

    return best;
  };

  const goTo = (index) => {
    const card = cards[Math.max(0, Math.min(cards.length - 1, index))];
    // Moves only the track (not the page): scroll it directly
    const left = card.offsetLeft - parseFloat(getComputedStyle(track).paddingLeft || 0);
    track.scrollTo({ left, behavior });
  };

  prev.addEventListener('click', () => goTo(currentIndex() - 1));
  next.addEventListener('click', () => goTo(currentIndex() + 1));

  // Disable an arrow when its end of the track is in view. Watching the first
  // and last card avoids a scroll listener.
  const ends = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      const visible = entry.intersectionRatio >= 0.95;
      if (entry.target === cards[0]) prev.disabled = visible;
      if (entry.target === cards[cards.length - 1]) next.disabled = visible;
    });
  }, { root: track, threshold: [0, 0.95, 1] });

  ends.observe(cards[0]);
  ends.observe(cards[cards.length - 1]);
}

export default function initMetricsCounterSection(root = document) {
  const sections = root.querySelectorAll('.js-mcs');
  if (!sections.length) return;

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const animationsEnabled = document.body.classList.contains('animations-enabled');

  sections.forEach((section) => {
    if (section.dataset.mcsInitialised) return;
    section.dataset.mcsInitialised = 'true';

    initCarousel(section);

    // Reduced motion, or animations turned off in the site options: the final numbers stay as they are
    if (!reducedMotion && animationsEnabled) initCounters(section);
  });
}
