<?php

/**
 * Contact Form 7 - "Contact us Form" step form.
 *
 * The step markup lives in the CF7 form template (see cf7-forms/contact-us-form.html),
 * styled by resources/css/components/_contact-step-form.scss and driven by
 * resources/js/contact-step-form.js. Any CF7 form whose template contains the
 * `data-cfs` wrapper is treated as a step form.
 */

use Illuminate\Support\Facades\Vite;

/**
 * Is the given (or current) CF7 form a step form?
 */
function bravo_is_cf7_step_form($contact_form = null)
{
    $contact_form = $contact_form ?: (class_exists('WPCF7_ContactForm') ? WPCF7_ContactForm::get_current() : null);

    return $contact_form && str_contains((string) $contact_form->prop('form'), 'data-cfs');
}

// The template is hand-written HTML, so don't let CF7 wrap it in <p>/<br>.
add_filter('wpcf7_autop_or_not', function ($autop) {
    return bravo_is_cf7_step_form() ? false : $autop;
});

// Button arrows: <span class="cfs-arrow"></span> -> the theme's arrow image, and
// {home} -> the site URL (for links inside the template).
add_filter('wpcf7_form_elements', function ($form) {
    if (!bravo_is_cf7_step_form()) {
        return $form;
    }

    $arrow = '<img src="' . esc_url(Vite::asset('resources/images/btn-left-arrow.svg')) . '" alt="" class="btn-contact-from-7__icon" aria-hidden="true" width="22" height="15">';

    return str_replace(
        ['<span class="cfs-arrow"></span>', '{home}'],
        [$arrow, untrailingslashit(home_url())],
        $form
    );
}, 20);

// "Where are your properties located?*" - tick a location or pick a region.
add_filter('wpcf7_validate_checkbox', function ($result, $tag) {
    if ('location' !== $tag->name || !bravo_is_cf7_step_form()) {
        return $result;
    }

    $location = array_filter((array) ($_POST['location'] ?? []));
    $region = trim((string) ($_POST['region'] ?? ''));

    if (!$location && '' === $region) {
        $result->invalidate($tag, wpcf7_get_message('invalid_required'));
    }

    return $result;
}, 10, 2);

// Phone: digits only, 8-12 of them (also checked in contact-step-form.js).
// CF7's own tel/minlength/maxlength checks run first and keep their messages;
// this catches what they let through (e.g. spaces, "+", "-").
add_filter('wpcf7_validate_tel*', function ($result, $tag) {
    if ('phone' !== $tag->name || !bravo_is_cf7_step_form()) {
        return $result;
    }

    $phone = trim((string) ($_POST['phone'] ?? ''));

    if ('' !== $phone && !preg_match('/^\d{8,12}$/', $phone)) {
        $result->invalidate($tag, __('Please enter a valid phone number (8-12 digits, numbers only).', 'sage'));
    }

    return $result;
}, 10, 2);

// "If you are human leave this field blank" - a filled-in honeypot is spam.
add_filter('wpcf7_spam', function ($spam) {
    if ($spam || !bravo_is_cf7_step_form()) {
        return $spam;
    }

    return '' !== trim((string) ($_POST['human-check'] ?? ''));
});
