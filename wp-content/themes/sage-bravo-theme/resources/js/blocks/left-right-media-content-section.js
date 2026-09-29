/**
 * Media moves a few pixels in from its side once when the section enters the
 * viewport. It stays in its final position; nothing ever animates back out.
 */
export default function initLeftRightMediaContentSection() {
  const media = document.querySelectorAll('.js-lrmcs-media');
  if (!media.length) return;

  const animationsEnabled = document.body.classList.contains('animations-enabled');
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!animationsEnabled || prefersReducedMotion || !('IntersectionObserver' in window)) {
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    });
  }, { threshold: 0.25 });

  media.forEach((item) => {
    item.classList.add('is-animate-ready');
    // Commit the offset state before observing so the transition runs
    void item.offsetWidth;
    observer.observe(item);
  });
}
