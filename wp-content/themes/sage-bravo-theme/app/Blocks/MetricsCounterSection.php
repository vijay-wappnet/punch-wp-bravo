<?php

namespace App\Blocks;

class MetricsCounterSection
{
    /**
     * Allowed values for content_alignment / content_alignment_mobile.
     */
    private const ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Allowed values for trend_type.
     */
    private const TRENDS = ['none', 'positive', 'negative'];

    /**
     * Render the Metrics Counter Section block
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
        $metrics = self::formatMetrics(get_field('metrics'));
        $content_alignment = get_field('content_alignment') ?: 'left';
        $content_alignment_mobile = get_field('content_alignment_mobile') ?: 'left';
        $section_bg_color = self::formatColor(get_field('section_bg_color'));
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        if (!in_array($content_alignment, self::ALIGNMENTS, true)) {
            $content_alignment = 'left';
        }
        if (!in_array($content_alignment_mobile, self::ALIGNMENTS, true)) {
            $content_alignment_mobile = 'left';
        }

        // Nothing to show without a metric
        if (!$metrics) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Metrics Counter Section: add a metric.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'mcs-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        // Inline styles are limited to the section background color and image
        $section_styles = [];
        if ($section_bg_color) {
            $section_styles[] = 'background-color: ' . $section_bg_color;
        }

        $section_bg_image_url = self::imageUrl($section_bg_image);
        if ($section_bg_image_url) {
            $section_styles[] = 'background-image: url("' . esc_url($section_bg_image_url) . '")';
        }

        // Render the Blade template with data using view helper
        echo view('blocks.metrics-counter-section', [
            'blockId'                  => $blockId,
            'responsiveCss'            => $responsiveCss,
            'metrics'                  => $metrics,
            'content_alignment'        => $content_alignment,
            'content_alignment_mobile' => $content_alignment_mobile,
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
     * Only a plain colour value is used as a colour.
     */
    private static function formatColor($color): string
    {
        $color = trim((string) $color);

        $isHex = (bool) preg_match('/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $color);
        $isRgb = (bool) preg_match('/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(,\s*[\d.]+\s*)?\)$/i', $color);

        return ($isHex || $isRgb) ? $color : '';
    }

    /**
     * Normalise the metrics repeater (order kept), skipping empty rows.
     *
     * For the counter each metric also gets its numeric target, how many
     * decimals it has and whether it is written with thousands separators, so
     * the script can animate 0 -> value and end on exactly the text the editor
     * typed. A value that is not a plain number is shown as it is, un-animated.
     */
    private static function formatMetrics($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $metrics = [];
        foreach ($rows as $row) {
            $value = trim((string) ($row['metric_value'] ?? ''));
            $label = trim((string) ($row['metric_label'] ?? ''));
            if ($value === '' && $label === '') {
                continue;
            }

            $prefix = trim((string) ($row['metric_prefix'] ?? ''));
            $suffix = trim((string) ($row['metric_suffix'] ?? ''));

            $trend = $row['trend_type'] ?? 'none';
            if (!in_array($trend, self::TRENDS, true)) {
                $trend = 'none';
            }

            // "54,800" / "1.5" / "70" -> a number to count up to
            $plain = str_replace([',', ' '], '', $value);
            $numeric = $value !== '' && is_numeric($plain);
            $decimals = 0;
            if ($numeric && strpos($plain, '.') !== false) {
                $decimals = strlen(substr($plain, strpos($plain, '.') + 1));
            }

            $color = self::formatColor($row['trend_color'] ?? '');

            $metrics[] = [
                'value'       => $value,
                'prefix'      => $prefix,
                'suffix'      => $suffix,
                'label'       => $label,
                // What a screen reader should hear instead of the counting number
                'accessible'  => $prefix . $value . $suffix,
                'numeric'     => $numeric,
                'target'      => $numeric ? $plain : '',
                'decimals'    => $decimals,
                'grouping'    => strpos($value, ',') !== false,
                'trend'       => $trend,
                'trend_style' => $color ? 'color: ' . $color . ';' : '',
            ];
        }

        return $metrics;
    }
}
