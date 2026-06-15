# Zeitfresser Theme — Architecture & Design Handbook

> Living reference for the Zeitfresser WordPress theme. It documents how the
> theme is structured, **why** it is built the way it is, and the design
> decisions and code reviews that shaped it. Use it before extending the theme
> so that new work stays consistent with the existing foundation.
>
> **Status:** current as of **v3.5**. Update this file whenever an
> architectural decision changes.

---

## Table of Contents

1. [Philosophy & Guiding Principles](#1-philosophy--guiding-principles)
2. [Repository Layout](#2-repository-layout)
3. [Bootstrap & Load Order](#3-bootstrap--load-order)
4. [Subsystems](#4-subsystems)
   - [4.1 Customizer](#41-customizer)
   - [4.2 Utilities](#42-utilities)
   - [4.3 Tools](#43-tools)
   - [4.4 Performance Layer](#44-performance-layer)
   - [4.5 Templates](#45-templates)
   - [4.6 CSS Architecture](#46-css-architecture)
   - [4.7 JavaScript](#47-javascript)
5. [Key Design Decisions](#5-key-design-decisions)
6. [Code Review History](#6-code-review-history)
7. [Conventions](#7-conventions)
8. [Known Constraints & Gotchas](#8-known-constraints--gotchas)
9. [How to Extend the Theme](#9-how-to-extend-the-theme)
10. [Release Evolution](#10-release-evolution)

---

## 1. Philosophy & Guiding Principles

Zeitfresser began as a fork of the **Daisy Blog** theme (Graphthemes, 2022) and
has since been re-architected into a self-contained, modular theme. The work is
driven by a small set of consistent principles:

- **Modularity over monolith.** Logic lives in focused files under `inc/`,
  grouped by responsibility. `functions.php` is a thin bootstrap, not a
  dumping ground.
- **Performance is a feature.** Conditional asset loading, cache-busting,
  deferred JS, lazy-loading with explicit LCP handling, transient caching, and
  modern image formats are baked in — not bolted on.
- **Self-contained.** No premium SDK, no external CDN dependencies. Fonts are
  local. The theme degrades gracefully when optional plugins (e.g. ActivityPub)
  are absent.
- **Single source of truth.** Colors live in one place (`colors.css`),
  typography in one place (`fonts.css`), repeated visual patterns are
  consolidated into shared CSS rules.
- **Render-neutral refactors.** Cleanup and optimization must not change the
  rendered result or behaviour unless that change is the explicit goal.
- **Security hygiene by default.** Output is escaped, input is sanitized,
  AJAX is nonce- and capability-guarded, and every PHP file guards `ABSPATH`.

---

## 2. Repository Layout

```
/
├── functions.php              # Bootstrap: constants, includes, theme setup, asset enqueue
├── header.php / footer.php    # Document shell
├── index.php / archive.php / search.php / single.php / page.php / 404.php
├── sidebar.php / comments.php
├── style.css                  # Main stylesheet (theme header + all component CSS)
├── readme.md / LICENSE / screenshot.png
│
├── template-parts/            # Reusable render fragments
│   ├── content.php                # Post card (blog/home)
│   ├── content-single.php         # Single post body (uses TOC payload)
│   ├── content-page.php           # Static page
│   ├── content-search.php         # Search result card
│   ├── content-none.php           # Empty state
│   ├── related-articles.php       # Related posts grid
│   └── social-links.php           # Social icon row
│
├── inc/
│   ├── customizer/            # All Customizer sections & settings
│   │   ├── core-settings.php          # Live-preview wiring + shared heading control
│   │   ├── general-settings.php       # Site title/tagline, excerpt length
│   │   ├── layout-settings.php         # Container width (+ dynamic CSS var)
│   │   ├── toc-settings.php             # TOC toggle + min-headline threshold
│   │   ├── social-settings.php          # Social link URLs + label registry
│   │   ├── image-optimizer-settings.php # Auto-optimize / auto-delete toggles + UI JS
│   │   └── fediverse-rss-settings.php   # RSS widget configuration
│   │
│   ├── utilities/            # Pure helpers & template logic
│   │   ├── helpers.php                # Asset versioning, theme-mod cache, social SVGs
│   │   ├── template-tags.php          # posted_on, entry_footer, post_thumbnail, etc.
│   │   ├── template-functions.php     # body_class & pingback filters
│   │   ├── pagination.php             # Numeric pagination
│   │   ├── toc.php                    # Floating TOC payload builder + renderer
│   │   └── fediverse.php              # ActivityPub metrics, auto-approve, comment filtering
│   │
│   ├── tools/                # Larger self-contained feature modules
│   │   ├── image-optimizer.php        # AVIF/WebP conversion, original cleanup, admin UI
│   │   ├── code-block.php             # Prism code blocks (frontend + editor + server wrap)
│   │   └── fediverse-rss.php          # Fediverse RSS feed widget + shortcode
│   │
│   └── performance/
│       └── performance.php            # Defer, preload, critical CSS, image attrs, speculation rules
│
├── assets/
│   ├── css/
│   │   ├── colors.css         # Color tokens (single source of truth)
│   │   ├── fonts.css          # @font-face + typography tokens
│   │   ├── code.css           # Code-block styling (loaded conditionally)
│   │   └── editor.css         # Gutenberg editor styling
│   ├── js/
│   │   ├── navigation.js      # Accessible menu navigation
│   │   ├── scripts.js         # Scroll-to-top + mobile nav toggle
│   │   ├── toc.js             # Floating TOC engine (scroll-driven)
│   │   ├── code-block.js      # Prism init + copy-to-clipboard
│   │   ├── prism.js           # Prism highlighter (custom build)
│   │   ├── editor.js          # Gutenberg/Classic code block integration
│   │   └── customizer.js      # Live preview handlers
│   ├── fonts/                 # Local Oswald + Roboto woff2 (400/500/700 each)
│   └── images/                # search icons, etc.
│
├── languages/                # zeitfresser.pot
└── docs/
    └── Architecture.md        # This document
```

---

## 3. Bootstrap & Load Order

`functions.php` is the single entry point and intentionally thin. Load order is
**deliberate** and some subsystems depend on it:

1. **Constants** — `ZEITFRESSER_VERSION`, `ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION`.
2. **Customizer** files — `core-settings.php` **first** (see below), then general,
   layout, toc, social, image-optimizer, fediverse-rss.
3. **Utilities** — helpers, template-tags, template-functions, pagination, toc, fediverse.
4. **Tools** — image-optimizer, code-block, fediverse-rss.
5. **Performance** — performance.php.
6. **Theme setup** (`after_setup_theme`), image sizes, content width, widgets, asset enqueue.

> **Load-order dependency:** the shared Customizer control class
> `ZTFR_Customize_Heading_Control` is defined **once** in `core-settings.php`
> via `zeitfresser_register_heading_control()`, hooked on `customize_register`
> with **priority 0** so it exists before any section callback runs. The other
> customizer files merely *use* the class. If you reorder includes or change
> that priority, re-verify the heading control still registers first.

### Asset strategy

- The base `style.css` is enqueued with `filemtime()` as its version for
  automatic cache-busting. `fonts.css` and `colors.css` are enqueued as
  dependents of the base handle (so they always load after it) via the
  `zeitfresser_asset_versioned()` helper.
- JS (`navigation.js`, `scripts.js`) is enqueued in the footer and deferred (see
  Performance layer). `comment-reply` is only enqueued on singular views with
  threaded comments enabled.
- Feature assets load **conditionally**: code-block CSS/JS only when a post
  contains a `<pre>` or the `ztfr/code-block` block; TOC JS only when the post
  has enough headings.

---

## 4. Subsystems

### 4.1 Customizer

Each settings file registers its own section/controls on `customize_register`.
Sections live under the shared `ztfr_general` section where it makes sense
(general, layout, toc, social all share it) or get their own section
(image-optimizer, fediverse-rss).

- **Shared heading control** (`ZTFR_Customize_Heading_Control`): a tiny custom
  control that renders a bold sub-heading to visually group related settings.
  Defined once in `core-settings.php` (see load-order note above).
- **Live preview**: `blogname`, `blogdescription`, `header_textcolor` use
  `postMessage` transport with `customizer.js` handlers; `blogname` /
  `blogdescription` additionally have selective-refresh partials.
- **Layout → CSS variable bridge**: `layout-settings.php` prints a tiny inline
  `<style>` on `wp_head` that sets `--container-width` from the
  `container_width` theme mod. This is the pattern for "Customizer value →
  runtime CSS custom property."
- **Image-optimizer settings** ship their own inline JS/CSS (in the customizer
  controls screen) to disable "auto delete" when "auto optimize" is off and to
  show a live status badge.

**Theme mods in use:** `show_hide_site_title`, `show_hide_site_tagline`,
`post_snippet_excerpt_size`, `container_width`, `show_article_toc`,
`article_toc_min_headlines`, `social_links_*`, `ztfr_auto_optimize`,
`ztfr_auto_delete`, `ztfr_auto_approve_activitypub_reactions`,
`ztfr_fediverse_*` (feed URL, display name, max posts, word limit, cache time).

### 4.2 Utilities

- **`helpers.php`**
  - `zeitfresser_asset_versioned( $path )` / `zeitfresser_asset( $path )`:
    the canonical way to reference anything under `/assets`. Always use these
    rather than hand-building URLs (this is what keeps cache-busting consistent).
  - `zeitfresser_get_mod( $key, $default )`: request-level static cache around
    `get_theme_mod()`.
  - `zeitfresser_get_social_*`: social link registry, defaults, and inline SVG
    icon set.
  - `zeitfresser_get_post_card_*`: deterministic card thumbnail size / featured
    image toggle / excerpt length.
- **`toc.php`** — the TOC data layer (see [Design Decisions](#5-key-design-decisions)).
  `zeitfresser_build_toc_payload()` runs the content through
  `apply_filters('the_content', …)` **once**, parses headings with
  `DOMDocument`, injects stable IDs, and returns `{ content, items }`. Result is
  statically cached per post ID so the single template can reuse the processed
  content without a second `the_content` pass.
- **`fediverse.php`** — ActivityPub integration *logic* (distinct from the RSS
  widget). Detects whether the ActivityPub plugin is active
  (`zeitfresser_is_activitypub_active()`, request-cached), auto-approves
  incoming Fediverse reactions, and — when the plugin is **disabled** — filters
  ActivityPub comments out of the comment list and count so the site degrades
  gracefully.
- **`pagination.php`** — numeric pagination (window of ±2 pages with ellipses).
- **`template-tags.php` / `template-functions.php`** — standard `_s`-style
  template helpers, all guarded with `function_exists`.

### 4.3 Tools

The "tools" are the heaviest, most self-contained features.

- **`image-optimizer.php`**
  - On upload: captures the original file path, optionally converts JPEG/PNG to
    AVIF (preferred) or WebP, marks the attachment with an optimization-version
    meta, and optionally deletes originals.
  - Manual batch UI under **Tools → Image Optimizer**: AJAX-driven
    (`zeitfresser_optimize_images` / `zeitfresser_delete_originals`), nonce- and
    `manage_options`-guarded, with progress bars and a cleanup workflow.
  - **Original "family" model**: an attachment's original-format files
    (uploaded original + scaled main + sub-size derivatives) are resolved both
    from metadata and a filesystem glob fallback, so cleanup is thorough.
  - **Site Icon is always excluded** from optimization/cleanup (favicons need
    classic PNG/ICO formats for external clients).
  - A request-scoped global `$GLOBALS['zeitfresser_force_image_optimization']`
    lets the manual tool force conversion even when auto-optimize is off.
- **`code-block.php`**
  - Registers a Prism-based code block for both Gutenberg and the Classic
    editor, plus a server-side `the_content` filter that wraps raw `<pre><code>`
    in the theme's markup and defaults missing language hints to `language-yaml`.
  - Assets load only when the post actually contains code.
- **`fediverse-rss.php`**
  - A widget + `[fediverse_feed]` shortcode that renders a styled feed of your
    Fediverse posts (Mastodon/GoToSocial RSS).
  - Uses WordPress's `fetch_feed()` (SimplePie) with a configurable transient
    cache lifetime; the avatar is read from the already-fetched feed
    (`get_image_url()`) with an og:image / default-SVG fallback chain.
  - HTML-aware word trimming preserves markup when truncating excerpts.

### 4.4 Performance Layer

`inc/performance/performance.php` centralizes front-end performance work:

- **Defer** non-critical scripts (`zeitfresser-navigation`, `zeitfresser-scripts`)
  via `script_loader_tag`.
- **`zeitfresser_image_attributes()`** — a single `wp_get_attachment_image_attributes`
  pass that: backfills missing `width`/`height` (CLS), sets the first image
  `loading=eager` (LCP) and the rest `lazy`, adds `decoding=async`, and gives the
  first image on singular views `fetchpriority=high`.
- **Image generation tuning** — lowers the big-image threshold and skips the
  unused `1536`/`2048` intermediate sizes, but **only** when optimization is
  active (so disabling auto-optimize truly leaves uploads untouched).
- **Font preloading** — preloads only the above-the-fold weights
  (`roboto-400` body + `oswald-500` headings/title). Other weights load on demand
  via `font-display: swap`.
- **Critical CSS** — a minimal inline `<style>` on `wp_head` for body background,
  container, and grid, so layout paints before the full stylesheet arrives.
- **Speculation Rules** — prerenders same-origin links on hover ("moderate"
  eagerness), excluding query-string/admin/login/nofollow/new-tab links and
  `.no-prerender`. Guarded off on WP 6.8+ which ships native speculative loading.
- **Head cleanup** — removes emoji scripts, RSD/WLW/generator links, and the
  `wp-embed` script.

### 4.5 Templates

Templates follow the classic WordPress hierarchy. Notable points:

- `single.php` renders the floating TOC (`zeitfresser_render_floating_toc()`),
  the post body via `template-parts/content-single.php`, post navigation,
  comments, and optional related posts.
- `content-single.php` outputs the **processed** content from the TOC payload
  (`$toc_payload['content']`) — it does **not** call `the_content()` again, which
  is what keeps the content filters running exactly once per request.
- Single posts intentionally **do not render the featured image** in the body;
  featured images appear only in cards (archive/home/search) and related posts.
  (This matters for any future "preload the LCP image" work — see Gotchas.)

### 4.6 CSS Architecture

Three stylesheets, by design:

| File | Responsibility | Why separate |
|------|----------------|--------------|
| `style.css` | All component/layout CSS + theme header | Main sheet |
| `colors.css` | Color custom properties + body bg | **Single source of truth** for color — change palette in one place |
| `fonts.css` | `@font-face` + typography tokens | Keeps typography/maintenance isolated |

> The separate `colors.css` / `fonts.css` are kept on purpose. Merging them into
> `style.css` would save only a negligible amount on HTTP/2 and was rejected in
> favour of maintainability (single-source-of-truth for color/type).

**`style.css` is organized into numbered sections:**
`1. ROOT / TOKENS` → `2. BASE` → `3. LAYOUT` → `4. TYPOGRAPHY / BUTTONS` →
`5. COMPONENTS` → `6. UTILITIES / FIXES`.

**Design tokens** are CSS custom properties:
- Color: `--light-color`, `--dark-color`, `--footer-color`, `--hover-color`,
  `--muted-color` (in `colors.css`).
- Type: `--primary-font` (Oswald, headings), `--secondary-font` (Roboto, body),
  `--site-identity-font-size`, `--font-weight`, `--line-height` (in `fonts.css`).
- Spacing/layout: `--space-md`, `--space-lg`, `--container-width`, TOC offsets.

**Shared visual patterns (consolidated — reuse these instead of re-declaring):**
- **Animated underline link effect** — one shared rule covers `.widget a`,
  `.main-navigation a`, `.post-navigation a`, `.pagination a` (base) plus those
  and `.site-title .logo` / `.news-snippet .news-title a` (hover). The
  underline is a `linear-gradient(currentColor, currentColor)` background that
  grows from `0 1px` to `100% 1px`. `.entry-content a` is the **always-visible**
  variant (the opposite direction). When adding a new link context that should
  share this effect, add its selector to the shared rule rather than copying the
  declarations.
- **Dark panel surface** — one shared rule (`background-color: var(--footer-color);
  border: 1px solid rgba(248,248,242,0.08); border-radius: 0;`) covers `.widget`,
  `.custom-box`, the 404/search empty states, and `.comment-list .comment`.

**Font weights actually rendered** (all six woff2 are used — verify before
removing any): Roboto 400/500/700, Oswald 400 (widget titles), Oswald 500
(headings/title), Oswald 700 (TOC title).

### 4.7 JavaScript

- **`toc.js`** — the floating TOC engine. Scroll-driven, `requestAnimationFrame`
  throttled. Key structure after the v3.5 review:
  - `update(includeLayout)` is the single rAF entry point. `onScroll` calls
    `update(false)`; `onResize` / `load` call `update(true)`.
  - **`syncPosition()` (layout math) only runs on resize/load**, because its
    inputs are scroll-invariant (the title's absolute offset doesn't change while
    scrolling). Scroll frames only run the genuinely scroll-dependent work:
    footer collision, progress bar, active-heading detection.
  - The active link is updated only when it actually changes (`lastActiveId`
    guard), avoiding redundant DOM writes and `scrollIntoView` jitter.
  - Desktop-only (`min-width: 1500px`); collapses/hides via `.is-colliding` when
    there isn't room beside the content column.
- **`scripts.js`** — scroll-to-top button + mobile nav (`#nav-icon3`) toggle.
- **`navigation.js`** — accessible keyboard navigation for the menu.
- **`code-block.js` / `prism.js` / `editor.js`** — code highlighting and editor
  integration (loaded conditionally).
- **`customizer.js`** — live-preview DOM updates for title/description/header color.

---

## 5. Key Design Decisions

These are the decisions a future contributor (human or AI) is most likely to
need the rationale for.

1. **Modular `/inc` over a monolithic `functions.php`** *(v2.0)*
   Each concern is one file with a clear name. `functions.php` only wires things
   together. **Why:** maintainability and discoverability; the theme grew real
   features (optimizer, TOC, Fediverse) that would have made a single file
   unmanageable.

2. **TOC content is processed once and cached** *(toc.php)*
   `zeitfresser_build_toc_payload()` runs `the_content` filters a single time,
   parses headings via `DOMDocument`, injects IDs, and caches the result per
   post ID. The single template renders that cached, processed content.
   **Why:** running `the_content` twice (once for the TOC, once for output)
   would double-process shortcodes/embeds and hurt performance.

3. **Graceful ActivityPub degradation** *(fediverse.php, v3.1+)*
   When the ActivityPub plugin is inactive, Fediverse comments are filtered out
   of both the comment list and the count, and reaction auto-approval is skipped.
   **Why:** the theme must look correct whether or not the optional plugin is
   installed. Activeness detection is request-cached to avoid repeated
   `get_option` / `get_comments` work.

4. **Site Icon is excluded from the image optimizer** *(image-optimizer.php, v2.x)*
   **Why:** favicons are consumed by external clients/crawlers/RSS readers that
   may not support AVIF/WebP; converting them breaks compatibility.

5. **Optimization tuning is gated on the auto-optimize toggle**
   `big_image_size_threshold` and intermediate-size filtering only apply when
   auto-optimize (or the manual force flag) is on. **Why:** with optimization
   off, uploads must be left completely untouched — no surprises.

6. **Local fonts + selective preloading** *(performance.php, v3.5)*
   All fonts are local woff2 with `font-display: swap`. Only the two
   above-the-fold weights are preloaded. **Why:** preloading everything makes
   fonts compete with the LCP image for bandwidth; "used site-wide" ≠
   "critical for first paint."

7. **Single source of truth for color & type** *(colors.css / fonts.css)*
   Hard-coded colors were deliberately migrated to CSS variables in `colors.css`.
   Kept as separate files for maintainability (see CSS Architecture).

8. **Shared CSS patterns over repetition** *(v3.5)*
   The underline-link effect and dark-panel surface are single rules with
   grouped selectors. **Why:** DRY, easier to restyle globally, smaller sheet —
   achieved render-neutrally.

9. **Speculation Rules with a native-feature guard** *(performance.php, v3.5)*
   Custom prerender rules ship only on WP < 6.8; 6.8+ has native speculative
   loading. **Why:** instant navigation without duplicating hints on modern WP.

10. **Conservative, render-neutral refactoring** *(v3.5 reviews)*
    Cleanup work must not change the rendered output or behaviour. Where a
    "fix" would alter rendering (e.g. the comma-separator pseudo-element
    interaction), it was deliberately left alone and documented instead.

---

## 6. Code Review History

A focused, multi-round review/optimization pass was performed for **v3.5**.
All changes were verified for render-/behaviour-neutrality (no PHP/JS linter is
available in the working environment, so verification was manual: brace balance,
orphan-reference checks, cascade/specificity analysis). Findings, in order:

### Round 1 — `refactor(theme)` (commit `eccedb5`)
- **Bug fix:** Customizer live-preview script was enqueued from `/js/customizer.js`
  (404) instead of `/assets/js/customizer.js`; corrected and switched to
  `filemtime` versioning. Restored live preview for header text color.
- **Dead code removed:** unused `masonry.pkgd.min.js` (~24 KB, never enqueued) and
  its dead init block in `scripts.js` (`.blog-grid-view` exists in no template);
  the unused Freemius/Daisy-Blog compatibility shims (`zeitfresser_fs`, `db_fs`,
  `graphthemes_get_social_link_default`).
- **Performance:** request-cached `zeitfresser_is_activitypub_active()` and the
  disabled-plugin comment-count query.
- **Refactor:** centralized `ZTFR_Customize_Heading_Control` (was defined 3×).

### Round 2 — `perf(assets)` (commit `3e38fb2`)
- Deleted the dead `style-rtl.css` (~44 KB, never enqueued / not RTL-wired).
- Removed a no-op same-origin `preconnect` resource hint.
- Removed an ineffective Critical-CSS header rule (referenced an undefined color
  variable at paint time and used the wrong color vs. the real stylesheet).
- Aligned font preloads with actual usage.

### Round 3 — `perf(theme)` (commit `b0f0573`)
- **CSS:** removed a dead duplicate `.custom-grid-view` block (its `clamp()` gap
  was being overridden); collapsed value-identical duplicate rules
  (`site-header` bg/position, sticky-header, `site-header-wrapper`, duplicate
  `.category/.tags` hover); merged the two `.screen-reader-text:focus` blocks
  render-neutrally; dropped declarations bound to **undefined** variables
  (`--logo-size`, `--site-identity-font-family`); **generalized** the
  underline-link effect and dark-panel surface into shared rules; removed
  obsolete `-webkit-`/`-moz-` transition/transform/max-content/clip-path prefixes.
- **performance.php:** reduced font preloads to `roboto-400` + `oswald-500`;
  merged the two `wp_get_attachment_image_attributes` filters into one pass;
  added Speculation Rules.
- **toc.js:** removed dead code (`isScrollingFromClick` was never set true;
  unused sticky-top vars; an undefined CSS var read); added the `lastActiveId`
  guard; took the scroll-invariant `syncPosition()` out of the scroll path.
- **fediverse-rss.php:** removed a duplicate feed `wp_remote_get` in the avatar
  lookup by reusing the already-fetched SimplePie object's `get_image_url()`.

### Notable findings deliberately **not** changed (would not be render-neutral)
- The category/tag comma-separator uses two interacting `::after` mechanisms
  (absolute positioning + margin). Removing either changes placement, so both
  were left in place.
- The `.widget-title` / `.widget_block h2` rules overlap but serve different
  selector lists (`.widget h2` vs `.widget_block h2`), so they were not merged.
- A latent cascade subtlety: `h1,h2,h3 { font-weight:400 }` in `style.css` was
  overridden by `fonts.css` (`500`) due to load order; the dead rule was removed
  but **headings render at 500** — keep this in mind before "fixing" heading
  weights.

---

## 7. Conventions

- **Function prefixes:** `zeitfresser_` (canonical), `ztfr_` (newer/shorter, esp.
  code-block + customizer settings), `gts_` (Fediverse RSS module). *This
  inconsistency is historical;* prefer `zeitfresser_` for new general code and
  keep a module's existing prefix when extending that module.
- **Escaping/sanitizing:** escape on output (`esc_html`, `esc_attr`, `esc_url`,
  `wp_kses[_post]`); sanitize on input (`sanitize_text_field`, `esc_url_raw`,
  `absint`, `wp_validate_boolean` as Customizer `sanitize_callback`s).
- **i18n:** all user-facing strings use the `zeitfresser` text domain. Keep
  `languages/zeitfresser.pot` in mind when adding strings.
- **Guards:** every PHP file starts with an `ABSPATH` check; template tags and
  helpers are wrapped in `function_exists()`.
- **Pluggable functions** use `function_exists` so child themes can override.
- **Asset references** go through `zeitfresser_asset()` /
  `zeitfresser_asset_versioned()` — never hand-build `/assets` URLs.
- **CSS:** follow the numbered section structure; use existing tokens; extend the
  shared link/panel rules rather than duplicating their declarations.

---

## 8. Known Constraints & Gotchas

- **No build step / no linter in CI.** The theme ships hand-written CSS/JS/PHP.
  Verify changes manually (and visually). A brace-balance + grep for orphan
  references is the minimum smoke test for CSS/JS edits.
- **`functions.php` include order is significant** — see the heading-control
  note in [§3](#3-bootstrap--load-order).
- **CSS load order matters for equal-specificity rules.** `style.css` loads
  before `fonts.css`/`colors.css`. The heading-weight gotcha above is a concrete
  example. When two equal-specificity rules conflict across files, the later
  file wins.
- **Undefined CSS variables fail silently.** A `var(--x)` with no definition and
  no fallback drops the whole declaration. Two such dead declarations were
  removed in v3.5; don't reintroduce variables that aren't defined in
  `colors.css`/`fonts.css` (or set at runtime, like the TOC vars set by `toc.js`).
- **All six font weights are in use** — confirm with a usage trace
  (`font-weight` *and* the `font:` shorthand) before removing any woff2.
- **Single posts have no featured image in the body.** Any future
  "preload the LCP image" work must account for this — on single posts the LCP is
  the title or the first in-content image, not the featured image; preloading the
  featured image there would fetch an unused asset.
- **`get_the_ID()` at `wp_enqueue_scripts`** — the TOC enqueue check builds the
  TOC payload during `wp_enqueue_scripts`, which runs `the_content` filters
  earlier than the loop. It is cached, so output reuses it; be aware if a plugin
  expects `the_content` only during the body.

---

## 9. How to Extend the Theme

**Add a Customizer setting**
1. Pick the right file in `inc/customizer/` (or add a new one and `require_once`
   it in `functions.php`).
2. Use `$wp_customize->add_setting()` with a `sanitize_callback`, then
   `add_control()`. Reuse `ZTFR_Customize_Heading_Control` for group headings.
3. Read it via `zeitfresser_get_mod( 'key', $default )` (cached) where it's used.
4. If it should drive CSS, follow the `--container-width` pattern in
   `layout-settings.php` (inline `<style>` on `wp_head` setting a custom property).

**Add a new feature module ("tool")**
1. Create `inc/tools/your-feature.php` with an `ABSPATH` guard and a clear prefix.
2. `require_once` it in the Tools block of `functions.php`.
3. Enqueue assets **conditionally** (only on pages that need them), versioned via
   `zeitfresser_asset_versioned()`.
4. Guard any admin/AJAX surface with capability + nonce checks.

**Add or change styling**
- Use existing tokens (`var(--…)`); add new color tokens to `colors.css`.
- Place rules in the correct numbered section of `style.css`.
- For link underlines or panel surfaces, **add your selector to the shared rule**
  rather than copying declarations.
- Keep changes render-neutral unless a visual change is the explicit goal.

**Add fonts / weights**
- Add the `@font-face` to `fonts.css`, apply it via the type tokens, and only
  add a `<link rel=preload>` in `performance.php` if the weight is genuinely
  above-the-fold.

**Performance work**
- Centralize in `inc/performance/performance.php`.
- Prefer one filter pass over several on the same hook.
- Measure LCP impact before adding preloads.

---

## 10. Release Evolution

A condensed history (full notes live on the Gitea releases page):

| Version | Theme |
|---------|-------|
| **1.7.1** | Image cleanup fix (removes full image family incl. sub-sizes). |
| **2.0** | Major refactor — modular `/inc`, reorganized Customizer, centralized assets. |
| **2.5** | CSS restructure, reworked floating TOC, unified spacing system. |
| **2.6** | Native code-block system (Prism, Gutenberg + Classic, copy-to-clipboard). |
| **2.7** | Code-block polish, modal editor, sidebar search UI, conditional assets. |
| **2.9** | Floating TOC engine overhaul — independent scrolling, footer collision. |
| **3.0** | Dynamic TOC collision detection (fades out when space is tight). |
| **3.1** | Fediverse integration — visible likes/boosts, remote interaction prompt. |
| **3.2** | Fediverse interaction refinements, tooltips, SVG consistency, a11y. |
| **3.3** | Native Fediverse RSS widget, centralized config, 3-layer avatar detection. |
| **3.4** | Fediverse RSS styling refinements (var-based colors, link/underline tweaks). |
| **3.5** | **Polish & performance** — CSS consolidation, font/image/TOC optimization, Speculation Rules, code-review cleanup (see [§6](#6-code-review-history)). |

---

*Maintainers: keep this document current. When you make an architectural choice,
record the decision and its rationale here — that is what turns it from notes
into a handbook.*
