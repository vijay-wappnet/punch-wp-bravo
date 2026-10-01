import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

/**
 * Builds the workflow once, left to right, when it scrolls into view:
 * line 1 draws from the shared start point -> icon 1 moves in -> line 2 -> icon 2 -> ...
 * Works for any number of steps. It plays once and everything stays in its
 * final place. Without JS, with reduced motion, or with animations turned off
 * in the site options, the finished drawing is simply shown.
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

    const groups = section.querySelectorAll('.js-pws-line-group');
    if (!groups.length) return;
    section.dataset.pwsInitialised = 'true';

    const icons = section.querySelectorAll('.js-pws-icon');
    const labels = section.querySelectorAll('.js-pws-label');

    // The step's own icon and label, by the position in their class (…--1, …--2)
    const byStep = (list, prefix, step) => Array.from(list).find((el) => el.classList.contains(`${prefix}--${step}`));

    // Start state: lines undrawn (pathLength is 1), icons just to the left
    groups.forEach((group) => {
      gsap.set(group.querySelectorAll('.js-pws-line'), { strokeDasharray: 1, strokeDashoffset: 1 });
    });
    // The icon's own box is centred with the CSS `translate` property, which GSAP
    // would mangle, so the image inside is what moves
    const iconImages = Array.from(icons).map((icon) => icon.querySelector('img') || icon);
    gsap.set(iconImages, { opacity: 0, x: -24, scale: 0.85 });
    gsap.set(labels, { opacity: 0 });

    const timeline = gsap.timeline({ paused: true, delay: 0.2 });

    groups.forEach((group, index) => {
      const step = index + 1;
      const iconBox = byStep(icons, 'pws__step-icon', step);
      const icon = iconBox ? (iconBox.querySelector('img') || iconBox) : null;
      const label = byStep(labels, 'pws__step-label', step);

      // Each line is a little longer than the one before, so give it a little longer
      timeline.to(group.querySelectorAll('.js-pws-line'), {
        strokeDashoffset: 0,
        duration: 0.9 + index * 0.15,
        ease: 'power1.inOut',
      });

      // The label and the icon arrive together, once the line is fully drawn
      if (icon) {
        timeline.to(icon, {
          opacity: 1,
          x: 0,
          scale: 1,
          duration: 0.6,
          ease: 'power2.out',
        });
      }

      if (label) {
        timeline.to(label, { opacity: 1, duration: 0.5, ease: 'power1.out' }, icon ? '<' : '>');
      }
    });

    ScrollTrigger.create({
      trigger: section.querySelector('.pws__workflow-visual') || section,
      start: 'top 75%',
      once: true,
      onEnter: () => timeline.play(),
    });
  });
}
