import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

/**
 * Plays the workflow once when it scrolls into view, as in the Figma
 * prototype:
 *  - the step labels and the largest (outer) circle are there from the start
 *  - each step's line then draws in turn from the shared start point - round
 *    the bottom of its circle and up to its label (for the last step only its
 *    vertical line is left to draw, as its circle is already shown)
 *  - finally all the icons slide in together from the left into place
 * Works for any number of steps and stays in its final state. Without JS,
 * with reduced motion, or with animations turned off in the site options,
 * the finished drawing is simply shown.
 *
 * @param {ParentNode} root Where to look for sections (the document, or a block preview in the editor)
 */
export default function initProcessWorkflowSection(root = document) {
  const sections = root.querySelectorAll('.js-pws');
  if (!sections.length) return;

  const animationsEnabled = document.body.classList.contains('animations-enabled');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!animationsEnabled || reducedMotion) return;

  sections.forEach((section) => {
    if (section.dataset.pwsInitialised) return;

    const groups = Array.from(section.querySelectorAll('.js-pws-line-group'));
    if (!groups.length) return;
    section.dataset.pwsInitialised = 'true';

    // The icon's own box is centred with the CSS `translate` property, which
    // GSAP would mangle, so the image inside is what moves
    const iconImages = Array.from(section.querySelectorAll('.js-pws-icon')).map((icon) => icon.querySelector('img') || icon);
    const lastIndex = groups.length - 1;

    // Start state. Lines use pathLength="1", so a dashoffset of 1 = undrawn.
    // (autoRound: false - GSAP rounds px values by default, which would turn
    // these 0-1 fractions into 0 or 1.)
    // The last step's circle is shown from the start: its main path (round
    // the bottom, then up) starts drawn up to the end of the arc, so only the
    // vertical line is left to draw; its "cap" (top half) is fully drawn.
    const lineTweens = groups.map((group, index) => {
      const lines = Array.from(group.querySelectorAll('.js-pws-line'));
      const main = lines[0];
      const caps = lines.slice(1);
      let from = 1;

      if (index === lastIndex && main) {
        from = 1 - arcShare(main);
        gsap.set(caps, { strokeDasharray: 1, strokeDashoffset: 0, autoRound: false });
      } else {
        gsap.set(caps, { strokeDasharray: 1, strokeDashoffset: 1, autoRound: false });
      }
      gsap.set(main, { strokeDasharray: 1, strokeDashoffset: from, autoRound: false });

      return { lines: index === lastIndex ? [main] : lines, from };
    });
    // Icons start a little to the left (same distance for all three, in
    // proportion to the diagram so it matches on mobile) and transparent
    const visual = section.querySelector('.pws__workflow-visual') || section;
    const slideFrom = () => -Math.round(visual.offsetWidth * 0.12);
    gsap.set(iconImages, { opacity: 0, x: slideFrom });

    const timeline = gsap.timeline({ paused: true, delay: 0.2 });

    // Lines one after another
    lineTweens.forEach(({ lines }) => {
      timeline.to(lines, { strokeDashoffset: 0, duration: 0.6, ease: 'power1.inOut', autoRound: false });
    });

    // Then the icons, all together: slide in from the left while fading in
    timeline.to(iconImages, { opacity: 1, x: 0, duration: 0.7, ease: 'power2.out' }, '+=0.1');

    ScrollTrigger.create({
      trigger: section.querySelector('.pws__workflow-visual') || section,
      start: 'top 75%',
      once: true,
      onEnter: () => timeline.play(),
    });
  });
}

/**
 * Share (0-1) of a step path taken by its arc: the path is "M0 0 A... L x
 * top", so the last segment is a vertical line from y=0 to `top`.
 */
function arcShare(path) {
  const total = path.getTotalLength();
  const match = /L\s*[-\d.]+[\s,]+([-\d.]+)\s*$/.exec(path.getAttribute('d') || '');
  if (!total || !match) return 0;
  const vertical = Math.abs(parseFloat(match[1]));
  return Math.min(1, Math.max(0, (total - vertical) / total));
}
