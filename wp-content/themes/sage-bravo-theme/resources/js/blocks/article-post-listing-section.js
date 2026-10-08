/**
 * Shows the article cards one page at a time. The page size is read from the
 * block (data-per-page on desktop, data-per-page-mobile at 767px and below)
 * and re-applied when the viewport crosses that breakpoint.
 *
 * @param {ParentNode} root Where to look for sections (the document, or a block preview in the editor)
 */
export default function initArticlePostListingSection(root = document) {
  root.querySelectorAll('.js-apls').forEach((section) => {
    if (section.dataset.aplsInit) return;

    const items = [...section.querySelectorAll('.js-apls-item')];
    const pager = section.querySelector('.js-apls-pager');
    const prev = section.querySelector('.js-apls-prev');
    const next = section.querySelector('.js-apls-next');
    const current = section.querySelector('.js-apls-current');
    const total = section.querySelector('.js-apls-total');

    if (!pager || !prev || !next || !items.length) return;
    section.dataset.aplsInit = 'true';

    const mobileQuery = window.matchMedia('(max-width: 767px)');
    let page = 1;

    const perPage = () => {
      const size = parseInt(mobileQuery.matches ? section.dataset.perPageMobile : section.dataset.perPage, 10);
      return size > 0 ? size : 4;
    };

    const render = () => {
      const size = perPage();
      const pages = Math.max(1, Math.ceil(items.length / size));
      page = Math.min(Math.max(page, 1), pages);

      items.forEach((item, index) => {
        item.hidden = Math.floor(index / size) + 1 !== page;
      });

      current.textContent = page;
      total.textContent = pages;
      prev.disabled = page <= 1;
      next.disabled = page >= pages;
      pager.hidden = pages < 2;
    };

    const go = (direction) => {
      page += direction;
      render();

      // Bring the top of the list back into view when the page changes
      const top = section.getBoundingClientRect().top;
      if (top < 0) {
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        section.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
      }
    };

    prev.addEventListener('click', () => go(-1));
    next.addEventListener('click', () => go(1));
    mobileQuery.addEventListener('change', render);

    render();
  });
}
