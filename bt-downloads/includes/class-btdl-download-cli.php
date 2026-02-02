<?php
/**
 * WP-CLI command to import downloads from CSV.
 *
 * Usage: wp btdl import_downloads --file=/path/to/downloads.csv
 *
 * @package BT_Downloads
 */

if (!defined('ABSPATH') && !defined('WP_CLI')) {
	exit;
}

/**
 * BTDL Download CLI.
 */
class BTDL_Download_CLI
{

	/**
	 * Resolve path to uploads URL if it starts with /.
	 *
	 * @param string $path Path.
	 * @return string
	 */
	private static function resolve_path($path)
	{
		$path = trim($path);
		if ($path === '' || strpos($path, 'http') === 0) {
			return $path;
		}
		if (strpos($path, '/') === 0) {
			$uploads = wp_upload_dir();
			return rtrim($uploads['baseurl'], '/') . $path;
		}
		return $path;
	}

	/**
	 * Import downloads from CSV.
	 *
	 * ## OPTIONS
	 *
	 * --file=<path>
	 * : Path to CSV file.
	 *
	 * ## EXAMPLES
	 *
	 *     wp btdl import_downloads --file=/path/to/downloads.csv
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Associative args.
	 */
	public function import_downloads($args, $assoc_args)
	{
		$file = isset($assoc_args['file']) ? sanitize_text_field($assoc_args['file']) : '';
		if ($file === '') {
			WP_CLI::error('Provide --file=/path/to/downloads.csv');
		}
		$file = str_replace('~', getenv('HOME'), $file);
		if (!is_readable($file)) {
			WP_CLI::error('File not found or not readable: ' . $file);
		}

		// Initialize WP_Filesystem
		global $wp_filesystem;
		if (empty($wp_filesystem)) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		// Read file contents
		$contents = $wp_filesystem->get_contents($file);
		if ($contents === false) {
			WP_CLI::error('Could not read file.');
		}

		// Parse CSV manually
		$lines = array_filter(explode("\n", $contents));
		if (empty($lines)) {
			WP_CLI::error('Empty or invalid CSV.');
		}

		$header = str_getcsv(array_shift($lines));
		if ($header === false || empty($header)) {
			WP_CLI::error('Empty or invalid CSV.');
		}
		$header = array_map('trim', $header);
		$count = 0;
		foreach ($lines as $line_str) {
			$line = str_getcsv($line_str);
			if ($line === false) {
				continue;
			}
			$row = array_combine($header, array_pad($line, count($header), ''));
			if (!isset($row['id']) || trim($row['id']) === '') {
				continue;
			}
			$id = trim($row['id']);
			// meta_query needed to find download by shortcode ID meta
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$existing = get_posts(
				array(
					'post_type' => BTDL_Download_CPT::POST_TYPE,
					'post_status' => 'any',
					'posts_per_page' => 1,
					'fields' => 'ids',
					'meta_query' => array(
						array(
							'key' => BTDL_Download_CPT::META_ID,
							'value' => $id,
							'compare' => '=',
						),
					),
				)
			);
			$post_data = array(
				'post_title' => isset($row['title']) ? sanitize_text_field($row['title']) : '',
				'post_name' => 'download-' . $id,
				'post_type' => BTDL_Download_CPT::POST_TYPE,
				'post_status' => 'publish',
			);
			if (!empty($existing)) {
				$post_data['ID'] = $existing[0];
				wp_update_post(wp_slash($post_data));
				$post_id = (int) $existing[0];
			} else {
				$post_id = wp_insert_post(wp_slash($post_data), true);
				if (is_wp_error($post_id)) {
					WP_CLI::warning('Failed to create download ' . $id . ': ' . $post_id->get_error_message());
					continue;
				}
			}
			update_post_meta($post_id, BTDL_Download_CPT::META_ID, $id);
			$file_path = isset($row['file']) ? trim($row['file']) : '';
			$icon_path = isset($row['icon']) ? trim($row['icon']) : '';
			update_post_meta($post_id, BTDL_Download_CPT::META_FILE, self::resolve_path($file_path));
			update_post_meta($post_id, BTDL_Download_CPT::META_VERSION, isset($row['version']) ? sanitize_text_field($row['version']) : '');
			update_post_meta($post_id, BTDL_Download_CPT::META_DESCRIPTION, isset($row['description']) ? sanitize_textarea_field($row['description']) : '');
			update_post_meta($post_id, BTDL_Download_CPT::META_INFO, isset($row['info']) ? esc_url_raw($row['info']) : '');
			update_post_meta($post_id, BTDL_Download_CPT::META_ICON, self::resolve_path($icon_path));
			update_post_meta($post_id, BTDL_Download_CPT::META_UPDATED, isset($row['updated']) ? sanitize_text_field($row['updated']) : '');
			update_post_meta($post_id, BTDL_Download_CPT::META_CREATED, isset($row['created']) ? sanitize_text_field($row['created']) : '');
			update_post_meta($post_id, BTDL_Download_CPT::META_CHANGELOG, isset($row['changelog']) ? esc_url_raw($row['changelog']) : '');
			++$count;
		}
		WP_CLI::success('Imported ' . $count . ' downloads.');
	}
}
