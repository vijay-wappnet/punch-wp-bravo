<?php

namespace App\Blocks;

class PredictiveInsightsDiagramSection
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
     * The design has five diagram elements; the repeater order places them
     * (see the block's CSS) and is the order they build in.
     */
    private const MAX_ELEMENTS = 5;

    /**
     * Connection paths, in the image's own units (the SVG viewBox is 312 x 346,
     * the image as drawn in the design). Connection N joins element N and
     * element N + 1, in the order they build in:
     *   1: left node -> across to the junction, up to the centre node
     *   2: junction down to the bottom node
     *   3: bottom node -> Action pill (with a badge between them it is drawn in
     *      two halves, 3 and 4)
     */
    private const CONNECTION_PATHS = [
        'M31 250 H59 V216 H82',
        'M59 216 V288 H82',
        'M145 288 H197',
    ];

    private const CONNECTION_PATHS_WITH_BADGE = [
        'M31 250 H59 V216 H82',
        'M59 216 V288 H82',
        'M145 288 H171',
        'M171 288 H197',
    ];

    /**
     * Render the Predictive Insights Diagram Section block
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
        $elements = self::formatElements(get_field('diagram_elements'));
        $connections = self::formatConnections(get_field('diagram_connections'), count($elements));
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
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Predictive Insights Diagram Section: add a heading, an image or a button.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'pid-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.predictive-insights-diagram-section', [
            'blockId'                  => $blockId,
            'responsiveCss'            => $responsiveCss,
            'heading_text'             => $heading_text,
            'heading_level'            => $heading_level,
            'image'                    => $image,
            'image_left'               => $image_left,
            'elements'                 => $elements,
            'connections'              => $connections,
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
     * Normalise the diagram_elements repeater, keeping its order (that order is
     * the build order and decides each element's place). Rows without an icon
     * are skipped. The uploaded icon is the whole visual; the title is only its
     * accessible name when the media library has no alt text.
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
                'slot'  => 0,
                'icon'  => $icon,
            ];

            if (count($elements) === self::MAX_ELEMENTS) {
                break;
            }
        }

        // The badge (slot 4) is optional, but the Action pill is always the last
        // element and always the last slot: with four elements the fourth is the
        // Action pill, not the badge.
        $count = count($elements);
        foreach ($elements as $i => &$element) {
            $element['slot'] = ($i + 1 === $count && $count >= 4) ? self::MAX_ELEMENTS : $i + 1;
        }
        unset($element);

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

        $paths = $elementCount >= self::MAX_ELEMENTS ? self::CONNECTION_PATHS_WITH_BADGE : self::CONNECTION_PATHS;

        $connections = [];
        foreach ($paths as $i => $path) {
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
            'class'       => implode(' ', array_merge(['btn', 'pid-btn'], $extra_classes ?: ['btn-red-fusion'])),
            'arrow_span'  => in_array('btn-trans-border-arrow', $extra_classes, true),
        ];
    }
}
