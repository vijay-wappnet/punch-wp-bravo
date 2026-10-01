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

        //Register the Video Banner Section Block
        acf_register_block_type([
            'name'            => 'video-banner-section',
            'title'           => __('Video Banner Section', 'sage'),
            'description'     => __('A fullscreen video banner section with heading and scroll arrow', 'sage'),
            'render_callback' => ['App\Blocks\VideoBannerSection', 'render'],
            'category'        => 'common',
            'icon'            => 'format-video',
            'mode'            => 'preview',
            'align'           => 'false',
            'api_version'     => 3,
            'supports'        => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

        // Register the Two Columns Image Icons with CTA Section Block
        acf_register_block_type([
            'name'                   => 'two-columns-image-icons-with-cta-section',
            'title'                  => __('Two Columns Image Icons with CTA Section', 'sage'),
            'description'            => __('Two columns: content with CTA buttons, and a central image with icons orbiting it', 'sage'),
            'render_callback'        => ['App\Blocks\TwoColumnsImageIconsWithCtaSection', 'render'],
            'category'               => 'common',
            'icon'                   => 'networking',
            'mode'                   => 'preview',
            'align'                  => false,
            'api_version'            => 3,
            'acf_block_version'      => 3,
            // Fields are edited inline via the block's pencil button, not the sidebar
            'hide_fields_in_sidebar' => true,
            'supports'               => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

        // Register the Floating Content Card Section Block
        acf_register_block_type([
            'name'                   => 'floating-content-card-section',
            'title'                  => __('Floating Content Card Section', 'sage'),
            'description'            => __('A full background image section with a floating content card and CTA buttons', 'sage'),
            'render_callback'        => ['App\Blocks\FloatingContentCardSection', 'render'],
            'category'               => 'common',
            'icon'                   => 'id-alt',
            'mode'                   => 'preview',
            'align'                  => false,
            'api_version'            => 3,
            'acf_block_version'      => 3,
            // Fields are edited inline via the block's pencil button, not the sidebar
            'hide_fields_in_sidebar' => true,
            'supports'               => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

        // Register the Trusted & Secure Section Block
        acf_register_block_type([
            'name'                   => 'trusted-secure-section',
            'title'                  => __('Trusted & Secure Section', 'sage'),
            'description'            => __('A row of trusted / certification icons alongside a heading, description and CTA buttons', 'sage'),
            'render_callback'        => ['App\Blocks\TrustedSecureSection', 'render'],
            'category'               => 'common',
            'icon'                   => 'shield',
            'mode'                   => 'preview',
            'align'                  => false,
            'api_version'            => 3,
            'acf_block_version'      => 3,
            // Fields are edited inline via the block's pencil button, not the sidebar
            'hide_fields_in_sidebar' => true,
            'supports'               => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

        // Register the Left Right Media Content Section Block
        acf_register_block_type([
            'name'                   => 'left-right-media-content-section',
            'title'                  => __('Left Right Media Content Section', 'sage'),
            'description'            => __('Two columns: a media image on the left or right, with heading, content and CTA buttons alongside', 'sage'),
            'render_callback'        => ['App\Blocks\LeftRightMediaContentSection', 'render'],
            'category'               => 'common',
            'icon'                   => 'align-pull-left',
            'mode'                   => 'preview',
            'align'                  => false,
            'api_version'            => 3,
            'acf_block_version'      => 3,
            // Fields are edited inline via the block's pencil button, not the sidebar
            'hide_fields_in_sidebar' => true,
            'supports'               => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

        // Register the Three Columns Card Grid Section Block
        acf_register_block_type([
            'name'                   => 'three-columns-card-grid-section',
            'title'                  => __('Three Columns Card Grid Section', 'sage'),
            'description'            => __('A three-column grid of image cards; hovering a card reveals its description', 'sage'),
            'render_callback'        => ['App\Blocks\ThreeColumnsCardGridSection', 'render'],
            'category'               => 'common',
            'icon'                   => 'grid-view',
            'mode'                   => 'preview',
            'align'                  => false,
            'api_version'            => 3,
            'acf_block_version'      => 3,
            // Fields are edited inline via the block's pencil button, not the sidebar
            'hide_fields_in_sidebar' => true,
            'supports'               => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

        // Register the Testimonial Slider Section Block
        acf_register_block_type([
            'name'                   => 'testimonial-slider-section',
            'title'                  => __('Testimonial Slider Section', 'sage'),
            'description'            => __('A slider of client testimonials, each with a quote, name/job title and company logo', 'sage'),
            'render_callback'        => ['App\Blocks\TestimonialSliderSection', 'render'],
            'category'               => 'common',
            'icon'                   => 'format-quote',
            'mode'                   => 'preview',
            'align'                  => false,
            'api_version'            => 3,
            'acf_block_version'      => 3,
            // Fields are edited inline via the block's pencil button, not the sidebar
            'hide_fields_in_sidebar' => true,
            'supports'               => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

        // Register the Product Showcase Slider Block
        acf_register_block_type([
            'name'                   => 'product-showcase-slider-section',
            'title'                  => __('Product Showcase Slider', 'sage'),
            'description'            => __('A slider of headings with product images, with automatic dot pagination and the hover dot grid background', 'sage'),
            'render_callback'        => ['App\Blocks\ProductShowcaseSliderSection', 'render'],
            'category'               => 'common',
            'icon'                   => 'images-alt2',
            'mode'                   => 'preview',
            'align'                  => false,
            'api_version'            => 3,
            'acf_block_version'      => 3,
            // Fields are edited inline via the block's pencil button, not the sidebar
            'hide_fields_in_sidebar' => true,
            'supports'               => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

        // Register the Process Workflow Section Block
        acf_register_block_type([
            'name'                   => 'process-workflow-section',
            'title'                  => __('Process Workflow Section', 'sage'),
            'description'            => __('An animated workflow drawing (lines and icons built left to right) beside a heading, description and CTA', 'sage'),
            'render_callback'        => ['App\Blocks\ProcessWorkflowSection', 'render'],
            'category'               => 'common',
            'icon'                   => 'randomize',
            'mode'                   => 'preview',
            'align'                  => false,
            'api_version'            => 3,
            'acf_block_version'      => 3,
            // Fields are edited inline via the block's pencil button, not the sidebar
            'hide_fields_in_sidebar' => true,
            'supports'               => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

        // Register the Tech Ecosystem Connections Block
        acf_register_block_type([
            'name'                   => 'tech-ecosystem-connections-section',
            'title'                  => __('Tech Ecosystem Connections Section', 'sage'),
            'description'            => __('A logo card in the centre with four items around it, joined by animated dashed and solid lines', 'sage'),
            'render_callback'        => ['App\Blocks\TechEcosystemConnectionsSection', 'render'],
            'category'               => 'common',
            'icon'                   => 'networking',
            'mode'                   => 'preview',
            'align'                  => false,
            'api_version'            => 3,
            'acf_block_version'      => 3,
            // Fields are edited inline via the block's pencil button, not the sidebar
            'hide_fields_in_sidebar' => true,
            'supports'               => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

        // Register the CTA Banner Section Block
        acf_register_block_type([
            'name'                   => 'cta-banner-section',
            'title'                  => __('CTA Banner Section', 'sage'),
            'description'            => __('A full-width banner with the hover dot grid, a heading and CTA buttons', 'sage'),
            'render_callback'        => ['App\Blocks\CtaBannerSection', 'render'],
            'category'               => 'common',
            'icon'                   => 'megaphone',
            'mode'                   => 'preview',
            'align'                  => false,
            'api_version'            => 3,
            'acf_block_version'      => 3,
            // Fields are edited inline via the block's pencil button, not the sidebar
            'hide_fields_in_sidebar' => true,
            'supports'               => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

        // Register the FAQs Accordion Section Block
        acf_register_block_type([
            'name'                   => 'faqs-accordion-section',
            'title'                  => __('FAQs Accordion Section', 'sage'),
            'description'            => __('A list of questions that expand to show their answers, one at a time', 'sage'),
            'render_callback'        => ['App\Blocks\FaqsAccordionSection', 'render'],
            'category'               => 'common',
            'icon'                   => 'editor-help',
            'mode'                   => 'preview',
            'align'                  => false,
            'api_version'            => 3,
            'acf_block_version'      => 3,
            // Fields are edited inline via the block's pencil button, not the sidebar
            'hide_fields_in_sidebar' => true,
            'supports'               => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

        // Register the Latest Article List Section Block
        acf_register_block_type([
            'name'                   => 'latest-article-list-section',
            'title'                  => __('Latest Article List Section', 'sage'),
            'description'            => __('The latest articles as image cards: a 3-column grid on desktop, a swipeable row on mobile', 'sage'),
            'render_callback'        => ['App\Blocks\LatestArticleListSection', 'render'],
            'category'               => 'common',
            'icon'                   => 'format-image',
            'mode'                   => 'preview',
            'align'                  => false,
            'api_version'            => 3,
            'acf_block_version'      => 3,
            // Fields are edited inline via the block's pencil button, not the sidebar
            'hide_fields_in_sidebar' => true,
            'supports'               => [
                'align'           => false,
                'mode'            => true,
                'jsx'             => true,
                'align_text'      => false,
            ],
        ]);

    }

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
