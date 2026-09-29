<?php
/**
 * Plugin Name: Punch ACF Compact Editor
 * Description: Safe, compact ACF Block v3 previews with inline editing and a generic field fallback.
 * Version: 0.2.4
 * Requires PHP: 8.0
 * Requires at least: 7.1
 * Author: Punch Hospitality
 * Text Domain: punch-acf-compact-editor
 */

if (! defined('ABSPATH')) {
    exit;
}

define('PUNCH_ACF_COMPACT_EDITOR_VERSION', '0.2.4');
define('PUNCH_ACF_COMPACT_EDITOR_FILE', __FILE__);
define('PUNCH_ACF_COMPACT_EDITOR_PATH', plugin_dir_path(__FILE__));
define('PUNCH_ACF_COMPACT_EDITOR_URL', plugin_dir_url(__FILE__));

require_once PUNCH_ACF_COMPACT_EDITOR_PATH . 'src/Editor.php';
require_once PUNCH_ACF_COMPACT_EDITOR_PATH . 'src/functions.php';

add_action('plugins_loaded', static function (): void {
    $acf_ready = function_exists('acf_register_block_type')
        && function_exists('acf_inline_toolbar_editing_attrs')
        && function_exists('acf_inline_text_editing_attrs');

    if ($acf_ready) {
        \Punch\ACFCompactEditor\Editor::boot();
        return;
    }

    add_action('admin_notices', static function (): void {
        if (! current_user_can('activate_plugins')) {
            return;
        }
        echo '<div class="notice notice-warning"><p>'
            . esc_html__('Punch ACF Compact Editor is inactive because a compatible ACF PRO Block v3 installation was not detected.', 'punch-acf-compact-editor')
            . '</p></div>';
    });
}, 20);
