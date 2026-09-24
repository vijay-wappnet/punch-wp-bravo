# Sage Bravo Theme

A custom WordPress theme for **Bravo** (AI-powered hotel intelligence platform), built on the
[Sage 11](https://roots.io/sage/) starter theme: Laravel Blade templating, Vite asset pipeline,
Acorn (Laravel-in-WordPress), ACF Pro blocks, Bootstrap 5, and GSAP scroll animations.

This document describes how *this specific theme* is put together — not generic Sage docs.
For Sage/Acorn/Blade fundamentals, see [https://roots.io/sage/docs/](https://roots.io/sage/docs/).

---

## Stack

| Layer | Tool |
|---|---|
| Templating | Laravel Blade (via [Acorn](https://roots.io/acorn/)) |
| Build | Vite 8 (`@roots/vite-plugin` + `laravel-vite-plugin`) |
| CSS | Sass (`.scss`), Bootstrap 5 as a base layer |
| JS | Vanilla JS modules (no framework), GSAP for scroll animation |
| Content | ACF Pro (Options pages, Flexible/Block fields, block-based page building) |
| PHP | Composer, PSR-4 autoload (`App\` → `app/`) |

---

## Local setup

```bash
composer install
npm install
npm run dev     # Vite dev server with HMR
npm run build   # production build → public/build/
```

`.env` sets `APP_URL` for the Vite/Laravel asset plugin — point it at whatever local URL serves
this WordPress install (see the committed `.env` for the current value).

### ⚠️ WSL vs Windows — pick one and stick to it

If you're on Windows, `node_modules` **must** be installed from the same environment you run
`npm run dev`/`npm run build` from. Vite's `rolldown` dependency ships platform-specific native
bindings (`@rolldown/binding-win32-x64-msvc` for native Windows, `@rolldown/binding-linux-x64-gnu`
for WSL). Installing from one and running from the other fails with:

```
Error: Cannot find native binding. npm has a bug related to optional dependencies (npm/cli#4828)
```

This project's active dev workflow is **WSL Ubuntu**. Always run `npm install` / `npm run dev` /
`npm run build` from a WSL terminal (`wsl.exe -d Ubuntu` or your WSL terminal app), even though the
files live under `/mnt/c/...`. If it ever breaks, the fix is:

```bash
rm -rf node_modules package-lock.json
npm install
```
— run **from WSL**, not PowerShell/Git Bash/cmd.

---

## Directory structure

```
app/
  Blocks/                 ACF block render controllers (App\Blocks\*)
  Providers/               ThemeServiceProvider (extends Acorn's SageServiceProvider)
  View/Composers/          Blade view composers (data shared across views)
  MenuWalker.php           Custom Walker_Nav_Menu for the fullscreen burger menu
  FooterBottomMenuWalker.php  Custom walker for the footer nav
  HeaderTopMenuWalker.php  Registered but currently empty/unused
  setup.php                Theme supports, sidebars, Typekit enqueue, editor asset injection
  filters.php              Small WP filters (excerpt "Continued" link)
  custom-function.php      ACF options page registration, misc filters/helpers
                            (incl. custom_acf_dimensions() — see below)
  blocks.php               acf_register_block_type() calls for every ACF block
  remove-default-blocks.php  Restricts the block inserter to ACF blocks only

resources/
  views/                   Blade templates (layouts, sections, partials, blocks)
  css/                     Sass, entry point app.scss (see "Styling" below)
  js/                      JS, entry point app.js (see "Scripts" below)
  images/, fonts/          Static assets, imported via import.meta.glob in app.js

public/build/              Vite build output (generated, not source)
vite.config.js             Vite/Laravel plugin config, path aliases, dev server base URL
theme.json                 Auto-generated at build time from Tailwind config — do not hand-edit
                            (see wordpressThemeJson() in vite.config.js)
```

### Theme bootstrap order

`functions.php` loads the Composer autoloader, boots the Acorn `Application` with
`ThemeServiceProvider`, then `locate_template()`s `app/setup.php` and `app/filters.php`.
`app/setup.php` in turn `require_once`s `custom-function.php`, `remove-default-blocks.php`, and
`blocks.php` at the bottom of the file — those three are **not** wired up by
`functions.php`'s `collect([...])->each(...)` loop, so if you add a new top-level `app/*.php`
file, remember to `require_once` it from `setup.php` (or add it to the `collect()` array if it
follows the `add_action`/`add_filter`-per-file pattern that `filters.php`/`setup.php` use).

Composer's `psr-4` autoload only covers **classes** under `App\` (e.g. `App\Blocks\*`,
`App\View\Composers\*`) — plain procedural files like `blocks.php` still need an explicit
`require`.

---

## Adding an ACF block

This theme uses `acf_register_block_type()` (not native block.json blocks). The pattern, using
`video-banner-section` as the reference:

1. **PHP render controller** — `app/Blocks/YourBlockName.php`, namespaced `App\Blocks`, with a
   static `render($block, $content, $is_preview, $post_id)` method. Pull ACF field values with
   `get_field()`, normalize them (image/video array vs. ID vs. URL — see `VideoBannerSection.php`
   for the pattern), then `echo view('blocks.your-block-name', [...])`.
2. **Register it** — add an `acf_register_block_type([...])` call in `app/blocks.php` inside the
   `add_action('init', ...)` block. Always set `'api_version' => 3`. For blocks that should
   render full-bleed on the front end but must **not** show a "Full width" alignment toolbar
   button in the editor, leave `'align' => 'full'` (sets the default attribute) but keep
   `'supports' => ['align' => false, ...]` — see "Known ACF/editor quirks" below for why this
   combination still needs a manual CSS fix for the editor canvas.
3. **Blade view** — `resources/views/blocks/your-block-name.blade.php`.
4. **Styles** — `resources/css/blocks/your-block-name.scss`, then `@use "blocks/your-block-name";`
   in `resources/css/app.scss`.
5. If the block needs margin/padding controls, reuse the `custom_acf_dimensions($margin, $padding,
   $blockId)` helper (`app/custom-function.php`) — it expects ACF Dimensions-plugin-shaped fields
   with `desktop`/`tablet`/`mobile` sub-arrays and returns a `<style>`-ready responsive CSS string,
   keyed off a unique `#vbs-block_...`/`#its-block_...`-style block ID.

`app/remove-default-blocks.php` restricts the block inserter to `acf/*` blocks only — native core
blocks are hidden from editors.

---

## Styling (`resources/css/app.scss`)

Import order matters (Sass `@use`, so each partial only pulls in what it explicitly needs via its
own `@use` lines):

```
bootstrap/scss/bootstrap        Bootstrap 5 base
common/variables                Colors, breakpoints (see below)
common/global                   Resets, base h1–h6/p/a, #app sticky-footer flex layout
common/fonts                    Typekit font-family/weight vars, @font-face (Dyslexie)
components/buttons              .btn base + reusable color variants (.btn-red-fusion, ...)
components/comments             ) currently empty — scaffolded, not yet styled
components/forms                )
components/accessibility-modal  ) scaffolded, no matching Blade markup exists yet either
components/accessibility-settings )   (see "Known incomplete areas")
components/newsletter-modal     )
layouts/header                  Site header, fullscreen burger menu, dot-grid hover effect
layouts/footer                  Site footer
layouts/404-page                currently empty
blocks/introtext-section        Per-block styles — one file per ACF block
blocks/video-banner-section
```

### Colors — `common/_variables.scss`

```scss
$back-color:                     #000
$black-off-color:                #2E2E2E   // primary dark text/bg
$red-fusion-color:               #FF5F5D   // brand accent (CTAs)
$gray-granite-color:             #6B6863
$gray-grayish-orange-color:      #BFB8B0
$white-off-light-grayish-color:  #DBD7D2
$white-off-color:                #F4F1EB   // "Bravo/Putty" — light section/card bg
$white-color:                    #FFF
```

Breakpoints (max-width, mobile-first-in-reverse):
`$screen-mobile-min-360/400/min` (360/400/480), `$screen-sm-min` (576), `$screen-md-min` (768,
`-767` variant also exists), `$screen-lg-min` (992), `$screen-xl-min` (1200).

### Typography — `common/_fonts.scss`

Primary typeface is **Articulat CF**, loaded via **Adobe Typekit** (`use.typekit.net/bff5fur.css`,
enqueued front-end-only in `setup.php`) — not self-hosted, so it's not in `resources/fonts/`.

```scss
$font-articulat-family:        "articulat-cf", sans-serif
$font-articulat-heavy-family:  "articulat-heavy-cf", sans-serif
$font-articulat-thin-weight:        100
$font-articulat-extra-light-weight: 200
$font-articulat-light-weight:       300   // used as the base body weight
$font-articulat-regular-weight:     400   // used for headings
$font-articulat-medium-weight:      600
$font-articulat-demi-bold-weight:   700   // strong/b
$font-articulat-bold-weight:        800
$font-articulat-extra-bold-weight:  900
$font-articulat-heavy-weight:       900   // paired with the heavy family
```

`Dyslexie` is the one self-hosted font (`resources/fonts/Dyslexie-Regular.woff{,2}`), used by the
accessibility settings feature to swap the reading font.

Base heading/paragraph sizes are set globally in `common/_global.scss` (h1 50px/60px line-height
down to h6 18px/25px, all in the Articulat family, `font-weight: $font-articulat-regular-weight`)
— override per-component only when the design actually differs from these defaults.

### Buttons — `components/_buttons.scss`

`.btn` is the shared base (padding, radius, font). Color/behavior variants are separate classes
layered on top, e.g. `.btn-red-fusion` (bg `$red-fusion-color` → hover bg `$black-off-color`,
350ms `ease-in-out`, icon `<img>` inside gets `translateX(0.5em)` + `filter: brightness(0)
invert(1)` on hover — this exact transition is lifted from the CTA button on
[bravo.works](https://www.bravo.works/) (`.btn-coral`), kept as the reference implementation).
When a block's button needs one of these variants, set it via the block's `button_class` ACF
field rather than hardcoding the class in the Blade view — that's what keeps the Blade markup
reusable across different button color treatments.

### Reusable effects

The **dot-grid hover canvas** (dots that "inflate" near the cursor) is implemented once in
`resources/js/header.js` (`initDotGrid()`) and applied to *any* element with a `.js-dot-grid`
class containing a `<canvas class="dot-grid-canvas">` child — it's wired up globally via
`document.querySelectorAll('.js-dot-grid').forEach(initDotGrid)` on `DOMContentLoaded`, so no
extra JS is needed to reuse it in a new block or section. `video-banner-section` reuses it this
way, gated behind its own `show_dot_grid_canvas` ACF field.

---

## Scripts (`resources/js/app.js`)

```
import.meta.glob(['../images/**', '../fonts/**'])   // makes static assets available to Vite
bootstrap (JS) exposed on window.bootstrap
./header.js         Sticky/transparent header, fullscreen burger menu, dot-grid canvas
./footer.js         Mobile footer accordion, CF7 newsletter submit-button wrapper
./gsap-animations.js  Scroll-triggered fade/slide-up animations, gated by:
                       - body.animations-enabled (ACF Options field `enable_animations`)
                       - prefers-reduced-motion (always respected, both here and in header.js)
```

`resources/js/editor.js` is a **separate Vite entry**, injected only into the block editor iframe
(see `setup.php`'s `admin_head` hook) — put editor-canvas-only JS there, not in `app.js`.

To add a new scroll animation with `gsap-animations.js`, add the selector to the
`animatedElements` array (uses sane defaults: fade+slide-up, `ScrollTrigger` at `top 80%`, fires
once) or push a per-selector override into `customAnimations`.

---

## Known ACF / block-editor quirks (already solved here — don't re-discover them)

- **`supports.align: false` fully disables WordPress's alignment mechanism**, including the
  `alignfull` class it would otherwise add to the block wrapper — even if you set a fixed
  top-level `'align' => 'full'`. That top-level key only pre-sets the block's *default attribute
  value*; it does not, by itself, add any CSS-relevant class. If a block needs to render
  full-bleed but must not expose an alignment toolbar control, you have to fix the editor-canvas
  width constraint yourself: `resources/css/editor.scss` targets the ACF-generated wrapper class
  directly (`.wp-block-acf-<block-name>`) rather than depending on `[data-align="full"]`/
  `.alignfull`, which never applies in this configuration.
- `resources/css/editor.scss` is injected into the block-editor iframe via the
  `block_editor_settings_all` filter in `setup.php` (as a raw `<style>@import ...</style>`, not
  enqueued the normal way) — it never reaches the front end, so it's the right place for any
  editor-canvas-only fixes.
- ACF blocks registered with `'mode' => 'preview'` render the *real* front-end Blade/CSS output
  inside the editor canvas (as opposed to `'mode' => 'edit'`, which shows the raw ACF field
  inputs instead) — useful to know when a block "looks broken in the editor" bug turns out to
  only be visible in preview mode.

---

## Known incomplete areas

- `resources/css/components/_accessibility-modal.scss`, `_accessibility-settings.scss`,
  `_newsletter-modal.scss`, `_comments.scss`, `_forms.scss`, and `resources/css/layouts/
  _404-page.scss` are all currently **empty** despite being `@use`d in `app.scss` — they're
  scaffolded placeholders, not a build error.
- `app/custom-function.php` wires up `data-toggle="modal" data-target="#accessibilityModal"` on
  footer nav items with a `site-accessibility` class, but no Blade view currently defines an
  element with `id="accessibilityModal"` — the trigger exists without its target markup yet.
- `app/HeaderTopMenuWalker.php` is an empty class file (unused).

---

## History note

Some files (`app/remove-default-blocks.php`'s docblock, sibling site `punch-wp-angelathotton`)
indicate this theme was forked from an earlier client project and rebranded for Bravo. If you're
debugging something that looks like a leftover from a different site, that's likely why.
