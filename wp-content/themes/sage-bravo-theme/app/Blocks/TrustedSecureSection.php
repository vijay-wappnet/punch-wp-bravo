<?php

namespace App\Blocks;

class TrustedSecureSection
{
    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Allowed values for the content_alignment field.
     */
    private const ALIGNMENTS = ['left', 'right'];

    /**
     * Render the Trusted & Secure Section block
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
        $trusted_icons = get_field('trusted_icons');
        $content_alignment = get_field('content_alignment') ?: 'left';
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        // Generate unique block ID
        $blockId = 'tss-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
            $heading_level = 'h2';
        }

        if (!in_array($content_alignment, self::ALIGNMENTS, true)) {
            $content_alignment = 'left';
        }

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
        echo view('blocks.trusted-secure-section', [
            'blockId'           => $blockId,
            'responsiveCss'     => $responsiveCss,
            'heading_text'      => $heading_text,
            'heading_level'     => $heading_level,
            'description'       => $description,
            'buttons'           => self::formatButtons($buttons),
            'icons'             => self::formatIcons($trusted_icons),
            'content_alignment' => $content_alignment,
            'section_style'     => $section_styles ? implode('; ', $section_styles) . ';' : '',
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
     * Normalise the trusted_icons repeater, skipping rows without an image.
     * Icons without alt text in the media library are treated as decorative (alt="").
     */
    private static function formatIcons($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $icons = [];
        foreach ($rows as $row) {
            $image = $row['icon'] ?? null;

            if (is_numeric($image)) {
                $image = acf_get_attachment($image) ?: null;
            }

            $url = self::imageUrl($image);
            if (!$url) {
                continue;
            }

            $icons[] = [
                'url'    => $url,
                'alt'    => is_array($image) ? trim($image['alt'] ?? '') : '',
                'width'  => is_array($image) ? ($image['width'] ?? '') : '',
                'height' => is_array($image) ? ($image['height'] ?? '') : '',
            ];
        }

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
                'class'       => trim($row['button_class'] ?? ''),
            ];
        }

        return $buttons;
    }
}
