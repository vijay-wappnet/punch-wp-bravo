<?php

namespace App\Blocks;

class TwoColumnsOrbitImageIconsWithCtaSection
{
    /**
     * Allowed heading tags for the heading_level / subheading_level fields.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Allowed values for the content_alignment field.
     */
    private const ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Allowed values for the content_alignment_in_mobile field.
     */
    private const MOBILE_POSITIONS = ['top', 'bottom'];

    /**
     * Allowed values for a pill's pill_shade field.
     */
    private const PILL_SHADES = ['light', 'medium', 'dark'];

    /**
     * Angle between two neighbouring icons on the curve (degrees), from the
     * design (seven icons, about 21 degrees apart). With many icons the step
     * shrinks so the curve never spans more than MAX_SPAN degrees.
     */
    private const ICON_STEP = 21;
    private const MAX_SPAN = 140;

    /**
     * Render the Two Columns Orbit Image Icons With CTA Section block
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
        $heading_level = self::level(get_field('heading_level'), 'h2');
        $subheading_text = trim((string) get_field('subheading_text'));
        $subheading_level = self::level(get_field('subheading_level'), 'h3');
        $description = get_field('description');
        $pills = self::formatPills(get_field('data_pills'));
        $icons = self::formatIcons(get_field('orbit_icons'));
        $buttons = self::formatButtons(get_field('buttons'));
        $visual_left = (get_field('image_alignment_left_side') ?: 'right') === 'left';
        $content_alignment = get_field('content_alignment') ?: 'left';
        $mobile_position = get_field('content_alignment_in_mobile') ?: 'bottom';
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        if (!in_array($content_alignment, self::ALIGNMENTS, true)) {
            $content_alignment = 'left';
        }
        if (!in_array($mobile_position, self::MOBILE_POSITIONS, true)) {
            $mobile_position = 'bottom';
        }

        $description = is_string($description) ? trim($description) : '';

        // Nothing to show on the front end without any content
        if (!$heading_text && !$subheading_text && !$description && !$pills && !$icons && !$buttons) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Two Columns Orbit Image Icons With CTA Section: add some content, pills or icons.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'tcoi-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.two-columns-orbit-image-icons-with-cta-section', [
            'blockId'           => $blockId,
            'responsiveCss'     => $responsiveCss,
            'heading_text'      => $heading_text,
            'heading_level'     => $heading_level,
            'subheading_text'   => $subheading_text,
            'subheading_level'  => $subheading_level,
            'description'       => $description,
            'pills'             => $pills,
            'icons'             => self::placeIcons($icons),
            'arc'               => self::arc($icons),
            'buttons'           => $buttons,
            'visual_left'       => $visual_left,
            'content_alignment' => $content_alignment,
            'mobile_position'   => $mobile_position,
            'section_style'     => $section_styles ? implode('; ', $section_styles) . ';' : '',
            'is_preview'        => $is_preview,
        ]);
    }

    /**
     * A valid heading tag, or the fallback.
     */
    private static function level($value, string $fallback): string
    {
        return in_array($value, self::HEADING_LEVELS, true) ? $value : $fallback;
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
            'alt'    => is_array($image) ? trim($image['alt'] ?? '') : '',
            'width'  => is_array($image) ? ($image['width'] ?? '') : '',
            'height' => is_array($image) ? ($image['height'] ?? '') : '',
        ];
    }

    /**
     * Normalise the data_pills repeater (order kept), skipping rows without a label.
     */
    private static function formatPills($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $pills = [];
        foreach ($rows as $row) {
            $label = trim((string) ($row['pill_label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $shade = $row['pill_shade'] ?? 'light';
            if (!in_array($shade, self::PILL_SHADES, true)) {
                $shade = 'light';
            }

            $pills[] = ['label' => $label, 'shade' => $shade];
        }

        return $pills;
    }

    /**
     * Normalise the orbit_icons repeater (order kept = order along the curve),
     * skipping rows without an icon. The icon is shown inside a white circle; the
     * title is only its accessible name when the media library has no alt text.
     */
    private static function formatIcons($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $icons = [];
        foreach ($rows as $row) {
            $icon = self::formatImage($row['icon'] ?? null);
            if (!$icon) {
                continue;
            }

            $icon['alt'] = $icon['alt'] ?: trim((string) ($row['title'] ?? ''));
            $icons[] = $icon;
        }

        return $icons;
    }

    /**
     * Spacing between the icons on the curve, in degrees.
     */
    private static function step(int $count): float
    {
        return $count > 1 ? min((float) self::ICON_STEP, self::MAX_SPAN / ($count - 1)) : 0.0;
    }

    /**
     * Put the icons on the curve, centred on the middle icon (angle 0):
     * angle = where it ends up; rank = how far it is from the middle icon (the
     * outer ones fan out a little later); z = the middle icon sits on top, the
     * others behind it, which is what they fan out from.
     */
    private static function placeIcons(array $icons): array
    {
        $count = count($icons);
        $step = self::step($count);

        foreach ($icons as $i => &$icon) {
            $offset = $i - ($count - 1) / 2;
            $icon['angle'] = round($offset * $step, 2);
            $icon['rank'] = (int) round(abs($offset));
            $icon['z'] = $count - $icon['rank'];
        }
        unset($icon);

        return $icons;
    }

    /**
     * The thin line along the curve through all the icons: an SVG arc of
     * radius 100 (the SVG is scaled to the real radius in CSS) from the first
     * icon's angle to the last one's. Null for fewer than two icons.
     */
    private static function arc(array $icons): ?string
    {
        $count = count($icons);
        if ($count < 2) {
            return null;
        }

        $span = self::step($count) * ($count - 1) / 2;
        $point = fn($degrees) => sprintf('%s %s', round(100 * cos(deg2rad($degrees)), 3), round(100 * sin(deg2rad($degrees)), 3));

        return sprintf('M%s A100 100 0 0 1 %s', $point(-$span), $point($span));
    }

    /**
     * Normalise the buttons repeater, skipping rows without a link URL.
     * The link's own title is the visible text.
     */
    private static function formatButtons($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $buttons = [];
        foreach ($rows as $row) {
            $link = $row['button_link'] ?? null;
            if (!is_array($link) || empty($link['url'])) {
                continue;
            }

            $title = trim($link['title'] ?? '') ?: __('Learn more', 'sage');
            $target = $link['target'] ?? '';

            // button_class may hold several space-separated classes; without one
            // the button is the standard red CTA
            $extra_classes = array_filter(array_map(
                'sanitize_html_class',
                preg_split('/\s+/', trim($row['button_class'] ?? ''))
            ));

            $buttons[] = [
                'url'         => $link['url'],
                'title'       => $title,
                'target'      => $target,
                'rel'         => $target === '_blank' ? 'noopener noreferrer' : '',
                'aria_label'  => trim($row['aria_label'] ?? ''),
                'event_label' => trim($row['button_google_event_label'] ?? ''),
                'class'       => implode(' ', array_merge(['btn', 'tcoiicwcs-btn'], $extra_classes ?: ['btn-red-fusion'])),
                'arrow_span'  => in_array('btn-trans-border-arrow', $extra_classes, true),
            ];
        }

        return $buttons;
    }
}
