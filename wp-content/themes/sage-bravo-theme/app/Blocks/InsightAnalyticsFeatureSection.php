<?php

namespace App\Blocks;

class InsightAnalyticsFeatureSection
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
     * Allowed values for mobile_image_position.
     */
    private const MOBILE_POSITIONS = ['top', 'bottom'];

    /**
     * The two sides an overlay card can float on, in the order they are output.
     */
    private const OVERLAY_SIDES = ['left', 'right'];

    /**
     * Render the Insight & Analytics Feature Section block
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
        $image = self::formatImage(get_field('image'));
        $image_left = (bool) get_field('image_alignment_left_side');
        $overlays = self::formatOverlays(get_field('overlay_items'));
        $button = self::formatButton(get_field('button'));
        $content_alignment = get_field('content_alignment') ?: 'left';
        $content_alignment_mobile = get_field('content_alignment_mobile') ?: 'left';
        $mobile_image_position = get_field('mobile_image_position') ?: 'top';
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
            $heading_level = 'h2';
        }
        if (!in_array($content_alignment, self::ALIGNMENTS, true)) {
            $content_alignment = 'left';
        }
        if (!in_array($content_alignment_mobile, self::ALIGNMENTS, true)) {
            $content_alignment_mobile = 'left';
        }
        if (!in_array($mobile_image_position, self::MOBILE_POSITIONS, true)) {
            $mobile_image_position = 'top';
        }

        // Nothing to show on the front end without any content
        if (!$heading_text && !$image && !$button) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Insight & Analytics Feature Section: add a heading, an image or a button.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'iafs-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.insight-analytics-feature-section', [
            'blockId'                  => $blockId,
            'responsiveCss'            => $responsiveCss,
            'heading_text'             => $heading_text,
            'heading_level'            => $heading_level,
            'image'                    => $image,
            'image_left'               => $image_left,
            'overlays'                 => $overlays,
            'button'                   => $button,
            'content_alignment'        => $content_alignment,
            'content_alignment_mobile' => $content_alignment_mobile,
            'mobile_image_position'    => $mobile_image_position,
            'section_style'            => $section_styles ? implode('; ', $section_styles) . ';' : '',
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
     * Only plain colour values are used as an overlay's background colour.
     */
    private static function formatColor($color): string
    {
        $color = trim((string) $color);

        $isHex = (bool) preg_match('/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $color);
        $isRgb = (bool) preg_match('/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(,\s*[\d.]+\s*)?\)$/i', $color);

        return ($isHex || $isRgb) ? $color : '';
    }

    /**
     * Normalise the overlay_items repeater: one card per side (the first one
     * set for it), left then right.
     */
    private static function formatOverlays($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $bySide = [];
        foreach ($rows as $row) {
            $side = $row['position'] ?? 'left';
            if (!in_array($side, self::OVERLAY_SIDES, true) || isset($bySide[$side])) {
                continue;
            }

            $title = trim((string) ($row['title'] ?? ''));
            $icon = self::formatImage($row['icon'] ?? null);
            if (!$title && !$icon) {
                continue;
            }

            $color = self::formatColor($row['background_color'] ?? '');

            $bySide[$side] = [
                'side'  => $side,
                'title' => $title,
                'icon'  => $icon,
                'style' => $color ? 'background-color: ' . $color . ';' : '',
            ];
        }

        $overlays = [];
        foreach (self::OVERLAY_SIDES as $side) {
            if (isset($bySide[$side])) {
                $overlays[] = $bySide[$side];
            }
        }

        return $overlays;
    }

    /**
     * Normalise the button group; null when it has no link URL.
     * The link's own title is the visible text.
     */
    private static function formatButton($group): ?array
    {
        $link = is_array($group) ? ($group['button_link'] ?? null) : null;
        if (!is_array($link) || empty($link['url'])) {
            return null;
        }

        $title = trim($link['title'] ?? '') ?: __('Learn more', 'sage');
        $target = $link['target'] ?? '';

        // button_class may hold several space-separated classes; without one the
        // button is the standard red CTA
        $extra_classes = array_filter(array_map(
            'sanitize_html_class',
            preg_split('/\s+/', trim($group['button_class'] ?? ''))
        ));

        return [
            'url'         => $link['url'],
            'title'       => $title,
            'target'      => $target,
            'rel'         => $target === '_blank' ? 'noopener noreferrer' : '',
            'aria_label'  => trim($group['aria_label'] ?? ''),
            'event_label' => trim($group['button_google_event_label'] ?? ''),
            'class'       => implode(' ', array_merge(['btn', 'iafs-btn'], $extra_classes ?: ['btn-red-fusion'])),
            'arrow_span'  => in_array('btn-trans-border-arrow', $extra_classes, true),
        ];
    }
}
