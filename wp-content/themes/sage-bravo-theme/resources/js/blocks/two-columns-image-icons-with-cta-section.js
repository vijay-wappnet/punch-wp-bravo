import $ from 'jquery';
import 'jquery-match-height';

const SECTION = '.two-columns-image-icons-with-cta-section';

/**
 * Equal-height columns per section. byRow (default) leaves stacked mobile
 * columns at their natural height.
 */
function initMatchHeight() {
  $(SECTION).each(function () {
    $(this).find(`${SECTION}__column`).matchHeight();
  });
}

/**
 * Icons build out from the centre once when the section enters the viewport.
 * They stay in their final position; nothing ever animates back in.
 */
function initOrbitAnimation() {
  const orbits = document.querySelectorAll('.js-tcic-orbit');
  if (!orbits.length) return;

  const animationsEnabled = document.body.classList.contains('animations-enabled');
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!animationsEnabled || prefersReducedMotion || !('IntersectionObserver' in window)) {
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    });
  }, { threshold: 0.3 });

  orbits.forEach((orbit) => {
    orbit.classList.add('is-animate-ready');
    // Commit the collapsed state before observing so the transition runs
    void orbit.offsetWidth;
    observer.observe(orbit);
  });
}

export default function initTwoColumnsImageIconsWithCtaSection() {
  initMatchHeight();
  initOrbitAnimation();
}
