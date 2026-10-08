<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

/**
 * Show the page holding the Search Results Section block for `?s=keyword`.
 *
 * - `/?s=term` (bare site search) is served by the published page that contains the
 *   block, instead of the default search.php list.
 * - `/that-page/?s=term` stays a normal page rather than turning into a site search.
 * In both cases `s` is removed from the query vars only; the block reads `$_GET['s']`.
 * Other searches (admin, custom post_type such as WooCommerce products) are untouched.
 */
add_filter('request', function ($vars) {
    if (is_admin() || !isset($vars['s']) || !empty($vars['post_type'])) {
        return $vars;
    }

    if (!empty($vars['pagename']) || !empty($vars['page_id'])) {
        unset($vars['s']);
        return $vars;
    }

    $page_id = search_results_page_id();
    if ($page_id && empty($vars['p']) && empty($vars['name'])) {
        unset($vars['s']);
        $vars['page_id'] = $page_id;
    }

    return $vars;
});

/**
 * ID of the published page that contains the Search Results Section block (0 if none).
 */
function search_results_page_id(): int
{
    $cached = get_transient('bravo_search_results_page_id');
    if ($cached !== false) {
        return (int) $cached;
    }

    global $wpdb;
    $id = (int) $wpdb->get_var(
        "SELECT ID FROM {$wpdb->posts}
         WHERE post_type = 'page' AND post_status = 'publish'
           AND post_content LIKE '%wp:acf/search-results-section%'
         ORDER BY ID ASC LIMIT 1"
    );

    set_transient('bravo_search_results_page_id', $id, DAY_IN_SECONDS);

    return $id;
}

// Re-detect the page whenever any content is saved
add_action('save_post', function () {
    delete_transient('bravo_search_results_page_id');
});
