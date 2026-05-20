### 1.0.3

2026-05-20 15:06

#### CHANGED

- Primary shortcode changed from [download ID] to [btdl_download ID] to avoid generic tag conflicts.
- Card template page uses built-in style presets (Default, Light, Dark, Modern) instead of a custom CSS textarea; further styling is expected via the theme Additional CSS editor
- Live preview and frontend card output apply the selected preset CSS instead of saved plugin custom CSS
- Saving a style preset removes the legacy `btdl_card_css` option; download cards that relied on plugin-stored custom CSS need styles re-applied in theme Additional CSS or a preset
- Tested up to WordPress 7.1 in plugin readme
- Load plugin translations from languages/ via load_plugin_textdomain so Settings and admin UI follow the site language in BT Keyboard Shortcuts and BT Downloads
- Add language files for BT Keyboard Shortcuts: .pot template plus en_US, de_DE, es_ES, fr_FR, it_IT (.po and compiled .mo) for settings page and Insert keyboard shortcut dialog
- Add language files for BT Downloads: .pot template plus en_US, de_DE, es_ES, fr_FR, it_IT (.po and compiled .mo) for Downloads CPT, edit screen, card template page, and Insert download UI
- Block editor format/toolbar and block JS strings (e.g. "Insert keyboard shortcut", "Insert download", "Select a download...") now use wp_set_script_translations so they are translated when the site language is not English
- Classic editor picker and block editor now insert the plugin-specific shortcode tag automatically.
- Custom card CSS is sanitized before being injected into style blocks for safer output handling.
- Card template preview behavior now uses proper enqueued admin scripts instead of inline script output.

### 1.0.2

2026-02-02 08:19

#### CHANGED

- Created and Updated dates use datetime-local inputs (date and time) instead of text; stored as YYYY-MM-DD HH:mm.
- Card preview meta box on download edit screen with iframe and "Preview with site theme" link.
- AJAX card preview for template/CSS settings so both template and custom CSS update the preview.
- "Set to current date/time" link for Created and Updated date fields in the download editor.
- Template sanitization preserves {{variable}} and style="{{...}}" placeholders; only strips script tags and event handlers.
- Default download card template and CSS: icon wrap is a link, new layout and typography, default download icon SVG overlay, .dl-meta for published/updated.

### 1.0.1

2026-02-02 08:15

#### CHANGED

- Created and Updated dates use datetime-local inputs (date and time) instead of text; stored as YYYY-MM-DD HH:mm.
- Card preview meta box on download edit screen with iframe and "Preview with site theme" link.
- AJAX card preview for template/CSS settings so both template and custom CSS update the preview.
- "Set to current date/time" link for Created and Updated date fields in the download editor.
- Template sanitization preserves {{variable}} and style="{{...}}" placeholders; only strips script tags and event handlers.
- Default download card template and CSS: icon wrap is a link, new layout and typography, default download icon SVG overlay, .dl-meta for published/updated.

### 1.0.0

2026-02-01 08:00

- **New**: Adds a custom `Downloads` post type with fields for file URL, version, description, info link, icon, and changelog.
- **New**: Download card system with editable HTML template (Mustache-style `{{variable}}` and `{{#variable}}...{{/variable}}` conditionals).
- **New**: Custom CSS editor with live preview for download cards.
- **New**: Classic editor integration via TinyMCE button and download picker.
- **New**: Block editor integration via a Download block that renders the configured card.
- **New**: WP-CLI import command `wp btdl import_downloads --file=/path/to/downloads.csv` for bulk-creating downloads from CSV.

