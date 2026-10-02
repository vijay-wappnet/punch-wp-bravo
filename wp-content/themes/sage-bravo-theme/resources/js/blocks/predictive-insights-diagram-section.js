import { gsap } from 'gsap';

/**
 * Builds the diagram from left to right when the section scrolls into view.
 * The order is the repeater order (no animation field):
 *   image -> icon 1 -> line 1 -> icon 2 -> line 2 -> icon 3 -> line 3 -> icon 4 -> line 4 -> icon 5
 * Each line is drawn on (stroke-dashoffset through its mask); each icon fades
 * and scales in a little, nudged in from the left. It plays once and
 * everything stays in its final place. Without JS, with reduced motion, or
 * with animations turned off in the site options, the finished diagram is
 * simply shown.
 *
 * @param {ParentNode} root Where to look for sections (the document, or a block preview in the editor)
 */
export default function initPredictiveInsightsDiagramSection(root = document) {
  const sections = root.querySelectorAll('.js-pid');
  if (!sections.length) return;

  const animationsEnabled = document.body.classList.contains('animations-enabled');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!animationsEnabled || reducedMotion) return;

  const byIndex = (a, b) => Number(a.dataset.index) - Number(b.dataset.index);

  sections.forEach((section) => {
    if (section.dataset.pidInitialised) return;

    const media = section.querySelector('.js-pid-media');
    if (!media) return;
    section.dataset.pidInitialised = 'true';

    const image = media.querySelector('.js-pid-image');
    const items = Array.from(media.querySelectorAll('.js-pid-item')).sort(byIndex);
    const lines = Array.from(media.querySelectorAll('.js-pid-line')).sort(byIndex);

    // Only the image inside each icon box moves: the box itself is centred
    // with the CSS `translate` property, which GSAP would mangle
    const itemImages = items.map((item) => item.querySelector('img') || item);

    // Start state. The reveal paths have pathLength=1, so 1 = not drawn yet.
    if (image) gsap.set(image, { opacity: 0 });
    gsap.set(itemImages, { opacity: 0, x: -10, scale: 0.9 });
    gsap.set(lines, { strokeDasharray: 1, strokeDashoffset: 1 });

    const play = () => {
      // Smaller nudges on mobile
      const compact = window.matchMedia('(max-width: 767px)').matches;
      const nudge = compact ? -6 : -10;

      const timeline = gsap.timeline();

      if (image) {
        timeline.to(image, { opacity: 1, duration: 0.7, ease: 'power1.out' });
      }

      itemImages.forEach((icon, index) => {
        // Icon n ...
        timeline.fromTo(
          icon,
          { opacity: 0, x: nudge, scale: 0.9 },
          { opacity: 1, x: 0, scale: 1, duration: 0.55, ease: 'power2.out' },
          index === 0 && image ? '>-0.2' : '>'
        );

        // ... then the line from it to the next one
        const line = lines[index];
        if (line) {
          timeline.to(line, { strokeDashoffset: 0, duration: 0.6, ease: 'power1.inOut' }, '>-0.1');
        }
      });
    };

    // Plays once, when a good part of the image is on screen
    const observer = new IntersectionObserver((entries) => {
      if (!entries.some((entry) => entry.isIntersecting)) return;

      observer.disconnect();
      section.classList.add('is-visible');
      play();
    }, { threshold: 0.35 });

    observer.observe(media);
  });
}
