import domReady from '@wordpress/dom-ready';
import initTestimonialSliderSection from './blocks/testimonial-slider-section.js';
import initFaqsAccordionSection from './blocks/faqs-accordion-section.js';
import initDotGrid from './dot-grid.js';

// Cleanup function of the dot grid currently running in each block preview
const dotGridCleanups = new WeakMap();

domReady(() => {
  // ACF re-renders a block's preview on every field change, so the slider is
  // (re)initialised on each render, scoped to that block's markup.
  if (window.acf?.addAction) {
    window.acf.addAction('render_block_preview/type=testimonial-slider-section', ($block) => {
      if ($block?.[0]) initTestimonialSliderSection($block[0]);
    });

    window.acf.addAction('render_block_preview/type=faqs-accordion-section', ($block) => {
      if ($block?.[0]) initFaqsAccordionSection($block[0]);
    });

    // Same for the dot grid: stop the previous render's animation loop
    // before starting one on the fresh markup.
    window.acf.addAction('render_block_preview/type=cta-banner-section', ($block) => {
      const block = $block?.[0];
      if (!block) return;

      dotGridCleanups.get(block)?.();
      const container = block.querySelector('.js-dot-grid');
      const cleanup = container ? initDotGrid(container) : null;
      if (cleanup) dotGridCleanups.set(block, cleanup);
      else dotGridCleanups.delete(block);
    });
  }
});
