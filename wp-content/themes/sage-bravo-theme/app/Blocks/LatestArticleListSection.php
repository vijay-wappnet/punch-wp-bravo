<?php

namespace App\Blocks;

class LatestArticleListSection
{
    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Allowed values for the select_post_type, orderby and order fields.
     */
    private const POST_TYPES = ['post'];
    private const ORDERBY = ['date', 'title', 'ID'];
    private const ORDER = ['DESC', 'ASC'];

    /**
     * Number of articles shown (one row of three on desktop).
     */
    private const POSTS_PER_PAGE = 3;

    /**
     * Render the Latest Article List Section block
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
        $button = self::formatButton(get_field('button'));
        $post_type = get_field('select_post_type');
        $orderby = get_field('orderby');
        $order = get_field('order');
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
            $heading_level = 'h2';
        }

        $articles = self::getArticles(
            in_array($post_type, self::POST_TYPES, true) ? $post_type : 'post',
            in_array($orderby, self::ORDERBY, true) ? $orderby : 'date',
            in_array($order, self::ORDER, true) ? $order : 'DESC',
            $post_id
        );

        // Nothing to show on the front end without any articles
        if (!$articles) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Latest Article List Section: no published articles found.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'lals-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.latest-article-list-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'heading_text'  => $heading_text,
            'heading_level' => $heading_level,
            'button'        => $button,
            'articles'      => $articles,
            'section_style' => $section_styles ? implode('; ', $section_styles) . ';' : '',
            'is_preview'    => $is_preview,
        ]);
    }

    /**
     * Latest published articles, excluding the post the block is placed on.
     */
    private static function getArticles(string $post_type, string $orderby, string $order, $post_id): array
    {
        $args = [
            'post_type'           => $post_type,
            'post_status'         => 'publish',
            'posts_per_page'      => self::POSTS_PER_PAGE,
            'orderby'             => $orderby,
            'order'               => $order,
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ];

        $current_id = is_numeric($post_id) && $post_id ? (int) $post_id : (is_singular() ? get_the_ID() : 0);
        if ($current_id) {
            $args['post__not_in'] = [$current_id];
        }

        $articles = [];
        foreach (get_posts($args) as $post) {
            $title = html_entity_decode(get_the_title($post), ENT_QUOTES, 'UTF-8');
            $thumbnail_id = get_post_thumbnail_id($post);

            $articles[] = [
                'title' => $title,
                'url'   => get_permalink($post),
                // Decorative: the title is right below the image inside the same link
                'image' => $thumbnail_id ? wp_get_attachment_image($thumbnail_id, 'large', false, [
                    'class'    => 'latest-article-list-section__image',
                    'alt'      => '',
                    'loading'  => 'lazy',
                    'decoding' => 'async',
                    'sizes'    => '(min-width: 1200px) 360px, (min-width: 992px) 300px, (min-width: 576px) 44vw, 64vw',
                ]) : '',
            ];
        }

        return $articles;
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
     * Normalise the button group; null when it has no link URL.
     * button_text wins over the link's own title.
     */
    private static function formatButton($group): ?array
    {
        $link = is_array($group) ? ($group['button_link'] ?? null) : null;
        if (!is_array($link) || empty($link['url'])) {
            return null;
        }

        $title = trim($group['button_text'] ?? '') ?: trim($link['title'] ?? '') ?: __('See all Articles', 'sage');
        $target = $link['target'] ?? '';

        // button_class may hold several space-separated classes
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
            'class'       => implode(' ', array_merge(['latest-article-list-section__btn'], $extra_classes)),
        ];
    }
}
