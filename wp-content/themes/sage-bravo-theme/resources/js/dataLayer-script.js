
/*
    Accessibility Panel Tracking (pushes changes to dataLayer for analytics)
*/
document.querySelectorAll('.btn-setting').forEach(button => {
  button.addEventListener('click', function () {

    const setting = this.getAttribute('data-setting');
    const value = this.getAttribute('data-value');

    // Format like: "Contrast: Higher"
    const formattedStatus =
      setting.charAt(0).toUpperCase() + setting.slice(1) +
      ': ' +
      value.charAt(0).toUpperCase() + value.slice(1);

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      event: 'accessibilityPanel',
      ap_status: formattedStatus
    });
  });
});

/*
  Search dataLayer Event (Desktop + Mobile)

  Every site search form is tracked:
  - the Search Results Section block's form (.search-results-section__form)
  - the accessibility panel's search box (.search-results-form)
  Both submit the keyword as "s" and land on the search results page.

  One event per search: on submit the keyword is saved in sessionStorage and
  pushed once the results page has loaded (a push made just before the page
  navigates away can be lost, and pushing at both moments counted each
  search twice).
*/
const SEARCH_FORMS = '.search-results-section__form, .search-results-form';

// Same breakpoint as the theme's mobile layout ($screen-md-min-767)
const getSearchEvent = (keyword, isMobile) => (
  isMobile
    ? { event: 'mobileSearch', ms_status: keyword }
    : { event: 'desktopSearch', ds_status: keyword }
);

document.querySelectorAll(SEARCH_FORMS).forEach(form => {

  const input = form.querySelector('input[name="s"]');

  // SUBMIT (Enter key or the form's own button)
  form.addEventListener('submit', function () {
    const keyword = input?.value.trim();
    if (!keyword) return;

    const isMobile = window.matchMedia('(max-width: 767px)').matches;

    sessionStorage.setItem('searchKeyword', keyword);
    sessionStorage.setItem('searchType', getSearchEvent(keyword, isMobile).event);
  });

  // ICON CLICK (accessibility panel) - just submits; the submit handler above
  // records the search
  const icon = form.closest('.input-group')?.querySelector('.input-group-text');

  icon?.addEventListener('click', function () {
    if (input?.value.trim()) {
      form.requestSubmit();
    }
  });

});


/*
  Push the search once the results page has loaded
*/
document.addEventListener('DOMContentLoaded', function () {

  const keyword = sessionStorage.getItem('searchKeyword');
  const eventType = sessionStorage.getItem('searchType');

  if (keyword && eventType) {

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(getSearchEvent(keyword, eventType === 'mobileSearch'));

    // Clear after use
    sessionStorage.removeItem('searchKeyword');
    sessionStorage.removeItem('searchType');
  }

});
