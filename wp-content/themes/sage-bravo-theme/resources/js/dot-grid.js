/* ==========================================
   DOT GRID — procedural "inflation" hover effect
   Dots grow the closer the cursor is to them and
   ease back to their resting size when it moves away.
   Reference: https://bravo.works
========================================== */

export default function initDotGrid(container) {
  const canvas = container.querySelector('.dot-grid-canvas');
  const ctx = canvas?.getContext('2d');
  if (!container || !canvas || !ctx) return;

  // Ported 1:1 from bravo.works' own hero-dots implementation (src/js/main.js)
  // so the feel matches exactly: the cursor position itself lags the real
  // pointer (cursor lerp), a separate "strength" envelope ramps up fast on
  // fresh movement but decays slowly once idle, and the falloff is a curve
  // (not linear) so only dots close to the cursor really swell.
  // Opt-in per section: data-dot-style="varied" gives slightly larger dots
  // and a resting grid with a scatter of brighter dots (CTA Banner Section).
  // Without it every dot rests at the same size and opacity (fullscreen
  // menu, video banner).
  const varied = container.dataset.dotStyle === 'varied';
  // data-dot-color="dark" draws the dots in the dark text colour for light
  // section backgrounds (Product Showcase Slider); the default is white.
  const dotRgb = container.dataset.dotColor === 'dark' ? '46, 46, 46' : '255, 255, 255';

  const baseCellSize = 33.36;
  const baseRadiusRatio = varied ? 0.15 : 0.12;
  const maxRadiusRatio = 0.45;
  const influenceCells = 4;
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  let cellSize = 0;
  let columns = 0;
  let rows = 0;
  let width = 0;
  let height = 0;
  let dpr = 1;
  let target = null;
  let cursor = null;
  let strength = 0;
  let lastMoveAt = 0;
  let frame = null;
  let idleDrawn = false;
  let edgeOpacityOverride = null;
  let fadeOverride = null;

  // Same breakpoint as the block's mobile layout ($screen-lg-min)
  const mobileQuery = window.matchMedia('(max-width: 992px)');

  // Stable pseudo-random number (0-1) from two integers, so the bright dots
  // keep their place across redraws and resizes instead of flickering.
  function hash(a, b) {
    const n = Math.sin(a * 12.9898 + b * 78.233) * 43758.5453;
    return n - Math.floor(n);
  }

  // Resting opacity for the "varied" style, measured from the design:
  // - The grid fades out linearly towards the centre so the dots disappear
  //   behind the content: horizontally on desktop (edges ~0.25), vertically
  //   on mobile (top/bottom ~0.3; the rows sit slightly in from the edge).
  // - ~5% of dots are ~2.4x brighter, in short vertical runs of 4 dots down
  //   a column (each column's runs start at a different row).
  function restingOpacity(column, row, x, y) {
    const mobile = mobileQuery.matches;
    // A section can override these from CSS (--dot-grid-edge-opacity and
    // --dot-grid-fade: "horizontal" | "top"), e.g. the Product Showcase Slider.
    const edgeOpacity = edgeOpacityOverride ?? (mobile ? 0.32 : 0.25);
    let fade;
    if (fadeOverride === 'top') {
      // Strongest at the top, fading to nothing at the bottom of the grid
      fade = 1 - y / height;
    } else if (fadeOverride === 'horizontal') {
      fade = Math.abs(x - width / 2) / (width / 2);
    } else {
      fade = mobile
        ? Math.abs(y - height / 2) / (height / 2)
        : Math.abs(x - width / 2) / (width / 2);
    }
    fade = Math.max(fade, 0);

    const runLength = 4;
    const seed = column + 17;
    const run = Math.floor((row + Math.floor(hash(seed, 0.5) * runLength)) / runLength);
    const brightness = hash(seed, run + 31) < 0.05 ? 2.4 : 1;

    return edgeOpacity * brightness * Math.min(fade, 1);
  }

  function draw() {
    // clearRect runs under the dpr scale transform below, so it must be
    // given CSS-space extents (width/height) - not canvas.width/height,
    // which are already device pixels and would get scaled by dpr a
    // second time. At dpr < 1 that under-clears the canvas every frame,
    // leaving a never-wiped strip where old dots keep compositing on top
    // of themselves frame after frame (the "doubled/ghosted dots" bug).
    ctx.clearRect(0, 0, width, height);

    for (let row = 0; row < rows; row += 1) {
      for (let column = 0; column < columns; column += 1) {
        const x = column * cellSize + 10;
        const y = (row + 0.5) * cellSize;
        let radius = cellSize * baseRadiusRatio;
        let opacity = varied ? restingOpacity(column, row, x, y) : 0.28;

        if (cursor && strength > 0.001) {
          const distance = Math.hypot((x - cursor.x) / cellSize, (y - cursor.y) / cellSize);
          if (distance < influenceCells) {
            const pull = (1 - distance / influenceCells) ** 2.5 * strength;
            radius += cellSize * (maxRadiusRatio - baseRadiusRatio) * pull;
            opacity += 0.35 * pull;
          }
        }

        // At non-integer browser zoom levels devicePixelRatio (and so the
        // dot centers in device-pixel space) lands off-grid, so the canvas
        // anti-aliases each dot's edge slightly differently - some read
        // crisper/brighter than others essentially at random. Snapping the
        // center to a whole device pixel makes every dot antialias the same
        // way regardless of zoom.
        const px = Math.round(x * dpr) / dpr;
        const py = Math.round(y * dpr) / dpr;

        ctx.beginPath();
        ctx.fillStyle = `rgba(${dotRgb}, ${Math.min(opacity, 1)})`;
        ctx.arc(px, py, radius, 0, Math.PI * 2);
        ctx.fill();
      }
    }
  }

  function resize() {
    const rect = container.getBoundingClientRect();
    width = rect.width;
    // Optional per-section CSS variable (0-1): how much of the section's
    // height the grid covers, from the top. Defaults to all of it.
    const coverage = parseFloat(getComputedStyle(container).getPropertyValue('--dot-grid-coverage'));
    height = rect.height * (coverage > 0 && coverage < 1 ? coverage : 1);

    const styles = getComputedStyle(container);
    const edgeOpacity = parseFloat(styles.getPropertyValue('--dot-grid-edge-opacity'));
    edgeOpacityOverride = edgeOpacity > 0 ? edgeOpacity : null;
    fadeOverride = styles.getPropertyValue('--dot-grid-fade').trim() || null;
    cellSize = baseCellSize;
    columns = Math.ceil(width / cellSize) + 1;
    rows = Math.ceil(height / cellSize) + 1;

    dpr = Math.min(window.devicePixelRatio || 1, 2);
    canvas.width = Math.round(width * dpr);
    canvas.height = Math.round(height * dpr);
    canvas.style.width = `${width}px`;
    canvas.style.height = `${height}px`;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    idleDrawn = false;
    draw();
  }

  function animate(now) {
    if (target) {
      if (!cursor) cursor = { ...target };
      else {
        cursor.x += (target.x - cursor.x) * 0.15;
        cursor.y += (target.y - cursor.y) * 0.15;
      }
    }

    const moving = target !== null && now - lastMoveAt < 90;
    strength += ((moving ? 1 : 0) - strength) * (moving ? 0.25 : 0.06);

    if (!moving && strength < 0.001) {
      if (!idleDrawn) {
        strength = 0;
        draw();
        idleDrawn = true;
      }
    } else {
      draw();
      idleDrawn = false;
    }

    frame = requestAnimationFrame(animate);
  }

  container.addEventListener('pointermove', (e) => {
    const rect = container.getBoundingClientRect();
    target = { x: e.clientX - rect.left, y: e.clientY - rect.top };
    lastMoveAt = performance.now();
  });

  container.addEventListener('pointerleave', () => {
    target = null;
  });

  // The container's box can settle after our first measurement (web font
  // swap, layout inside the still-hidden fullscreen menu, etc.), so a
  // one-off resize() on init can freeze the canvas at a too-small size
  // until something happens to trigger a window resize. ResizeObserver
  // re-measures whenever the box itself actually changes, immediately
  // included.
  const resizeObserver = new ResizeObserver(resize);
  resizeObserver.observe(container);

  if (!reduceMotion) {
    frame = requestAnimationFrame(animate);
  }

  return () => {
    cancelAnimationFrame(frame);
    resizeObserver.disconnect();
  };
}
