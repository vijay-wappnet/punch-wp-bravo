/* ==========================================
   HEADER + FULLSCREEN MENU BEHAVIOUR
========================================== */

import initDotGrid from './dot-grid.js';

const header = document.getElementById('site-header');
const menuToggle = document.querySelector('.menu-toggle');
const fullscreenMenu = document.getElementById('fullscreen-menu');
const closeBtn = fullscreenMenu?.querySelector('.close-btn');

// Elements made inert while the menu is open (everything outside it)
let inertElements = [];

const FOCUSABLE = 'a[href]:not([tabindex="-1"]), button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

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

// The menu's links/buttons that can actually take focus right now (e.g. the
// mobile-only "Get Started" bar is display:none on desktop and is skipped)
function visibleFocusables() {
  return [...fullscreenMenu.querySelectorAll(FOCUSABLE)]
    .filter((el) => el.getClientRects().length > 0);
}

// Tab / Shift+Tab cycle inside the menu: from the last link back to Close,
// and from Close back to the last link
function trapFocus(e) {
  if (e.key !== 'Tab') return;

  const items = visibleFocusables();
  if (!items.length) return;

  const first = items[0];
  const last = items[items.length - 1];
  const inside = fullscreenMenu.contains(document.activeElement);

  if (e.shiftKey) {
    if (!inside || document.activeElement === first) {
      e.preventDefault();
      last.focus();
    }
  } else if (!inside || document.activeElement === last) {
    e.preventDefault();
    first.focus();
  }
}

// Everything outside the menu: the header bar (menu button, logo, Login,
// Get Started), the page content, footer, etc. `inert` takes it out of the
// Tab order and away from screen readers and the mouse. Walking up from the
// menu covers every branch of the page except the one the menu is in.
function setOutsideInert(on) {
  if (!on) {
    inertElements.forEach((el) => el.removeAttribute('inert'));
    inertElements = [];
    return;
  }

  for (let node = fullscreenMenu; node && node !== document.body; node = node.parentElement) {
    [...(node.parentElement?.children || [])].forEach((sibling) => {
      if (sibling !== node && !sibling.hasAttribute('inert') && !['SCRIPT', 'STYLE', 'TEMPLATE'].includes(sibling.tagName)) {
        sibling.setAttribute('inert', '');
        inertElements.push(sibling);
      }
    });
  }
}

// The menu fades in (visibility: hidden -> visible), and the browser won't
// focus anything inside it until it is visible - so keep trying briefly
function focusWhenVisible(el, tries = 20) {
  if (!el) return;
  el.focus();
  if (document.activeElement !== el && tries > 0 && fullscreenMenu.classList.contains('is-open')) {
    setTimeout(() => focusWhenVisible(el, tries - 1), 25);
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
  closeBtn?.setAttribute('aria-expanded', 'true');
  lockBodyScroll();

  // Only the menu can be reached while it is open
  setOutsideInert(true);
  document.addEventListener('keydown', trapFocus);

  focusWhenVisible(closeBtn || visibleFocusables()[0]);
}

function closeMenu() {
  if (!fullscreenMenu) return;

  fullscreenMenu.classList.remove('is-open');
  fullscreenMenu.setAttribute('aria-hidden', 'true');
  menuToggle.setAttribute('aria-expanded', 'false');
  closeBtn?.setAttribute('aria-expanded', 'false');
  unlockBodyScroll();

  document.removeEventListener('keydown', trapFocus);

  // Page is reachable again; focus goes back to the button that opened the menu
  setOutsideInert(false);
  menuToggle.focus();
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
