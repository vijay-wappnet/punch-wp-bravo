# Punch ACF Compact Editor

A reusable, opt-in editor layer for ACF PRO Block v3. It renders lightweight,
editable Gutenberg previews without loading a theme's frontend modules, scripts,
carousels, forms or embeds through their registered ACF asset callbacks. Assets
loaded elsewhere still need a theme audit; see Asset policy below.

Current release: **0.2.4**. Earlier fixes are included; install this version
directly rather than applying earlier releases in sequence.

## Quick start

### Updating to 0.2.4

WordPress **7.1 or newer** is now required. This release targets the WordPress
7.1 editor integration work; earlier WordPress versions are outside the supported
scope. Upgrade WordPress before installing this release on an older site.
PHP and ACF requirements are unchanged, as is the one-line theme opt-in.
This release changes requirements and documentation, not editor behaviour.

### Included fix: 0.2.3

The compatible legacy padding/margin (Dimensions) repair is now automatic.
The existing `add_theme_support('punch-acf-compact-editor');` is enough: no extra
theme filter is required. Replace the plugin and fully reload the editor after
saving any wanted changes. This loads the existing input sizing, device tabs,
and linked-value fixes; it does not change stored values or public rendering.

Detection checks the registered `NS_ACF_Field_Dimensions` renderer and its
four-device, top/bottom/linked/unit markup, not its installation folder. Normal
plugins and MU plugins are supported. Different implementations are left alone.
The repair runs only on block-editor screens with theme support or managed
blocks. An existing explicit opt-in continues to work; see the opt-out below.

### Included fix: 0.2.2

Version 0.2.2 selects the owning Gutenberg block before activating one of its
compact-preview fields. This fixes the missing-form path when a user clicks a
field directly after loading the editor, without first selecting the card.
Selection runs during capture for pointer, focus and click events in both the
parent document and editor iframe. It preserves field focus and leaves ACF's
toolbar events intact. No theme changes or content migration are required.

Replace the plugin, then fully reload the editor to load the updated script.
Check direct-first-click text, WYSIWYG, media and expanded controls, keyboard
focus, and switching straight between two different blocks. This release does
not address the separate nested-clone inline-text persistence investigation.

### Included fix: 0.2.1

When upgrading from 0.2.0 on WordPress 7.1 or newer, install 0.2.4, then clear the site's page cache and
reload the public page. No theme changes or resaving blocks are needed for
this update. Version 0.2.1 fixes registered module CSS/JS being suppressed on
the public site: ACF v3 also sets `acf_doing_block_preview` during frontend
rendering, so that flag cannot distinguish editor previews from public output.
Assets now run through ACF's native enqueue function immediately before the
original public renderer, using the actual `$is_preview` callback argument.
They remain excluded from compact editor previews.

### First installation

On WordPress 7.1 or newer, upload `punch-acf-compact-editor-0.2.4.zip` through **Plugins → Add New Plugin →
Upload Plugin**, then activate it. When updating, replace the existing plugin;
do not install a second copy under another folder name.

Installing and activating the plugin does not automatically change blocks. Add
this line to the active theme's `functions.php`, before its ACF blocks are
registered:

```php
add_theme_support('punch-acf-compact-editor');
```

Reload Gutenberg. ACF blocks registered after this opt-in will use the generic
compact preview, unless a theme supplies a tailored renderer. No custom preview
template or module registration edits are required for the generic view. Blocks
registered unusually early need the opt-in moved before their registration.

The generic view shows text and clickable field summaries such as “Media
selected” or “Configured”. It does **not** automatically recreate the Bread
Street image/text cards or a theme's public layout. Those require an optional
theme preview template. The one-line setup also enables automatic repair of the
compatible legacy Dimensions control; no second opt-in is needed.

If the website contains third-party ACF blocks, use an allow-list instead:

```php
add_theme_support('punch-acf-compact-editor', [
    'blocks' => [
        'acf/hero',
        'acf/text-and-image',
        'acf/gallery',
    ],
]);
```

See `examples/theme-setup.php` for callback-based selection and optional toolbar
settings and the optional Dimensions opt-out.

## Requirements

- WordPress **7.1 or newer**; the installed ACF PRO
  release may require a newer WordPress version.
- PHP 8.0 or newer.
- ACF PRO supporting Block v3 and inline editing. The local integration tests
  use ACF PRO 6.8.9; the plugin checks helper availability, not a version number.
- A one-line theme opt-in or a theme adapter selecting the managed blocks.

The plugin intentionally manages no blocks immediately after activation. An
admin notice now explains the required opt-in when no blocks have been selected.

The WordPress `Requires Plugins` header is intentionally not used, allowing
ACF PRO loaded as a normal plugin or through an MU-plugin loader. A folder inside
`mu-plugins` alone is not enough: its loader must actually load ACF.
At `plugins_loaded` priority 20 the plugin checks `acf_register_block_type`,
`acf_inline_toolbar_editing_attrs` and `acf_inline_text_editing_attrs`. If any
are absent, its editor hooks are not registered and an admin warning is shown.

## What the plugin owns

- Upgrade participating registrations to ACF Block v3 preview mode.
- Preserve and call each original callback/template on the public site.
- Suppress the participating block's registered frontend assets in Gutenberg.
- Provide a framework-independent generic PHP preview.
- Resolve top-level fields and seamless clone metadata.
- Bundle non-text clone siblings with an inline text target.
- Filter preview targets using supported ACF conditional rules.
- Generate inline-text, toolbar and expanded-editor attributes.
- Keep eligible fields reachable through the generic fallback; nested fields
  are edited through their parent field's form.
- Flatten legacy top-level tabs/accordions in managed inline forms only.
- Prevent links and forms in previews from navigating/submitting.
- Automatically repair the compatible legacy ACF Dimensions field inside remounted forms.

## What remains in each theme

- The opt-in rule identifying the theme's blocks.
- The public renderer and public templates.
- Optional tailored compact preview markup.
- Editor-specific visual styling and width choices.
- Preferred top-toolbar field names.
- Explicit handling of unusual/custom fields and data-driven modules.
- Testing against the theme's actual ACF schemas and editor pages.

## Alternative filter-based adapter

Theme support is the simplest option. For more control, load this before the
theme registers its ACF blocks and replace the callback with the one actually
used by the destination theme.

```php
<?php

add_filter('punch/acf_compact_editor/manage_block', function ($managed, $args) {
    return $managed || ($args['render_callback'] ?? null) === 'Theme\\render_block';
}, 10, 2);
```

That is enough to use the generic plugin preview. Existing module registration
files do not need `mode`, version or editor asset changes: the plugin wraps the
selected registrations after the theme supplies them.

## Optional tailored renderer

Return HTML from any PHP callable. The callback receives the prepared values
used by the generic renderer and the block as a second argument. It may return
a string or echo output. This plain-PHP example uses WordPress's template
lookup; it does not require Sage/Blade or an undefined theme helper.

```php
add_filter('punch/acf_compact_editor/preview_renderer', function ($renderer, $block) {
    // Replace this with the real registered block name in the destination theme.
    if (($block['name'] ?? '') !== 'acf/hero') {
        return $renderer;
    }

    $template = locate_template('blocks/editor-preview.php');
    if (! $template) {
        return $renderer;
    }

    return static function (array $args) use ($template): void {
        include $template; // The template reads $args and echoes escaped HTML.
    };
}, 10, 2);
```

The argument array contains `block`, `id`, `class`, `block_name`, `content`,
`base`, `is_preview`, `inline_fields`, `post_id`, `wp_block` and `context`.
Use the public `punch_acf_compact_editor_*()` helpers in the view. Do not call
the public module renderer from the preview renderer.

Wrap tailored markup in `.acf-editor-preview` so the first-click selection
handler and preview navigation protection apply. Escape text, URLs and ordinary
attributes with the appropriate WordPress functions. Helper-generated attribute
strings should be echoed directly, not escaped as one whole string. Use unique
`uid` options if you render more than one target for the same field.

For example, inside `blocks/editor-preview.php`, this shows an image while
retaining its native ACF toolbar. Replace `image` with the actual resolved field
name (including any clone prefix). It handles the standard image ID, array and
URL return formats:

```php
<?php
$image = $args['content']['image'] ?? null;
$image_id = is_array($image) ? ($image['ID'] ?? $image['id'] ?? 0) : $image;
$image_url = is_array($image) ? ($image['url'] ?? '')
    : (is_string($image) && ! is_numeric($image) ? $image : '');
$image_html = is_numeric($image_id) && (int) $image_id > 0
    ? wp_get_attachment_image((int) $image_id, 'medium_large') : '';
?>
<section class="acf-editor-preview">
    <div class="acf-editor-preview__body">
        <div class="acf-editor-preview__target"
            <?php echo punch_acf_compact_editor_toolbar_attrs(true, ['image'], [
                'toolbar_title' => 'Image',
            ]); ?>>
            <?php if ($image_html) : ?>
                <?php echo $image_html; ?>
            <?php elseif ($image_url) : ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="">
            <?php else : ?>
                <span>Select an image</span>
            <?php endif; ?>
        </div>
    </div>
</section>
```

This is a minimal image example, not a catch-all template. Add the other field
targets or render `punch_acf_compact_editor_remaining_fields()` yourself so
unhandled fields remain discoverable. Once a tailored renderer emits HTML, the
plugin does not append the generic preview automatically. Returning no output,
returning `null` from the filter, or a renderer exception uses the generic
fallback. Renderer errors are logged when `WP_DEBUG` is enabled.

Scope image sizing and layout rules to the preview in a theme editor stylesheet;
do not load frontend carousel scripts just to display a static preview image.

## Public helper functions

```php
punch_acf_compact_editor_inline_text_attrs(true, 'title', ['placeholder' => 'Add a title']);
punch_acf_compact_editor_toolbar_attrs(true, ['image', 'mobile_image'], ['toolbar_title' => 'Images']);
punch_acf_compact_editor_expanded_attrs(true, 'items', ['toolbar_title' => 'Items']);
punch_acf_compact_editor_remaining_fields();
punch_acf_compact_editor_field_summary($field);
```

Use inline text only for text/textarea content. Use toolbar attributes for
choices, media and rich text. Use expanded attributes for repeaters, groups,
clones, flexible content or any field whose full form is easier to understand.
The last argument is helper options, not the full renderer `$args` array. These
attribute and remaining-field helpers rely on the active plugin preview context;
call them from the preview renderer, not from arbitrary frontend code.

Other exported helpers are `punch_acf_compact_editor_fields($block)`,
`punch_acf_compact_editor_visible_fields($fields, $read_value = null)`,
`punch_acf_compact_editor_field_value($field)` and
`punch_acf_compact_editor_render_preview($block, $content, $post_id, $wp_block, $context)`.

## Theme filters

### `punch/acf_compact_editor/manage_block`

Optional filter-based opt-in. Receives `(bool $managed, array $registration_args)`.
Match a known callback, block name or an explicit registration property. Avoid
blanket `acf/*` matching unless the theme owns every one of those blocks.

### `punch/acf_compact_editor/preview_renderer`

Optional tailored renderer. Receives `($renderer, $block, $prepared_args)`.
Returning `null` uses the generic PHP preview.

### `punch/acf_compact_editor/top_toolbar_candidates`

Receives `($candidates, $block)` and returns an ordered list of top-level field
names. Only fields present and currently visible in that block are added. The
default list is empty and the default limit for additions is four, including
any toolbar fields already present. Existing toolbar entries are preserved.

```php
add_filter('punch/acf_compact_editor/top_toolbar_candidates', fn () => [
    'media_mode', 'background_colour', 'content_alignment', 'mobile_image',
]);
```

### `punch/acf_compact_editor/top_toolbar_limit`

Receives `($limit, $block)`. Stops adding preferred fields once the toolbar
reaches this count; it does not truncate entries already supplied by ACF or
another filter. Keep this small so the block toolbar remains understandable.

### `punch/acf_compact_editor/field_behavior`

Receives `($behavior, $field, $block)`. Valid useful values are:

- `inline_text`: direct text editing.
- `toolbar`: native ACF field UI in a popover or expanded editor.
- `hidden`: skip a field's standalone target in the generic preview; it remains
  in Edit all fields and stored data.

Use exact field keys/names or custom field types. Do not infer behaviour from
labels such as "Title" or "Settings".
Tailored renderers must honour `hidden` themselves. Explicit toolbar targets and
automatically grouped clone-sibling toolbar controls are not removed by this
setting; it is not an access-control mechanism.

### `punch/acf_compact_editor/editor_fields`

Last-resort adjustment of the resolved top-level field map. Prefer field
behaviour or explicit preview targets. Never recursively promote repeater/group
subfields into block toolbar targets. Receives `($fields_by_name, $block)`.
Unnamed fields, layout-only tabs/accordions/messages and ambiguous duplicate
names are omitted before this filter runs.

### `punch/acf_compact_editor/edit_all_label`

Receives `($label, $registration_args)` and customises the expanded editor
button text. The default is “Edit all fields”.

### `punch/acf_compact_editor/enable_dimensions_adapter`

Since 0.2.3 the compatible legacy Dimensions field is detected automatically.
To disable its repair for a particular theme:

```php
add_filter('punch/acf_compact_editor/enable_dimensions_adapter', '__return_false');
```

Existing `__return_true` overrides still work. Use one only for a separately
verified compatible fork: it bypasses detection, but not editor-only scoping.
Do not add it for unrelated widgets just because they share a field name.

## Conditional fields and clones

The compact preview supports `==`, `!=` and `!==` rules for ordinary scalar and
choice controllers, with AND inside a rule group and OR between groups. It also
resolves clone-local source keys so two prefixed instances do not cross-target.
Unsupported or unresolved rules do not themselves hide a field; supported
rules in the same AND group can still hide it. ACF's native form remains
authoritative. This is not a complete reimplementation of ACF conditional logic.

When an inline text field belongs to a seamless clone, visible non-text siblings
from that same clone instance are attached to its toolbar. This is why a cloned
Title, Title Tag and Title Display Tag component appears as one editor target.
It relies on ACF's `_clone` and `__key` metadata rather than labels or naming
conventions.

## Asset policy

For managed registrations, `enqueue_style`, `enqueue_script` and
`enqueue_assets` are preserved for the frontend but suppressed while ACF renders
the editor preview. This covers assets declared directly on the ACF block.

Each theme must still audit assets loaded globally, by `block.json`, by unrelated
hooks, or directly inside a template. Scripts needed by an ACF input field must
remain available. A custom field widget that does not support ACF's `remount`
lifecycle still needs a specific compatibility adapter.

Base preview CSS is loaded into the editor parent and editor-canvas styles.
Some base rules target `.editor-styles-wrapper` itself (typography and box
sizing), so review core and unmanaged blocks too when integrating a theme.
The Dimensions adapter is editor-screen-wide once enabled, not per block.

## Deactivation safety

The one-line `add_theme_support()` call is safe to leave in place when the
plugin is inactive. The plugin wraps registrations at runtime; it does not
rewrite public templates or migrate saved block content on activation.

Keep original render callbacks/templates independent of the plugin. If theme
code calls a plugin helper outside a plugin-invoked preview callback, guard it
with `function_exists()` (or `class_exists()` for the class) and retain the
normal rendering path when unavailable. Do not replace a public block's render
callback with a plugin helper. Those hard dependencies can cause critical
errors on deactivation even though the theme-support line itself is safe.

## Verification and known limits

Before rolling out to another theme, test on staging:

1. Reload, then click a field directly without first selecting its block. Check
   the toolbar, keyboard focus, reopening forms and switching between blocks.
2. Change text, choices, media and repeater/group values; save, reload and verify
   persistence. A changed preview alone does not prove that data was saved.
3. Check cloned titles/settings and conditional fields, including hidden media.
4. Check padding/margin across all four devices, linking, units, zero and empty
   values. Unsupported Dimensions implementations need a separate adapter.
5. Compare the public page with and without the plugin, including carousels,
   responsive layout and scripts. Check deactivation for critical errors.

Local regression coverage includes the automatic Dimensions asset selection,
schema rejection, opt-out, editor-only scoping, original frontend asset handling
and first-click event handling. These checks are not an end-to-end guarantee for
every theme or ACF field. The nested-clone inline-text persistence issue remains
an open investigation in 0.2.4; verify save/reload for those fields before rollout.

If the repair is missing, confirm the installed plugin version, save wanted
edits and fully reload the editor. Check for an explicit Dimensions opt-out,
an unsupported renderer/schema, or cached old JS/CSS. Do not add another opt-in
just to work around a stale plugin upload.

## Development and deployment

This repository keeps the development copy at:

`packages/punch-acf-compact-editor/`

Install the folder as:

`wp-content/plugins/punch-acf-compact-editor/`

Distribute the ZIP built from that development folder. This package does not
include Composer metadata; Composer installation requires the destination
project's own package/installer configuration. Do not copy the plugin into each
theme and load both copies. An existing unversioned `dist/punch-acf-compact-editor/`
folder may be an older export; use the current versioned ZIP.
