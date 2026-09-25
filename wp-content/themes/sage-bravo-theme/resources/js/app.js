import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

//import leftArrow from '../images/add_image_name.svg';

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

/* ==========================================
   Header & Footer SCRIPTS
========================================== */
import './header.js'; // Header JS
import './footer.js'; // Footer JS

/* ==========================================
   BLOCK SCRIPTS
========================================== */
import './gsap-animations.js';
import initTwoColumnsImageIconsWithCtaSection from './blocks/two-columns-image-icons-with-cta-section.js';

document.addEventListener('DOMContentLoaded', initTwoColumnsImageIconsWithCtaSection);
