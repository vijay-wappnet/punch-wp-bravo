<?php

namespace App\Blocks;

class ImageBannerSection
{
    /**
     * Render the Image Banner Section block
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
        $banner_image = self::formatImage(get_field('banner_image'));
        $border_radius = get_field('banner_border_radius');
        $image_filter = get_field('banner_image_filter');
        $section_bg_color = self::formatColor(get_field('section_bg_color'));
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        $section_bg_image_url = self::imageUrl($section_bg_image);

        // Nothing to show without a banner image or a section background image
        // (a section with only a background image is still shown)
        if (!$banner_image && !$section_bg_image_url) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Image Banner Section: add a banner image or a section background image.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'ibs-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        // Inline styles are limited to the section background color and image
        $section_styles = [];
        if ($section_bg_color) {
            $section_styles[] = 'background-color: ' . $section_bg_color;
        }

        if ($section_bg_image_url) {
            $section_styles[] = 'background-image: url("' . esc_url($section_bg_image_url) . '")';
        }

        // ... and the banner's corner radius (a whole number of px)
        $radius = is_numeric($border_radius) ? max(0, (int) round((float) $border_radius)) : 0;

        // Render the Blade template with data using view helper
        echo view('blocks.image-banner-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'banner_image'  => $banner_image,
            'image_style'   => $radius > 0 ? 'border-radius: ' . $radius . 'px;' : '',
            'filtered'      => $image_filter === 'yes',
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
     * The alt text is the media library's; an image without one is treated as
     * decorative (alt="") and the file name is never used.
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
     * Only a plain colour value is used as the section background colour.
     */
    private static function formatColor($color): string
    {
        $color = trim((string) $color);

        $isHex = (bool) preg_match('/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $color);
        $isRgb = (bool) preg_match('/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(,\s*[\d.]+\s*)?\)$/i', $color);

        return ($isHex || $isRgb) ? $color : '';
    }
}
