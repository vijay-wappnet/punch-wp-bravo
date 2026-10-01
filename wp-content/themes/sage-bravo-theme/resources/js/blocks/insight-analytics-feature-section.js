/**
 * The two overlay cards settle a few px into place (opacity + transform, set
 * in the block's CSS) when the section scrolls into view. The left card comes
 * from a little toward the image, the right card likewise from the other
 * side. It plays once and the final state stays.
 *
 * Without JS, with reduced motion, or with animations turned off in the site
 * options, no start state is applied and the cards are simply in place.
 *
 * @param {ParentNode} root Where to look for sections (the document, or a block preview in the editor)
 */
export default function initInsightAnalyticsFeatureSection(root = document) {
  const sections = root.querySelectorAll('.js-iafs');
  if (!sections.length) return;

  const animationsEnabled = document.body.classList.contains('animations-enabled');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!animationsEnabled || reducedMotion) return;

  sections.forEach((section) => {
    if (section.dataset.iafsInitialised) return;
    if (!section.querySelector('.js-iafs-overlay')) return;
    section.dataset.iafsInitialised = 'true';

    // Start state (CSS), then reveal once a good part of the image is on screen
    section.classList.add('is-animatable');

    const target = section.querySelector('.iafs__media') || section;

    const observer = new IntersectionObserver((entries) => {
      if (!entries.some((entry) => entry.isIntersecting)) return;

      observer.disconnect();
      section.classList.add('is-visible');
    }, { threshold: 0.35 });

    observer.observe(target);
  });
}
