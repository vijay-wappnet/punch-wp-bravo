<?php

namespace App\Blocks;

class ArticlePostListingSection
{
    /**
     * Allowed values for the select_post_type, orderby and order fields.
     */
    private const POST_TYPES = ['post'];
    private const ORDERBY = ['date', 'title', 'ID'];
    private const ORDER = ['DESC', 'ASC'];

    /**
     * Articles shown per page when the posts_per_page fields are empty.
     */
    private const DEFAULT_PER_PAGE = 4;

    /**
     * All matching articles are rendered and paged in the browser (the page
     * size differs between desktop and mobile), so the query is capped.
     */
    private const MAX_ARTICLES = 100;

    /**
     * Render the Article Post Listing Section block
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
        $post_type = get_field('select_post_type');
        $per_page = self::count(get_field('posts_per_page'), self::DEFAULT_PER_PAGE);
        $per_page_mobile = self::count(get_field('posts_per_page_mobile'), $per_page);
        $orderby = get_field('orderby');
        $order = get_field('order');
        $section_bg_color = get_field('section_bg_color');
        $margin = get_field('margin');
        $padding = get_field('padding');

        $articles = self::getArticles(
            in_array($post_type, self::POST_TYPES, true) ? $post_type : 'post',
            in_array($orderby, self::ORDERBY, true) ? $orderby : 'date',
            in_array($order, self::ORDER, true) ? $order : 'DESC',
            $post_id
        );

        // Nothing to show on the front end without any articles
        if (!$articles) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Article Post Listing Section: no published articles found.', 'sage') . '</p>';
            }
            return;
        }

        // Generate unique block ID
        $blockId = 'apls-' . ($block['id'] ?? uniqid());

        // Generate responsive CSS for margin and padding
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        // Inline style is limited to the background color
        $section_style = $section_bg_color ? 'background-color: ' . $section_bg_color . ';' : '';

        // Render the Blade template with data using view helper
        echo view('blocks.article-post-listing-section', [
            'blockId'         => $blockId,
            'responsiveCss'   => $responsiveCss,
            'articles'        => $articles,
            'per_page'        => $per_page,
            'per_page_mobile' => $per_page_mobile,
            'section_style'   => $section_style,
            'is_preview'      => $is_preview,
        ]);
    }

    /**
     * A positive whole number from a text field, or the fallback.
     */
    private static function count($value, int $fallback): int
    {
        $number = is_numeric($value) ? (int) $value : 0;

        return $number > 0 ? $number : $fallback;
    }

    /**
     * Published articles with their category, title, featured image and permalink.
     */
    private static function getArticles(string $post_type, string $orderby, string $order, $post_id): array
    {
        $args = [
            'post_type'           => $post_type,
            'post_status'         => 'publish',
            'posts_per_page'      => self::MAX_ARTICLES,
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
            $categories = get_the_category($post->ID);

            $articles[] = [
                'title'    => $title,
                'url'      => get_permalink($post),
                'category' => $categories ? html_entity_decode($categories[0]->name, ENT_QUOTES, 'UTF-8') : '',
                // Decorative: the title is right below the image inside the same link
                'image'    => $thumbnail_id ? wp_get_attachment_image($thumbnail_id, 'large', false, [
                    'class'    => 'article-post-card__img',
                    'alt'      => '',
                    'loading'  => 'lazy',
                    'decoding' => 'async',
                    'sizes'    => '(min-width: 1200px) 540px, (min-width: 768px) 45vw, 100vw',
                ]) : '',
            ];
        }

        return $articles;
    }
}
