<?php

if (function_exists('acf_add_options_page')) {
    // Punch Theme General Settings
    $general_settings = array(
        'page_title' => __('Global Website Options', 'punch_theme'),
        'menu_title' => __('Website Options', 'punch_theme'),
        'menu_slug' => 'punch_theme-general-settings',
        'capability' => 'edit_posts',
        'redirect' => false,
        'icon_url' => 'dashicons-admin-customizer'
    );
    acf_add_options_page($general_settings);
}

// added dynamic
function add_dynamic_id_to_menu_links($atts, $item, $args, $depth)
{
    // Apply only to the 'primary_navigation' menu
    if (in_array('menu-item-has-children', $item->classes)) {
        $atts['id'] = 'menu-item-' . esc_attr($item->ID);
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'add_dynamic_id_to_menu_links', 10, 4);


/* ====== Remove WordPress Version from Source Code ====== */
function remove_wp_version()
{
    return '';
}
add_filter('the_generator', 'remove_wp_version');

function remove_wp_version_from_rss()
{
    return '';
}
add_filter('the_generator', 'remove_wp_version_from_rss');

/**================ // Disable Plugin Update notification in admin panel ================
/**
 * Prevent update notification for plugin
 */
function disable_plugin_updates($value)
{
    $pluginsToDisable = [
        'advanced-custom-fields-pro/acf.php'
    ];
    if (isset($value) && is_object($value)) {
        foreach ($pluginsToDisable as $plugin) {
            if (isset($value->response[$plugin])) {
                unset($value->response[$plugin]);
            }
        }
    }
    return $value;
}
add_filter('site_transient_update_plugins', 'disable_plugin_updates');

// Secure SVG /Webp Uploads (Sanitize SVG Content)
function allow_svg_upload($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    $mimes['webp'] = 'image/webp'; //
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');

/* ACF Google Map API Key from wp-config.php */
function my_acf_google_map_api($api)
{
    $api['key'] = WP_GOOGLE_MAPS_API_KEY;
    return $api;
}
add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');

/* ============================================================
   GSAP — Body class based on ACF Options field 'enable_animations'
   ============================================================ */
add_filter('body_class', function (array $classes): array {
    // Only modify on the frontend
    if (is_admin()) {
        return $classes;
    }

    // Read ACF options field (falls back to false if ACF is inactive or field missing)
    $animations_enabled = function_exists('get_field')
        ? get_field('enable_animations', 'option')
        : false;

    if ($animations_enabled) {
        $classes[] = 'animations-enabled';
    } else {
        $classes[] = 'animations-disabled';
    }

    return $classes;
});

/* Add data attributes to menu items in footer for accessibility modal */
add_filter('nav_menu_link_attributes', function ($atts, $item, $args) {
    // Check for the correct menu location and class
    if (
        isset($args->theme_location) && $args->theme_location === 'footer_main_navigation' &&
        isset($item->classes) && is_array($item->classes) &&
        in_array('site-accessibility', $item->classes)
    ) {
        $atts['data-toggle'] = 'modal';
        $atts['data-target'] = '#accessibilityModal';
        $atts['aria-label'] = 'Accessibility settings';
    }
    return $atts;
}, 10, 3);


/*
// add cf7 name field
Add hidden field with contact form name
This will add a hidden field containing the name of the form as the first input field in the form.
*/
add_filter('wpcf7_form_elements', function ($form) {
    // Get the current contact form object
    $contact_form = WPCF7_ContactForm::get_current();
    if ($contact_form) {
        $title = esc_attr($contact_form->title());
        // Create the hidden input HTML
        $hidden_field = '<input type="hidden" name="contact_form_name" value="' . $title . '" />';
        // Insert it right after the opening form tag (you can also append/prepend to $form)
        $form = $hidden_field . $form;
    }
    return $form;
});



/**
 * Generate responsive CSS for ACF dimension fields (margin/padding)
 *
 * @param array $margin The margin field values with desktop, tablet, mobile breakpoints
 * @param array $padding The padding field values with desktop, tablet, mobile breakpoints
 * @param string $blockId The unique block ID for CSS selector
 * @return string The generated responsive CSS
 */
function custom_acf_dimensions($margin, $padding, $blockId)
{
    // Helper function to format dimension values
    $formatDimension = function ($values) {
        if (!is_array($values))
            return '';
        $unit = $values['unit'] ?? 'px';
        $top = $values['top'] !== '' ? $values['top'] : null;
        $right = $values['right'] !== '' ? $values['right'] : null;
        $bottom = $values['bottom'] !== '' ? $values['bottom'] : null;
        $left = $values['left'] !== '' ? $values['left'] : null;
        // Only return if at least one value is set
        if ($top === null && $right === null && $bottom === null && $left === null)
            return '';
        return ($top ?? 0) . $unit . ' ' . ($right ?? 0) . $unit . ' ' . ($bottom ?? 0) . $unit . ' ' . ($left ?? 0) . $unit;
    };

    // Desktop styles (default)
    $desktopMargin = is_array($margin) && isset($margin['desktop']) ? $formatDimension($margin['desktop']) : '';
    $desktopPadding = is_array($padding) && isset($padding['desktop']) ? $formatDimension($padding['desktop']) : '';

    // Tablet styles (max-width: 991px)
    $tabletMargin = is_array($margin) && isset($margin['tablet']) ? $formatDimension($margin['tablet']) : '';
    $tabletPadding = is_array($padding) && isset($padding['tablet']) ? $formatDimension($padding['tablet']) : '';

    // Mobile styles (max-width: 767px)
    $mobileMargin = is_array($margin) && isset($margin['mobile']) ? $formatDimension($margin['mobile']) : '';
    $mobilePadding = is_array($padding) && isset($padding['mobile']) ? $formatDimension($padding['mobile']) : '';

    // Build CSS string with media queries
    $responsiveCss = '';

    // Desktop (base styles)
    $desktopRules = [];
    if (!empty($desktopMargin))
        $desktopRules[] = 'margin: ' . $desktopMargin;
    if (!empty($desktopPadding))
        $desktopRules[] = 'padding: ' . $desktopPadding;
    if (!empty($desktopRules)) {
        $responsiveCss .= '#' . $blockId . ' { ' . implode('; ', $desktopRules) . '; }';
    }

    // Tablet
    $tabletRules = [];
    if (!empty($tabletMargin))
        $tabletRules[] = 'margin: ' . $tabletMargin;
    if (!empty($tabletPadding))
        $tabletRules[] = 'padding: ' . $tabletPadding;
    if (!empty($tabletRules)) {
        $responsiveCss .= ' @media (max-width: 991px) { #' . $blockId . ' { ' . implode('; ', $tabletRules) . '; } }';
    }

    // Mobile
    $mobileRules = [];
    if (!empty($mobileMargin))
        $mobileRules[] = 'margin: ' . $mobileMargin;
    if (!empty($mobilePadding))
        $mobileRules[] = 'padding: ' . $mobilePadding;
    if (!empty($mobileRules)) {
        $responsiveCss .= ' @media (max-width: 767px) { #' . $blockId . ' { ' . implode('; ', $mobileRules) . '; } }';
    }

    return $responsiveCss;
}
