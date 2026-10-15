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
    // One entry per element: never list an element together with its own
    // parent/child, and leave out parts a block already animates itself
    // (orbits, diagrams, media reveals, counters - see the notes below).
    const animatedElements = [
      // Intro Section
      '.intro-section__content-wrapper .intro-section__content',
      '.intro-section__content-wrapper .intro-section__buttons',

      // Video Banner Section (video/dot grid stay static)
      '.video-banner-section__box',

      // Two Columns Image Icons with CTA (orbit animates itself)
      '.two-columns-image-icons-with-cta-section__content',

      // Floating Content Card Section
      '.floating-content-card-section__card',

      // Trusted Secure Section
      '.trusted-secure-section__icons',
      '.trusted-secure-section__content',

      // Left Right Media Content (media reveals itself)
      '.left-right-media-content-section__content',

      // Three Columns Card Grid Section
      '.three-columns-card-grid-section__item',

      // Testimonial Slider Section - the container, not __inner: the arrows are
      // positioned against the container, and a transform on __inner would
      // pull them in to the quote column while it animates.
      '.testimonial-slider-section > .container',

      // Product Showcase Slider Section
      '.product-showcase-slider-section__inner',

      // Process Workflow Section (workflow lines animate themselves)
      '.process-workflow-section__content',

      // Tech Ecosystem Connections (visual animates itself)
      '.tec__heading',

      // Insight Analytics Feature Section (image overlay animates itself)
      '.iafs__content',

      // Predictive Insights Diagram Section (diagram animates itself)
      '.pid__content',

      // Pricing Plans / Agency Pricing Plans
      '.pricing-plans-section .pps__card',
      '.agency-pricing-card',

      // Image Banner / Two Image Banner
      '.image-banner-section .image-banner',
      '.two-image-banner-section .image-banner',

      // Two Column Image with Content CTA
      '.tciwccs__media',
      '.tciwccs__content',

      // Brand Logo Section
      '.bls__list',

      // Two Columns Orbit Image Icons with CTA (orbit animates itself)
      '.tcoi__content',

      // Metrics Counter Section (numbers count up themselves)
      '.metrics-counter-wrapper',

      // Listed Item / Compare Listed Item
      '.lis__item',
      '.clis__card',

      // CTA Banner Section
      '.cta-banner-section__content',
      '.cta-banner-section__buttons',

      // FAQs Accordion Section
      '.faqs-accordion-section__item',

      // Latest Article List Section
      '.latest-article-list-section__header',
      '.latest-article-list-section__list',

      // Article Post Listing Section (whole list - items are paged by JS)
      '.article-post-listing-section__list',

      // Meet the Team Section
      '.meet-the-team-section__header',
      '.meet-the-team-section__slider',

      // Search Results Section
      '.search-results-section__title',
      '.search-results-section__form',
      '.search-results-section__results',

      // Contact Form Block Section
      '.contact-form-block-section .cfs',

      // Data Processing Workflow Section: not listed - its diagram already
      // animates itself on scroll.
    ];


    // Optional per-selector overrides (fromVars, toVars, trigger).
    const customAnimations = [];

    const DEFAULT_FROM = { y: 80, opacity: 0 };
    // clearProps: once finished, hand transform/opacity back to the block's own
    // CSS (a leftover inline transform would break hovers and sticky/fixed children).
    const DEFAULT_TO = { y: 0, opacity: 1, duration: 1.2, ease: 'power3.out', clearProps: 'transform,opacity' };
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
