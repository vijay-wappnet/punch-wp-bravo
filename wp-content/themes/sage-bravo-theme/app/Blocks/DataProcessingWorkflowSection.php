<?php

namespace App\Blocks;

class DataProcessingWorkflowSection
{
    /**
     * The design has six elements (Data, logo, the two circles, arrow, Action);
     * the repeater order places them and is the order they build in.
     */
    private const MAX_ELEMENTS = 6;

    /**
     * Connection paths, in the diagram's own units (the SVG viewBox is
     * 775 x 250, measured from the design). Connection N joins element N and
     * element N + 1, in the order they build in:
     *   1: Data -> logo
     *   2: logo -> across to the junction, up to the top circle
     *   3: junction down to the bottom circle
     *   4: top circle -> across and down to the arrow
     *   5: arrow -> Action
     */
    private const CONNECTION_PATHS = [
        'M147 126 H210',
        'M286 126 H349 V80',
        'M349 126 V171',
        'M387 42 H470 V89',
        'M508 126 H627',
    ];

    /**
     * Render the Data Processing Workflow Section block
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
        $elements = self::formatElements(get_field('diagram_elements'));
        $connections = self::formatConnections(get_field('diagram_connections'), count($elements));
        $section_bg_color = self::formatColor(get_field('section_bg_color'));
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        // Nothing to show without a diagram element
        if (!$elements) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Data Processing Workflow Section: add a diagram element.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'dpw-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.data-processing-workflow-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'elements'      => $elements,
            'connections'   => $connections,
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

    /**
     * Normalise the diagram_elements repeater, keeping its order (that order is
     * the build order, left to right, and decides each element's place). Rows
     * without an icon are skipped. The uploaded icon is the whole visual; the
     * title is only its accessible name when the media library has no alt text.
     */
    private static function formatElements($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $elements = [];
        foreach ($rows as $row) {
            $icon = self::formatImage($row['icon'] ?? null);
            if (!$icon) {
                continue;
            }

            $title = trim((string) ($row['title'] ?? ''));
            $icon['alt'] = $icon['alt'] ?: $title;

            $elements[] = [
                'index' => count($elements) + 1,
                'icon'  => $icon,
            ];

            if (count($elements) === self::MAX_ELEMENTS) {
                break;
            }
        }

        return $elements;
    }

    /**
     * Pair the connection repeater (in order) with the drawing's paths. A
     * connection is only drawn when both elements it joins exist; a missing
     * row defaults to solid.
     */
    private static function formatConnections($rows, int $elementCount): array
    {
        $rows = is_array($rows) ? array_values($rows) : [];

        $connections = [];
        foreach (self::CONNECTION_PATHS as $i => $path) {
            if ($elementCount < $i + 2) {
                break;
            }

            $type = $rows[$i]['connection_type'] ?? 'solid';

            $connections[] = [
                'index'  => $i + 1,
                'path'   => $path,
                'dashed' => $type === 'dashed',
            ];
        }

        return $connections;
    }
}
