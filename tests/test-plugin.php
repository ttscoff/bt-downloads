<?php
/**
 * Tests for the BT Downloads plugin.
 *
 * @package BT_Downloads
 */

class BT_Downloads_Plugin_Tests extends WP_UnitTestCase
{

	/**
	 * Shortcode returns error markup when download ID is missing or not found.
	 */
	public function test_shortcode_returns_error_when_download_not_found()
	{
		$output = do_shortcode('[download 999999]');
		$this->assertStringContainsString('btdl-download-error', $output);
		$this->assertStringContainsString('Download not found', $output);
	}

	/**
	 * Shortcode returns empty string when no ID is provided.
	 */
	public function test_shortcode_returns_empty_when_no_id()
	{
		$output = do_shortcode('[download]');
		$this->assertSame('', $output);
	}

	/**
	 * BTDL_Download::get returns null for empty or non-existent ID.
	 */
	public function test_get_returns_null_for_empty_id()
	{
		$this->assertNull(BTDL_Download::get(''));
	}

	/**
	 * BTDL_Download::get returns null for non-existent shortcode ID.
	 */
	public function test_get_returns_null_for_nonexistent_id()
	{
		$this->assertNull(BTDL_Download::get('999999'));
	}

	/**
	 * Download shortcode is registered.
	 */
	public function test_download_shortcode_is_registered()
	{
		$this->assertTrue(shortcode_exists('download'));
		$this->assertTrue(shortcode_exists('btdl'));
	}

	/**
	 * btdl shortcode alias works the same as download.
	 */
	public function test_btdl_shortcode_alias()
	{
		$out_download = do_shortcode('[download 999999]');
		$out_btdl = do_shortcode('[btdl 999999]');
		$this->assertSame($out_download, $out_btdl);
		$this->assertStringContainsString('Download not found', $out_btdl);
	}

	/**
	 * Shortcode with valid download renders card with title and link.
	 */
	public function test_shortcode_renders_card_for_valid_download()
	{
		$post_id = $this->factory()->post->create(
			array(
				'post_type' => BTDL_Download_CPT::POST_TYPE,
				'post_status' => 'publish',
				'post_title' => 'Test Download',
			)
		);
		update_post_meta($post_id, BTDL_Download_CPT::META_ID, '42');
		update_post_meta($post_id, BTDL_Download_CPT::META_FILE, 'https://example.com/file.zip');
		update_post_meta($post_id, BTDL_Download_CPT::META_VERSION, '1.0');
		update_post_meta($post_id, BTDL_Download_CPT::META_DESCRIPTION, 'A test download.');

		$output = do_shortcode('[download 42]');

		$this->assertStringContainsString('btdl-download-card', $output);
		$this->assertStringContainsString('Test Download', $output);
		$this->assertStringContainsString('https://example.com/file.zip', $output);
		$this->assertStringContainsString('A test download.', $output);
		$this->assertStringNotContainsString('btdl-download-error', $output);
	}

	/**
	 * Shortcode uses first attribute as ID (numeric string).
	 */
	public function test_shortcode_uses_first_attribute_as_id()
	{
		$post_id = $this->factory()->post->create(
			array(
				'post_type' => BTDL_Download_CPT::POST_TYPE,
				'post_status' => 'publish',
				'post_title' => 'First ID Test',
			)
		);
		update_post_meta($post_id, BTDL_Download_CPT::META_ID, '7');

		$output = do_shortcode('[download 7]');
		$this->assertStringContainsString('First ID Test', $output);
	}

	/**
	 * BTDL_Download::get returns row for published download with matching shortcode ID.
	 */
	public function test_get_returns_row_for_valid_id()
	{
		$post_id = $this->factory()->post->create(
			array(
				'post_type' => BTDL_Download_CPT::POST_TYPE,
				'post_status' => 'publish',
				'post_title' => 'Get Test',
			)
		);
		update_post_meta($post_id, BTDL_Download_CPT::META_ID, '11');
		update_post_meta($post_id, BTDL_Download_CPT::META_FILE, 'https://example.com/get.zip');

		$row = BTDL_Download::get('11');
		$this->assertIsArray($row);
		$this->assertSame('Get Test', $row['title']);
		$this->assertSame('https://example.com/get.zip', $row['file']);
	}

	/**
	 * get_template_data builds title_str with version.
	 */
	public function test_get_template_data_includes_version_in_title_str()
	{
		$row = array(
			'title' => 'My Plugin',
			'file' => 'https://example.com/plugin.zip',
			'version' => '2.1',
			'description' => '',
			'info' => '',
			'icon' => '',
			'updated' => '',
			'created' => '',
			'changelog' => '',
		);
		$data = BTDL_Download::get_template_data($row);
		$this->assertSame('My Plugin v2.1', $data['title_str']);
		$this->assertSame('2.1', $data['version']);
	}

	/**
	 * get_template_data formats dates.
	 */
	public function test_get_template_data_formats_dates()
	{
		$row = array(
			'title' => 'Dated',
			'file' => 'https://example.com/f.zip',
			'version' => '',
			'description' => '',
			'info' => '',
			'icon' => '',
			'updated' => '2024-06-15',
			'created' => '2024-01-01',
			'changelog' => '',
		);
		$data = BTDL_Download::get_template_data($row);
		$this->assertSame('01/01/2024', $data['created_formatted']);
		$this->assertSame('06/15/2024', $data['updated_formatted']);
	}

	/**
	 * render_card produces HTML with template variables replaced.
	 */
	public function test_render_card_replaces_template_variables()
	{
		$row = array(
			'title' => 'Rendered Card',
			'file' => 'https://example.com/rendered.zip',
			'version' => '1.0',
			'description' => 'Description here.',
			'info' => '',
			'icon' => '',
			'updated' => '',
			'created' => '',
			'changelog' => '',
		);
		$output = BTDL_Download::render_card($row);
		$this->assertStringContainsString('Rendered Card v1.0', $output);
		$this->assertStringContainsString('https://example.com/rendered.zip', $output);
		$this->assertStringContainsString('Description here.', $output);
	}

	/**
	 * BTDL_Download_CPT::post_to_row returns array with meta.
	 */
	public function test_post_to_row_returns_meta()
	{
		$post_id = $this->factory()->post->create(
			array(
				'post_type' => BTDL_Download_CPT::POST_TYPE,
				'post_status' => 'publish',
				'post_title' => 'Row Test',
			)
		);
		update_post_meta($post_id, BTDL_Download_CPT::META_FILE, 'https://example.com/row.zip');
		update_post_meta($post_id, BTDL_Download_CPT::META_DESCRIPTION, 'Row desc');

		$post = get_post($post_id);
		$row = BTDL_Download_CPT::post_to_row($post);
		$this->assertSame('Row Test', $row['title']);
		$this->assertSame('https://example.com/row.zip', $row['file']);
		$this->assertSame('Row desc', $row['description']);
	}

	/**
	 * BTDL_Download_CPT::get_next_shortcode_id returns 1 when no downloads exist.
	 */
	public function test_get_next_shortcode_id_returns_one_when_empty()
	{
		wp_cache_delete('btdl_max_shortcode_id');
		$next = BTDL_Download_CPT::get_next_shortcode_id();
		$this->assertSame(1, $next);
	}

	/**
	 * BTDL_Download_Template::render replaces simple variables.
	 */
	public function test_template_render_replaces_simple_variables()
	{
		$template = '<h1>{{title_str}}</h1><a href="{{file}}">Download</a>';
		$data = array(
			'title_str' => 'Test Title',
			'file' => 'https://example.com/file.zip',
		);
		$output = BTDL_Download_Template::render($template, $data);
		$this->assertStringContainsString('Test Title', $output);
		$this->assertStringContainsString('https://example.com/file.zip', $output);
		$this->assertStringNotContainsString('{{title_str}}', $output);
	}

	/**
	 * BTDL_Download_Template::render respects {{#var}} when value is truthy.
	 */
	public function test_template_render_conditional_block_truthy()
	{
		$template = 'Before {{#description}}<p>{{description}}</p>{{/description}} After';
		$data = array('description' => 'Shown.');
		$output = BTDL_Download_Template::render($template, $data);
		$this->assertStringContainsString('<p>Shown.</p>', $output);
		$this->assertStringContainsString('Before', $output);
		$this->assertStringContainsString('After', $output);
	}

	/**
	 * BTDL_Download_Template::render omits {{#var}} block when value is empty.
	 */
	public function test_template_render_conditional_block_falsy()
	{
		$template = 'Before {{#description}}<p>{{description}}</p>{{/description}} After';
		$data = array('description' => '');
		$output = BTDL_Download_Template::render($template, $data);
		$this->assertStringNotContainsString('<p>', $output);
		$this->assertStringContainsString('Before  After', $output);
	}

	/**
	 * BTDL_Download_Template::get_template returns default when option not set.
	 */
	public function test_template_get_returns_default_when_empty()
	{
		delete_option(BTDL_Download_Template::OPTION_KEY);
		$template = BTDL_Download_Template::get_template();
		$this->assertStringContainsString('btdl-download-card', $template);
		$this->assertStringContainsString('{{title_str}}', $template);
		$this->assertStringContainsString('{{file}}', $template);
	}

	/**
	 * Shortcode enqueues frontend styles when used.
	 */
	public function test_shortcode_enqueues_styles()
	{
		$post_id = $this->factory()->post->create(
			array(
				'post_type' => BTDL_Download_CPT::POST_TYPE,
				'post_status' => 'publish',
				'post_title' => 'Style Test',
			)
		);
		update_post_meta($post_id, BTDL_Download_CPT::META_ID, '1');

		do_shortcode('[download 1]');
		do_action('wp_enqueue_scripts');

		$this->assertTrue(wp_style_is('btdl-download-card', 'enqueued'));
	}
}
