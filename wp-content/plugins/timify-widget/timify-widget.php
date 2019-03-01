<?php
/**
 * @package Timify Widget
 */
/*
Plugin Name: Timify Widget
Plugin URI: https://www.timify.com/
Description: Timify is the largest booking system in the world!
Version: 1.0
Author: Timify
Author URI: https://www.timify.com/
License: GPLv2 or later
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

class TimifyWidget {

	public $timifyWidgetId			= false;
	public $timifyWidgetLanguage	= false;
	public $timifyWidgetPosition	= false;
	public $timifyWidgetButtonLabel	= false;

	/** Front-end functionality **/
	public function __construct() {

		//Load widget data
		$this->timifyWidgetId			= get_option('timify_widget_id');
		$this->timifyWidgetLanguage		= get_option('timify_widget_language');
		$this->timifyWidgetPosition		= get_option('timify_widget_position');
		$this->timifyWidgetButtonLabel	= get_option('timify_widget_button_label');

		if ($this->timifyWidgetLanguage === false) {
			$this->timifyWidgetLanguage = 'de';
		}
		if ($this->timifyWidgetPosition === false) {
			$this->timifyWidgetPosition = 'left';
		}

		if ($this->timifyWidgetButtonLabel === false) {
			$this->timifyWidgetButtonLabel = 'Make an appointment';
		}

		//Add widget after every post
		if ($this->timifyWidgetPosition == 'after_post') {
			add_filter('the_content', array($this, 'insertWidgetAfterContent'));
		}

		add_action('wp_footer', array($this, 'addToFooter'));

	}

	public function addToFooter() {

		//Add widget code to the footer
		if ($this->timifyWidgetId !== false && trim($this->timifyWidgetId) !== '') {
?>
			<script async id="timify"
				<?php if ($this->timifyWidgetPosition == 'left' || $this->timifyWidgetPosition == 'right') { ?>
					data-id="<?php echo $this->timifyWidgetId; ?>"
				<?php } ?>
				data-lang="<?php echo $this->timifyWidgetLanguage; ?>"
				type="text/javascript"
				data-position="<?php echo ($this->timifyWidgetPosition != 'left' && $this->timifyWidgetPosition != 'right' ? 'multiple' : $this->timifyWidgetPosition); ?>"
				src="https://widget.timify.com/js/widget.js">
			</script>
<?php
		}

	}

	public function insertWidgetAfterContent($content) {

		if ($this->timifyWidgetId !== false && trim($this->timifyWidgetId) !== '') {
			$content .= '<button class="timify-button" data-id="' . $this->timifyWidgetId . '">' .
					$this->timifyWidgetButtonLabel .
				'</button>';
		}

		return $content;
	}

	/** Back-end functionality **/
	public static function initWidgetSettings() {

		add_menu_page(
			__( 'Timify Widget Settings', 'textdomain' ),
			'Timify Widget',
			'manage_options',
			'timify-widget/timify-widget-admin.php',
			'',
			//plugins_url( 'timify-widget/img/icon.png' ),
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIHdpZHRoPSIxMzBweCIgaGVpZ2h0PSIxOTNweCIgdmlld0JveD0iMCAwIDEzMCAxOTMiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+ICAgICAgICA8dGl0bGU+VGltaWZ5LUFsb25lPC90aXRsZT4gICAgPGRlc2M+Q3JlYXRlZCB3aXRoIFNrZXRjaC48L2Rlc2M+ICAgIDxkZWZzPiAgICAgICAgPHBvbHlnb24gaWQ9InBhdGgtMSIgcG9pbnRzPSIwIDAgMTI4LjU0ODQyMyAwIDEyOC41NDg0MjMgMTkyLjY2Mjk4OCAwIDE5Mi42NjI5ODgiPjwvcG9seWdvbj4gICAgPC9kZWZzPiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4gICAgICAgIDxnIGlkPSJUaW1pZnktQWxvbmUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0xOC4wMDAwMDAsIDAuMDAwMDAwKSI+ICAgICAgICAgICAgPHBvbHlnb24gaWQ9IkZpbGwtMSIgZmlsbD0iI0NCM0I0MyIgcG9pbnRzPSI2My44NTgxOTY1IDQzLjc2NDM3MzEgMTAyLjU2ODI0NSA0My43NjQzNzMxIDEwMi41NjgyNDUgODIuNDIxNjIzOSA2My44NTgxOTY1IDgyLjQyMTYyMzkiPjwvcG9seWdvbj4gICAgICAgICAgICA8cG9seWdvbiBpZD0iRmlsbC0yIiBmaWxsPSIjQ0IzQjQzIiBwb2ludHM9IjYzLjg1ODE5NjUgODcuMDI5NDI1MSAxMDIuNjI4OTI5IDg3LjAyOTQyNTEgMTAyLjYyODkyOSAxMjUuODY5NjQyIDYzLjg1ODE5NjUgMTI1Ljg2OTY0MiI+PC9wb2x5Z29uPiAgICAgICAgICAgIDxwb2x5Z29uIGlkPSJGaWxsLTMiIGZpbGw9IiNDQjNCNDMiIHBvaW50cz0iMTA3LjE5MDE5MSAwLjMzNzAxMjIzMiAxNDUuOTYwOTI0IDAuMzM3MDEyMjMyIDE0NS45NjA5MjQgMzkuMTc3MjI5NCAxMDcuMTkwMTkxIDM5LjE3NzIyOTQiPjwvcG9seWdvbj4gICAgICAgICAgICA8cG9seWdvbiBpZD0iRmlsbC00IiBmaWxsPSIjQ0IzQjQzIiBwb2ludHM9IjYzLjg1ODE5NjUgMC4zMzcwMTIyMzIgMTAyLjYyODkyOSAwLjMzNzAxMjIzMiAxMDIuNjI4OTI5IDM5LjE3NzIyOTQgNjMuODU4MTk2NSAzOS4xNzcyMjk0Ij48L3BvbHlnb24+ICAgICAgICAgICAgPGcgaWQ9Ikdyb3VwLTgiPiAgICAgICAgICAgICAgICA8bWFzayBpZD0ibWFzay0yIiBmaWxsPSJ3aGl0ZSI+ICAgICAgICAgICAgICAgICAgICA8dXNlIHhsaW5rOmhyZWY9IiNwYXRoLTEiPjwvdXNlPiAgICAgICAgICAgICAgICA8L21hc2s+ICAgICAgICAgICAgICAgIDxnIGlkPSJDbGlwLTYiPjwvZz4gICAgICAgICAgICAgICAgPHBvbHlnb24gaWQ9IkZpbGwtNSIgZmlsbD0iI0NCM0I0MyIgbWFzaz0idXJsKCNtYXNrLTIpIiBwb2ludHM9IjIwLjU3MTU2NjggMC4zMzcwMTIyMzIgNTkuMzI2OTgwOSAwLjMzNzAxMjIzMiA1OS4zMjY5ODA5IDM5LjE3NzIyOTQgMjAuNTcxNTY2OCAzOS4xNzcyMjk0Ij48L3BvbHlnb24+ICAgICAgICAgICAgICAgIDxwb2x5bGluZSBpZD0iRmlsbC03IiBmaWxsPSIjQ0IzQjQzIiBtYXNrPSJ1cmwoI21hc2stMikiIHBvaW50cz0iMTguOTc0MzU5IDE2MC45MDQxNTkgMTguOTc0MzU5IDE2Ny4wMjcwNCAyNy45OTAyNDY2IDE2Ny4wMjcwNCAyNy45OTAyNDY2IDE5Mi45ODE3MDMgMzUuNTAxNDI0MiAxOTIuOTgxNzAzIDM1LjUwMTQyNDIgMTY3LjAyNzA0IDQ0LjUxNzMxMTggMTY3LjAyNzA0IDQ0LjUxNzMxMTggMTYwLjkwNDE1OSAxOC45NzQzNTkgMTYwLjkwNDE1OSI+PC9wb2x5bGluZT4gICAgICAgICAgICA8L2c+ICAgICAgICAgICAgPHBhdGggZD0iTTYwLjY3Njc0MjMsMTY3LjMzMTU5IEM2MC4zODgwNTQ4LDE2OS4wMzc4OTkgNjAuMTQ0NzMyNSwxNzAuOTU2Njg1IDU5LjkwMTQxMDEsMTczLjA1ODQzNyBDNTkuNjQyNzY5NywxNzUuMTQ1NDM0IDU5LjQ0NDgxMjYsMTc3LjM1NDAxNSA1OS4yNjI3NjI3LDE3OS42Mzg3MzQgQzU5LjA4MDEyMzYsMTgxLjkyMzQ1MyA1OC44OTgwNzM4LDE4NC4yMjM1MTcgNTguNzYwNzk5OSwxODYuNTA4MjM1IEM1OC41OTM0NzksMTg4LjgwODMgNTguNDcyMTEyNCwxOTAuOTU1NDk4IDU4LjM2NTQ3NDgsMTkyLjk4MTcwMyBMNjQuMTU4NjY3MSwxOTIuOTgxNzAzIEM2NC4yMzQ2Njg1LDE5MC40ODMzMjcgNjQuMzI1Mzk4OSwxODcuNzU3MTI4IDY0LjQzMjAzNjUsMTg0LjgxNzI3MiBDNjQuNTUzOTkyMywxODEuODc4MDA2IDY0LjcyMDcyNCwxNzguODkyMTEzIDY0Ljk2NDA0NjQsMTc1Ljg3NjcwOSBDNjUuMzE0MDA2MywxNzYuODk3MTkgNjUuNzA5MzMxNSwxNzguMDIzOTA4IDY2LjE1MDAyMTgsMTc5LjI0MjcgQzY2LjU3NTk4MzEsMTgwLjQ3NjI0OCA2Ny4wMDEzNTUzLDE4MS43MjUxNDEgNjcuNDI3MzE2NywxODIuOTQzOTMzIEM2Ny44NjgwMDcsMTg0LjE2MjcyNSA2OC4yNjMzMzIyLDE4NS4zNTAyMzUgNjguNjU4NjU3MywxODYuNDYyMTk5IEM2OS4wNjkzMDA2LDE4Ny41ODk1MDggNjkuMzg4NjI0MywxODguNTMzODUgNjkuNjc3MzExOCwxODkuMzI1OTE3IEw3My40OTM4Nzg1LDE4OS4zMjU5MTcgTDc0LjU0MjU4LDE4Ni40NjIxOTkgTDc1Ljg1MDUxMTIsMTgyLjk0MzkzMyBDNzYuMjkxMjAxNSwxODEuNzI1MTQxIDc2LjczMjQ4MSwxODAuNDc2MjQ4IDc3LjIwMzgwNzUsMTc5LjI0MjcgTDc4LjQ2NTc4NDMsMTc1Ljg3NjcwOSBDNzguNjc4NDcwNCwxNzguODkyMTEzIDc4Ljg2MTEwOTUsMTgxLjg3ODAwNiA3OC45NjcxNTc5LDE4NC44MTcyNzIgQzc5LjA3Mzc5NTYsMTg3Ljc1NzEyOCA3OS4xOTUxNjIyLDE5MC40ODMzMjcgNzkuMjU2NDM0NiwxOTIuOTgxNzAzIEw4NS4wNjQzNTU5LDE5Mi45ODE3MDMgQzg0Ljk1NzcxODMsMTkwLjk1NTQ5OCA4NC44MDU3MTU1LDE4OC44MDgzIDg0LjY2OTAzMDgsMTg2LjUwODIzNSBDODQuNTAxNzA5OSwxODQuMjIzNTE3IDg0LjMzNDM4ODksMTgxLjkyMzQ1MyA4NC4xNTE3NDk5LDE3OS42Mzg3MzQgQzgzLjk4NDQyOSwxNzcuMzU0MDE1IDgzLjc1NjQyNDgsMTc1LjE0NTQzNCA4My41Mjg0MjA2LDE3My4wNTg0MzcgQzgzLjI2OTc4MDEsMTcwLjk1NjY4NSA4My4wMTE3Mjg4LDE2OS4wMzc4OTkgODIuNzM3NzcwMywxNjcuMzMxNTkgTDc3LjcwNTE4MTEsMTY3LjMzMTU5IEM3Ny4yOTUxMjcsMTY4LjEwODMxMiA3Ni44NjkxNjU3LDE2OS4wODMzNDYgNzYuMzgyNTIxLDE3MC4yMjU0MSBDNzUuODY1ODI5MywxNzEuMzY4MDY0IDc1LjM2Mzg2NjUsMTcyLjYxNjk1NyA3NC44MTY1Mzg2LDE3My45NDE5ODggQzc0LjI2OTIxMDYsMTc1LjI4MjM2NCA3My43MjE4ODI3LDE3Ni42Njg3NzcgNzMuMjA1MTkxLDE3OC4wNTQ1OTkgQzcyLjY3MjU5MiwxNzkuNDU1NzY4IDcyLjE4NTk0NzMsMTgwLjc2NjA0MyA3MS43MTUyMDk5LDE4MS45ODQyNDUgQzcxLjI1ODYxMjMsMTgwLjc2NjA0MyA3MC43ODcyODU4LDE3OS40NTU3NjggNzAuMjg1OTEyMiwxNzguMDU0NTk5IEM2OS43Njg2MzEzLDE3Ni42Njg3NzcgNjkuMjUxOTM5NiwxNzUuMjgyMzY0IDY4LjczNDY1ODcsMTczLjk0MTk4OCBDNjguMjE3OTY3LDE3Mi42MTY5NTcgNjcuNzAxMjc1MywxNzEuMzY4MDY0IDY3LjIyOTk0ODcsMTcwLjIyNTQxIEM2Ni43NTg2MjIyLDE2OS4wODMzNDYgNjYuMzQ3OTc4OSwxNjguMTA4MzEyIDY1Ljk4MjcwMDksMTY3LjMzMTU5IEw2MC42NzY3NDIzLDE2Ny4zMzE1OSIgaWQ9IkZpbGwtOSIgZmlsbD0iI0NCM0I0MyI+PC9wYXRoPiAgICAgICAgICAgIDxwb2x5Z29uIGlkPSJGaWxsLTEwIiBmaWxsPSIjQ0IzQjQzIiBwb2ludHM9IjkwLjIzMzYyOTggMTY3LjMzMTU5IDk2LjA0MTU1MTEgMTY3LjMzMTU5IDk2LjA0MTU1MTEgMTkyLjk4MTcwMyA5MC4yMzM2Mjk4IDE5Mi45ODE3MDMiPjwvcG9seWdvbj4gICAgICAgICAgICA8cG9seWdvbiBpZD0iRmlsbC0xMSIgZmlsbD0iI0NCM0I0MyIgcG9pbnRzPSI0Ny41NTc5NTcxIDE2Ny4zMzE1OSA1My4zNjU4Nzg1IDE2Ny4zMzE1OSA1My4zNjU4Nzg1IDE5Mi45ODE3MDMgNDcuNTU3OTU3MSAxOTIuOTgxNzAzIj48L3BvbHlnb24+ICAgICAgICAgICAgPHBvbHlsaW5lIGlkPSJGaWxsLTEyIiBmaWxsPSIjQ0IzQjQzIiBwb2ludHM9IjExOS44MjM1MSAxNzIuMjA1NTc4IDExOS44MjM1MSAxNjcuMzMxNTkgMTAyLjQxNDQ3NSAxNjcuMzMxNTkgMTAyLjQxNDQ3NSAxOTIuOTgxNzAzIDEwOC4yMjIzOTYgMTkyLjk4MTcwMyAxMDguMjIyMzk2IDE4MS44MTY2MjQgMTE3LjUxMjI0MyAxODEuODE2NjI0IDExNy41MTIyNDMgMTc2Ljk0MjYzNiAxMDguMjIyMzk2IDE3Ni45NDI2MzYgMTA4LjIyMjM5NiAxNzIuMjA1NTc4IDExOS44MjM1MSAxNzIuMjA1NTc4Ij48L3BvbHlsaW5lPiAgICAgICAgICAgIDxwYXRoIGQ9Ik0xMzIuMzA4OTUsMTkzIEwxMzIuMzA4OTUsMTgyLjg4MTM3IEMxMzIuMzA4OTUsMTgyLjYyODc1OCAxMzIuMjM1MzA1LDE4Mi4zODA4NjkgMTMyLjA5NzQ0MiwxODIuMTY4OTgyIEwxMjIuNDM2NDI3LDE2Ny4zMzE1OSBMMTI4LjY1ODUyNiwxNjcuMzMxNTkgQzEyOC42NTg1MjYsMTY3LjMzMTU5IDEyOS4zMTc3OTQsMTY3LjgzMDkxMSAxMzIuMDgyNzEzLDE3Mi4zMDIzNzMgQzEzNC41MzEyNTUsMTc2LjI2MzMgMTM1LjAzNjE2MywxNzcuNjQ2NzYxIDEzNS4wMzYxNjMsMTc3LjY0Njc2MSBDMTM1LjAzNjE2MywxNzcuNjQ2NzYxIDEzNS41MDc0OSwxNzYuMjA5IDEzNy44Nzg4NTIsMTcyLjMyNjU3MiBDMTQwLjI2MDgxOCwxNjguNDI2NDM3IDE0MS4zMzU0NDMsMTY3LjMzMTU5IDE0MS4zMzU0NDMsMTY3LjMzMTU5IEwxNDcuNTU4MTMxLDE2Ny4zMzE1OSBMMTM3Ljk3NjY1MiwxODIuMTYzNjcgQzEzNy44Mzc2MTEsMTgyLjM3OTA5OCAxMzcuNzYzMzc3LDE4Mi42MzA1MjkgMTM3Ljc2MzM3NywxODIuODg3MjcyIEwxMzcuNzYzMzc3LDE5MyBMMTMyLjMwODk1LDE5MyIgaWQ9IkZpbGwtMTMiIGZpbGw9IiNDQjNCNDMiPjwvcGF0aD4gICAgICAgIDwvZz4gICAgPC9nPjwvc3ZnPg==',
			100
		);

	}

}

//Init widget
$timifyWidget		= new TimifyWidget();

if (is_admin()) {
	add_action( 'admin_menu', array('TimifyWidget', 'initWidgetSettings') );
}

?>
