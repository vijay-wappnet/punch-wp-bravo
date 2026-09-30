/* ==========================================
   HEADER + FULLSCREEN MENU BEHAVIOUR
========================================== */

import initDotGrid from './dot-grid.js';

const header = document.getElementById('site-header');
const menuToggle = document.querySelector('.menu-toggle');
const fullscreenMenu = document.getElementById('fullscreen-menu');
const closeBtn = fullscreenMenu?.querySelector('.close-btn');

let focusableElements = [];
let firstFocusable = null;
let lastFocusable = null;

/* ==========================================
   HEADER SCROLL EFFECT
   Fixed header switches from the transparent/dark
   home-hero style to the light "scrolled" style.
   Inner pages already render in the light style.
========================================== */

function updateScrollState() {
  if (!header) return;

  if (window.scrollY > 80) {
    header.classList.add('header-scrolled');
  } else {
    header.classList.remove('header-scrolled');
  }
}

/* ==========================================
   FOCUS TRAP (ACCESSIBILITY)
========================================== */

function trapFocus(e) {
  if (e.key !== 'Tab') return;
  if (!firstFocusable || !lastFocusable) return;

  if (e.shiftKey) {
    if (document.activeElement === firstFocusable) {
      e.preventDefault();
      lastFocusable.focus();
    }
  } else if (document.activeElement === lastFocusable) {
    e.preventDefault();
    firstFocusable.focus();
  }
}

function lockBodyScroll() {
  document.body.classList.add('no-scroll');
}

function unlockBodyScroll() {
  document.body.classList.remove('no-scroll');
}

/* ==========================================
   OPEN / CLOSE MENU
========================================== */

function openMenu() {
  if (!fullscreenMenu) return;

  fullscreenMenu.classList.add('is-open');
  fullscreenMenu.setAttribute('aria-hidden', 'false');
  menuToggle.setAttribute('aria-expanded', 'true');
  lockBodyScroll();

  focusableElements = fullscreenMenu.querySelectorAll(
    'a:not([tabindex="-1"]), button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'
  );

  firstFocusable = closeBtn || focusableElements[0];
  lastFocusable = focusableElements[focusableElements.length - 1];

  firstFocusable?.focus();

  document.querySelectorAll('body > *:not(#fullscreen-menu)').forEach((el) => {
    el.setAttribute('aria-hidden', 'true');
  });

  document.addEventListener('keydown', trapFocus);
}

function closeMenu() {
  if (!fullscreenMenu) return;

  fullscreenMenu.classList.remove('is-open');
  fullscreenMenu.setAttribute('aria-hidden', 'true');
  menuToggle.setAttribute('aria-expanded', 'false');
  unlockBodyScroll();

  document.removeEventListener('keydown', trapFocus);
  menuToggle.focus();

  document.querySelectorAll('body > *:not(#fullscreen-menu)').forEach((el) => {
    el.removeAttribute('aria-hidden');
  });
}

function toggleMenu() {
  if (!fullscreenMenu) return;

  if (fullscreenMenu.classList.contains('is-open')) {
    closeMenu();
  } else {
    openMenu();
  }
}

function handleEsc(e) {
  if (e.key === 'Escape' && fullscreenMenu?.classList.contains('is-open')) {
    closeMenu();
  }
}

/* ==========================================
   INIT
========================================== */

document.addEventListener('DOMContentLoaded', () => {
  updateScrollState();
  window.addEventListener('scroll', updateScrollState);

  if (menuToggle) {
    menuToggle.addEventListener('click', toggleMenu);
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', closeMenu);
  }

  document.addEventListener('keydown', handleEsc);

  document.querySelectorAll('.js-dot-grid').forEach(initDotGrid);
});
