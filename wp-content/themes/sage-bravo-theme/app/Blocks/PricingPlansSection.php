<?php

namespace App\Blocks;

class PricingPlansSection
{
    /**
     * Allowed values for content_alignment / content_alignment_mobile.
     */
    private const ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Render the Pricing Plans Section block
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
        $plans = self::formatPlans(get_field('pricing_plans'));
        $content_alignment = get_field('content_alignment') ?: 'left';
        $content_alignment_mobile = get_field('content_alignment_mobile') ?: 'left';
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        if (!in_array($content_alignment, self::ALIGNMENTS, true)) {
            $content_alignment = 'left';
        }
        if (!in_array($content_alignment_mobile, self::ALIGNMENTS, true)) {
            $content_alignment_mobile = 'left';
        }

        // Nothing to show on the front end without a plan
        if (!$plans) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Pricing Plans Section: add a pricing plan.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'pps-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.pricing-plans-section', [
            'blockId'                  => $blockId,
            'responsiveCss'            => $responsiveCss,
            'plans'                    => $plans,
            'columns'                  => self::columnClasses(count($plans)),
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
     * Bootstrap row-cols classes for the number of plans: the cards stack on
     * mobile, then share the row (three across for three plans, and so on).
     */
    private static function columnClasses(int $count): string
    {
        if ($count <= 1) {
            return 'row-cols-1';
        }

        if ($count === 2) {
            return 'row-cols-1 row-cols-md-2';
        }

        if ($count === 3) {
            return 'row-cols-1 row-cols-md-3';
        }

        if ($count === 4) {
            return 'row-cols-1 row-cols-md-2 row-cols-lg-4';
        }

        return 'row-cols-1 row-cols-md-2 row-cols-lg-3';
    }

    /**
     * Only plain colour values are used as a card / tag background colour.
     */
    private static function formatColor($color): string
    {
        $color = trim((string) $color);

        $isHex = (bool) preg_match('/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $color);
        $isRgb = (bool) preg_match('/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(,\s*[\d.]+\s*)?\)$/i', $color);

        return ($isHex || $isRgb) ? $color : '';
    }

    /**
     * Normalise the pricing_plans repeater (order kept), skipping plans with
     * nothing to show.
     */
    private static function formatPlans($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $plans = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['plan_name'] ?? ''));
            $price = trim((string) ($row['price'] ?? ''));
            $period = trim((string) ($row['price_period'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));
            $features = self::formatFeatures($row['features'] ?? null);
            $button = self::formatButton($row['button'] ?? null);

            if (!$name && !$price && !$description && !$features && !$button) {
                continue;
            }

            // The popular tag needs its label; without one there is nothing to show
            $popular_label = trim((string) ($row['popular_label'] ?? ''));
            $is_popular = !empty($row['is_popular']) && $popular_label !== '';

            $card_color = self::formatColor($row['card_background_color'] ?? '');
            $tag_color = self::formatColor($row['popular_tag_bg_color'] ?? '');

            $plans[] = [
                'name'          => $name,
                'price'         => $price,
                'period'        => $period,
                'description'   => $description,
                'features'      => $features,
                'is_popular'    => $is_popular,
                'popular_label' => $popular_label,
                'tag_style'     => $tag_color ? 'background-color: ' . $tag_color . ';' : '',
                'card_style'    => $card_color ? 'background-color: ' . $card_color . ';' : '',
                'button'        => $button,
            ];
        }

        return $plans;
    }

    /**
     * Normalise the features repeater, skipping empty rows.
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
     * Normalise a plan's button group; null when it has no link URL.
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
            'class'       => implode(' ', array_merge(['btn', 'pps-btn'], $extra_classes ?: ['btn-red-fusion'])),
            'arrow_span'  => in_array('btn-trans-border-arrow', $extra_classes, true),
        ];
    }
}
