<?php
$fields = \Punch\ACFCompactEditor\Editor::remaining_fields();
$rendered_fields = 0;
?>
<section class="acf-editor-preview acf-editor-preview--generic" id="<?php echo esc_attr($id); ?>" data-editor-block="<?php echo esc_attr($base); ?>">
    <header class="acf-editor-preview__header">
        <span class="acf-editor-preview__type"><?php echo esc_html($block_name); ?></span>
        <span class="acf-editor-preview__hint"><?php esc_html_e('Select an outlined area to edit', 'punch-acf-compact-editor'); ?></span>
    </header>
    <div class="acf-editor-preview__body">
        <div class="acf-editor-preview__fields">
            <?php foreach ($fields as $field_name => $field) : ?>
                <?php
                if (! array_key_exists($field_name, \Punch\ACFCompactEditor\Editor::remaining_fields())) {
                    continue;
                }
                $label = $field['label'] ?: $field_name;
                $uid = $id . '-field-' . ($field['key'] ?? sanitize_key($field_name));
                $behavior = \Punch\ACFCompactEditor\Editor::field_behavior($field);
                if ($behavior === 'hidden') {
                    \Punch\ACFCompactEditor\Editor::mark_field($field_name);
                    continue;
                }
                ++$rendered_fields;
                ?>
                <div class="acf-editor-preview__field">
                    <span class="acf-editor-preview__field-label"><?php echo esc_html($label); ?></span>
                    <?php if ($behavior === 'inline_text') : ?>
                        <?php $value = \Punch\ACFCompactEditor\Editor::field_value($field); ?>
                        <div class="acf-editor-preview__copy acf-editor-preview__target" <?php echo \Punch\ACFCompactEditor\Editor::inline_text_attrs(true, $field_name, ['placeholder' => sprintf(__('Add %s', 'punch-acf-compact-editor'), $label), 'uid' => $uid]); ?>><?php echo esc_html(is_scalar($value) ? (string) $value : ''); ?></div>
                    <?php else : ?>
                        <div class="acf-editor-preview__action acf-editor-preview__target" <?php echo \Punch\ACFCompactEditor\Editor::toolbar_attrs(true, [$field_name], ['uid' => $uid, 'toolbar_title' => $label]); ?>><?php echo esc_html(\Punch\ACFCompactEditor\Editor::field_summary($field)); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (! $rendered_fields) : ?>
                <p class="acf-editor-preview__empty"><?php esc_html_e('This module has no directly editable ACF fields.', 'punch-acf-compact-editor'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
