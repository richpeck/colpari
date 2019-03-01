<?php
/**
 * Handles iframe embeds for privacy.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.5.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handle iframe embeds.
 */
class Avada_Privacy_Embeds {

	/**
	 * An array of options to control privacy embeds.
	 *
	 * @since 5.7
	 * @access private
	 * @var array
	 */
	private $options = array();

	/**
	 * An array of embed types.
	 *
	 * @access public
	 * @var array
	 */
	public $embed_types = array();

	/**
	 * Default embed types.
	 *
	 * @access public
	 * @var array
	 */
	public $embed_default = array();

	/**
	 * An array of consents.
	 *
	 * @access public
	 * @var array
	 */
	public $consents = array();

	/**
	 * An array of default consents.
	 *
	 * @access public
	 * @var array
	 */
	public $default_consents = array();

	/**
	 * Check if consent for all is given.
	 *
	 * @access public
	 * @var array
	 */
	public $all_consents = false;

	/**
	 * Cookie name.
	 *
	 * @access public
	 * @var array
	 */
	private $cookie_args = array();

	/**
	 * The class constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->set_cookie_args();
		$this->set_embed_types();
		$this->set_consents();

		add_action( 'init', array( $this, 'init' ) );

	}

	/**
	 * Init.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @return  void
	 */
	public function init() {

		$this->set_options();
		$this->set_cookie_expiry();
		$this->update_embed_types();
		$this->set_default_consents();

		// Only run filter if privacy TO is enabled and we do not already have all consents.
		if ( $this->options['privacy_embeds'] && ! $this->all_consents ) {
			add_filter( 'do_shortcode_tag', array( $this, 'shortcode_replace' ), 20, 4 );
			add_filter( 'the_content', array( $this, 'replace' ), 99999 );
			add_filter( 'privacy_iframe_embed', array( $this, 'replace' ), 20 );
			add_filter( 'script_loader_tag', array( $this, 'replace_script_loader_tag' ), 20, 3 );
			add_filter( 'privacy_script_embed', array( $this, 'script_block' ), 20, 5 );
			add_filter( 'privacy_image_embed', array( $this, 'image_block' ), 20, 5 );
			add_filter( 'fusion_attr_google-map-shortcode', array( $this, 'hide_google_map' ) );
			add_filter( 'fusion_attr_avada-google-map', array( $this, 'hide_google_map' ) );
			add_filter( 'avada_google_analytics', array( $this, 'tracking_script_replace' ), 20, 3 );
		}

		if ( $this->options['privacy_embeds'] ) {
			add_filter( 'avada_dynamic_css_array', array( $this, 'add_styling' ) );
		}

		if ( '0' !== $this->options['privacy_bar'] ) {
			add_filter( 'avada_dynamic_css_array', array( $this, 'add_bar_styling' ) );
			add_action( 'wp_footer', array( $this, 'display_privacy_bar' ), 10 );
		}
	}

	/**
	 * Gets the options for privacy embeds.
	 *
	 * @access public
	 * @since  5.7
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Sets the options for privacy embeds.
	 *
	 * @access  public
	 * @since   5.7
	 * @return  void
	 */
	public function set_options() {
		$this->options = apply_filters(
			'avada_privacy_options',
			array(
				'privacy_embeds'                 => Avada()->settings->get( 'privacy_embeds' ),
				'privacy_bar'                    => Avada()->settings->get( 'privacy_bar' ),
				'privacy_expiry'                 => Avada()->settings->get( 'privacy_expiry' ),
				'privacy_embed_types'            => Avada()->settings->get( 'privacy_embed_types' ),
				'privacy_embed_defaults'         => Avada()->settings->get( 'privacy_embed_defaults' ),
				'privacy_bar_content'            => Avada()->settings->get( 'privacy_bar_content' ),
				'privacy_bg_color'               => Avada()->settings->get( 'privacy_bg_color' ),
				'privacy_color'                  => Avada()->settings->get( 'privacy_color' ),
				'privacy_bar_bg_color'           => Avada()->settings->get( 'privacy_bar_bg_color' ),
				'privacy_bar_color'              => Avada()->settings->get( 'privacy_bar_color' ),
				'privacy_bar_link_color'         => Avada()->settings->get( 'privacy_bar_link_color' ),
				'privacy_bar_link_hover_color'   => Avada()->settings->get( 'privacy_bar_link_hover_color' ),
				'privacy_bar_padding'            => array(
					'top'    => Avada()->settings->get( 'privacy_bar_padding', 'top' ),
					'right'  => Avada()->settings->get( 'privacy_bar_padding', 'right' ),
					'bottom' => Avada()->settings->get( 'privacy_bar_padding', 'bottom' ),
					'left'   => Avada()->settings->get( 'privacy_bar_padding', 'left' ),
				),
				'privacy_bar_button_save'        => Avada()->settings->get( 'privacy_bar_button_save' ),
				'privacy_bar_text'               => Avada()->settings->get( 'privacy_bar_text' ),
				'privacy_bar_button_text'        => Avada()->settings->get( 'privacy_bar_button_text' ),
				'privacy_bar_more_text'          => Avada()->settings->get( 'privacy_bar_more_text' ),
				'privacy_bar_headings_color'     => Avada()->settings->get( 'privacy_bar_headings_color' ),
				'privacy_bar_font_size'          => Avada()->settings->get( 'privacy_bar_font_size' ),
				'privacy_bar_headings_font_size' => Avada()->settings->get( 'privacy_bar_headings_font_size' ),
			)
		);
	}

	/**
	 * Sets the args for the cookie.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @return  void
	 */
	public function set_cookie_args() {

		// Filterable time for expiration.
		$default_args = array(
			'name' => 'privacy_embeds',
			'days' => '30',
			'path' => '/',
		);

		$this->cookie_args = apply_filters( 'fusion_privacy_cookie_args', $default_args );
	}

	/**
	 * Sets the expiry for the cookie.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @return  void
	 */
	public function set_cookie_expiry() {
		$this->cookie_args['days'] = $this->options['privacy_expiry'];
	}

	/**
	 * Gets the args for the cookie.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @return  array
	 */
	public function get_cookie_args() {
		return $this->cookie_args;
	}

	/**
	 * Sets array of embed types.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @return  void
	 */
	public function set_embed_types() {

		$this->embed_types    = apply_filters(
			'fusion_privacy_embeds',
			array(
				'youtube'    => array(
					'search' => 'youtube.com',
					'label'  => esc_attr__( 'YouTube', 'Avada' ),
				),
				'vimeo'      => array(
					'search' => 'vimeo.com',
					'label'  => esc_attr__( 'Vimeo', 'Avada' ),
				),
				'soundcloud' => array(
					'search' => 'soundcloud.com',
					'label'  => esc_attr__( 'SoundCloud', 'Avada' ),
				),
				'facebook'   => array(
					'search' => 'facebook.com',
					'label'  => esc_attr__( 'Facebook', 'Avada' ),
				),
				'flickr'     => array(
					'search' => 'flickr.com',
					'label'  => esc_attr__( 'Flickr', 'Avada' ),
				),
				'twitter'    => array(
					'search' => 'twitter.com',
					'label'  => esc_attr__( 'Twitter', 'Avada' ),
				),
				'gmaps'      => array(
					'search' => array(
						'maps.googleapis.com',
						'infobox_packed',
						'google.com/maps/embed',
					),
					'label'  => esc_attr__( 'Google Maps', 'Avada' ),
				),
				'tracking'   => array(
					'search' => array(),
					'label'  => esc_attr__( 'Tracking Cookies', 'Avada' ),
				),
			)
		);
		$this->embed_defaults = $this->embed_types;
	}

	/**
	 * Get embed type.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   string $name Name of embed type.
	 * @param   string $subkey Name of embed type sub key.
	 * @return  array
	 */
	public function get_embed_type( $name = '', $subkey = false ) {

		$key = esc_attr( strtolower( $name ) );

		if ( ! $subkey && isset( $this->embed_types[ $key ] ) ) {
			return $this->embed_types[ $key ];
		} elseif ( $subkey && isset( $this->embed_types[ $key ] ) && isset( $this->embed_types[ $key ][ $subkey ] ) ) {
			return $this->embed_types[ $key ][ $subkey ];
		}

		return false;
	}

	/**
	 * Get embed default types.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   boolean $simple If you need simplified version.
	 * @return  array
	 */
	public function get_embed_defaults( $simple = false ) {
		if ( $simple && is_array( $this->embed_defaults ) ) {
			$simplified = array();
			foreach ( $this->embed_defaults as $key => $embed ) {
				$simplified[ $key ] = $embed['label'];
			}
			return $simplified;
		}
		return $this->embed_defaults;
	}

	/**
	 * Get embed types.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @return  array
	 */
	public function get_embed_types() {
		return $this->embed_types;
	}

	/**
	 * Updates embed types.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @return  void
	 */
	public function update_embed_types() {
		$defaults = $this->get_embed_defaults();
		$selected = $this->options['privacy_embed_types'];
		$update   = array();

		if ( is_array( $selected ) ) {
			foreach ( $selected as $embed ) {
				if ( isset( $defaults[ $embed ] ) ) {
					$update[ $embed ] = $defaults[ $embed ];
				}
			}
		}
		$this->embed_types = $update;
	}

	/**
	 * Set default consents.
	 *
	 * @access  public
	 * @since   5.6
	 * @return  void
	 */
	public function set_default_consents() {
		$this->default_consents = $this->options['privacy_embed_defaults'];
	}

	/**
	 * Set consents from cookie.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   array $consents Consents which you want to save.
	 * @return  void
	 */
	public function set_consents( $consents = false ) {
		$cookie_name = $this->cookie_args['name'];

		if ( ! $consents ) {
			$consents = array();
			if ( isset( $_COOKIE ) && isset( $_COOKIE[ $cookie_name ] ) ) {
				$consents = wp_unslash( $_COOKIE[ $cookie_name ] ); // WPCS: sanitization ok.
			}
		}

		if ( ! is_array( $consents ) ) {
			$consents = explode( ',', $consents );
		}

		$this->consents = $consents;

		$this->set_all_consents();
	}

	/**
	 * Checks if embed type should be selected.
	 *
	 * @access  public
	 * @since   5.6
	 * @param   string $type Name of embed type.
	 * @return  boolean
	 */
	public function is_selected( $type ) {
		$consents = $this->get_consents();
		$defaults = $this->get_default_consents();

		// If consent has been given.
		if ( in_array( $type, $consents ) ) {
			return true;
		}

		// No consent but is within default selection.
		if ( empty( $consents ) && in_array( $type, $defaults ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get privacy bar content.
	 *
	 * @access  public
	 * @since   5.6
	 * @return  array
	 */
	public function get_privacy_content() {
		$content   = $this->options['privacy_bar_content'];
		$formatted = array();

		if ( isset( $content['title'] ) && is_array( $content['title'] ) ) {
			foreach ( $content['title'] as $key => $content_id ) {
				$data = array(
					'type'        => isset( $content['type'][ $key ] ) ? $content['type'][ $key ] : 'custom',
					'title'       => isset( $content['title'][ $key ] ) ? $content['title'][ $key ] : '',
					'description' => isset( $content['description'][ $key ] ) ? $content['description'][ $key ] : '',
				);

				$formatted[] = $data;
			}
		}
		return $formatted;
	}

	/**
	 * Set consents from cookie.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @return  void
	 */
	public function set_all_consents() {
		$embeds   = $this->get_embed_types();
		$consents = $this->get_consents();

		foreach ( $embeds as $key => $embed ) {
			if ( ! $this->get_consent( $key ) ) {
				$this->all_consents = false;
				return;
			}
		}

		$this->all_consents = true;
	}

	/**
	 * Get consents.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @return  array
	 */
	public function get_consents() {
		return $this->consents;
	}

	/**
	 * Get default consents.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @return  array
	 */
	public function get_default_consents() {
		return $this->default_consents;
	}

	/**
	 * Get specific consent.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   string $name Name of embed type.
	 * @return  array
	 */
	public function get_consent( $name = '' ) {
		$key = esc_attr( strtolower( $name ) );

		if ( ! array_key_exists( $key, $this->embed_types ) && 'consent' !== $key ) {
			return true;
		}
		return in_array( $key, $this->consents );
	}

	/**
	 * Save consent.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   string $name Name of embed type.
	 * @return  void
	 */
	public function add_consent( $name = '' ) {
		$consents   = $this->consents;
		$consents[] = strtolower( esc_attr( $name ) );
		$consents   = array_unique( $consents );

		$this->consents = $consents;
		$this->save_cookie();
	}

	/**
	 * Remove specific consent.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param string $name Name of embed type.
	 * @return void
	 */
	public function remove_consent( $name = '' ) {
		$consents = $this->consents;
		$key      = esc_attr( strtolower( $name ) );

		if ( '' !== $name && isset( $consents[ $name ] ) ) {
			unset( $consents[ $name ] );
		}

		$this->set_consents( $consents );
		$this->save_cookie();
	}

	/**
	 * Save cookie.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   array $consents Consents which you want to save.
	 * @return  void
	 */
	public function save_cookie( $consents = false ) {
		$cookie_args = $this->cookie_args;

		// If passing on consents, set them first.
		if ( $consents ) {
			$this->set_consents( $consents );
		}

		$consents = $this->consents;
		if ( is_array( $consents ) ) {
			$consents = implode( ',', $consents );
		}

		$time = strtotime( '+' . $cookie_args['days'] . ' days' );

		setcookie( $cookie_args['name'], $consents, $time, $cookie_args['path'] );
	}

	/**
	 * Clears the saved cookie.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @return  void
	 */
	public function clear_cookie() {
		$cookie_name = $this->cookie_args['name'];

		if ( isset( $_COOKIE ) && isset( $_COOKIE[ $cookie_name ] ) ) {
			unset( $_COOKIE[ $cookie_name ] );
			setcookie( $cookie_name, '', time() - 3600, '/' );
			$this->consents = array();
		}

	}

	/**
	 * Search string.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   string $type Embed type.
	 * @param   string $src Url src for embed.
	 * @return  string
	 */
	public function search( $type, $src = '' ) {
		$embed = $this->get_embed_type( $type );

		if ( ! $embed ) {
			return false;
		}

		if ( isset( $embed['search'] ) && is_string( $embed['search'] ) ) {
			return ( strpos( $src, $embed['search'] ) );
		}

		if ( isset( $embed['search'] ) && is_array( $embed['search'] ) ) {
			foreach ( $embed['search'] as $search ) {
				if ( strpos( $src, $search ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Replaces iframe src with temporary.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   string $src Url src for embed.
	 * @return  string
	 */
	public function get_src_type( $src = '' ) {
		$embed_types = (array) $this->embed_types;
		foreach ( $embed_types as $name => $embed ) {
			if ( $this->search( $name, $src ) ) {
				return $name;
			}
		}
		return false;
	}

	/**
	 * Replace in shortcodes.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param string       $output Shortcode output.
	 * @param string       $tag    Shortcode name.
	 * @param array|string $attr   Shortcode attributes array or empty string.
	 * @param array        $m      Regular expression match array.
	 * @return string
	 */
	public function shortcode_replace( $output, $tag, $attr, $m ) {
		return $this->replace( $output );
	}

	/**
	 * Replaces iframe src with temporary.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   string $content HTML content to filter.
	 * @return  string
	 */
	public function replace( $content ) {

		// Iframe replacements.
		preg_match_all( '/<iframe.*src=\"(.*)\".*><\/iframe>/isU', $content, $iframes );
		if ( array_key_exists( 1, $iframes ) ) {
			foreach ( $iframes[0] as $key => $frame ) {

				$src  = $iframes[1][ $key ];
				$orig = $frame;

				// Its already been filtered.
				if ( strpos( $frame, 'data-privacy-src' ) ) {
					continue;
				}

				// Check the iframe type and continue if not one of ours.
				$type = $this->get_src_type( $src );
				if ( ! $type ) {
					continue;
				}

				// Check if we already have consent.
				if ( $this->get_consent( $type ) ) {
					continue;
				}

				// Replace src with data attribute.
				$frame = str_replace( $src, '$$temp$$', $frame );
				$frame = str_replace( 'src', 'data-privacy-src', $frame );
				$frame = str_replace( '$$temp$$', $src, $frame );
				$frame = str_replace( '<iframe ', '<iframe data-privacy-type="' . $type . '" src="" ', $frame );

				if ( strpos( $frame, 'class="' ) || strpos( $frame, "class='" ) ) {
					$frame = str_replace( array( 'class="', "class='" ), 'class="fusion-hidden ', $frame );
				} else {
					$frame = str_replace( '<iframe ', '<iframe class="fusion-hidden" ', $frame );
				}

				$frame_width  = false;
				$frame_height = false;

				// Get dimensions if set.
				preg_match( '/width="(.*?)"/', $frame, $width );
				if ( isset( $width[1] ) ) {
					preg_match( '/height="(.*?)"/', $frame, $height );
					if ( isset( $height[1] ) ) {
						$frame_width  = $width[1];
						$frame_height = $height[1];
					}
				}

				// Add placeholder.
				$placeholder = '';
				if ( ! strpos( $frame, 'data-fusion-no-placeholder' ) ) {
					$placeholder = $this->script_placeholder( $type, $frame_width, $frame_height );

					// Allow custom placeholder additions.
					$placeholder = apply_filters( 'avada_privacy_placeholder', $placeholder, $type, $frame_width, $frame_height, $src );
				}

				// Replace iframe.
				$content = str_replace( $orig, $frame . $placeholder, $content );
			}
		}

		return $content;
	}

	/**
	 * Replaces all script tags with spans.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   string $content Content you want to replace script tags..
	 * @param   string $type Type of embed.
	 * @return  string
	 */
	public function script_replace( $content, $type ) {

		if ( ! $this->get_consent( $type ) ) {
			preg_match_all( '/<script(.*?)>(.*?)<\/script>/is', $content, $scripts );
			if ( array_key_exists( 1, $scripts ) ) {
				foreach ( $scripts[0] as $key => $script ) {

					$orig = $script;

					// Replace src with data attribute.
					$script = str_replace( 'src=', 'data-privacy-src=', $script );
					$script = str_replace( '<script', '<noscript class="fusion-hidden" data-privacy-script="true" data-privacy-type="' . $type . '"', $script );
					$script = str_replace( '</script>', '</noscript>', $script );

					// Replace script.
					$content = str_replace( $orig, $script, $content );
				}
			}
		}
		return $content;
	}

	/**
	 * Filters enqueued JS files.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param string $tag    The <script> tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 * @param string $src    The script's source URL.
	 * @return string
	 */
	public function replace_script_loader_tag( $tag, $handle, $src ) {
		$embeds   = $this->get_embed_types();
		$consents = $this->get_consents();

		foreach ( $embeds as $key => $embed ) {
			if ( $this->search( $key, $src ) ) {
				return $this->script_replace( $tag, $key );
			}
		}
		return $tag;
	}

	/**
	 * Replaces image src with temporary.
	 *
	 * @access  public
	 * @since   5.6.1
	 * @param   string $content Content you want to replace script tags..
	 * @param   string $type Type of embed.
	 * @param   bool   $placeholder Whether to have a placeholder or not.
	 * @param   string $width Width of iframe if set .
	 * @param   string $height Height of iframe if set.
	 * @return  string
	 */
	public function image_block( $content, $type, $placeholder = true, $width = false, $height = false ) {
		$content = $this->image_replace( $content, $type );

		if ( $placeholder ) {
			$placeholder = $this->script_placeholder( $type, $width, $height );
		}

		return $placeholder . $content;
	}

	/**
	 * Replaces all images src,
	 *
	 * @access  public
	 * @since   5.6.1
	 * @param   string $content Content you want to replace script tags..
	 * @param   string $type Type of embed.
	 * @return  string
	 */
	public function image_replace( $content, $type ) {

		if ( ! $this->get_consent( $type ) ) {
			preg_match_all( '/<img\s+[^>]*src="([^"]*)"[^>]*>/isU', $content, $images );
			if ( array_key_exists( 1, $images ) ) {
				foreach ( $images[0] as $key => $image ) {

					$orig = $image;

					// Replace src with data attribute.
					$image = str_replace( 'src=', 'data-privacy-src=', $image );
					$image = str_replace( '<img', '<img class="fusion-hidden" data-privacy-script="true" data-privacy-type="' . $type . '"', $image );

					// Replace script.
					$content = str_replace( $orig, $image, $content );
				}
			}
		}
		return $content;
	}

	/**
	 * Replaces scripts and adds a placeholder.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   string $content Content you want to replace script tags..
	 * @param   string $type Type of embed.
	 * @param   bool   $placeholder Whether to have a placeholder or not.
	 * @param   string $width Width of iframe if set .
	 * @param   string $height Height of iframe if set.
	 * @return  string
	 */
	public function script_block( $content, $type, $placeholder = true, $width = false, $height = false ) {

		$content = $this->script_replace( $content, $type );

		if ( $placeholder ) {
			$placeholder = $this->script_placeholder( $type, $width, $height );
		}

		return $placeholder . $content;
	}

	/**
	 * Replaces scripts for tracking cookies.
	 *
	 * @access  public
	 * @since   5.6
	 * @param   string $content Content you want to replace script tags..
	 * @return  string
	 */
	public function tracking_script_replace( $content ) {

		if ( array_key_exists( 'tracking', $this->embed_types ) ) {
			$content = $this->script_replace( $content, 'tracking' );
		}

		return $content;
	}

	/**
	 * Returns a placeholder iframe.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   string $type Name of embed type.
	 * @param   string $width Width of iframe if set .
	 * @param   string $height Height of iframe if set.
	 * @return  string
	 */
	public function script_placeholder( $type, $width = false, $height = false ) {

		if ( ! $this->get_consent( $type ) ) {
			$style = '';
			$label = esc_html( $this->get_embed_type( $type, 'label' ) );

			if ( $width && $height ) {
				$width  = Fusion_Sanitize::get_value_with_unit( $width );
				$height = Fusion_Sanitize::get_value_with_unit( $height );
				$style  = 'style="width:' . $width . '; height:' . $height . ';"';
			}
			$html = '<div class="fusion-privacy-placeholder" ' . $style . ' data-privacy-type="' . $type . '"><div class="fusion-privacy-placeholder-content">';

			/* translators: The placeholder label (embed-type). */
			$content = sprintf( esc_html__( 'For privacy reasons %s needs your permission to be loaded.', 'Avada' ), $label );

			if ( function_exists( 'get_the_privacy_policy_link' ) ) {
				$privacy_link = get_the_privacy_policy_link();
				if ( ! empty( $privacy_link ) ) {
					/* translators: The link to the privacy page (embed-type). */
					$content .= ' ' . sprintf( esc_html__( 'For more details, please see our %s.', 'Avada' ), $privacy_link );
				}
			}

			$content = '<div class="fusion-privacy-label">' . $content . '</div>';

			$html .= apply_filters( 'avada_embeds_consent_text', $content, $label, $type );
			$html .= '<a href="" data-privacy-type="' . $type . '" class="fusion-button button-default fusion-button-default-size button fusion-privacy-consent">' . esc_html__( 'I Accept', 'Avada' ) . '</a>';
			$html .= '</div></div>';

			return $html;
		}
		return '';
	}

	/**
	 * Returns a placeholder iframe.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   array $css CSS dynamic array.
	 * @return  array
	 */
	public function add_styling( $css ) {

		$css['global']['.fusion-privacy-placeholder']['background'] = Fusion_Sanitize::color( $this->options['privacy_bg_color'] );
		$css['global']['.fusion-privacy-placeholder']['color']      = Fusion_Sanitize::color( $this->options['privacy_color'] );

		return $css;
	}

	/**
	 * Hides the container for google map.
	 *
	 * @access  public
	 * @since   5.5.2
	 * @param   array $attributes Attributes to filter.
	 * @return  string
	 */
	public function hide_google_map( $attributes ) {
		if ( ! $this->get_consent( 'gmaps' ) && isset( $attributes['class'] ) && ! strpos( $attributes['class'], 'fusion-maps-embed-type' ) ) {
			$attributes['class'] .= ' fusion-hidden';
		}
		return $attributes;
	}

	/**
	 * Displays the privacy bar.
	 *
	 * @access  public
	 * @since   5.6
	 * @return  void
	 */
	public function display_privacy_bar() {
		if ( ! $this->get_consent( 'consent' ) ) {
			get_template_part( 'templates/privacy-bar' );
		}
	}

	/**
	 * Adds bar dynamic styling.
	 *
	 * @access  public
	 * @since   5.6
	 * @param   array $css CSS dynamic array.
	 * @return  array
	 */
	public function add_bar_styling( $css ) {

		$css['global']['.fusion-privacy-bar']['background']                          = Fusion_Sanitize::color( $this->options['privacy_bar_bg_color'] );
		$css['global']['.fusion-privacy-bar']['color']                               = Fusion_Sanitize::color( $this->options['privacy_bar_color'] );
		$css['global']['.fusion-privacy-bar a:not( .fusion-button )']['color']       = Fusion_Sanitize::color( $this->options['privacy_bar_link_color'] );
		$css['global']['.fusion-privacy-bar a:not( .fusion-button ):hover']['color'] = Fusion_Sanitize::color( $this->options['privacy_bar_link_hover_color'] );
		$css['global']['.fusion-privacy-bar']['padding-right']                       = Fusion_Sanitize::size( $this->options['privacy_bar_padding']['right'] );
		$css['global']['.fusion-privacy-bar']['padding-bottom']                      = Fusion_Sanitize::size( $this->options['privacy_bar_padding']['bottom'] );
		$css['global']['.fusion-privacy-bar']['padding-left']                        = Fusion_Sanitize::size( $this->options['privacy_bar_padding']['left'] );
		$css['global']['.fusion-privacy-bar']['padding-top']                         = Fusion_Sanitize::size( $this->options['privacy_bar_padding']['top'] );
		$css['global']['.fusion-privacy-bar-full .column-title']['color']            = Fusion_Sanitize::color( $this->options['privacy_bar_headings_color'] );
		$css['global']['.fusion-privacy-bar, .fusion-privacy-bar-full']['font-size'] = Fusion_Sanitize::size( $this->options['privacy_bar_font_size'] );
		$css['global']['.fusion-privacy-bar-full .column-title']['font-size']        = Fusion_Sanitize::size( $this->options['privacy_bar_headings_font_size'] );
		$css['global']['.fusion-privacy-bar-full .column-title']['line-height']      = Fusion_Sanitize::size( $this->options['privacy_bar_headings_font_size'] );

		$top_padding          = $this->options['privacy_bar_padding']['top'];
		$double_padding_value = 2 * (int) Fusion_Sanitize::number( $top_padding );
		$padding_unit         = Fusion_Sanitize::get_unit( $top_padding );

		$css['global']['.fusion-privacy-bar-full']['padding-top'] = $double_padding_value . $padding_unit;

		return $css;
	}
}
