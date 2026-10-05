<?php

namespace App\Blocks;

class TwoColumnImageWithContentCtaSection
{
    /**
     * Allowed heading tags for the heading_level field.
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
     * Render the Two Column Image With Content CTA Section block
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
        $body = get_field('content');
        $buttons = self::formatButtons(get_field('buttons'));
        $image = self::formatImage(get_field('image'));
        $image_left = (get_field('image_alignment_left_side') ?: 'left') !== 'right';
        $content_alignment = get_field('content_alignment') ?: 'left';
        $content_alignment_in_mobile = get_field('content_alignment_in_mobile') ?: 'top';
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
        if (!in_array($content_alignment_in_mobile, self::MOBILE_POSITIONS, true)) {
            $content_alignment_in_mobile = 'top';
        }

        $body = is_string($body) ? trim($body) : '';

        // Nothing to show on the front end without any content
        if (!$heading_text && !$body && !$buttons && !$image) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Two Column Image With Content CTA Section: add an image or some content.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'tciwccs-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.two-column-image-with-content-cta-section', [
            'blockId'           => $blockId,
            'responsiveCss'     => $responsiveCss,
            'heading_text'      => $heading_text,
            'heading_level'     => $heading_level,
            'content'           => $body,
            'buttons'           => $buttons,
            'image'             => $image,
            'image_left'        => $image_left,
            'content_alignment' => $content_alignment,
            'mobile_position'   => $content_alignment_in_mobile,
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
     * Normalise the buttons repeater, skipping rows without a link URL.
     * The link's own title is the visible text. Every button is "btn tciwccs-btn";
     * the button_class value (for example btn-red-fusion) is added to that, and
     * there is no fallback style when it is empty.
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

            // button_class may hold several space-separated classes
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
                'class'       => implode(' ', array_merge(['btn', 'tciwccs-btn'], $extra_classes)),
                'arrow_span'  => in_array('btn-trans-border-arrow', $extra_classes, true),
            ];
        }

        return $buttons;
    }
}
