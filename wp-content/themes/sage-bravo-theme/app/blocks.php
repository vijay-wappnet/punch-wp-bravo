<?php

/**
 * Register custom Gutenberg blocks.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Register custom blocks
 */
add_action('init', function () {

    // Check if ACF is active and the function exists
    if (function_exists('acf_register_block_type')) {
        // Register the Introtext Section Block
        acf_register_block_type([
            'name'            => 'introtext-section',
            'title'           => __('Intro Section', 'sage'),
            'description'     => __('A section with heading, content, background, and buttons', 'sage'),
            'render_callback' => ['App\Blocks\IntrotextSection', 'render'],
            'category'        => 'common',
            'icon'            => 'text',
            'mode'            => 'edit',
            'align'           => 'full',
            'api_version'     => 3,
            'supports'        => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);
    }
    // Register a custom block with ACF
});

/**
 * ACF PRO 6.8.10 removed the old snake_case JS API (`acf.add_action`, `acf.add_filter`,
 * `acf.do_action`, `acf.apply_filters`) in favor of the camelCase versions
 * (`acf.addAction`/`acf.addFilter`/etc.) that have existed alongside them for years. The
 * "ACF Dimensions" field plugin (github.com/ernilambar/acf-dimensions, used for the Margin/
 * Padding fields on the Intro Section block) still calls the removed snake_case names to wire up
 * its device-switch and link/unlink button clicks, guarded by
 * `if (typeof acf.add_action !== 'undefined')` - since that function no longer exists, the guard
 * silently fails and those buttons never get their click handlers attached at all.
 *
 * Restore the old names as aliases for the current ones so plugins built against ACF's older API
 * keep working, without touching the vendored plugin's own code (which a plugin update would
 * overwrite anyway).
 */
add_action('enqueue_block_assets', function () {
    if (!is_admin() || !wp_script_is('acf-input', 'registered')) {
        return;
    }

    wp_add_inline_script(
        'acf-input',
        '(function () {'
            . 'if (!window.acf) { return; }'
            . '["add_action:addAction", "add_filter:addFilter", "do_action:doAction", "apply_filters:applyFilters"].forEach(function (pair) {'
            . 'var names = pair.split(":");'
            . 'if (!acf[names[0]] && acf[names[1]]) { acf[names[0]] = acf[names[1]].bind(acf); }'
            . '});'
            . '})();',
        'after'
    );
});
