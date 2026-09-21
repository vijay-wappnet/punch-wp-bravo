/* ==========================================
   HEADER + FULLSCREEN MENU BEHAVIOUR
========================================== */

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
   DOT GRID — procedural "inflation" hover effect
   Dots grow the closer the cursor is to them and
   ease back to their resting size when it moves away.
   Reference: https://bravo.works
========================================== */

function initDotGrid(container) {
  const canvas = container.querySelector('.dot-grid-canvas');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  const spacing = 26;
  const baseRadius = 2;
  const maxRadius = 6;
  const influenceRadius = 130;
  const easing = 0.18;

  let dots = [];
  let width = 0;
  let height = 0;
  let dpr = window.devicePixelRatio || 1;
  let pointer = { x: -9999, y: -9999, active: false };
  let frame = null;

  function buildDots() {
    dots = [];
    for (let y = spacing / 2; y < height; y += spacing) {
      for (let x = spacing / 2; x < width; x += spacing) {
        dots.push({ x, y, r: baseRadius });
      }
    }
  }

  function resize() {
    const rect = container.getBoundingClientRect();
    width = rect.width;
    height = rect.height;
    dpr = window.devicePixelRatio || 1;

    canvas.width = width * dpr;
    canvas.height = height * dpr;
    canvas.style.width = `${width}px`;
    canvas.style.height = `${height}px`;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    buildDots();
  }

  function draw() {
    ctx.clearRect(0, 0, width, height);

    dots.forEach((dot) => {
      let targetRadius = baseRadius;

      if (pointer.active) {
        const dx = dot.x - pointer.x;
        const dy = dot.y - pointer.y;
        const dist = Math.sqrt(dx * dx + dy * dy);

        if (dist < influenceRadius) {
          const strength = 1 - dist / influenceRadius;
          targetRadius = baseRadius + (maxRadius - baseRadius) * strength;
        }
      }

      dot.r += (targetRadius - dot.r) * easing;

      ctx.beginPath();
      ctx.arc(dot.x, dot.y, dot.r, 0, Math.PI * 2);
      ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
      ctx.fill();
    });

    frame = requestAnimationFrame(draw);
  }

  container.addEventListener('pointermove', (e) => {
    const rect = container.getBoundingClientRect();
    pointer.x = e.clientX - rect.left;
    pointer.y = e.clientY - rect.top;
    pointer.active = true;
  });

  container.addEventListener('pointerleave', () => {
    pointer.active = false;
  });

  window.addEventListener('resize', resize);

  resize();
  frame = requestAnimationFrame(draw);

  return () => {
    cancelAnimationFrame(frame);
    window.removeEventListener('resize', resize);
  };
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
