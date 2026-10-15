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
     * Orbit geometry from the design (Figma Group 210, 599 x 692.96 - mobile
     * Group 2285 is the same at 0.8397 scale). Positions are in units of the
     * outer ring radius (599 / 2), measured from the orbit centre.
     */
    private const ICON_CIRCLE_SIZE = 102.09;          // icon circle, px at 599 wide
    private const INNER_RING_RATIO = 383.97 / 599;    // inner ring / outer ring

    /**
     * The design's six outer icons, clockwise from the top. They are not on an
     * even 60deg spread: the side icons sit ~40deg from horizontal, slightly
     * outside the ring, and the top/bottom ones slightly inside it.
     */
    private const OUTER_SLOTS_6 = [
        [0, -0.9879],
        [0.7753, -0.6493],
        [0.7753, 0.6493],
        [0, 0.9879],
        [-0.7753, 0.6493],
        [-0.7753, -0.6493],
    ];

    /**
     * Glyph width (as % of the icon circle) when an icon's own size is unknown.
     */
    private const DEFAULT_GLYPH_RATIO = 55.11 / 102.09;

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
        echo view('blocks.two-columns-image-icons-with-cta-section', [
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
            'id'     => is_array($image) ? (int) ($image['ID'] ?? $image['id'] ?? 0) : 0,
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
     * Rows 3+ go around the outer ring, clockwise from the top: at the design's
     * positions when there are six of them, otherwise evenly spaced.
     * `x` / `y` are the icon centre in outer-ring radii from the orbit centre.
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
                $angle = deg2rad(180 + (360 / $innerCount) * $index);
                $x = self::INNER_RING_RATIO * cos($angle);
                $y = self::INNER_RING_RATIO * sin($angle);
            } else {
                $icon['ring'] = 'outer';
                $icon['order'] = $index - $innerCount;
                if ($outerCount === count(self::OUTER_SLOTS_6)) {
                    [$x, $y] = self::OUTER_SLOTS_6[$icon['order']];
                } else {
                    // Any other count: evenly spaced on the ring, from the top
                    $angle = deg2rad(-90 + (360 / $outerCount) * $icon['order']);
                    $x = cos($angle);
                    $y = sin($angle);
                }
            }

            $icon['x'] = round($x, 4);
            $icon['y'] = round($y, 4);
            $icon['glyph'] = self::glyphPercent($icon);
        }
        unset($icon);

        return $icons;
    }

    /**
     * Icon artwork width as a % of the icon circle. The SVGs are exported at
     * their design size (e.g. 66 x 47 for Salesforce), so each keeps its own
     * proportion to the 102.09px circle, as in the design.
     */
    private static function glyphPercent(array $icon): float
    {
        $width = (float) ($icon['width'] ?: 0);

        if ($width <= 1 && !empty($icon['id']) && 'image/svg+xml' === get_post_mime_type($icon['id'])) {
            $file = get_attached_file($icon['id']);
            $svg = $file && is_readable($file) ? (string) file_get_contents($file, false, null, 0, 2048) : '';

            if (preg_match('/<svg\b[^>]*\swidth="([\d.]+)(?:px)?"/i', $svg, $match)) {
                $width = (float) $match[1];
            } elseif (preg_match('/viewBox="[\d.\s-]+?\s([\d.]+)\s[\d.]+"/i', $svg, $match)) {
                $width = (float) $match[1];
            }
        }

        $ratio = $width > 1 ? $width / self::ICON_CIRCLE_SIZE : self::DEFAULT_GLYPH_RATIO;

        return round(min($ratio, 0.8) * 100, 2);
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
