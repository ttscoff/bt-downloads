## 1.0.0 — Initial release

- **New**: Adds a custom `Downloads` post type with fields for file URL, version, description, info link, icon, and changelog.
- **New**: Download card system with editable HTML template (Mustache-style `{{variable}}` and `{{#variable}}…{{/variable}}` conditionals).
- **New**: Custom CSS editor with live preview for download cards.
- **New**: Classic editor integration via TinyMCE button and download picker.
- **New**: Block editor integration via a Download block that renders the configured card.
- **New**: WP‑CLI import command `wp btdl import_downloads --file=/path/to/downloads.csv` for bulk-creating downloads from CSV.

