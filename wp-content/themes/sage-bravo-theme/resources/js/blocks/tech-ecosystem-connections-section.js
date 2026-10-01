import { gsap } from 'gsap';

/**
 * Builds the ecosystem once, when it scrolls into view:
 *   1. the centre logo card fades/scales in
 *   2. the items travel out from the centre to their places: top, right, bottom, left
 *   3. the dashed line is drawn round the ecosystem
 *   4. the solid line is drawn from the top item to the bottom item
 * It plays once and everything stays in its final place. Without JS, with
 * reduced motion, or with animations turned off in the site options, the
 * finished drawing is simply shown.
 *
 * @param {ParentNode} root Where to look for sections (the document, or a block preview in the editor)
 */
export default function initTechEcosystemConnectionsSection(root = document) {
  const sections = root.querySelectorAll('.js-tec');
  if (!sections.length) return;

  const animationsEnabled = document.body.classList.contains('animations-enabled');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!animationsEnabled || reducedMotion) return;

  sections.forEach((section) => {
    if (section.dataset.tecInitialised) return;

    const visual = section.querySelector('.js-tec-visual');
    if (!visual) return;
    section.dataset.tecInitialised = 'true';

    const card = section.querySelector('.js-tec-card');
    // Already in clockwise order (top, right, bottom, left) from the template
    const items = Array.from(section.querySelectorAll('.js-tec-item'));
    const dashed = section.querySelectorAll('.js-tec-dashed-reveal');
    const solid = section.querySelectorAll('.js-tec-solid');

    // Start state: nothing but the (empty) drawing area
    if (card) gsap.set(card, { opacity: 0, scale: 0.95 });
    gsap.set(items, { opacity: 0 });
    // The paths have pathLength=1, so 1 = not drawn yet
    gsap.set([...dashed, ...solid], { strokeDasharray: 1, strokeDashoffset: 1 });

    const play = () => {
      // Slightly shorter and shorter-travelling on mobile
      const compact = window.matchMedia('(max-width: 767px)').matches;
      const speed = compact ? 0.85 : 1;

      // Where each item starts: the middle of the card, whatever the screen size
      const origin = (card || visual).getBoundingClientRect();
      const originX = origin.left + origin.width / 2;
      const originY = origin.top + origin.height / 2;

      const timeline = gsap.timeline();

      if (card) {
        timeline.to(card, { opacity: 1, scale: 1, duration: 0.8 * speed, ease: 'power2.out' });
        timeline.addLabel('items', '>+0.3'); // a short pause after the card appears
      } else {
        timeline.addLabel('items');
      }

      items.forEach((item, index) => {
        const rect = item.getBoundingClientRect();
        const x = originX - (rect.left + rect.width / 2);
        const y = originY - (rect.top + rect.height / 2);

        timeline.fromTo(
          item,
          { x, y, opacity: 0 },
          { x: 0, y: 0, opacity: 1, duration: 0.9 * speed, ease: 'power3.out' },
          `items+=${index * 0.35 * speed}`
        );
      });

      // Only once every item has arrived
      if (dashed.length) {
        timeline.addLabel('dashed', '>+0.2');
        timeline.to(dashed, { strokeDashoffset: 0, duration: 1.6 * speed, ease: 'power1.inOut' }, 'dashed');
      }

      // ... and only once the dashed line is complete
      if (solid.length) {
        timeline.to(solid, { strokeDashoffset: 0, duration: 1.2 * speed, ease: 'power1.inOut' }, '>+0.1');
      }
    };

    // Plays once, when a good part of the drawing is on screen
    const observer = new IntersectionObserver((entries) => {
      if (!entries.some((entry) => entry.isIntersecting)) return;

      observer.disconnect();
      section.classList.add('is-visible');
      play();
    }, { threshold: 0.35 });

    observer.observe(visual);
  });
}
