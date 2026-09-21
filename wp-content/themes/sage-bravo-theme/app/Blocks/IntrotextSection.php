<?php

namespace App\Blocks;

class IntrotextSection
{
    /**
     * Render the Introtext Section block
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
        $use_dynamic_heading = get_field('get_dynamic_heading_from_page_post') ?? false;
        $heading_text = get_field('heading_text');
        $heading_level = get_field('heading_level') ?: 'h2';
        $content = get_field('content');
        $bg_color = get_field('intro_section_bg');
        $buttons = get_field('intro_section_button');
        $margin = get_field('margin');
        $padding = get_field('padding');
        $content_alignment = get_field('content_alignment') ?: 'left';

        // Generate unique block ID
        $blockId = 'its-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        // Validate button structure
        if (!is_array($buttons)) {
            $buttons = [];
        }

        // Get dynamic heading from post/page if enabled
        if ($use_dynamic_heading) {
            $post_title = get_the_title();
            if ($post_title) {
                $heading_text = html_entity_decode($post_title, ENT_QUOTES, 'UTF-8');
            }
        }

        // Render the Blade template with data using view helper
        echo view('blocks.introtext-section', [
            'blockId'          => $blockId,
            'responsiveCss'    => $responsiveCss,
            'heading_text'     => $heading_text,
            'heading_level'    => $heading_level,
            'content'          => $content,
            'bg_color'         => $bg_color,
            'buttons'          => $buttons,
            'use_dynamic_heading' => $use_dynamic_heading,
            'content_alignment' => $content_alignment,
            'is_preview'       => $is_preview,
        ]);
    }
}
