<?php

namespace App\Blocks;

class ContactFormBlockSection
{
    /**
     * Render the Contact Form Block Section block
     *
     * @param array $block The block settings and attributes
     * @param string $content The block content
     * @param bool $is_preview Whether we are in preview mode
     * @param int $post_id The post ID
     * @return void
     */
    public static function render($block, $content = '', $is_preview = false, $post_id = 0)
    {
        // Get field values using ACF
        $form_id = (int) get_field('contact_form');
        $section_bg_color = get_field('section_bg_color');
        $margin = get_field('margin');
        $padding = get_field('padding');

        $form = $form_id && function_exists('wpcf7_contact_form') ? wpcf7_contact_form($form_id) : null;

        // Nothing to show on the front end without a Contact Form 7 form
        if (!$form) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Contact Form Block Section: select a Contact Form 7 form.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'cfbs-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        // Render the Blade template with data using view helper
        echo view('blocks.contact-form-block-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'form_html'     => do_shortcode(sprintf('[contact-form-7 id="%d" title="%s"]', $form->id(), esc_attr($form->title()))),
            'section_style' => $section_bg_color ? 'background-color: ' . $section_bg_color . ';' : '',
            'is_preview'    => $is_preview,
        ]);
    }
}
