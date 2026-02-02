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
	<div class="dl-icon-wrap">
		<p class="dl-icon" style="{{icon_style}}"><a href="{{file}}" title="Download {{title_str}}"></a></p>
	</div>
	<div class="dl-content">
		<h4>{{title_str}}</h4>
		<div class="dl-body">
		<p class="dl-link"><a href="{{file}}" title="Download {{title_str}}">Download {{title_str}}</a></p>
		{{#description}}
		<p class="dl-description">{{description}}</p>
		{{/description}}
		{{#created_formatted}}
		<p class="dl-published">Published {{created_formatted}}.</p>
		{{/created_formatted}}
		{{#updated_formatted}}
		<p class="dl-updated">Updated {{updated_formatted}}.{{#changelog_url}} {{changelog_link}}{{/changelog_url}}</p>
		{{/updated_formatted}}
		{{#info}}
		<p class="dl-info"><a href="{{donate_url}}">Donate</a> &bull; <a href="{{info}}" title="More information on {{title}}">More info&hellip;</a></p>
		{{/info}}
		</div>
	</div>
</div>';
	}

	/**
	 * Get default CSS.
	 *
	 * @return string
	 */
	public static function get_default_css()
	{
		return '/* BT Downloads - flexbox layout */
.btdl-download-card {
	display: flex;
	flex-direction: row;
	align-items: stretch;
	gap: 1rem;
	max-width: 100%;
	border: 1px solid currentColor;
	border-radius: 6px;
	overflow: hidden;
	font-family: inherit;
	font-size: inherit;
	color: inherit;
}
.btdl-download-card .dl-icon-wrap {
	flex: 0 0 100px;
	min-width: 100px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: rgba(0,0,0,.05);
}
.btdl-download-card .dl-icon {
	width: 80px;
	height: 80px;
	margin: 0;
	border-radius: 4px;
	background-size: cover !important;
	background-position: center !important;
}
.btdl-download-card .dl-icon a {
	display: block;
	width: 100%;
	height: 100%;
}
.btdl-download-card .dl-content {
	flex: 1;
	min-width: 0;
	padding: 0.75rem 1rem 0.75rem 0;
	display: flex;
	flex-direction: column;
	gap: 0.35rem;
}
.btdl-download-card .dl-content h4 {
	margin: 0 0 0.25rem;
	font-size: 1.1em;
	font-weight: 600;
	line-height: 1.3;
}
.btdl-download-card .dl-body {
	margin: 0;
}
.btdl-download-card .dl-body p {
	margin: 0.2em 0;
	font-size: 0.9em;
	line-height: 1.4;
}
.btdl-download-card .dl-link {
	font-weight: 500;
}
.btdl-download-card .dl-link a {
	color: inherit;
	text-decoration: underline;
}
.btdl-download-card .dl-link a:hover {
	text-decoration: none;
}
.btdl-download-card .dl-published,
.btdl-download-card .dl-updated {
	font-size: 0.85em;
	opacity: 0.85;
}
.btdl-download-card .dl-info {
	margin-top: 0.5rem !important;
}
.btdl-download-card .dl-info a {
	color: inherit;
	text-decoration: underline;
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
