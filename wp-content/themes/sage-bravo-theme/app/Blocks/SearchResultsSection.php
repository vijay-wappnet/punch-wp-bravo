<?php

namespace App\Blocks;

class SearchResultsSection
{
    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Post types searched. Missing ones (e.g. client-stories when its plugin
     * is off) are skipped so the query never targets an unregistered type.
     */
    private const POST_TYPES = ['page', 'post', 'client-stories'];

    private const RESULTS_PER_PAGE = 9;

    /**
     * Query-string keys. WordPress' own `s` keyword is used
     * (see the `request` filter in app/filters.php for how /?s= reaches the page).
     */
    private const SEARCH_PARAM = 's';
    private const PAGE_PARAM = 'spage';

    /**
     * Render the Search Results Section block
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
        $main_title = trim((string) get_field('main_search_title'));
        $heading_level = get_field('heading_level');
        $placeholder = trim((string) get_field('search_placeholder'));
        $button = self::formatButton(get_field('search_button'));
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
            $heading_level = 'h2';
        }

        // Search term and page number come from the query string
        $search_term = self::searchTerm();
        $current_page = max(1, absint($_GET[self::PAGE_PARAM] ?? 1));

        $results = [];
        $pagination = '';
        if ($search_term !== '' && !$is_preview) {
            [$results, $total_pages] = self::search($search_term, $current_page, $post_id);
            $pagination = self::pagination($total_pages, $current_page, $search_term);
        }

        // Where the form goes: the button link when set, otherwise the site root, so the
        // URL is /?s=keyword (the request filter in app/filters.php serves this block's page)
        $form_action = $button['url'] ?: home_url('/');

        // Generate unique block ID
        $blockId = 'srs-' . ($block['id'] ?? uniqid());

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
        echo view('blocks.search-results-section', [
            'blockId'       => $blockId,
            'responsiveCss' => $responsiveCss,
            'main_title'    => $main_title,
            'heading_level' => $heading_level,
            'placeholder'   => $placeholder !== '' ? $placeholder : __('Search', 'sage'),
            'button'        => $button,
            'form_action'   => $form_action,
            'search_param'  => self::SEARCH_PARAM,
            'search_term'   => $search_term,
            'results'       => $results,
            'pagination'    => $pagination,
            'section_style' => $section_styles ? implode('; ', $section_styles) . ';' : '',
            'is_preview'    => $is_preview,
        ]);
    }

    /**
     * The submitted keyword, trimmed and unslashed.
     */
    private static function searchTerm(): string
    {
        $term = $_GET[self::SEARCH_PARAM] ?? '';

        return is_string($term) ? trim(sanitize_text_field(wp_unslash($term))) : '';
    }

    /**
     * Search pages, posts and client stories.
     *
     * @return array{0: array, 1: int} [results, total pages]
     */
    private static function search(string $term, int $paged, $post_id): array
    {
        $post_types = array_values(array_filter(self::POST_TYPES, 'post_type_exists'));
        if (!$post_types) {
            return [[], 0];
        }

        $args = [
            's'                   => $term,
            'post_type'           => $post_types,
            'post_status'         => 'publish',
            'posts_per_page'      => self::RESULTS_PER_PAGE,
            'paged'               => $paged,
            'ignore_sticky_posts' => true,
        ];

        // The page showing the results shouldn't list itself
        $current_id = is_numeric($post_id) && $post_id ? (int) $post_id : (is_singular() ? get_the_ID() : 0);
        if ($current_id) {
            $args['post__not_in'] = [$current_id];
        }

        $query = new \WP_Query($args);

        $results = [];
        foreach ($query->posts as $post) {
            $results[] = [
                'title' => html_entity_decode(get_the_title($post), ENT_QUOTES, 'UTF-8'),
                'link'  => get_permalink($post),
            ];
        }

        return [$results, (int) $query->max_num_pages];
    }

    /**
     * « 1 2 3 » links that keep the search term; empty when there is one page or none.
     */
    private static function pagination(int $total_pages, int $current_page, string $term): string
    {
        if ($total_pages < 2) {
            return '';
        }

        $links = paginate_links([
            'base'      => esc_url_raw(add_query_arg(self::PAGE_PARAM, '%#%', remove_query_arg(self::PAGE_PARAM))),
            'format'    => '',
            'current'   => min($current_page, $total_pages),
            'total'     => $total_pages,
            'prev_text' => '«<span class="visually-hidden">' . esc_html__('Previous page', 'sage') . '</span>',
            'next_text' => '»<span class="visually-hidden">' . esc_html__('Next page', 'sage') . '</span>',
            'mid_size'  => 2,
            'add_args'  => [self::SEARCH_PARAM => $term],
        ]);

        return $links ?: '';
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
     * Normalise the button group. Always returns an array: the search button
     * is a submit button, so the link is optional (it only sets the form action).
     */
    private static function formatButton($group): array
    {
        $group = is_array($group) ? $group : [];
        $link = is_array($group['button_link'] ?? null) ? $group['button_link'] : [];

        // button_class may hold several space-separated classes
        $extra_classes = array_filter(array_map(
            'sanitize_html_class',
            preg_split('/\s+/', trim($group['button_class'] ?? ''))
        ));

        return [
            'url'         => $link['url'] ?? '',
            'title'       => trim($link['title'] ?? '') ?: __('Search', 'sage'),
            'aria_label'  => trim($group['aria_label'] ?? ''),
            'event_label' => trim($group['button_google_event_label'] ?? ''),
            'class'       => implode(' ', array_merge(['srs-btn'], $extra_classes)),
        ];
    }
}
