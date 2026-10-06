/**
 * The icons fan out from behind the middle one, once, when the visual enters
 * the viewport (the movement itself is CSS: `--spread` goes from 0 to 1, see
 * the block's stylesheet). They stay in their final place; nothing ever
 * animates back.
 *
 * Without JS, with reduced motion, or with animations turned off in the site
 * options, no start state is applied and the finished arrangement is shown.
 *
 * @param {ParentNode} root Where to look for visuals (the document, or a block preview in the editor)
 */
export default function initTwoColumnsOrbitImageIconsWithCtaSection(root = document) {
  const visuals = root.querySelectorAll('.js-tcoi-visual');
  if (!visuals.length) return;

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
  }, { threshold: 0.35 });

  visuals.forEach((visual) => {
    if (visual.dataset.tcoiInitialised || !visual.querySelector('.tcoi__icon-item')) return;
    visual.dataset.tcoiInitialised = 'true';

    visual.classList.add('is-animate-ready');
    // Commit the stacked start state before observing so the transition runs
    void visual.offsetWidth;
    observer.observe(visual);
  });
}
