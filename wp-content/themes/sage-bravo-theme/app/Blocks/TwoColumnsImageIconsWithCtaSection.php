<?php

namespace App\Blocks;

class TwoColumnsImageIconsWithCtaSection
{
    /**
     * Number of icons placed on the inner orbit ring.
     * All remaining icons are placed on the outer ring.
     */
    private const INNER_RING_MAX = 2;

    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Render the Two Columns Image Icons with CTA Section block
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
        $description = get_field('description');
        $image = get_field('image');
        $image_icons = get_field('image_icons');
        $buttons = get_field('buttons');
        $image_alignment_left_side = get_field('image_alignment_left_side') === 'yes';
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        // Generate unique block ID
        $blockId = 'tcic-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
            $heading_level = 'h2';
        }

        // Inline styles are limited to background color / image
        $section_styles = [];
        if ($section_bg_color) {
            $section_styles[] = 'background-color: ' . $section_bg_color;
        }
        $section_bg_image_url = self::imageUrl($section_bg_image);
        if ($section_bg_image_url) {
            $section_styles[] = 'background-image: url("' . esc_url($section_bg_image_url) . '")';
        }

        // Render the Blade template with data using view helper
        echo view('blocks.2-columns-image-icons-with-cta-section', [
            'blockId'                   => $blockId,
            'responsiveCss'             => $responsiveCss,
            'heading_text'              => $heading_text,
            'heading_level'             => $heading_level,
            'description'               => $description,
            'image'                     => self::formatImage($image),
            'icons'                     => self::formatIcons($image_icons),
            'buttons'                   => self::formatButtons($buttons),
            'image_alignment_left_side' => $image_alignment_left_side,
            'section_style'             => implode('; ', $section_styles),
            'is_preview'                => $is_preview,
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
            'alt'    => is_array($image) ? ($image['alt'] ?? '') : '',
            'width'  => is_array($image) ? ($image['width'] ?? '') : '',
            'height' => is_array($image) ? ($image['height'] ?? '') : '',
        ];
    }

    /**
     * Build the icon list with its orbit ring and angle.
     *
     * Rows 1-2 sit on the inner ring (row 1 on the left, row 2 on the right).
     * Rows 3+ are spread evenly around the outer ring, clockwise from the top.
     * `order` is the icon's position within its ring, used to stagger the
     * build-out animation (inner ring first, then outer ring).
     */
    private static function formatIcons($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $icons = [];
        foreach ($rows as $row) {
            $icon = self::formatImage($row['icon'] ?? null);
            if ($icon) {
                $icons[] = $icon;
            }
        }

        $innerCount = min(count($icons), self::INNER_RING_MAX);
        $outerCount = count($icons) - $innerCount;

        foreach ($icons as $index => &$icon) {
            if ($index < $innerCount) {
                $icon['ring'] = 'inner';
                $icon['order'] = $index;
                $icon['angle'] = 180 + (360 / $innerCount) * $index;
            } else {
                $icon['ring'] = 'outer';
                $icon['order'] = $index - $innerCount;
                $icon['angle'] = -90 + (360 / $outerCount) * $icon['order'];
            }
            $icon['angle'] = round($icon['angle'], 2);
        }
        unset($icon);

        return $icons;
    }

    /**
     * Normalise the buttons repeater, skipping rows without a URL.
     */
    private static function formatButtons($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $buttons = [];
        foreach ($rows as $row) {
            $link = $row['button_link'] ?? null;
            $url = is_array($link) ? ($link['url'] ?? '') : (is_string($link) ? $link : '');
            if (!$url) {
                continue;
            }

            $buttons[] = [
                'url'         => $url,
                'title'       => (is_array($link) && !empty($link['title'])) ? $link['title'] : __('Learn more', 'sage'),
                'target'      => is_array($link) ? ($link['target'] ?? '') : '',
                'aria_label'  => trim($row['aria_label'] ?? ''),
                'event_label' => trim($row['button_google_event_label'] ?? ''),
                // Fall back to the theme's red CTA (matches the design) when no class is set
                'class'       => trim($row['button_class'] ?? '') ?: 'btn-red-fusion',
            ];
        }

        return $buttons;
    }
}
