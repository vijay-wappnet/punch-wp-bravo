/**
 * GTM tracking for the search form: pushes `desktopSearch` (ds_status) or
 * `mobileSearch` (ms_status) with the keyword(s) when the form is submitted.
 * Mobile = 767px and below, the same breakpoint the block's CSS uses.
 *
 * @param {ParentNode} root Where to look for forms (the document, or a block preview in the editor)
 */
export default function initSearchResultsSection(root = document) {
  root.querySelectorAll('.js-srs-form').forEach((form) => {
    if (form.dataset.srsInit) return;
    form.dataset.srsInit = 'true';

    form.addEventListener('submit', () => {
      const input = form.querySelector('input[name="s"]');
      const keywords = input ? input.value.trim() : '';
      if (!keywords) return;

      const isMobile = window.matchMedia('(max-width: 767px)').matches;
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push(
        isMobile
          ? { event: 'mobileSearch', ms_status: keywords }
          : { event: 'desktopSearch', ds_status: keywords }
      );
    });
  });
}
