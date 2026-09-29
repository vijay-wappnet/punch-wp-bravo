<?php

namespace Punch\ACFCompactEditor;

final class Editor
{
    private static array $blocks = [];

    private static ?array $context = null;

    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::$booted = true;
        add_filter('acf/register_block_type_args', [self::class, 'register_block'], 20);
        add_filter('acf/pre_render_fields', [self::class, 'filter_form_fields'], 10, 2);
        add_filter('acf/blocks/top_toolbar_fields', [self::class, 'top_toolbar_fields'], 10, 2);
        add_filter('block_editor_settings_all', [self::class, 'editor_settings'], 5);
        add_action('enqueue_block_editor_assets', [self::class, 'enqueue_editor_assets']);
        add_filter('script_loader_src', [self::class, 'dimensions_script'], 10, 2);
        add_action('acf/input/admin_enqueue_scripts', [self::class, 'enqueue_dimensions_style'], 20);
        add_action('admin_notices', [self::class, 'configuration_notice']);
    }

    public static function register_block(array $args): array
    {
        $managed = self::theme_supports_block($args);
        $managed = (bool) apply_filters('punch/acf_compact_editor/manage_block', $managed, $args);
        if (! $managed || empty($args['name'])) {
            return $args;
        }

        $name = self::normalise_name($args['name']);
        $plugin_callback = [self::class, 'render_block'];
        if (($args['render_callback'] ?? null) === $plugin_callback) {
            return $args;
        }

        self::$blocks[$name] = [
            'render_callback' => $args['render_callback'] ?? null,
            'render_template' => $args['render_template'] ?? null,
            'enqueue_assets' => $args['enqueue_assets'] ?? null,
            'enqueue_style' => $args['enqueue_style'] ?? null,
            'enqueue_script' => $args['enqueue_script'] ?? null,
        ];

        $args['render_callback'] = $plugin_callback;
        $args['render_template'] = false;
        $args['enqueue_style'] = false;
        $args['enqueue_script'] = false;
        // Defer assets until render_block supplies the actual $is_preview value.
        // ACF v3 sets acf_doing_block_preview during frontend rendering too.
        $args['enqueue_assets'] = false;
        $args['acf_block_version'] = 3;
        $args['auto_inline_editing'] = false;
        $args['hide_fields_in_sidebar'] = false;
        $args['expanded_editor_buttons'] = true;
        $args['expanded_editor_button_text'] = apply_filters(
            'punch/acf_compact_editor/edit_all_label',
            __('Edit all fields', 'punch-acf-compact-editor'),
            $args
        );
        $args['mode'] = 'preview';

        return $args;
    }

    public static function render_block($block, $content = '', $is_preview = false, $post_id = 0, $wp_block = null, $context = []): void
    {
        $name = self::normalise_name($block['name'] ?? '');
        $original = self::$blocks[$name] ?? null;

        if (! $is_preview) {
            if ($original) {
                // Give native ACF and the theme their original asset/template settings.
                $block = array_replace($block, $original);
                acf_enqueue_block_type_assets($block);
            }
            if (is_callable($original['render_callback'] ?? null)) {
                call_user_func($original['render_callback'], $block, $content, false, $post_id, $wp_block, $context);
                return;
            }

            if (! empty($original['render_template'])) {
                $block['render_template'] = $original['render_template'];
                do_action('acf_block_render_template', $block, $content, false, $post_id, $wp_block, $context);
            }
            return;
        }

        self::render_preview($block, $content, $post_id, $wp_block, $context);
    }

    public static function render_preview($block, $content = '', $post_id = 0, $wp_block = null, $wp_context = []): void
    {
        if (! empty($block['data']['preview_image_help'])) {
            echo '<img src="' . esc_url($block['data']['preview_image_help']) . '" alt="" style="display:block;height:auto;width:100%">';
            return;
        }

        $previous = self::$context;
        $fields = self::visible_fields(self::fields($block));
        self::$context = [
            'block' => $block,
            'fields' => $fields,
            'used' => [],
        ];

        $base = preg_replace('#^acf/#', '', (string) ($block['name'] ?? 'acf-block'));
        $id = ! empty($block['anchor'])
            ? $block['anchor']
            : $base . '-' . ($block['id'] ?? wp_unique_id('block-'));
        $class = trim('web-block ' . $base . ' ' . ($block['className'] ?? '') . (! empty($block['align']) ? ' align' . $block['align'] : ''));
        $args = [
            'block' => $block,
            'id' => $id,
            'class' => $class,
            'block_name' => ucwords(str_replace('-', ' ', $base)),
            'content' => function_exists('get_fields') ? (get_fields() ?: []) : [],
            'base' => $base,
            'is_preview' => true,
            'inline_fields' => array_keys($fields),
            'post_id' => $post_id,
            'wp_block' => $wp_block,
            'context' => $wp_context,
        ];

        $buffer_level = ob_get_level();
        try {
            $renderer = apply_filters('punch/acf_compact_editor/preview_renderer', null, $block, $args);
            $rendered = '';
            if (is_callable($renderer)) {
                ob_start();
                $result = call_user_func($renderer, $args, $block);
                $rendered = (string) ob_get_clean();
                if (is_string($result) || $result instanceof \Stringable) {
                    $rendered .= (string) $result;
                }
            }

            if ($rendered !== '') {
                echo $rendered; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            } else {
                self::$context['used'] = [];
                self::render_generic($args);
            }
        } catch (\Throwable $error) {
            while (ob_get_level() > $buffer_level) {
                ob_end_clean();
            }
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Punch ACF Compact Editor: ' . $error->getMessage());
            }
            self::$context['used'] = [];
            self::render_generic($args);
        } finally {
            self::$context = $previous;
        }
    }

    private static function render_generic(array $args): void
    {
        extract($args, EXTR_SKIP);
        include PUNCH_ACF_COMPACT_EDITOR_PATH . 'views/preview.php';
    }

    public static function fields(array $block): array
    {
        if (! function_exists('acf_get_block_fields')) {
            return [];
        }

        $result = [];
        $seen = [];
        foreach (acf_get_block_fields($block) as $field) {
            $name = $field['name'] ?? '';
            if ($name === '' || in_array($field['type'] ?? '', ['accordion', 'tab', 'message'], true)) {
                continue;
            }
            if (isset($seen[$name])) {
                unset($result[$name]);
                continue;
            }
            $seen[$name] = true;
            $result[$name] = $field;
        }

        return apply_filters('punch/acf_compact_editor/editor_fields', $result, $block);
    }

    public static function field_value(array $field)
    {
        if (! function_exists('acf_get_value') || ! function_exists('acf_get_valid_post_id')) {
            return null;
        }

        return acf_get_value(acf_get_valid_post_id(false), $field);
    }

    public static function visible_fields(array $fields, ?callable $read_value = null): array
    {
        $read_value ??= [self::class, 'field_value'];
        return array_filter($fields, function ($field) use ($fields, $read_value) {
            if (empty($field['conditional_logic'])) {
                return true;
            }

            foreach ($field['conditional_logic'] as $rules) {
                $matches = true;
                foreach ($rules as $rule) {
                    $controllers = array_filter($fields, function ($candidate) use ($field, $rule) {
                        if (($candidate['key'] ?? '') === ($rule['field'] ?? '')) {
                            return true;
                        }
                        return ! empty($field['_clone'])
                            && ($candidate['_clone'] ?? null) === $field['_clone']
                            && ($candidate['__key'] ?? null) === ($rule['field'] ?? '');
                    });
                    $operator = $rule['operator'] ?? '';
                    $supported_types = ['text', 'textarea', 'number', 'range', 'email', 'url', 'select', 'checkbox', 'radio', 'button_group', 'true_false'];
                    if (count($controllers) !== 1 || ! in_array($operator, ['==', '!=', '!=='], true)) {
                        continue;
                    }
                    $controller = reset($controllers);
                    if (! in_array($controller['type'] ?? '', $supported_types, true)) {
                        continue;
                    }

                    $value = $read_value($controller);
                    if (($value === null || $value === false || $value === '')
                        && empty($controller['allow_null'])
                        && in_array($controller['type'], ['radio', 'button_group'], true)
                        && ! empty($controller['choices'])) {
                        $value = array_key_first($controller['choices']);
                    }
                    if (is_object($value)) {
                        continue;
                    }
                    $values = array_map(
                        static fn ($item) => is_bool($item) ? ($item ? '1' : '0') : (string) $item,
                        (array) ($value ?? '')
                    );
                    $equal = in_array((string) ($rule['value'] ?? ''), $values, true);
                    if (($operator === '==' && ! $equal) || ($operator !== '==' && $equal)) {
                        $matches = false;
                        break;
                    }
                }
                if ($matches) {
                    return true;
                }
            }

            return false;
        });
    }

    public static function context(?array $replacement = null, bool $replace = false): ?array
    {
        if ($replace) {
            self::$context = $replacement;
        }
        return self::$context;
    }

    public static function mark_field(string $name): void
    {
        if (self::$context !== null) {
            self::$context['used'][$name] = true;
        }
    }

    public static function remaining_fields(): array
    {
        return self::$context ? array_diff_key(self::$context['fields'], self::$context['used']) : [];
    }

    public static function component_fields(string $field_name): array
    {
        $fields = self::$context['fields'] ?? [];
        $clone = $fields[$field_name]['_clone'] ?? null;
        if (! $clone) {
            return [];
        }
        return array_filter($fields, static fn ($field) => ($field['_clone'] ?? null) === $clone);
    }

    public static function field_behavior(array $field): string
    {
        $default = in_array($field['type'] ?? '', ['text', 'textarea'], true) ? 'inline_text' : 'toolbar';
        return (string) apply_filters(
            'punch/acf_compact_editor/field_behavior',
            $default,
            $field,
            self::$context['block'] ?? []
        );
    }

    public static function inline_text_attrs(bool $is_preview, string $field_name, array $args = []): string
    {
        if (! $is_preview || ! function_exists('acf_inline_text_editing_attrs')) {
            return '';
        }
        $field = self::$context['fields'][$field_name] ?? null;
        if (! $field || self::field_behavior($field) !== 'inline_text') {
            return '';
        }

        $siblings = array_filter(
            self::component_fields($field_name),
            static fn ($sibling) => ! in_array($sibling['type'] ?? '', ['text', 'textarea'], true)
        );
        unset($siblings[$field_name]);
        $text_args = $args;
        if ($siblings) {
            unset($text_args['toolbar_title'], $text_args['toolbar_icon']);
        }
        $attrs = acf_inline_text_editing_attrs($field_name, $text_args);
        if ($attrs) {
            self::mark_field($field_name);
        }
        if ($attrs && $siblings) {
            $attrs .= ' ' . self::toolbar_attrs(true, array_keys($siblings), $args);
        }
        return $attrs;
    }

    public static function toolbar_attrs(bool $is_preview, $fields, array $args = []): string
    {
        if (! $is_preview || ! function_exists('acf_inline_toolbar_editing_attrs')) {
            return '';
        }

        $resolved = [];
        $field_names = [];
        foreach ((array) $fields as $field) {
            $name = is_array($field) ? ($field['field_name'] ?? '') : $field;
            if (! is_string($name) || $name === '' || ! isset(self::$context['fields'][$name])) {
                continue;
            }
            $definition = self::$context['fields'][$name];
            $descriptor = is_array($field) ? $field : ['field_name' => $name];
            if (! isset($descriptor['use_expanded_editor'])
                && in_array($definition['type'] ?? '', ['group', 'clone', 'repeater', 'flexible_content'], true)) {
                $descriptor['use_expanded_editor'] = true;
            }
            $lookup = $definition['__key'] ?? $definition['key'] ?? $name;
            $descriptor['field_name'] = $lookup;
            $descriptor['field_label'] ??= $definition['label'] ?? $name;
            $field_names[$lookup][] = $name;
            $resolved[] = $descriptor;
        }
        if (! $resolved) {
            return '';
        }

        $args['uid'] = $args['uid'] ?? acf_get_valid_post_id(false) . '-' . implode('-', array_merge(...array_values($field_names)));
        $args['return_array'] = true;
        $attributes = acf_inline_toolbar_editing_attrs($resolved, $args);
        if (! $attributes) {
            return '';
        }
        $processed = json_decode(html_entity_decode($attributes['data-acf-inline-fields'], ENT_QUOTES, 'UTF-8'), true);
        if (! $processed) {
            return '';
        }
        foreach ($processed as &$field) {
            $field['fieldName'] = array_shift($field_names[$field['fieldName']]);
            self::mark_field($field['fieldName']);
        }
        unset($field);
        $attributes['data-acf-inline-fields'] = esc_attr(wp_json_encode($processed));

        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= $key . '="' . $value . '" ';
        }
        return rtrim($html);
    }

    public static function expanded_attrs(bool $is_preview, string $field_name, array $args = []): string
    {
        return self::toolbar_attrs($is_preview, [[
            'field_name' => $field_name,
            'use_expanded_editor' => true,
        ]], $args);
    }

    public static function field_summary(array $field): string
    {
        $value = self::field_value($field);
        if (($field['type'] ?? '') === 'true_false') {
            return $value ? __('On', 'punch-acf-compact-editor') : __('Off', 'punch-acf-compact-editor');
        }
        if ($value === null || $value === false || $value === '' || $value === []) {
            return __('Not set', 'punch-acf-compact-editor');
        }
        if (in_array($field['type'] ?? '', ['image', 'file'], true)) {
            return __('Media selected', 'punch-acf-compact-editor');
        }
        if (($field['type'] ?? '') === 'post_object' && is_numeric($value)) {
            return get_the_title((int) $value) ?: __('Item selected', 'punch-acf-compact-editor');
        }
        if (is_scalar($value) && isset($field['choices'][$value])) {
            return wp_strip_all_tags((string) $field['choices'][$value]);
        }
        if (is_array($value)) {
            return in_array($field['type'] ?? '', ['repeater', 'flexible_content', 'gallery', 'relationship'], true)
                ? sprintf(_n('%s item', '%s items', count($value), 'punch-acf-compact-editor'), count($value))
                : __('Configured', 'punch-acf-compact-editor');
        }
        if (! is_scalar($value)) {
            return __('Configured', 'punch-acf-compact-editor');
        }
        return wp_trim_words(strip_shortcodes(wp_strip_all_tags((string) $value)), 18, '…');
    }

    public static function filter_form_fields($fields, $post_id): array
    {
        if (! is_array($fields) || ! is_string($post_id) || ! str_starts_with($post_id, 'block_') || ! self::owns_form($fields)) {
            return is_array($fields) ? $fields : [];
        }
        return array_values(array_filter($fields, static function ($field) {
            if (! is_array($field)) {
                return false;
            }
            $prefix = $field['prefix'] ?? '';
            if (! str_starts_with($prefix, 'acf-') || str_contains($prefix, '[')) {
                return true;
            }
            if (in_array($field['type'] ?? '', ['accordion', 'tab'], true)) {
                return false;
            }
            return ! str_starts_with($field['key'] ?? '', 'module_label_message_');
        }));
    }

    public static function owns_form(array $fields): bool
    {
        if (! function_exists('acf_get_block_types') || ! function_exists('acf_get_block_fields')) {
            return false;
        }
        $keys = array_column(array_filter($fields, 'is_array'), 'key');
        foreach (acf_get_block_types() as $block) {
            if (self::is_managed($block) && $keys === array_column(acf_get_block_fields($block), 'key')) {
                return true;
            }
        }
        return false;
    }

    public static function is_managed(array $block): bool
    {
        return isset(self::$blocks[self::normalise_name($block['name'] ?? '')]);
    }

    public static function configuration_notice(): void
    {
        if (self::$blocks || ! current_user_can('edit_theme_options')) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && ! in_array($screen->base, ['plugins', 'post', 'themes'], true)) {
            return;
        }

        echo '<div class="notice notice-info"><p>'
            . wp_kses_post(__(
                '<strong>Punch ACF Compact Editor is active, but no ACF blocks are enabled.</strong> Add <code>add_theme_support(\'punch-acf-compact-editor\');</code> to the theme before its blocks are registered, or configure an allow-list.',
                'punch-acf-compact-editor'
            ))
            . '</p></div>';
    }

    public static function top_toolbar_fields($toolbar_fields, $block): array
    {
        $toolbar_fields = is_array($toolbar_fields) ? $toolbar_fields : [];
        if (! self::is_managed($block)) {
            return $toolbar_fields;
        }
        $available = array_keys(self::visible_fields(self::fields($block)));
        $preferred = (array) apply_filters('punch/acf_compact_editor/top_toolbar_candidates', [], $block);
        $maximum = max(0, (int) apply_filters('punch/acf_compact_editor/top_toolbar_limit', 4, $block));
        foreach ($preferred as $field_name) {
            if (count($toolbar_fields) >= $maximum) {
                break;
            }
            if (in_array($field_name, $available, true) && ! in_array($field_name, $toolbar_fields, true)) {
                $toolbar_fields[] = $field_name;
            }
        }
        return $toolbar_fields;
    }

    public static function editor_settings(array $settings): array
    {
        $css = @file_get_contents(PUNCH_ACF_COMPACT_EDITOR_PATH . 'assets/editor.css');
        if ($css) {
            $settings['styles'][] = ['css' => $css];
        }
        return $settings;
    }

    public static function enqueue_editor_assets(): void
    {
        wp_enqueue_style(
            'punch-acf-compact-editor',
            PUNCH_ACF_COMPACT_EDITOR_URL . 'assets/editor.css',
            [],
            self::asset_version('assets/editor.css')
        );
        wp_enqueue_script(
            'punch-acf-compact-editor',
            PUNCH_ACF_COMPACT_EDITOR_URL . 'assets/editor.js',
            ['wp-data', 'wp-block-editor'],
            self::asset_version('assets/editor.js'),
            true
        );
    }

    public static function dimensions_script(string $src, string $handle): string
    {
        if ($handle === 'acf-dimensions' && self::is_block_editor_screen() && self::dimensions_enabled()) {
            return PUNCH_ACF_COMPACT_EDITOR_URL . 'assets/acf-dimensions.js?ver=' . self::asset_version('assets/acf-dimensions.js');
        }
        return $src;
    }

    public static function enqueue_dimensions_style(): void
    {
        if (! self::is_block_editor_screen() || ! wp_style_is('acf-dimensions', 'registered') || ! self::dimensions_enabled()) {
            return;
        }
        wp_enqueue_style(
            'punch-acf-compact-editor-dimensions',
            PUNCH_ACF_COMPACT_EDITOR_URL . 'assets/acf-dimensions.css',
            ['acf-dimensions'],
            self::asset_version('assets/acf-dimensions.css')
        );
    }

    private static function dimensions_enabled(): bool
    {
        $enabled = (self::$blocks || get_theme_support('punch-acf-compact-editor'))
            && self::compatible_dimensions_field();

        // Existing explicit opt-ins remain valid; false is an unconditional opt-out.
        return (bool) apply_filters('punch/acf_compact_editor/enable_dimensions_adapter', $enabled);
    }

    private static function compatible_dimensions_field(): bool
    {
        // Older fields register only metadata in ACF's type registry. The render
        // hook holds the actual instance. Fail closed if another renderer exists.
        global $wp_filter;
        $renderers = [];
        foreach (($wp_filter['acf/render_field/type=dimensions']->callbacks ?? []) as $callbacks) {
            foreach ($callbacks as $callback) {
                $renderers[] = $callback['function'];
            }
        }
        $renderer = $renderers[0] ?? null;
        if (count($renderers) !== 1 || ! is_array($renderer)
            || ! is_object($renderer[0]) || get_class($renderer[0]) !== 'NS_ACF_Field_Dimensions'
            || ($renderer[1] ?? '') !== 'render_field') {
            return false;
        }
        $field = $renderer[0];

        // The same class/version also exists with different schemas. Probe only
        // this known renderer, with synthetic empty data, never a saved field.
        // Cache by instance; do not cache a missing, not-yet-registered field.
        static $matches;
        $matches ??= new \WeakMap();
        if (isset($matches[$field])) {
            return $matches[$field];
        }
        $matches[$field] = false;
        $level = ob_get_level();
        ob_start();
        try {
            $field->render_field(['name' => 'punch_dimensions_probe', 'value' => []]);
            $html = ob_get_contents();
        } catch (\Throwable $error) {
            return false;
        } finally {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
        }

        return $matches[$field] = self::compatible_dimensions_markup($html);
    }

    private static function compatible_dimensions_markup(string $html): bool
    {
        $tags = new \WP_HTML_Tag_Processor($html);
        $classes = [];
        $names = [];
        $tabs = [];
        while ($tags->next_tag()) {
            foreach (preg_split('/\s+/', (string) $tags->get_attribute('class')) as $class) {
                $classes[$class] = true;
            }
            if (in_array($tags->get_tag(), ['INPUT', 'SELECT'], true)) {
                $names[(string) $tags->get_attribute('name')] = true;
            }
            if ($tags->get_tag() === 'A') {
                $tabs[(string) $tags->get_attribute('rel')] = true;
            }
        }

        foreach (['acf-dimensions', 'acf-dimensions__buttons', 'acf-dimensions__device',
            'acf-dimensions__inputs', 'acf-dimensions__texts', 'acf-dimensions__input',
            'input-top', 'input-bottom', 'input-linked', 'btn--linker'] as $class) {
            if (! isset($classes[$class])) {
                return false;
            }
        }
        if (count($tabs) !== 4 || count($names) !== 16) {
            return false;
        }
        foreach (['desktop', 'tablet_landscape', 'tablet_portrait', 'mobile'] as $device) {
            $target = 'acf-dimensions__device--' . $device;
            if (! isset($tabs[$target], $classes[$target])) {
                return false;
            }
            foreach (['top', 'bottom', 'linked', 'unit'] as $key) {
                if (! isset($names["punch_dimensions_probe[$device][$key]"])) {
                    return false;
                }
            }
        }

        return true;
    }

    private static function is_block_editor_screen(): bool
    {
        return is_admin() && function_exists('get_current_screen') && get_current_screen()?->is_block_editor();
    }

    private static function asset_version(string $file): string
    {
        $path = PUNCH_ACF_COMPACT_EDITOR_PATH . $file;
        return file_exists($path) ? (string) filemtime($path) : PUNCH_ACF_COMPACT_EDITOR_VERSION;
    }

    private static function theme_supports_block(array $args): bool
    {
        $support = get_theme_support('punch-acf-compact-editor');
        if ($support === true) {
            return true;
        }
        if (! is_array($support) || ! isset($support[0])) {
            return false;
        }

        $config = $support[0];
        if ($config === true || $config === 'all') {
            return true;
        }
        if (! is_array($config)) {
            return false;
        }
        if (! empty($config['all'])) {
            return true;
        }

        $name = self::normalise_name((string) ($args['name'] ?? ''));
        foreach ((array) ($config['blocks'] ?? []) as $block_name) {
            if (is_string($block_name) && self::normalise_name($block_name) === $name) {
                return true;
            }
        }

        $callback = $args['render_callback'] ?? null;
        foreach ((array) ($config['render_callbacks'] ?? []) as $candidate) {
            if ($candidate === $callback) {
                return true;
            }
        }

        return false;
    }

    private static function normalise_name(string $name): string
    {
        return str_starts_with($name, 'acf/') ? $name : 'acf/' . ltrim($name, '/');
    }
}
