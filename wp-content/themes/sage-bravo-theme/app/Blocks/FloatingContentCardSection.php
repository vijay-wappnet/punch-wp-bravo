<?php

namespace App\Blocks;

class FloatingContentCardSection
{
    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Allowed values for the content_alignment field.
     */
    private const ALIGNMENTS = ['left', 'right', 'center'];

    /**
     * Render the Floating Content Card Section block
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
        $buttons = get_field('buttons');
        $content_alignment = get_field('content_alignment') ?: 'left';
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        // Generate unique block ID
        $blockId = 'fccs-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
            $heading_level = 'h2';
        }

        if (!in_array($content_alignment, self::ALIGNMENTS, true)) {
            $content_alignment = 'left';
        }

        // Background color stays on the section as the base / fallback color
        $section_style = $section_bg_color ? 'background-color: ' . $section_bg_color : '';

        // Background image is rendered on its own layer so a grayscale
        // filter can be applied to it without affecting the card
        $section_bg_image_url = self::imageUrl($section_bg_image);
        $bg_image_style = $section_bg_image_url
            ? 'background-image: url("' . esc_url($section_bg_image_url) . '")'
            : '';

        // Render the Blade template with data using view helper
        echo view('blocks.floating-content-card-section', [
            'blockId'           => $blockId,
            'responsiveCss'     => $responsiveCss,
            'heading_text'      => $heading_text,
            'heading_level'     => $heading_level,
            'description'       => $description,
            'buttons'           => self::formatButtons($buttons),
            'content_alignment' => $content_alignment,
            'section_style'     => $section_style,
            'bg_image_style'    => $bg_image_style,
            'is_preview'        => $is_preview,
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
