<?php
/**
 * Registers the Download custom post type and meta boxes.
 * Shortcode [download ID] uses _btdl_id meta. Uses btdl_ prefix for uniqueness.
 *
 * @package BT_Downloads
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * BTDL Download CPT.
 */
class BTDL_Download_CPT
{

	const POST_TYPE = 'btdl_download';
	const META_ID = '_btdl_id';
	const META_FILE = '_btdl_file';
	const META_VERSION = '_btdl_version';
	const META_DESCRIPTION = '_btdl_description';
	const META_INFO = '_btdl_info';
	const META_ICON = '_btdl_icon';
	const META_UPDATED = '_btdl_updated';
	const META_CREATED = '_btdl_created';
	const META_CHANGELOG = '_btdl_changelog';

	/**
	 * Init hooks.
	 */
	public static function init()
	{
		add_action('init', array(__CLASS__, 'register_post_type'));
		add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
		add_action('save_post_' . self::POST_TYPE, array(__CLASS__, 'save_meta'), 10, 2);
		add_filter('manage_' . self::POST_TYPE . '_posts_columns', array(__CLASS__, 'list_columns'));
		add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array(__CLASS__, 'list_column_content'), 10, 2);
		add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue_scripts'));
		add_filter('upload_dir', array(__CLASS__, 'filter_upload_dir'));
		add_action('admin_menu', array(__CLASS__, 'add_template_submenu'), 20);
		add_action('admin_init', array(__CLASS__, 'save_template_settings'));
	}

	/**
	 * Register post type.
	 */
	public static function register_post_type()
	{
		$labels = array(
			'name' => __('Downloads', 'bt-downloads'),
			'singular_name' => __('Download', 'bt-downloads'),
			'add_new' => __('Add New', 'bt-downloads'),
			'add_new_item' => __('Add New Download', 'bt-downloads'),
			'edit_item' => __('Edit Download', 'bt-downloads'),
			'new_item' => __('New Download', 'bt-downloads'),
			'view_item' => __('View Download', 'bt-downloads'),
			'search_items' => __('Search Downloads', 'bt-downloads'),
			'not_found' => __('No downloads found.', 'bt-downloads'),
			'not_found_in_trash' => __('No downloads found in Trash.', 'bt-downloads'),
			'menu_name' => __('Downloads', 'bt-downloads'),
		);
		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_icon' => 'dashicons-download',
			'menu_position' => 25,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array('title'),
			'rewrite' => false,
			'query_var' => false,
		);
		register_post_type(self::POST_TYPE, $args);
	}

	/**
	 * Add meta boxes.
	 */
	public static function add_meta_boxes()
	{
		add_meta_box(
			'btdl_download_details',
			__('Download Details', 'bt-downloads'),
			array(__CLASS__, 'render_meta_box'),
			self::POST_TYPE,
			'normal',
			'high'
		);
		add_meta_box(
			'btdl_download_shortcode',
			__('Shortcode', 'bt-downloads'),
			array(__CLASS__, 'render_shortcode_box'),
			self::POST_TYPE,
			'side'
		);
	}

	/**
	 * Render shortcode sidebar box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public static function render_shortcode_box($post)
	{
		$id = get_post_meta($post->ID, self::META_ID, true);
		if ($id !== '') {
			echo '<p><code>[download ' . esc_html($id) . ']</code></p>';
			echo '<p class="description">' . esc_html__('Use this ID in the shortcode. Assigned automatically for new downloads.', 'bt-downloads') . '</p>';
		} else {
			echo '<p class="description">' . esc_html__('Publish or update to assign the next available ID for [download N].', 'bt-downloads') . '</p>';
		}
	}

	/**
	 * Render meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public static function render_meta_box($post)
	{
		wp_nonce_field('btdl_download_save', 'btdl_download_nonce');
		$file = get_post_meta($post->ID, self::META_FILE, true);
		$version = get_post_meta($post->ID, self::META_VERSION, true);
		$desc = get_post_meta($post->ID, self::META_DESCRIPTION, true);
		$info = get_post_meta($post->ID, self::META_INFO, true);
		$icon = get_post_meta($post->ID, self::META_ICON, true);
		$updated = get_post_meta($post->ID, self::META_UPDATED, true);
		$created = get_post_meta($post->ID, self::META_CREATED, true);
		$changelog = get_post_meta($post->ID, self::META_CHANGELOG, true);
		?>
		<table class="form-table">
			<tr>
				<th><label for="btdl_download_file"><?php esc_html_e('File URL', 'bt-downloads'); ?></label></th>
				<td>
					<input type="url" id="btdl_download_file" name="btdl_download_file" value="<?php echo esc_attr($file); ?>"
						class="large-text" placeholder="/path/to/file.zip or https://...">
					<button type="button" class="button btdl-upload-file"
						data-target="btdl_download_file"><?php esc_html_e('Upload / Select file', 'bt-downloads'); ?></button>
				</td>
			</tr>
			<tr>
				<th><label for="btdl_download_version"><?php esc_html_e('Version', 'bt-downloads'); ?></label></th>
				<td><input type="text" id="btdl_download_version" name="btdl_download_version"
						value="<?php echo esc_attr($version); ?>" class="regular-text" placeholder="1.0"></td>
			</tr>
			<tr>
				<th><label for="btdl_download_description"><?php esc_html_e('Description', 'bt-downloads'); ?></label></th>
				<td><textarea id="btdl_download_description" name="btdl_download_description" rows="4"
						class="large-text"><?php echo esc_textarea($desc); ?></textarea></td>
			</tr>
			<tr>
				<th><label for="btdl_download_info"><?php esc_html_e('Info URL', 'bt-downloads'); ?></label></th>
				<td><input type="url" id="btdl_download_info" name="btdl_download_info" value="<?php echo esc_attr($info); ?>"
						class="large-text" placeholder="https://... More info link"></td>
			</tr>
			<tr>
				<th><label for="btdl_download_icon"><?php esc_html_e('Icon URL', 'bt-downloads'); ?></label></th>
				<td>
					<input type="url" id="btdl_download_icon" name="btdl_download_icon" value="<?php echo esc_attr($icon); ?>"
						class="large-text" placeholder="/path/to/icon.jpg">
					<button type="button" class="button btdl-upload-file"
						data-target="btdl_download_icon"><?php esc_html_e('Upload / Select image', 'bt-downloads'); ?></button>
				</td>
			</tr>
			<tr>
				<th><label for="btdl_download_updated"><?php esc_html_e('Updated date', 'bt-downloads'); ?></label></th>
				<td><input type="text" id="btdl_download_updated" name="btdl_download_updated"
						value="<?php echo esc_attr($updated); ?>" class="regular-text" placeholder="YYYY-MM-DD"></td>
			</tr>
			<tr>
				<th><label for="btdl_download_created"><?php esc_html_e('Created date', 'bt-downloads'); ?></label></th>
				<td><input type="text" id="btdl_download_created" name="btdl_download_created"
						value="<?php echo esc_attr($created); ?>" class="regular-text" placeholder="YYYY-MM-DD"></td>
			</tr>
			<tr>
				<th><label for="btdl_download_changelog"><?php esc_html_e('Changelog URL', 'bt-downloads'); ?></label></th>
				<td><input type="url" id="btdl_download_changelog" name="btdl_download_changelog"
						value="<?php echo esc_attr($changelog); ?>" class="large-text"></td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save meta.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function save_meta($post_id, $post)
	{
		$nonce = isset($_POST['btdl_download_nonce']) ? sanitize_text_field(wp_unslash($_POST['btdl_download_nonce'])) : '';
		if ($nonce === '' || !wp_verify_nonce($nonce, 'btdl_download_save')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$existing_id = get_post_meta($post_id, self::META_ID, true);
		if ($existing_id === '' || $existing_id === false) {
			$next = self::get_next_shortcode_id();
			update_post_meta($post_id, self::META_ID, (string) $next);
		}
		$fields = array(
			'btdl_download_file' => self::META_FILE,
			'btdl_download_version' => self::META_VERSION,
			'btdl_download_description' => self::META_DESCRIPTION,
			'btdl_download_info' => self::META_INFO,
			'btdl_download_icon' => self::META_ICON,
			'btdl_download_updated' => self::META_UPDATED,
			'btdl_download_created' => self::META_CREATED,
			'btdl_download_changelog' => self::META_CHANGELOG,
		);
		foreach ($fields as $input => $meta_key) {
			$value = isset($_POST[$input]) ? sanitize_text_field(wp_unslash($_POST[$input])) : '';
			if ($input === 'btdl_download_description') {
				$value = isset($_POST[$input]) ? sanitize_textarea_field(wp_unslash($_POST[$input])) : '';
			}
			update_post_meta($post_id, $meta_key, $value);
		}
	}

	/**
	 * Convert post to row array.
	 *
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	public static function post_to_row($post)
	{
		return array(
			'title' => $post->post_title,
			'file' => get_post_meta($post->ID, self::META_FILE, true),
			'version' => get_post_meta($post->ID, self::META_VERSION, true),
			'description' => get_post_meta($post->ID, self::META_DESCRIPTION, true),
			'info' => get_post_meta($post->ID, self::META_INFO, true),
			'icon' => get_post_meta($post->ID, self::META_ICON, true),
			'updated' => get_post_meta($post->ID, self::META_UPDATED, true),
			'created' => get_post_meta($post->ID, self::META_CREATED, true),
			'changelog' => get_post_meta($post->ID, self::META_CHANGELOG, true),
		);
	}

	/**
	 * List columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function list_columns($columns)
	{
		$new = array();
		$new['cb'] = $columns['cb'];
		$new['title'] = $columns['title'];
		$new['btdl_download_id'] = __('Shortcode ID', 'bt-downloads');
		$new['btdl_download_version'] = __('Version', 'bt-downloads');
		$new['date'] = $columns['date'];
		return $new;
	}

	/**
	 * Get next shortcode ID.
	 *
	 * @return int
	 */
	public static function get_next_shortcode_id()
	{
		global $wpdb;

		// Check cache first
		$cache_key = 'btdl_max_shortcode_id';
		$max = wp_cache_get($cache_key);

		if ($max === false) {
			// Direct query needed: no WP function can find MAX() of meta values across posts
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$max = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT MAX(CAST(pm.meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} pm
					INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
					WHERE p.post_type = %s AND p.post_status != 'trash' AND pm.meta_key = %s",
					self::POST_TYPE,
					self::META_ID
				)
			);
			// Cache for 5 minutes
			wp_cache_set($cache_key, $max, '', 300);
		}

		return $max ? (int) $max + 1 : 1;
	}

	/**
	 * List column content.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public static function list_column_content($column, $post_id)
	{
		if ($column === 'btdl_download_id') {
			$id = get_post_meta($post_id, self::META_ID, true);
			echo $id !== '' ? esc_html($id) : '—';
		}
		if ($column === 'btdl_download_version') {
			$v = get_post_meta($post_id, self::META_VERSION, true);
			echo $v ? esc_html($v) : '—';
		}
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Screen hook.
	 */
	public static function admin_enqueue_scripts($hook)
	{
		$screen = get_current_screen();
		if (!$screen || $screen->id !== self::POST_TYPE || $screen->base !== 'post') {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script(
			'btdl-download-admin',
			BTDL_URL . 'admin/download-edit.js',
			array('jquery'),
			BTDL_VERSION,
			true
		);
	}

	/**
	 * Filter upload dir for download files.
	 *
	 * @param array $uploads Upload dir array.
	 * @return array
	 */
	public static function filter_upload_dir($uploads)
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified by WP upload handler; we only read post_id
		$post_id = isset($_REQUEST['post_id']) ? absint(wp_unslash($_REQUEST['post_id'])) : 0;
		if ($post_id && get_post_type($post_id) === self::POST_TYPE) {
			$uploads['subdir'] = '/downloads' . $uploads['subdir'];
			$uploads['path'] = $uploads['basedir'] . $uploads['subdir'];
			$uploads['url'] = $uploads['baseurl'] . $uploads['subdir'];
		}
		return $uploads;
	}

	/**
	 * Add template submenu.
	 */
	public static function add_template_submenu()
	{
		add_submenu_page(
			'edit.php?post_type=' . self::POST_TYPE,
			__('Download card template', 'bt-downloads'),
			__('Card template', 'bt-downloads'),
			'edit_posts',
			'btdl-download-template',
			array(__CLASS__, 'render_template_page')
		);
	}

	/**
	 * Save template settings.
	 */
	public static function save_template_settings()
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified below
		if (!isset($_POST['btdl_template_nonce'])) {
			return;
		}
		$nonce = sanitize_text_field(wp_unslash($_POST['btdl_template_nonce']));
		if (!wp_verify_nonce($nonce, 'btdl_template_save')) {
			return;
		}
		if (!current_user_can('edit_posts')) {
			return;
		}
		if (isset($_POST['btdl_template_reset']) && sanitize_text_field(wp_unslash($_POST['btdl_template_reset'])) === '1') {
			delete_option(BTDL_Download_Template::OPTION_KEY);
			wp_safe_redirect(add_query_arg('updated', '1', wp_get_referer()));
			exit;
		}
		if (isset($_POST['btdl_css_reset']) && sanitize_text_field(wp_unslash($_POST['btdl_css_reset'])) === '1') {
			delete_option(BTDL_Download_Template::OPTION_CSS_KEY);
			wp_safe_redirect(add_query_arg('updated', '1', wp_get_referer()));
			exit;
		}
		$saved = false;
		if (isset($_POST['btdl_template'])) {
			// HTML template: use wp_kses_post to allow safe HTML
			$template = wp_kses_post(wp_unslash($_POST['btdl_template']));
			BTDL_Download_Template::save_template($template);
			$saved = true;
		}
		if (isset($_POST['btdl_custom_css'])) {
			// CSS: strip tags but preserve CSS content
			$css = wp_strip_all_tags(wp_unslash($_POST['btdl_custom_css']));
			BTDL_Download_Template::save_custom_css($css);
			$saved = true;
		}
		if ($saved) {
			wp_safe_redirect(add_query_arg('updated', '1', wp_get_referer()));
			exit;
		}
	}

	/**
	 * Render template page.
	 */
	public static function render_template_page()
	{
		$template = BTDL_Download_Template::get_template();
		$custom_css = BTDL_Download_Template::get_custom_css();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only flag from redirect after nonce-verified save
		$updated = isset($_GET['updated']) && sanitize_text_field(wp_unslash($_GET['updated'])) === '1';
		?>
		<div class="wrap">
			<h1><?php esc_html_e('Download card template', 'bt-downloads'); ?></h1>
			<?php if ($updated): ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e('Settings saved.', 'bt-downloads'); ?></p>
				</div>
			<?php endif; ?>

			<form method="post" action="">
				<?php wp_nonce_field('btdl_template_save', 'btdl_template_nonce'); ?>

				<h2><?php esc_html_e('HTML template', 'bt-downloads'); ?></h2>
				<p class="description">
					<?php esc_html_e('Edit the HTML template for download cards. Use {{variable}} for values. Use {{#variable}}...{{/variable}} to show a block only when the variable is set. Available: title, title_str, file, version, description, info, icon_style, created_formatted, updated_formatted, changelog_url, changelog_link, donate_url.', 'bt-downloads'); ?>
				</p>
				<table class="form-table">
					<tr>
						<td><textarea name="btdl_template" id="btdl_template" rows="20" class="large-text code"
								style="font-family: monospace;"><?php echo esc_textarea($template); ?></textarea></td>
					</tr>
				</table>
				<p>
					<button type="submit"
						class="button button-primary"><?php esc_html_e('Save template', 'bt-downloads'); ?></button>
					<button type="submit" name="btdl_template_reset" value="1" class="button"
						onclick="return confirm('<?php echo esc_js(__('Reset to default template?', 'bt-downloads')); ?>');"><?php esc_html_e('Reset to default', 'bt-downloads'); ?></button>
				</p>

				<hr>

				<h2><?php esc_html_e('Custom CSS', 'bt-downloads'); ?></h2>
				<p class="description">
					<?php esc_html_e('Add custom CSS to style the download cards. Use selectors like .btdl-download-card, .dl-icon-wrap, .dl-content, etc.', 'bt-downloads'); ?>
				</p>
				<table class="form-table">
					<tr>
						<td><textarea name="btdl_custom_css" id="btdl_custom_css" rows="12" class="large-text code"
								style="font-family: monospace;"
								placeholder=".btdl-download-card { border-radius: 8px; }"><?php echo esc_textarea($custom_css); ?></textarea>
						</td>
					</tr>
					<tr>
						<td>
							<h3><?php esc_html_e('Preview', 'bt-downloads'); ?></h3>
							<p class="description"><?php esc_html_e('Preview updates as you type.', 'bt-downloads'); ?></p>
							<div class="btdl-preview-frame"
								style="border: 1px solid #c3c4c7; border-radius: 4px; padding: 16px; background: #fff; min-height: 200px; max-width: 500px;">
								<iframe id="btdl_css_preview" title="<?php esc_attr_e('CSS preview', 'bt-downloads'); ?>"
									style="width: 100%; height: 220px; border: none; display: block;"></iframe>
							</div>
						</td>
					</tr>
				</table>
				<p>
					<button type="submit"
						class="button button-primary"><?php esc_html_e('Save all', 'bt-downloads'); ?></button>
					<button type="submit" name="btdl_css_reset" value="1" class="button"
						onclick="return confirm('<?php echo esc_js(__('Clear custom CSS?', 'bt-downloads')); ?>');"><?php esc_html_e('Clear custom CSS', 'bt-downloads'); ?></button>
				</p>
			</form>

			<script>
				(function () {
					var defaultCss = <?php echo wp_json_encode(BTDL_Download_Template::get_default_css()); ?>;
					var sampleHtml = <?php echo wp_json_encode('<div class="download btdl-download-card" id="dlbox"><div class="dl-icon-wrap"><p class="dl-icon" style="background: linear-gradient(0deg, rgb(179, 179, 179) 8%, rgb(255, 255, 255) 45%); background-size: cover; width: 80px; height: 80px; margin: 0;"><a href="#" title="Sample"></a></p></div><div class="dl-content"><h4>Sample Download v1.0</h4><div class="dl-body"><p class="dl-link"><a href="#">Download Sample Download v1.0</a></p><p class="dl-description">A sample description for the preview.</p><p class="dl-published">Published 01/09/14.</p><p class="dl-updated">Updated 09/14/20. <a href="#" class="changelog">Changelog</a></p><p class="dl-info"><a href="#">Donate</a> &bull; <a href="#">More info&hellip;</a></p></div></div></div>'); ?>;
					var textarea = document.getElementById('btdl_custom_css');
					var iframe = document.getElementById('btdl_css_preview');
					var timeout;
					function buildPreview() {
						var customCss = textarea ? textarea.value : '';
						var css = defaultCss + (customCss ? '\n\n/* Custom */\n' + customCss : '');
						var doc = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>' + css + '</style></head><body style="margin:12px;font-family:system-ui,sans-serif;font-size:14px;">' + sampleHtml + '</body></html>';
						if (iframe) iframe.srcdoc = doc;
					}
					function debouncedPreview() { clearTimeout(timeout); timeout = setTimeout(buildPreview, 250); }
					buildPreview();
					if (textarea) textarea.addEventListener('input', debouncedPreview);
					if (textarea) textarea.addEventListener('change', buildPreview);
				})();
			</script>
		</div>
		<?php
	}
}
