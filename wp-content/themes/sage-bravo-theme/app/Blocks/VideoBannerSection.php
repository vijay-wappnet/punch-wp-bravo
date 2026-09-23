<?php

namespace App\Blocks;

class VideoBannerSection
{
    /**
     * Render the Video Banner Section block
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
        $heading_text = get_field('heading_text');
        $heading_level = get_field('heading_level') ?: 'h2';
        $button = get_field('button');
        $show_dot_grid_canvas = get_field('show_dot_grid_canvas');
        $video_image = get_field('video_image');
        $video_file = get_field('video_file');
        $section_bg = get_field('section_bg');
        $margin = get_field('margin');
        $padding = get_field('padding');

        // Generate unique block ID
        $blockId = 'vbs-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        // Ensure video_file is properly formatted
        if (is_array($video_file) && isset($video_file['url'])) {
            $video_file = $video_file['url'];
        } elseif (is_numeric($video_file)) {
            $video_file = wp_get_attachment_url($video_file);
        }

        // Ensure video_image is properly formatted
        if (is_array($video_image) && isset($video_image['url'])) {
            $video_image = $video_image['url'];
        } elseif (is_numeric($video_image)) {
            $video_image = wp_get_attachment_url($video_image);
        }

        // Render the Blade template with data using view helper
        echo view('blocks.video-banner-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'heading_text'  => $heading_text,
            'heading_level' => $heading_level,
            'button'        => $button,
            'video_image'   => $video_image,
            'video_file'    => $video_file,
            'section_bg'    => $section_bg,
            'is_preview'    => $is_preview,
            'show_dot_grid_canvas' => $show_dot_grid_canvas,
        ]);
    }
}
