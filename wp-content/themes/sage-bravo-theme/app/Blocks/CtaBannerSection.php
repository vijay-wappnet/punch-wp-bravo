<?php

namespace App\Blocks;

class CtaBannerSection
{
    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Allowed values for the alignment fields.
     */
    private const ALIGNMENTS = ['left', 'center', 'right'];
    private const BUTTON_SIDES = ['left', 'right'];
    private const MOBILE_BUTTON_POSITIONS = ['top', 'bottom'];
    private const DOT_MOBILE_ALIGNMENTS = ['top', 'middle', 'bottom'];

    /**
     * Render the CTA Banner Section block
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
        $heading_level = get_field('heading_level');
        $description = get_field('description');
        $buttons = self::formatButtons(get_field('buttons'));
        $button_alignment = get_field('button_alignment');
        $button_alignment_mobile = get_field('button_alignment_mobile');
        $content_alignment = get_field('content_alignment');
        $content_alignment_mobile = get_field('content_alignment_mobile');
        $dot_alignment = get_field('dot_grid_canvas_alignment');
        $dot_alignment_mobile = get_field('dot_grid_canvas_alignment_mobile');
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        $description = is_string($description) ? trim($description) : '';

        // Blocks saved before these fields existed have no value for them, so they
        // keep the original look: centred, the button below, dots at both edges.
        // An empty button_alignment means "below"; an empty mobile dot alignment
        // means the default (both edges).
        $button_alignment = in_array($button_alignment, self::BUTTON_SIDES, true) ? $button_alignment : 'below';
        $button_alignment_mobile = in_array($button_alignment_mobile, self::MOBILE_BUTTON_POSITIONS, true) ? $button_alignment_mobile : 'bottom';
        $content_alignment = in_array($content_alignment, self::ALIGNMENTS, true) ? $content_alignment : 'center';
        $content_alignment_mobile = in_array($content_alignment_mobile, self::ALIGNMENTS, true) ? $content_alignment_mobile : 'center';
        $dot_alignment = in_array($dot_alignment, self::ALIGNMENTS, true) ? $dot_alignment : 'center';
        $dot_alignment_mobile = in_array($dot_alignment_mobile, self::DOT_MOBILE_ALIGNMENTS, true) ? $dot_alignment_mobile : 'edges';

        // Nothing to show on the front end without a heading, description or a button
        if (!$heading_text && !$description && !$buttons) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('CTA Banner Section: add a heading, a description or a button.', 'sage') . '</p>';
            }
            return;
        }

        if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
            $heading_level = 'h2';
        }

        // Generate unique block ID
        $blockId = 'ctabs-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.cta-banner-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'heading_text'  => $heading_text,
            'heading_level' => $heading_level,
            'description'   => $description,
            'buttons'       => $buttons,
            'button_alignment'         => $button_alignment,
            'button_alignment_mobile'  => $button_alignment_mobile,
            'content_alignment'        => $content_alignment,
            'content_alignment_mobile' => $content_alignment_mobile,
            'dot_alignment'            => $dot_alignment,
            'dot_alignment_mobile'     => $dot_alignment_mobile,
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
     * Normalise the buttons repeater, skipping rows without a link URL.
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

            $title = trim($link['title'] ?? '');
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
                'aria_label'  => trim($row['aria_label'] ?? '') ?: $title,
                'event_label' => trim($row['button_google_event_label'] ?? ''),
                'class'       => implode(' ', array_merge(['btn', 'ctabs-btn'], $extra_classes)),
            ];
        }

        return $buttons;
    }
}
