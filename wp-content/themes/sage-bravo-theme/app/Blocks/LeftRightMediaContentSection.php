<?php

namespace App\Blocks;

class LeftRightMediaContentSection
{
    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Allowed values for the content_alignment_in_mobile field.
     */
    private const MOBILE_ALIGNMENTS = ['top', 'bottom'];

    /**
     * Render the Left Right Media Content Section block
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
        $show_left_side_image = (bool) get_field('show_left_side_image');
        $media_image = get_field('media_image');
        $title = get_field('title');
        $heading_level = get_field('heading_level') ?: 'h2';
        $body = get_field('content');
        $buttons = get_field('buttons');
        $content_alignment_in_mobile = get_field('content_alignment_in_mobile') ?: 'top';
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        // Generate unique block ID
        $blockId = 'lrmcs-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
            $heading_level = 'h2';
        }

        if (!in_array($content_alignment_in_mobile, self::MOBILE_ALIGNMENTS, true)) {
            $content_alignment_in_mobile = 'top';
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
        echo view('blocks.left-right-media-content-section', [
            'blockId'        => $blockId,
            'responsiveCss'  => $responsiveCss,
            'title'          => is_string($title) ? trim($title) : '',
            'heading_level'  => $heading_level,
            'content'        => is_string($body) ? trim($body) : '',
            'image'          => self::formatImage($media_image),
            'buttons'        => self::formatButtons($buttons),
            'media_side'     => $show_left_side_image ? 'left' : 'right',
            'mobile_content' => $content_alignment_in_mobile,
            'section_style'  => $section_styles ? implode('; ', $section_styles) . ';' : '',
            'is_preview'     => $is_preview,
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
