import { gsap } from 'gsap';

/**
 * Builds the diagram from left to right when the section scrolls into view,
 * starting with the first element (Data) and ending with the last (Action).
 * The order is the repeater order (no animation field):
 *   icon 1 -> line 1 -> icon 2 -> line 2 -> ... -> the last icon
 * Each line is drawn on (stroke-dashoffset through its mask); each icon fades
 * and scales in a little, nudged in from the left. It plays once and
 * everything stays in its final place. Without JS, with reduced motion, or
 * with animations turned off in the site options, the finished diagram is
 * simply shown.
 *
 * @param {ParentNode} root Where to look for sections (the document, or a block preview in the editor)
 */
export default function initDataProcessingWorkflowSection(root = document) {
  const sections = root.querySelectorAll('.js-dpw');
  if (!sections.length) return;

  const animationsEnabled = document.body.classList.contains('animations-enabled');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!animationsEnabled || reducedMotion) return;

  const byIndex = (a, b) => Number(a.dataset.index) - Number(b.dataset.index);

  sections.forEach((section) => {
    if (section.dataset.dpwInitialised) return;

    const diagram = section.querySelector('.js-dpw-diagram');
    if (!diagram) return;
    section.dataset.dpwInitialised = 'true';

    const items = Array.from(diagram.querySelectorAll('.js-dpw-item')).sort(byIndex);
    const lines = Array.from(diagram.querySelectorAll('.js-dpw-line')).sort(byIndex);

    // Only the image inside each icon box moves: the box itself is centred
    // with the CSS `translate` property, which GSAP would mangle
    const itemImages = items.map((item) => item.querySelector('img') || item);

    // Start state. The reveal paths have pathLength=1, so 1 = not drawn yet.
    gsap.set(itemImages, { opacity: 0, x: -12, scale: 0.9 });
    gsap.set(lines, { strokeDasharray: 1, strokeDashoffset: 1 });

    const play = () => {
      // Smaller nudges on mobile
      const compact = window.matchMedia('(max-width: 767px)').matches;
      const nudge = compact ? -6 : -12;

      const timeline = gsap.timeline();

      itemImages.forEach((icon, index) => {
        // Icon n ...
        timeline.fromTo(
          icon,
          { opacity: 0, x: nudge, scale: 0.9 },
          { opacity: 1, x: 0, scale: 1, duration: 0.55, ease: 'power2.out' },
          '>'
        );

        // ... then the line from it to the next one
        const line = lines[index];
        if (line) {
          timeline.to(line, { strokeDashoffset: 0, duration: 0.6, ease: 'power1.inOut' }, '>-0.1');
        }
      });
    };

    // Plays once, when a good part of the diagram is on screen
    const observer = new IntersectionObserver((entries) => {
      if (!entries.some((entry) => entry.isIntersecting)) return;

      observer.disconnect();
      section.classList.add('is-visible');
      play();
    }, { threshold: 0.35 });

    observer.observe(diagram);
  });
}
