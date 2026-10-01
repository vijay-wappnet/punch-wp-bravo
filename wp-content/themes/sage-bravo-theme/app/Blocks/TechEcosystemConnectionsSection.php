<?php

namespace App\Blocks;

class TechEcosystemConnectionsSection
{
    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Allowed values for content_alignment / content_alignment_mobile.
     */
    private const ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * The four ecosystem positions, in the order they animate (clockwise).
     */
    private const POSITIONS = ['top', 'right', 'bottom', 'left'];

    /**
     * The design has four logos in the centre card.
     */
    private const MAX_LOGOS = 4;

    /**
     * Render the Tech Ecosystem Connections block
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
        $heading_text = trim((string) get_field('heading_text'));
        $heading_level = get_field('heading_level') ?: 'h2';
        $center_logos = get_field('center_logos');
        $ecosystem_items = get_field('ecosystem_items');
        $content_alignment = get_field('content_alignment') ?: 'center';
        $content_alignment_mobile = get_field('content_alignment_mobile') ?: 'center';
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
            $heading_level = 'h2';
        }
        if (!in_array($content_alignment, self::ALIGNMENTS, true)) {
            $content_alignment = 'center';
        }
        if (!in_array($content_alignment_mobile, self::ALIGNMENTS, true)) {
            $content_alignment_mobile = 'center';
        }

        $logos = self::formatLogos($center_logos);
        $items = self::formatItems($ecosystem_items);

        // Nothing to show on the front end without any content
        if (!$heading_text && !$logos && !$items) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Tech Ecosystem Connections Section: add a heading, logos or ecosystem items.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'tec-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        // Inline styles are limited to the background color and image; the image
        // goes on its own layer (like the Floating Content Card Section) so the
        // filter only affects the image
        $section_style = $section_bg_color ? 'background-color: ' . $section_bg_color . ';' : '';

        $section_bg_image_url = self::imageUrl($section_bg_image);
        $bg_image_style = $section_bg_image_url
            ? 'background-image: url("' . esc_url($section_bg_image_url) . '");'
            : '';

        // Render the Blade template with data using view helper
        echo view('blocks.tech-ecosystem-connections-section', [
            'blockId'                  => $blockId,
            'responsiveCss'            => $responsiveCss,
            'heading_text'             => $heading_text,
            'heading_level'            => $heading_level,
            'logos'                    => $logos,
            'items'                    => $items,
            'content_alignment'        => $content_alignment,
            'content_alignment_mobile' => $content_alignment_mobile,
            'section_style'            => $section_style,
            'bg_image_style'           => $bg_image_style,
            'is_preview'               => $is_preview,
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
     * The alt text comes from the media library; with $fallbackToTitle an image
     * without alt text uses its media title instead (for informative images
     * such as the logos). Otherwise it is treated as decorative (alt="").
     */
    private static function formatImage($image, bool $fallbackToTitle = false): ?array
    {
        if (is_numeric($image)) {
            $image = acf_get_attachment($image);
        }

        $url = self::imageUrl($image);
        if (!$url) {
            return null;
        }

        $alt = is_array($image) ? trim($image['alt'] ?? '') : '';
        if (!$alt && $fallbackToTitle && is_array($image)) {
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
     * Normalise the center_logos repeater, skipping empty rows.
     */
    private static function formatLogos($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $logos = [];
        foreach ($rows as $row) {
            $logo = self::formatImage($row['logo'] ?? null, true);
            if ($logo) {
                $logos[] = $logo;
            }
        }

        return array_slice($logos, 0, self::MAX_LOGOS);
    }

    /**
     * Normalise the ecosystem_items repeater: one item per position, ordered
     * clockwise (top, right, bottom, left) - the order they animate in.
     */
    private static function formatItems($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $byPosition = [];
        foreach ($rows as $row) {
            $position = $row['item_position'] ?? '';
            if (!in_array($position, self::POSITIONS, true) || isset($byPosition[$position])) {
                continue;
            }

            $title = trim((string) ($row['item_title'] ?? ''));
            $icon = self::formatImage($row['item_icon'] ?? null);
            if (!$title && !$icon) {
                continue;
            }

            $byPosition[$position] = [
                'position' => $position,
                'title'    => $title,
                'icon'     => $icon,
            ];
        }

        $items = [];
        foreach (self::POSITIONS as $position) {
            if (isset($byPosition[$position])) {
                $items[] = $byPosition[$position];
            }
        }

        return $items;
    }
}
