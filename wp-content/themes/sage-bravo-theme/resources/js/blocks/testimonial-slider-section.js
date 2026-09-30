import jQuery from '../jquery-compat.js';
import '@accessible360/accessible-slick';

/**
 * Builds a slick arrow button. Both arrows use the CTA button arrow icon;
 * the "previous" one is flipped in CSS.
 */
function arrowButton(direction, icon, label) {
  return `<button class="slick-${direction} testimonial-slider-section__arrow testimonial-slider-section__arrow--${direction}" type="button">`
    + `<img src="${icon}" alt="" aria-hidden="true" width="22" height="15">`
    + `<span class="visually-hidden">${label}</span>`
    + '</button>';
}

/**
 * One testimonial at a time, prev/next arrows only (no dots, no autoplay).
 * Arrows sit either side of the quote on desktop and below it on mobile (CSS).
 *
 * @param {ParentNode} root Where to look for sliders (the document, or a block preview in the editor)
 */
export default function initTestimonialSliderSection(root = document) {
  const sliders = root.querySelectorAll('.js-tss-slider');
  if (!sliders.length) return;

  sliders.forEach((slider) => {
    const $slider = jQuery(slider);

    if (slider.classList.contains('slick-initialized') || $slider.children().length < 2) {
      return;
    }

    const section = slider.closest('.testimonial-slider-section');
    const icon = section?.dataset.arrow || '';
    const arrows = section?.querySelector('.js-tss-arrows');

    try {
      $slider.slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        infinite: true,
        adaptiveHeight: false,
        arrows: true,
        dots: false,
        autoplay: false,
        swipe: true,
        draggable: true,
        regionLabel: 'testimonials',
        prevArrow: arrowButton('prev', icon, 'Previous testimonial'),
        nextArrow: arrowButton('next', icon, 'Next testimonial'),
        appendArrows: arrows ? jQuery(arrows) : $slider,
      });
    } catch (error) {
      console.error('Error initializing testimonial slider:', error);
    }
  });
}
