<?php

use Punch\ACFCompactEditor\Editor;

function punch_acf_compact_editor_render_preview($block, $content = '', $post_id = 0, $wp_block = null, $context = []): void
{
    Editor::render_preview($block, $content, $post_id, $wp_block, $context);
}

function punch_acf_compact_editor_fields(array $block): array
{
    return Editor::fields($block);
}

function punch_acf_compact_editor_visible_fields(array $fields, ?callable $read_value = null): array
{
    return Editor::visible_fields($fields, $read_value);
}

function punch_acf_compact_editor_field_value(array $field)
{
    return Editor::field_value($field);
}

function punch_acf_compact_editor_remaining_fields(): array
{
    return Editor::remaining_fields();
}

function punch_acf_compact_editor_inline_text_attrs(bool $is_preview, string $field_name, array $args = []): string
{
    return Editor::inline_text_attrs($is_preview, $field_name, $args);
}

function punch_acf_compact_editor_toolbar_attrs(bool $is_preview, $fields, array $args = []): string
{
    return Editor::toolbar_attrs($is_preview, $fields, $args);
}

function punch_acf_compact_editor_expanded_attrs(bool $is_preview, string $field_name, array $args = []): string
{
    return Editor::expanded_attrs($is_preview, $field_name, $args);
}

function punch_acf_compact_editor_field_summary(array $field): string
{
    return Editor::field_summary($field);
}

