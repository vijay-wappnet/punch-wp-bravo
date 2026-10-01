import jQuery from '../jquery-compat.js';
import '@accessible360/accessible-slick';

/**
 * One item at a time, with one pagination dot per item (no arrows, no autoplay).
 * Slick builds the dots from the number of slides, so there is no ACF field
 * for them; adding or removing a showcase item adds or removes a dot.
 *
 * @param {ParentNode} root Where to look for sliders (the document, or a block preview in the editor)
 */
export default function initProductShowcaseSliderSection(root = document) {
  const sliders = root.querySelectorAll('.js-pss-slider');
  if (!sliders.length) return;

  sliders.forEach((slider) => {
    const $slider = jQuery(slider);

    if (slider.classList.contains('slick-initialized') || $slider.children().length < 2) {
      return;
    }

    const section = slider.closest('.product-showcase-slider-section');
    const dots = section?.querySelector('.js-pss-dots');

    try {
      $slider.slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        infinite: true,
        adaptiveHeight: true,
        arrows: false,
        dots: true,
        autoplay: false,
        swipe: true,
        draggable: true,
        regionLabel: 'product showcase',
        appendDots: dots ? jQuery(dots) : $slider,
        dotsClass: 'product-showcase-slider-section__dots-list',
        customPaging: (_slick, index) => `<button type="button" class="product-showcase-slider-section__dot"><span class="visually-hidden">Go to slide ${index + 1}</span></button>`,
      });
    } catch (error) {
      console.error('Error initializing product showcase slider:', error);
    }
  });
}
