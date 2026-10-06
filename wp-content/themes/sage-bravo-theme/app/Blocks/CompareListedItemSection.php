<?php

namespace App\Blocks;

class CompareListedItemSection
{
    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Render the Compare Listed Item Section block
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
        $columns = self::formatColumns(get_field('compare_columns'));
        $section_bg_color = self::formatColor(get_field('section_bg_color'));
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        // Nothing to show without a column
        if (!$columns) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Compare Listed Item Section: add a column with a heading or a list item.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'clis-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.compare-listed-item-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'columns'       => $columns,
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
     * Normalise the compare_columns repeater (order kept: the first column is
     * the left one). Columns with no heading and no list item are skipped.
     */
    private static function formatColumns($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $columns = [];
        foreach ($rows as $row) {
            $heading = trim((string) ($row['heading_text'] ?? ''));
            $items = self::formatItems($row['list_items'] ?? null);
            if (!$heading && !$items) {
                continue;
            }

            $level = $row['heading_level'] ?? 'h2';
            if (!in_array($level, self::HEADING_LEVELS, true)) {
                $level = 'h2';
            }

            $columns[] = [
                'heading' => $heading,
                'level'   => $level,
                'items'   => $items,
            ];
        }

        return $columns;
    }

    /**
     * Normalise a column's list_items repeater (order kept), skipping empty rows.
     * icon_type: false (the default) = cross, true = tick.
     */
    private static function formatItems($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $items = [];
        foreach ($rows as $row) {
            $text = trim((string) ($row['item_text'] ?? ''));
            if ($text === '') {
                continue;
            }

            $items[] = [
                'text' => $text,
                'tick' => !empty($row['icon_type']),
            ];
        }

        return $items;
    }
}
