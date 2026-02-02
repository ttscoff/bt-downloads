<?php
/**
 * Editable download card template. Mustache-style {{var}}, {{#var}}...{{/var}}, {{^var}}...{{/var}}.
 *
 * @package BT_Downloads
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * BTDL Download Template.
 */
class BTDL_Download_Template
{

	const OPTION_KEY = 'btdl_card_template';
	const OPTION_CSS_KEY = 'btdl_card_css';

	/**
	 * Get default template.
	 *
	 * @return string
	 */
	public static function get_default_template()
	{
		return '<div class="download btdl-download-card" id="dlbox">
	<a class="dl-icon-wrap" href="{{file}}" title="Download {{title_str}}">
		<span class="dl-icon" style="{{icon_style}}"></span>
	</a>
	<div class="dl-content">
		<h3 class="dl-title">{{title_str}}</h3>
		<p class="dl-link"><a href="{{file}}" title="Download {{title_str}}">Download {{title_str}}</a></p>
		{{#description}}
		<p class="dl-description">{{description}}</p>
		{{/description}}
		<div class="dl-meta">
			{{#created_formatted}}
			<span class="dl-published">Published {{created_formatted}}.</span>
			{{/created_formatted}}
			{{#updated_formatted}}
			<span class="dl-updated">Updated {{updated_formatted}}.{{#changelog_url}} {{changelog_link}}{{/changelog_url}}</span>
			{{/updated_formatted}}
		</div>
		{{#info}}
		<p class="dl-info"><a href="{{donate_url}}">Donate</a> &bull; <a href="{{info}}" title="More information on {{title}}">More info&hellip;</a></p>
		{{/info}}
	</div>
</div>';
	}

	/**
	 * Sample data for admin preview (same shape as get_template_data output).
	 *
	 * @return array
	 */
	public static function get_preview_sample_data()
	{
		return array(
			'title' => 'Sample Download',
			'title_str' => 'Sample Download v1.0',
			'file' => '#',
			'version' => '1.0',
			'description' => 'A sample description for the preview.',
			'info' => '#',
			'icon_style' => '',
			'created_formatted' => '01/09/14',
			'updated_formatted' => '09/14/20',
			'changelog_url' => '#',
			'changelog_link' => '<a href="#" class="changelog">Changelog</a>',
			'donate_url' => '#',
		);
	}

	/**
	 * Default download icon SVG (minimalistic, 24x24 viewBox).
	 *
	 * @return string
	 */
	private static function get_download_icon_data_uri()
	{
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">'
			. '<path opacity="0.5" d="M3 15C3 17.8284 3 19.2426 3.87868 20.1213C4.75736 21 6.17157 21 9 21H15C17.8284 21 19.2426 21 20.1213 20.1213C21 19.2426 21 17.8284 21 15" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
			. '<path d="M12 3V16M12 16L16 11.625M12 16L8 11.625" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
			. '</svg>';
		return 'data:image/svg+xml,' . rawurlencode($svg);
	}

	/**
	 * Get default CSS.
	 *
	 * @return string
	 */
	public static function get_default_css()
	{
		$download_icon_uri = self::get_download_icon_data_uri();
		return '/* BT Downloads - card layout (low specificity for overrides) */
.btdl-download-card {
	display: flex;
	flex-direction: row;
	align-items: stretch;
	gap: 1.25rem;
	max-width: 100%;
	border: 1px solid #e5e7eb;
	border-radius: 8px;
	overflow: hidden;
	font-family: inherit;
	font-size: 1rem;
	line-height: 1.5;
	color: #374151;
	background: #fff;
}
.btdl-download-card .dl-icon-wrap {
	flex: 0 0 112px;
	min-width: 112px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #f3f4f6;
	text-decoration: none;
	color: inherit;
}
.btdl-download-card .dl-icon-wrap:hover {
	background: #e5e7eb;
}
.btdl-download-card .dl-icon {
	display: block;
	position: relative;
	width: 100%;
	height: 100%;
	border-radius: 6px;
	background-size: cover;
	background-position: center;
	background-color: #e5e7eb;
}
.btdl-download-card .dl-icon::after {
	content: "";
	position: absolute;
	right: 0;
	top: 5px;
	width: 40px;
	height: 40px;
	background-image: url(' . $download_icon_uri . ');
	background-size: contain;
	background-repeat: no-repeat;
	background-position: center;
	pointer-events: none;
}
.btdl-download-card .dl-content {
	flex: 1;
	min-width: 0;
	padding: 1rem 1.25rem 1rem 0;
	display: grid;
	grid-template-columns: 1fr;
	gap: 0.25rem 0;
	align-content: start;
}
.btdl-download-card .dl-title {
	margin: 0;
	font-size: 1.125rem;
	font-weight: 600;
	line-height: 1.35;
	color: #111827;
}
.btdl-download-card .dl-link {
	margin: 0.25rem 0 0;
	font-weight: 500;
	font-size: 0.9375rem;
}
.btdl-download-card .dl-description {
	margin: 0.5rem 0 0;
	font-size: 0.9375rem;
	line-height: 1.5;
	color: #6b7280;
}
.btdl-download-card .dl-meta {
	margin: 0.5rem 0 0;
	font-size: 0.8125rem;
	line-height: 1.4;
	color: #9ca3af;
}
.btdl-download-card .dl-meta .dl-published + .dl-updated::before {
	content: " · ";
}
.btdl-download-card .dl-info {
	margin: 0.5rem 0 0;
	font-size: 0.8125rem;
	color: #9ca3af;
}
.btdl-download-card .dl-info a {
	color: inherit;
	text-decoration: underline;
}
.btdl-download-card .dl-info a:hover {
	color: #6b7280;
}
.btdl-download-card .changelog {
	text-decoration: underline;
}';
	}

	/**
	 * Get custom CSS.
	 *
	 * @return string
	 */
	public static function get_custom_css()
	{
		return (string) get_option(self::OPTION_CSS_KEY, '');
	}

	/**
	 * Save custom CSS.
	 *
	 * @param string $css CSS.
	 * @return bool
	 */
	public static function save_custom_css($css)
	{
		return update_option(self::OPTION_CSS_KEY, $css);
	}

	/**
	 * Enqueue styles on frontend.
	 */
	public static function enqueue_styles()
	{
		if (wp_style_is('btdl-download-card', 'enqueued')) {
			return;
		}
		wp_register_style('btdl-download-card', false, array(), BTDL_VERSION);
		wp_enqueue_style('btdl-download-card');
		$default = self::get_default_css();
		$custom = trim(self::get_custom_css());
		$inline = $default . ($custom !== '' ? "\n\n/* Custom CSS */\n" . $custom : '');
		wp_add_inline_style('btdl-download-card', $inline);
	}

	/**
	 * Get active template.
	 *
	 * @return string
	 */
	public static function get_template()
	{
		$saved = get_option(self::OPTION_KEY, '');
		return $saved !== '' ? $saved : self::get_default_template();
	}

	/**
	 * Save template.
	 *
	 * @param string $template Template HTML.
	 * @return bool
	 */
	public static function save_template($template)
	{
		return update_option(self::OPTION_KEY, $template);
	}

	/**
	 * Find block end position.
	 *
	 * @param string $template Template string.
	 * @param string $name     Block name.
	 * @param int    $start    Start offset.
	 * @return int|false
	 */
	private static function find_block_end($template, $name, $start)
	{
		$len = strlen($template);
		$open = '{{#' . $name . '}}';
		$open_neg = '{{^' . $name . '}}';
		$close = '{{/' . $name . '}}';
		$depth = 1;
		$i = $start;
		while ($i < $len) {
			if (substr($template, $i, strlen($close)) === $close) {
				--$depth;
				if ($depth === 0) {
					return $i;
				}
				$i += strlen($close);
				continue;
			}
			if (substr($template, $i, strlen($open)) === $open) {
				++$depth;
				$i += strlen($open);
				continue;
			}
			if (substr($template, $i, strlen($open_neg)) === $open_neg) {
				++$depth;
				$i += strlen($open_neg);
				continue;
			}
			++$i;
		}
		return false;
	}

	/**
	 * Render template with data.
	 *
	 * @param string $template Template string.
	 * @param array  $data     Data array.
	 * @return string
	 */
	public static function render($template, array $data)
	{
		$pattern = '/\{\{(#|\^)([a-zA-Z_][a-zA-Z0-9_]*)\}\}/';
		$max_passes = 30;
		$pass = 0;
		while ($pass < $max_passes && preg_match($pattern, $template)) {
			if (!preg_match($pattern, $template, $m, PREG_OFFSET_CAPTURE)) {
				break;
			}
			$type = $m[1][0];
			$key = $m[2][0];
			$start = $m[0][1] + strlen($m[0][0]);
			$end = self::find_block_end($template, $key, $start);
			if ($end === false) {
				break;
			}
			$inner = substr($template, $start, $end - $start);
			$value = isset($data[$key]) ? $data[$key] : '';
			$truthy = ($value !== '' && $value !== false && $value !== null);
			$replacement = (($type === '#' && $truthy) || ($type === '^' && !$truthy)) ? $inner : '';
			$template = substr($template, 0, $m[0][1]) . $replacement . substr($template, $end + strlen('{{/' . $key . '}}'));
			++$pass;
		}
		$template = preg_replace_callback(
			'/\{\{([a-zA-Z_][a-zA-Z0-9_]*)\}\}/',
			function ($m) use ($data) {
				$key = $m[1];
				if (!array_key_exists($key, $data)) {
					return '';
				}
				$v = $data[$key];
				if ($v === null || $v === false) {
					return '';
				}
				if (preg_match('/(_style|_link|_html)$/', $key)) {
					return (string) $v;
				}
				return esc_html((string) $v);
			},
			$template
		);
		return $template;
	}
}
