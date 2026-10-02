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
import initLeftRightMediaContentSection from './blocks/left-right-media-content-section.js';
import initTestimonialSliderSection from './blocks/testimonial-slider-section.js';
import initProductShowcaseSliderSection from './blocks/product-showcase-slider-section.js';
import initProcessWorkflowSection from './blocks/process-workflow-section.js';
import initTechEcosystemConnectionsSection from './blocks/tech-ecosystem-connections-section.js';
import initInsightAnalyticsFeatureSection from './blocks/insight-analytics-feature-section.js';
import initPredictiveInsightsDiagramSection from './blocks/predictive-insights-diagram-section.js';
import initMetricsCounterSection from './blocks/metrics-counter-section.js';
import initDataProcessingWorkflowSection from './blocks/data-processing-workflow-section.js';
import initFaqsAccordionSection from './blocks/faqs-accordion-section.js';
import initLatestArticleListSection from './blocks/latest-article-list-section.js';

document.addEventListener('DOMContentLoaded', initTwoColumnsImageIconsWithCtaSection);
document.addEventListener('DOMContentLoaded', initLeftRightMediaContentSection);
document.addEventListener('DOMContentLoaded', () => initTestimonialSliderSection());
document.addEventListener('DOMContentLoaded', () => initProductShowcaseSliderSection());
document.addEventListener('DOMContentLoaded', () => initProcessWorkflowSection());
document.addEventListener('DOMContentLoaded', () => initTechEcosystemConnectionsSection());
document.addEventListener('DOMContentLoaded', () => initInsightAnalyticsFeatureSection());
document.addEventListener('DOMContentLoaded', () => initPredictiveInsightsDiagramSection());
document.addEventListener('DOMContentLoaded', () => initMetricsCounterSection());
document.addEventListener('DOMContentLoaded', () => initDataProcessingWorkflowSection());
document.addEventListener('DOMContentLoaded', () => initFaqsAccordionSection());
document.addEventListener('DOMContentLoaded', () => initLatestArticleListSection());
