<?php

// punch-acf-compact-editor plugin is used to compact the ACF editor in the admin panel. This is useful for improving the user experience when editing content with ACF fields, especially when there are many fields or complex layouts. The plugin provides a more streamlined and organized interface for managing ACF fields, making it easier for users to navigate and edit content without being overwhelmed by too much information at once.
add_theme_support('punch-acf-compact-editor');

// Registered on acf/init (not while the theme loads): ACF translates the page
// title with __(), and doing that before init triggers a "translation loading
// was triggered too early" notice (which Acorn turns into a 500 page).
add_action('acf/init', function () {
    if (!function_exists('acf_add_options_page')) {
        return;
    }

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
});

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
        isset($args->theme_location) && $args->theme_location === 'footer_main_navigation_one' &&
        isset($item->classes) && is_array($item->classes) &&
        in_array('site-accessibility', $item->classes)
    ) {
        $atts['data-toggle'] = 'modal';
        $atts['data-target'] = '#accessibilityModal';
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
        $top = ($values['top'] ?? '') !== '' ? $values['top'] : null;
        $right = ($values['right'] ?? '') !== '' ? $values['right'] : null;
        $bottom = ($values['bottom'] ?? '') !== '' ? $values['bottom'] : null;
        $left = ($values['left'] ?? '') !== '' ? $values['left'] : null;
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

/**
 * SVG uploads: WordPress stores no width/height for them, so every
 * <img width/height> built from an ACF image (or wp_get_attachment_image)
 * was missing its dimensions. Read them from the file instead - the svg's own
 * width/height attributes, else its viewBox - and cache them per request.
 */
function bravo_svg_dimensions($file)
{
    static $cache = [];

    if (!$file || !is_readable($file)) {
        return null;
    }

    if (array_key_exists($file, $cache)) {
        return $cache[$file];
    }

    $svg = (string) file_get_contents($file, false, null, 0, 4096);
    $dims = null;

    if (preg_match('/<svg\b[^>]*>/i', $svg, $tag)) {
        $number = static function ($name) use ($tag) {
            // Plain numbers or px only - a percentage is not a size
            return preg_match('/\s' . $name . '\s*=\s*["\']\s*([\d.]+)\s*(?:px)?\s*["\']/i', $tag[0], $m) ? (float) $m[1] : 0;
        };

        $width = $number('width');
        $height = $number('height');

        if ((!$width || !$height) && preg_match('/viewBox\s*=\s*["\']\s*[-\d.]+[\s,]+[-\d.]+[\s,]+([\d.]+)[\s,]+([\d.]+)/i', $tag[0], $box)) {
            $width = (float) $box[1];
            $height = (float) $box[2];
        }

        if ($width > 0 && $height > 0) {
            $dims = [(int) round($width), (int) round($height)];
        }
    }

    return $cache[$file] = $dims;
}

add_filter('wp_get_attachment_metadata', function ($data, $attachment_id) {
    if ('image/svg+xml' !== get_post_mime_type($attachment_id)) {
        return $data;
    }

    if (is_array($data) && !empty($data['width']) && !empty($data['height'])) {
        return $data;
    }

    $dims = bravo_svg_dimensions(get_attached_file($attachment_id));
    if ($dims) {
        $data = is_array($data) ? $data : [];
        [$data['width'], $data['height']] = $dims;
    }

    return $data;
}, 10, 2);

/**
 * Image alt text: the first non-empty candidate, as plain text. Blocks pass
 * the image's own Alt Text (Media Library) first, then block content (a
 * heading, a name...) and finally a block-related label, so no <img> ends up
 * with an empty alt when its Alt Text field was left blank.
 */
function bravo_alt(...$candidates)
{
    foreach ($candidates as $candidate) {
        $text = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags(html_entity_decode((string) $candidate, ENT_QUOTES, 'UTF-8'))));
        if ('' !== $text) {
            return $text;
        }
    }

    return '';
}

/**
 * aria-label / data-event value: the first non-empty candidate, as plain text
 * (the ACF field first, then the button's visible title or a fixed label).
 * Same rules as bravo_alt(); named separately so templates read clearly.
 */
function bravo_label(...$candidates)
{
    return bravo_alt(...$candidates);
}

/**
 * Links and buttons inside editable HTML (ACF WYSIWYG, Contact Form 7, WP
 * pagination...) - add aria-label and data-event where they are missing,
 * taken from the element's own text. Existing values are never changed.
 * $event_prefix (e.g. "Contact form") is added in front of data-event.
 */
function bravo_link_attrs($html, $event_prefix = '')
{
    if (!is_string($html) || '' === $html || (false === stripos($html, '<a') && false === stripos($html, '<button'))) {
        return $html;
    }

    return preg_replace_callback('#<(a|button)\b([^>]*)>(.*?)</\1>#is', function ($m) use ($event_prefix) {
        [$all, $tag, $attrs, $inner] = $m;
        $text = bravo_label($inner);
        if ('' === $text) {
            return $all;
        }

        // A link that is just a number (pagination) reads as "Page 2"
        $label = ctype_digit($text) ? sprintf(__('Page %s', 'sage'), $text) : $text;

        $extra = '';
        if (!preg_match('/\saria-label\s*=/i', $attrs)) {
            $extra .= ' aria-label="' . esc_attr($label) . '"';
        }
        if (!preg_match('/\sdata-event\s*=/i', $attrs)) {
            $extra .= ' data-event="' . esc_attr($event_prefix ? $event_prefix . ' - ' . $label : $label) . '"';
        }

        return $extra ? '<' . $tag . $attrs . $extra . '>' . $inner . '</' . $tag . '>' : $all;
    }, $html);
}
