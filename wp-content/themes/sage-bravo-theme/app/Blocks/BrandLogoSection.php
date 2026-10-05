<?php

namespace App\Blocks;

class BrandLogoSection
{
    /**
     * Render the Brand Logo Section block
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
        $logos = self::formatLogos(get_field('brand_logos'));
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        // Nothing to show on the front end without a logo
        if (!$logos) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Brand Logo Section: add a brand logo.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'bls-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.brand-logo-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'logos'         => $logos,
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
     * The alt text comes from the media library; a logo without alt text uses
     * its media title, so it still has a name for screen readers.
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

        $alt = is_array($image) ? trim($image['alt'] ?? '') : '';
        if (!$alt && is_array($image)) {
            $alt = trim($image['title'] ?? '');
        }

        return [
            'url'    => $url,
            'alt'    => $alt,
            'width'  => is_array($image) ? ($image['width'] ?? '') : '',
            'height' => is_array($image) ? ($image['height'] ?? '') : '',
        ];
    }

    /**
     * Normalise the brand_logos repeater (order kept), skipping rows without an image.
     */
    private static function formatLogos($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $logos = [];
        foreach ($rows as $row) {
            $logo = self::formatImage($row['logo_image'] ?? null);
            if ($logo) {
                $logos[] = $logo;
            }
        }

        return $logos;
    }
}
