<?php

namespace App\Blocks;

class FaqsAccordionSection
{
    /**
     * Render the FAQs Accordion Section block
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
        $faqs = self::formatFaqs(get_field('faqs'));
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        // Nothing to show on the front end without at least one question
        if (!$faqs) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('FAQs Accordion Section: add at least one question.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'faqas-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.faqs-accordion-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'faqs'          => $faqs,
            'section_style' => $section_styles ? implode('; ', $section_styles) . ';' : '',
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
     * Normalise the faqs repeater, skipping rows without a question.
     * The answer (a plain Text Area) is escaped, then split into paragraphs.
     */
    private static function formatFaqs($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $faqs = [];
        foreach ($rows as $row) {
            $question = trim((string) ($row['question'] ?? ''));
            if ($question === '') {
                continue;
            }

            $answer = trim((string) ($row['answer'] ?? ''));

            $faqs[] = [
                'question' => $question,
                'answer'   => $answer !== '' ? wpautop(esc_html($answer)) : '',
            ];
        }

        return $faqs;
    }
}
