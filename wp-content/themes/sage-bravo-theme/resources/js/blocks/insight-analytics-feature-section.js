/**
 * The two overlay cards fade in when the section reaches the viewport and then
 * keep drifting a few px side to side for as long as the section is on screen
 * (the top card left <-> right, the bottom card right <-> left; the movement
 * itself is CSS, in the block's stylesheet). The movement is paused while the
 * section is off screen.
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

    // Start state (CSS): cards hidden until the section is reached
    section.classList.add('is-animatable');

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        // Fade in once; run the drift only while the section is on screen
        if (entry.isIntersecting) section.classList.add('is-visible');
        section.classList.toggle('is-in-view', entry.isIntersecting);
      });
    }, { threshold: 0.25 });

    observer.observe(section);
  });
}
