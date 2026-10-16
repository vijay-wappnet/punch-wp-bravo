<?php

namespace App\Blocks;

class LineBlockSection
{
    /**
     * Line colour when line_color is empty (design: $gray-grayish-orange-color)
     */
    private const DEFAULT_LINE_COLOR = '#BFB8B0';

    /**
     * Render the Line Block Section block
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
        $line_color = get_field('line_color');
        $line_width = get_field('line_width');
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        // Generate unique block ID
        $blockId = 'lbs-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        // Line width: a percentage of the container, 1-100 (empty = 100)
        $line_width = is_numeric($line_width) ? min(100, max(1, (float) $line_width)) : 100;

        // Inline styles are limited to the background color and image
        $section_styles = [];
        if ($section_bg_color) {
            $section_styles[] = 'background-color: ' . $section_bg_color;
        }

        $section_bg_image_url = self::imageUrl($section_bg_image);
        if ($section_bg_image_url) {
            $section_styles[] = 'background-image: url("' . esc_url($section_bg_image_url) . '")';
        }

        // Render the Blade template with data using view helper
        echo view('blocks.line-block-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'line_style'    => sprintf(
                '--lbs-line-color: %s; --lbs-line-width: %s%%;',
                sanitize_hex_color($line_color) ?: self::DEFAULT_LINE_COLOR,
                rtrim(rtrim(number_format($line_width, 2, '.', ''), '0'), '.')
            ),
            'section_style' => $section_styles ? implode('; ', $section_styles) . ';' : '',
            'is_preview'    => $is_preview,
        ]);
    }

    /**
     * Resolve an ACF image value (array, ID or URL) to a URL.
     */
    private static function imageUrl($image): string
    {
        if (is_array($image)) {
            return $image['url'] ?? '';
        }

        if (is_numeric($image)) {
            return wp_get_attachment_url($image) ?: '';
        }

        return is_string($image) ? $image : '';
    }
}
