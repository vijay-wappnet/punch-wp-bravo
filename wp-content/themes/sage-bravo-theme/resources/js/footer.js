/* ==========================================
   FOOTER — MOBILE "SITE MAP" ACCORDION
========================================== */

document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('.footer-nav-toggle');
  const nav = document.getElementById('footer-nav-menu');

  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const isOpen = toggle.getAttribute('aria-expanded') === 'true';

      toggle.setAttribute('aria-expanded', String(!isOpen));
      toggle.classList.toggle('is-open', !isOpen);
      nav.classList.toggle('is-open', !isOpen);
    });
  }

  /* ==========================================
     NEWSLETTER FORM — "Subscribe →" decoration
     Wraps the Contact Form 7 submit button so CSS can
     add the arrow, without editing the CF7 field markup.
  ========================================== */
  document.querySelectorAll('.footer-newsletter-form input[type="submit"]').forEach((submit) => {
    if (submit.closest('.footer-newsletter-submit')) return;

    const wrapper = document.createElement('span');
    wrapper.className = 'footer-newsletter-submit';
    submit.parentNode.insertBefore(wrapper, submit);
    wrapper.appendChild(submit);
  });
});
