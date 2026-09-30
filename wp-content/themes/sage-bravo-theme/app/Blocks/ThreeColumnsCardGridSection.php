<?php

namespace App\Blocks;

class ThreeColumnsCardGridSection
{
    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Render the Three Columns Card Grid Section block
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
        $cards = get_field('image_contents_with_link');
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        // Generate unique block ID
        $blockId = 'tccgs-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

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
        echo view('blocks.three-columns-card-grid-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'cards'         => self::formatCards($cards),
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

    /**
     * Normalise an ACF image value to ['url', 'alt', 'width', 'height'].
     * Images without alt text in the media library are treated as decorative (alt="").
     */
    private static function formatImage($image): ?array
    {
        if (is_numeric($image)) {
            $image = acf_get_attachment($image);
        }

        $url = self::imageUrl($image);
        if (!$url) {
            return null;
        }

        return [
            'url'    => $url,
            'alt'    => is_array($image) ? trim($image['alt'] ?? '') : '',
            'width'  => is_array($image) ? ($image['width'] ?? '') : '',
            'height' => is_array($image) ? ($image['height'] ?? '') : '',
        ];
    }

    /**
     * Normalise the image_contents_with_link repeater, skipping empty rows.
     */
    private static function formatCards($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $cards = [];
        foreach ($rows as $row) {
            $heading = trim($row['heading_text'] ?? '');
            $description = trim($row['description'] ?? '');
            $image = self::formatImage($row['image'] ?? null);

            if (!$heading && !$description && !$image) {
                continue;
            }

            $heading_level = $row['heading_level'] ?? 'h3';
            if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
                $heading_level = 'h3';
            }

            $cards[] = [
                'heading'       => $heading,
                'heading_level' => $heading_level,
                'description'   => $description,
                'image'         => $image,
            ];
        }

        return $cards;
    }
}
