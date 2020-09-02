<?php
/**
 * Load assets and meta for browser legacy support.
 *
 * @package VibeBP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load general assets for our accordion polyfill
 *
 * @since 1.0.0
 */
class VibeBP_Accordion_IE_Support {


	/**
	 * This plugin's instance.
	 *
	 * @var VibeBP_Accordion_IE_Support
	 */
	private static $instance;

	/**
	 * Registers the plugin.
	 */
	public static function register() {
		if ( null === self::$instance ) {
			self::$instance = new VibeBP_Accordion_IE_Support();
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

		add_action( 'wp_enqueue_scripts', array( $this, 'load_assets' ) );
		add_action( 'the_post', array( $this, 'load_assets' ) );
	}

	/**
	 * Enqueue front-end assets for blocks.
	 *
	 * @access public
	 */
	public function load_assets() {

		global $post;

		// Validate Post ID
		if ( ! isset( $post->ID ) || empty( $post->ID ) ) {

			return;

		}

		$legacy_support = get_post_meta( $post->ID, '_vibebp_accordion_ie_support', true );

		// Determine whether a $post contains an Accordion block.
		if ( has_block( 'vibebp/accordion' ) && "'true'" === $legacy_support ) {

			$dir = VibeBP_Blocks()->asset_source( 'js' );

			wp_enqueue_script(
				$this->_slug . '-accordion-polyfill',
				$dir . $this->_slug . '-accordion-polyfill' . VIBEBP_ASSET_SUFFIX . '.js',
				array(),
				$this->_version,
				true
			);
		}
	}
}

VibeBP_Accordion_IE_Support::register();
