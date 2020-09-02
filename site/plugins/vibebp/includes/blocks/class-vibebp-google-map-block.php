<?php
/**
 * Load assets and meta for Google Map Block
 *
 * @package VibeBP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load assets and settings for the Google Map block.
 *
 * @since 1.0.0
 */
class VibeBP_Google_Map_Block {


	/**
	 * This plugin's instance.
	 *
	 * @var VibeBP_Google_Map_Block
	 */
	private static $instance;

	/**
	 * Registers the plugin.
	 */
	public static function register() {
		if ( null === self::$instance ) {
			self::$instance = new VibeBP_Google_Map_Block();
		}
	}

	/**
	 * The base URL path (without trailing slash).
	 *
	 * @var string $_url
	 */
	private $_url;

	/**
	 * The Plugin version.
	 *
	 * @var string $_version
	 */
	private $_version;

	/**
	 * The Plugin version.
	 *
	 * @var string $_slug
	 */
	private $_slug;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->_version = VIBEBP_VERSION;
		$this->_slug    = 'vibebp';
		$this->_url     = untrailingslashit( plugins_url( '/', dirname( __FILE__ ) ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'map_assets' ) );
		add_action( 'the_post', array( $this, 'map_assets' ) );
		add_action( 'init', array( $this, 'register_settings' ) );
	}

	/**
	 * Enqueue front-end assets for blocks.
	 *
	 * @access public
	 */
	public function map_assets() {

		// Retrieve the Google Maps API key.
		$key = get_option( 'vibebp_google_maps_api_key' );

		// Define where the asset is loaded from.
		$dir = VibeBP_Blocks()->asset_source( 'js' );

		// Determine whether a $post contains a Map block.
		if ( has_block( 'vibebp/map' ) && $key ) {

			wp_enqueue_script(
				$this->_slug . '-google-maps',
				$dir . $this->_slug . '-google-maps' . VIBEBP_ASSET_SUFFIX . '.js',
				array( 'jquery' ),
				$this->_version,
				true
			);

			if ( ! is_admin() ) {

				wp_enqueue_script(
					$this->_slug . '-google-maps-api',
					'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $key ),
					array( $this->_slug . '-google-maps' ),
					$this->_version,
					true
				);

			}

			wp_localize_script( $this->_slug . '-google-maps', 'vibebpGoogleMaps', array( 'url' => $this->_url ) );
		}
	}

	/**
	 * Register block settings.
	 *
	 * @access public
	 */
	public function register_settings() {
		register_setting(
			'vibebp_google_maps_api_key',
			'vibebp_google_maps_api_key',
			array(
				'type'              => 'string',
				'description'       => __( 'Google Map API key for map rendering', 'vibebp' ),
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'default'           => '',
			)
		);
	}

}

VibeBP_Google_Map_Block::register();
