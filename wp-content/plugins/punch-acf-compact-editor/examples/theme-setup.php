<?php
/**
 * Copy ONE of these examples into the destination theme's functions.php.
 * It must run before that theme registers its ACF blocks.
 */

// Simplest: enable every ACF block registered on this website.
add_theme_support('punch-acf-compact-editor');

// Safer on a site containing third-party ACF blocks: replace the example names.
// add_theme_support('punch-acf-compact-editor', [
//     'blocks' => [
//         'acf/hero',
//         'acf/text-and-image',
//         'acf/gallery',
//     ],
// ]);

// Alternative: enable blocks that use the theme's shared render callback.
// add_theme_support('punch-acf-compact-editor', [
//     'render_callbacks' => [
//         'App\\render_block',
//     ],
// ]);

// Optional: put common controls in the main block toolbar.
// add_filter('punch/acf_compact_editor/top_toolbar_candidates', function () {
//     return ['media_mode', 'background_colour', 'content_alignment'];
// });

// Compatible legacy Dimensions controls are repaired automatically in 0.2.3+.
// Optional opt-out:
// add_filter('punch/acf_compact_editor/enable_dimensions_adapter', '__return_false');
