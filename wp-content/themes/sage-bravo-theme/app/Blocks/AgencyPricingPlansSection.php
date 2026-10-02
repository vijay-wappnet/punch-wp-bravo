<?php

namespace App\Blocks;

class AgencyPricingPlansSection
{
    /**
     * Allowed values for content_alignment / content_alignment_mobile.
     */
    private const ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Render the Agency Pricing Plans Section block (one plan card)
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
        $plan_name = trim((string) get_field('plan_name'));
        $price = trim((string) get_field('price'));
        $price_period = trim((string) get_field('price_period'));
        $description = trim((string) get_field('description'));
        $features = self::formatFeatures(get_field('features'));
        $button = self::formatButton(get_field('button'));
        $content_alignment = get_field('content_alignment') ?: 'center';
        $content_alignment_mobile = get_field('content_alignment_mobile') ?: 'center';
        $card_background_color = self::formatColor(get_field('card_background_color'));
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        if (!in_array($content_alignment, self::ALIGNMENTS, true)) {
            $content_alignment = 'center';
        }
        if (!in_array($content_alignment_mobile, self::ALIGNMENTS, true)) {
            $content_alignment_mobile = 'center';
        }

        // Nothing to show on the front end without any content
        if (!$plan_name && !$price && !$description && !$features && !$button) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Agency Pricing Plans Section: add a plan name, price or features.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'apps-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.agency-pricing-plans-section', [
            'blockId'                  => $blockId,
            'responsiveCss'            => $responsiveCss,
            'plan_name'                => $plan_name,
            'price'                    => $price,
            'price_period'             => $price_period,
            'description'              => $description,
            'features'                 => $features,
            'button'                   => $button,
            'content_alignment'        => $content_alignment,
            'content_alignment_mobile' => $content_alignment_mobile,
            'card_style'               => $card_background_color ? 'background-color: ' . $card_background_color . ';' : '',
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
     * Only a plain colour value is used as the card background colour.
     */
    private static function formatColor($color): string
    {
        $color = trim((string) $color);

        $isHex = (bool) preg_match('/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $color);
        $isRgb = (bool) preg_match('/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(,\s*[\d.]+\s*)?\)$/i', $color);

        return ($isHex || $isRgb) ? $color : '';
    }

    /**
     * Normalise the features repeater (order kept), skipping empty rows.
     */
    private static function formatFeatures($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $features = [];
        foreach ($rows as $row) {
            $text = trim((string) ($row['feature_text'] ?? ''));
            if ($text !== '') {
                $features[] = $text;
            }
        }

        return $features;
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
            'class'       => implode(' ', array_merge(['btn', 'apps-btn'], $extra_classes ?: ['btn-red-fusion'])),
            'arrow_span'  => in_array('btn-trans-border-arrow', $extra_classes, true),
        ];
    }
}
