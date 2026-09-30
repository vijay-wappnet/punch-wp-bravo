import domReady from '@wordpress/dom-ready';
import initTestimonialSliderSection from './blocks/testimonial-slider-section.js';

domReady(() => {
  // ACF re-renders a block's preview on every field change, so the slider is
  // (re)initialised on each render, scoped to that block's markup.
  if (window.acf?.addAction) {
    window.acf.addAction('render_block_preview/type=testimonial-slider-section', ($block) => {
      if ($block?.[0]) initTestimonialSliderSection($block[0]);
    });
  }
});
