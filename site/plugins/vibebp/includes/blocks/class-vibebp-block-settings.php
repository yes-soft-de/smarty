<?php
/**
 * Register settings to enable and disable blocks.
 *
 * @package VibeBP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers setting for the VibeBP Block Manager.
 *
 * @since 1.0.0
 */
class VibeBP_Block_Settings {


	/**
	 * This plugin's instance.
	 *
	 * @var VibeBP_Block_Settings
	 */
	private static $instance;

	/**
	 * Registers the plugin.
	 */
	public static function register() {
		if ( null === self::$instance ) {
			self::$instance = new VibeBP_Block_Settings();
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

		add_action( 'init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register block settings.
	 *
	 * @access public
	 */
	public function register_settings() {
		register_setting(
			'vibebp_settings_api',
			'vibebp_settings_api',
			array(
				'type'              => 'string',
				'description'       => __( 'Enable or disable blocks', 'vibebp' ),
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'default'           => '',
			)
		);
	}

}

VibeBP_Block_Settings::register();
