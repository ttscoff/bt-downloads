<?php
/**
 * Plugin Name: BT Downloads
 * Plugin URI: https://github.com/ttscoff/bt-downloads/
 * Description: Download cards with editable template and custom CSS. Shortcode [download] for insertable download cards.
 * Version: 1.0.3
 * Author: Brett Terpstra
 * Author URI: https://brettterpstra.com
 * License: GPLv2 or later
 * Text Domain: bt-downloads
 */

if (!defined('ABSPATH')) {
	exit;
}

define('BTDL_PATH', plugin_dir_path(__FILE__));
define('BTDL_URL', plugin_dir_url(__FILE__));
define('BTDL_VERSION', '1.0.0');

require_once BTDL_PATH . 'includes/class-btdl-download-cpt.php';
require_once BTDL_PATH . 'includes/class-btdl-download-template.php';
require_once BTDL_PATH . 'includes/class-btdl-download.php';
require_once BTDL_PATH . 'includes/class-btdl-download-editor.php';

add_action('init', function () {
	load_plugin_textdomain('bt-downloads', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

BTDL_Download_CPT::init();
BTDL_Download_Editor::init();
BTDL_Download::register_shortcode();

if (defined('WP_CLI') && WP_CLI) {
	require_once BTDL_PATH . 'includes/class-btdl-download-cli.php';
	WP_CLI::add_command('btdl', 'BTDL_Download_CLI');
}
