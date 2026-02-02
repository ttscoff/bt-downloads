<?php
/**
 * Download shortcode: outputs a download card from the Download custom post type.
 *
 * @package BT_Downloads
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * BTDL Download shortcode.
 */
class BTDL_Download
{

	/**
	 * Register shortcode(s).
	 */
	public static function register_shortcode()
	{
		add_shortcode('download', array(__CLASS__, 'shortcode'));
		add_shortcode('btdl', array(__CLASS__, 'shortcode'));
	}

	/**
	 * Get download by shortcode ID.
	 *
	 * @param string $id Shortcode ID.
	 * @return array|null
	 */
	public static function get($id)
	{
		$id = trim((string) $id);
		if ($id === '') {
			return null;
		}
		// meta_query needed to find download by shortcode ID meta
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$posts = get_posts(
			array(
				'post_type' => BTDL_Download_CPT::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'meta_query' => array(
					array(
						'key' => BTDL_Download_CPT::META_ID,
						'value' => $id,
						'compare' => '=',
					),
				),
			)
		);
		if (empty($posts)) {
			return null;
		}
		return BTDL_Download_CPT::post_to_row($posts[0]);
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array  $atts    Attributes.
	 * @param string $content Content.
	 * @param string $tag     Tag.
	 * @return string
	 */
	public static function shortcode($atts, $content = null, $tag = '')
	{
		$atts = array_values(array_filter(array_map('trim', (array) $atts)));
		$id = isset($atts[0]) ? $atts[0] : '';
		if ($id === '') {
			return '';
		}
		$row = self::get($id);
		if (!$row) {
			return '<p class="btdl-download-error">' . esc_html__('Download not found:', 'bt-downloads') . ' ' . esc_html($id) . '</p>';
		}
		BTDL_Download_Template::enqueue_styles();
		return self::render_card($row);
	}

	/**
	 * Build template data from row.
	 *
	 * @param array $row Row data.
	 * @return array
	 */
	public static function get_template_data(array $row)
	{
		$title = isset($row['title']) ? $row['title'] : '';
		$file = isset($row['file']) ? $row['file'] : '';
		$version = isset($row['version']) ? trim($row['version']) : '';
		$desc = isset($row['description']) ? trim($row['description']) : '';
		$info = isset($row['info']) ? trim($row['info']) : '';
		$icon = isset($row['icon']) ? trim($row['icon']) : '';
		$updated = isset($row['updated']) ? trim($row['updated']) : '';
		$created = isset($row['created']) ? trim($row['created']) : '';
		$changelog = isset($row['changelog']) ? trim($row['changelog']) : '';
		$version_str = $version === '' ? '' : ' v' . $version;
		$title_str = $title . $version_str;

		$uploads = wp_upload_dir();
		$cdn = (defined('BT_IMPORT_IMAGE_BASE_URL') && BT_IMPORT_IMAGE_BASE_URL !== '') ? rtrim(BT_IMPORT_IMAGE_BASE_URL, '/') : home_url();
		if ($file !== '' && strpos($file, 'http') !== 0) {
			if (strpos($file, '/') === 0) {
				$file = rtrim($uploads['baseurl'], '/') . $file;
			} else {
				$file = $cdn . '/' . $file;
			}
		}
		$icon_style = '';
		if ($icon !== '') {
			if (strpos($icon, 'http') === 0) {
				$icon_url = $icon;
			} elseif (strpos($icon, '/') === 0) {
				$icon_url = rtrim($uploads['baseurl'], '/') . $icon;
			} else {
				$icon_url = $cdn . '/' . $icon;
			}
			$icon_style = 'background:100% / cover no-repeat url(' . esc_url($icon_url) . '), linear-gradient(0deg, rgb(179, 179, 179) 8%, rgb(255, 255, 255) 45%, rgb(255, 255, 255) 100%);';
		}

		$created_formatted = '';
		if ($created !== '') {
			$ts = strtotime($created);
			$created_formatted = $ts ? gmdate('m/d/Y', $ts) : '';
		}
		$updated_formatted = '';
		if ($updated !== '') {
			$ts = strtotime($updated);
			$updated_formatted = $ts ? gmdate('m/d/Y', $ts) : '';
		}
		$changelog_link = $changelog === '' ? '' : '<a href="' . esc_url($changelog) . '" class="changelog">' . esc_html__('Changelog', 'bt-downloads') . '</a>';
		$donate_url = home_url('/donate/');

		return array(
			'title' => $title,
			'title_str' => $title_str,
			'file' => esc_url($file),
			'version' => $version,
			'description' => $desc,
			'info' => $info,
			'icon_style' => $icon_style,
			'created_formatted' => $created_formatted,
			'updated_formatted' => $updated_formatted,
			'changelog_url' => $changelog,
			'changelog_link' => $changelog_link,
			'donate_url' => esc_url($donate_url),
		);
	}

	/**
	 * Render download card.
	 *
	 * @param array $row Row data.
	 * @return string
	 */
	public static function render_card(array $row)
	{
		$data = self::get_template_data($row);
		$template = BTDL_Download_Template::get_template();
		return BTDL_Download_Template::render($template, $data);
	}
}
