=== BT Downloads ===
Contributors: bterp
Tags: download, shortcode, cards, files
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Download cards with editable HTML template and custom CSS. Shortcode [download] for insertable download cards.

== Description ==

BT Downloads provides a custom post type for managing downloadable projects with an editable card template, custom CSS with live preview, and insert tools for both the classic and block editors. Use the `[download ID]` shortcode anywhere shortcodes are supported.

= Features =
* Custom post type for downloads with file URL, version, description, info link, icon, and changelog
* Editable HTML template with Mustache-style conditionals ({{#var}}...{{/var}})
* Custom CSS with live-updating preview
* Upload buttons for file and icon on the download edit screen (files go to uploads/downloads/)
* Insert download picker for classic editor (TinyMCE button) and block editor (Download block)
* WP-CLI import from CSV: `wp btdl import_downloads --file=/path/to/downloads.csv`

= Shortcode =
Use `[download 27]` where 27 is the shortcode ID assigned to the download.

= Accessing Downloads in the admin =
In the WordPress admin sidebar, click **Downloads**. You get three sub-items: **Downloads** (list of all download entries, with shortcode IDs), **Add New Download**, and **Card template** (editable HTML template and custom CSS for the download cards). Use the list to edit or add downloads; use Card template to change how cards look on the front end.

== Installation ==

1. Upload the `bt-downloads` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. In the sidebar, go to **Downloads** to add your first download or **Downloads → Card template** to customize the HTML and CSS.

== Frequently Asked Questions ==

= How do I import downloads from a CSV? =
Use WP-CLI: `wp btdl import_downloads --file=/path/to/downloads.csv`. The CSV should have columns: id, title, file, version, description, info, icon, updated, created, changelog. Paths starting with / are converted to your uploads URL.

== Changelog ==

= 1.0.0 =
* Initial release.
