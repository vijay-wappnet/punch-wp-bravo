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
  const baseCellSize = 33.36;
  const baseRadiusRatio = 0.12;
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
        let opacity = 0.28;

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
        ctx.fillStyle = `rgba(255, 255, 255, ${opacity})`;
        ctx.arc(px, py, radius, 0, Math.PI * 2);
        ctx.fill();
      }
    }
  }

  function resize() {
    const rect = container.getBoundingClientRect();
    width = rect.width;
    height = rect.height;
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
