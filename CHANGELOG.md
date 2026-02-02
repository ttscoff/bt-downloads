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
- **New**: Download card system with editable HTML template (Mustache-style `{{variable}}` and `{{#variable}}…{{/variable}}` conditionals).
- **New**: Custom CSS editor with live preview for download cards.
- **New**: Classic editor integration via TinyMCE button and download picker.
- **New**: Block editor integration via a Download block that renders the configured card.
- **New**: WP‑CLI import command `wp btdl import_downloads --file=/path/to/downloads.csv` for bulk-creating downloads from CSV.

