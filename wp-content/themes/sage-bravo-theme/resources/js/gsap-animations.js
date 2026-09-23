import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

// ACF Options field 'enable_animations'
const animationsEnabled = document.body.classList.contains('animations-enabled');
// System setting animation (prefers-reduced-motion).
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!animationsEnabled || prefersReducedMotion) {
  // Animations disabled via ACF Options — exit silently.
} else {
  document.addEventListener('DOMContentLoaded', () => {
    // Add CSS selectors here to animate matching elements on scroll.
    const animatedElements = [
      '.intro-section__content-wrapper .intro-section__content',
      '.intro-section__content-wrapper .intro-section__buttons',
    ];


    // Optional per-selector overrides (fromVars, toVars, trigger).
    const customAnimations = [];

    const DEFAULT_FROM = { y: 80, opacity: 0 };
    const DEFAULT_TO = { y: 0, opacity: 1, duration: 1.2, ease: 'power3.out' };
    const DEFAULT_TRIGGER = { start: 'top 80%', once: true, markers: false };

    function buildTrigger(el, overrides = {}) {
      return { ...DEFAULT_TRIGGER, trigger: el, ...overrides };
    }

    function getCustomConfig(selector) {
      return customAnimations.find((cfg) => cfg.selector === selector) ?? null;
    }

    function applyAnimation(selector) {
      const elements = document.querySelectorAll(selector);
      if (!elements.length) return;

      const custom = getCustomConfig(selector);

      elements.forEach((el) => {
        if (el.dataset.gsapInitialised) return;
        el.dataset.gsapInitialised = 'true';

        const fromVars = custom?.fromVars ?? DEFAULT_FROM;
        const toVars = {
          ...(custom?.toVars ?? DEFAULT_TO),
          scrollTrigger: buildTrigger(el, custom?.trigger ?? {}),
        };

        gsap.fromTo(el, fromVars, toVars);
      });
    }

    function initScrollAnimations() {
      animatedElements.forEach(applyAnimation);
    }

    initScrollAnimations();
  });
}
