/* ==========================================
   CF7 HIDDEN META FIELDSET (ACCESSIBILITY)
   CF7 prints its hidden meta inputs (_wpcf7, _wpcf7_unit_tag, ...) in a
   <fieldset class="hidden-fields-container"> with no <legend>, which
   accessibility checkers flag. Move them into a fieldset that has a
   (screen-reader-only) legend. Runs on every CF7 form: the footer
   newsletter and the "Contact us" step form.
========================================== */

import jQuery from './jquery-compat.js';

export default function initCf7HiddenMeta() {
  const $ = jQuery;

  $('.wpcf7 form').each(function () {
    const $form = $(this);
    const $hiddenFields = $form.find('input[type="hidden"]');

    if ($hiddenFields.length > 0) {
      // Create the fieldset and legend
      const $fieldset = $('<fieldset class="cf7-hidden-meta"></fieldset>');
      const $legend = $('<legend class="screen-reader-text sr-only">Form Metadata</legend>');

      // Add the legend to the fieldset
      $fieldset.append($legend);

      // Move the hidden fields into the fieldset
      $hiddenFields.each(function () {
        $(this).appendTo($fieldset);
      });

      // Prepend the fieldset to the form
      $form.prepend($fieldset);
      $form.find('.hidden-fields-container').remove();
    }
  });
}
