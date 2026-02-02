<?php
/**
 * Insert-download picker for classic and block editor.
 *
 * @package BT_Downloads
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * BTDL Download Editor.
 */
class BTDL_Download_Editor
{

	/**
	 * Init.
	 */
	public static function init()
	{
		add_action('rest_api_init', array(__CLASS__, 'register_rest_route'));
		add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue_scripts'));
		add_action('enqueue_block_editor_assets', array(__CLASS__, 'enqueue_block_editor_assets'));
		add_filter('mce_external_plugins', array(__CLASS__, 'mce_external_plugins'));
		add_filter('mce_buttons', array(__CLASS__, 'mce_buttons'));
		add_action('admin_footer-post.php', array(__CLASS__, 'render_picker_modal'));
		add_action('admin_footer-post-new.php', array(__CLASS__, 'render_picker_modal'));
		add_action('init', array(__CLASS__, 'register_block'), 20);
	}

	/**
	 * Register REST route.
	 */
	public static function register_rest_route()
	{
		register_rest_route(
			'btdl/v1',
			'/downloads',
			array(
				'methods' => 'GET',
				'permission_callback' => function () {
					return current_user_can('edit_posts');
				},
				'callback' => array(__CLASS__, 'rest_list_downloads'),
			)
		);
	}

	/**
	 * REST list downloads callback.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function rest_list_downloads($request)
	{
		$posts = get_posts(
			array(
				'post_type' => BTDL_Download_CPT::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
			)
		);
		$list = array();
		foreach ($posts as $post) {
			$id = get_post_meta($post->ID, BTDL_Download_CPT::META_ID, true);
			if ($id === '' || $id === false) {
				continue;
			}
			$list[] = array(
				'id' => $id,
				'title' => $post->post_title,
			);
		}
		return rest_ensure_response($list);
	}

	/**
	 * Admin enqueue scripts.
	 *
	 * @param string $hook Hook.
	 */
	public static function admin_enqueue_scripts($hook)
	{
		if ($hook !== 'post.php' && $hook !== 'post-new.php') {
			return;
		}
		wp_enqueue_script(
			'btdl-download-picker',
			BTDL_URL . 'admin/insert-download-picker.js',
			array('jquery'),
			BTDL_VERSION,
			true
		);
		wp_localize_script(
			'btdl-download-picker',
			'btdlDownloadPicker',
			array(
				'restUrl' => rest_url('btdl/v1/downloads'),
				'nonce' => wp_create_nonce('wp_rest'),
			)
		);
		wp_enqueue_style(
			'btdl-download-picker',
			BTDL_URL . 'admin/insert-download-picker.css',
			array(),
			BTDL_VERSION
		);
	}

	/**
	 * TinyMCE external plugins.
	 *
	 * @param array $plugins Plugins.
	 * @return array
	 */
	public static function mce_external_plugins($plugins)
	{
		$plugins['btdl_insert_download'] = BTDL_URL . 'admin/tinymce-insert-download.js';
		return $plugins;
	}

	/**
	 * TinyMCE buttons.
	 *
	 * @param array $buttons Buttons.
	 * @return array
	 */
	public static function mce_buttons($buttons)
	{
		$buttons[] = 'btdl_insert_download';
		return $buttons;
	}

	/**
	 * Render picker modal.
	 */
	public static function render_picker_modal()
	{
		$screen = get_current_screen();
		if (!$screen || !post_type_supports($screen->post_type, 'editor')) {
			return;
		}
		?>
		<div id="btdl-download-picker-modal" class="btdl-download-picker-modal" style="display:none;" aria-hidden="true">
			<div class="btdl-download-picker-backdrop"></div>
			<div class="btdl-download-picker-dialog" role="dialog"
				aria-label="<?php esc_attr_e('Insert download', 'bt-downloads'); ?>">
				<div class="btdl-download-picker-header">
					<h2><?php esc_html_e('Insert download', 'bt-downloads'); ?></h2>
					<button type="button" class="btdl-download-picker-close"
						aria-label="<?php esc_attr_e('Close', 'bt-downloads'); ?>">&times;</button>
				</div>
				<div class="btdl-download-picker-body">
					<p class="btdl-download-picker-loading"><?php esc_html_e('Loading downloads…', 'bt-downloads'); ?></p>
					<div class="btdl-download-picker-search-wrap" style="display:none;">
						<input type="search" class="btdl-download-picker-search"
							placeholder="<?php esc_attr_e('Search downloads…', 'bt-downloads'); ?>" autocomplete="off">
					</div>
					<ul class="btdl-download-picker-list" style="display:none;"></ul>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue block editor assets.
	 */
	public static function enqueue_block_editor_assets()
	{
		$list = array();
		$posts = get_posts(
			array(
				'post_type' => BTDL_Download_CPT::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
			)
		);
		foreach ($posts as $post) {
			$id = get_post_meta($post->ID, BTDL_Download_CPT::META_ID, true);
			if ($id !== '' && $id !== false) {
				$list[] = array('id' => $id, 'title' => $post->post_title);
			}
		}
		wp_enqueue_script(
			'btdl-download-block',
			BTDL_URL . 'admin/download-block.js',
			array('wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor'),
			BTDL_VERSION,
			true
		);
		wp_enqueue_script(
			'btdl-download-format',
			BTDL_URL . 'admin/download-format.js',
			array('wp-rich-text', 'wp-element', 'wp-i18n', 'wp-block-editor'),
			BTDL_VERSION,
			true
		);
		wp_localize_script(
			'btdl-download-block',
			'btdlDownloadPicker',
			array(
				'restUrl' => rest_url('btdl/v1/downloads'),
				'nonce' => wp_create_nonce('wp_rest'),
				'list' => $list,
			)
		);
	}

	/**
	 * Register block.
	 */
	public static function register_block()
	{
		register_block_type(
			'btdl/download',
			array(
				'api_version' => 2,
				'title' => __('Download', 'bt-downloads'),
				'description' => __('Insert a download card by selecting from your downloads.', 'bt-downloads'),
				'category' => 'embed',
				'icon' => 'download',
				'attributes' => array(
					'downloadId' => array(
						'type' => 'string',
						'default' => '',
					),
				),
				'editor_script' => 'btdl-download-block',
				'render_callback' => array(__CLASS__, 'render_download_block'),
			)
		);
	}

	/**
	 * Render download block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render_download_block($attributes)
	{
		$id = isset($attributes['downloadId']) ? $attributes['downloadId'] : '';
		if ($id === '') {
			return '';
		}
		return do_shortcode('[download ' . esc_attr($id) . ']');
	}
}
