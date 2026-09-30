<?php

namespace App\Blocks;

class TestimonialSliderSection
{
    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Render the Testimonial Slider Section block
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
        $testimonials = get_field('testimonials');
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        // Generate unique block ID
        $blockId = 'tss-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.testimonial-slider-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'testimonials'  => self::formatTestimonials($testimonials),
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
     * Normalise the testimonials repeater, skipping empty rows.
     * Quote marks are added by the template, so any typed around the text are stripped.
     */
    private static function formatTestimonials($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $testimonials = [];
        foreach ($rows as $row) {
            $quote = trim($row['heading_text'] ?? '');
            $quote = trim(preg_replace('/^["“”\']+|["“”\']+$/u', '', $quote));
            $person = trim($row['person_job_title'] ?? '');
            $logo = self::formatImage($row['company_logo'] ?? null);

            if (!$quote && !$person && !$logo) {
                continue;
            }

            $heading_level = $row['heading_level'] ?? 'p';
            if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
                $heading_level = 'p';
            }

            $testimonials[] = [
                'quote'         => $quote,
                'heading_level' => $heading_level,
                'person'        => $person,
                'logo'          => $logo,
            ];
        }

        return $testimonials;
    }
}
