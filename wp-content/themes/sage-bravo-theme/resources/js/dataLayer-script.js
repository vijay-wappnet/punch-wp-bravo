
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

    console.log('dataLayer push:', formattedStatus); // debug
  });
});

/*
  Search dataLayer Event (Desktop + Mobile)
*/
document.querySelectorAll('.search-results-form').forEach(form => {

  const input = form.querySelector('input[name="search-field"]');

  // Helper → detect device
  const getEventData = (keyword) => {
    const isMobile = window.matchMedia('(max-width: 768px)').matches;

    return isMobile
      ? {
          event: 'mobileSearch',
          ms_status: keyword
        }
      : {
          event: 'desktopSearch',
          ds_status: keyword
        };
  };

  // SUBMIT (Enter key)
  form.addEventListener('submit', function () {
    const keyword = input?.value.trim();
    if (!keyword) return;

    const eventData = getEventData(keyword);

    // Save in sessionStorage
    sessionStorage.setItem('searchKeyword', keyword);
    sessionStorage.setItem('searchType', eventData.event);

    // Push immediately
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(eventData);

    console.log('Search pushed (before redirect):', eventData);
  });

  // ICON CLICK
  const icon = form.closest('.input-group')?.querySelector('.input-group-text');

  icon?.addEventListener('click', function () {
    const keyword = input?.value.trim();
    if (!keyword) return;

    const eventData = getEventData(keyword);

    sessionStorage.setItem('searchKeyword', keyword);
    sessionStorage.setItem('searchType', eventData.event);

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(eventData);

    console.log('Search (icon):', eventData);

    form.requestSubmit();
  });

});


/*
  Step 2: Push again after page reload
*/
document.addEventListener('DOMContentLoaded', function () {

  const keyword = sessionStorage.getItem('searchKeyword');
  const eventType = sessionStorage.getItem('searchType');

  if (keyword && eventType) {

    const eventData =
      eventType === 'mobileSearch'
        ? { event: 'mobileSearch', ms_status: keyword }
        : { event: 'desktopSearch', ds_status: keyword };

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(eventData);

    console.log('Search pushed (after reload):', eventData);

    // Clear after use
    sessionStorage.removeItem('searchKeyword');
    sessionStorage.removeItem('searchType');
  }

});
