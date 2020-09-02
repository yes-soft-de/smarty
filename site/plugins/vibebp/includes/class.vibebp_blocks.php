<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VibeBP_Blocks' ) ) :


	final class VibeBP_Blocks {

		private static $instance;
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof VibeBP ) ) {
				self::$instance = new VibeBP_Blocks();
				self::$instance->init();
				self::$instance->asset_suffix();
				self::$instance->includes();
			}
			return self::$instance;
		}

		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheating huh?', 'vibebp' ), '1.0' );
		}


		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheating huh?', 'vibebp' ), '1.0' );
		}


		private function includes() {
			require_once VIBEBP_PLUGIN_DIR . 'includes/blocks/class-vibebp-block-assets.php';
			require_once VIBEBP_PLUGIN_DIR . 'includes/blocks/class-vibebp-register-blocks.php';
			require_once VIBEBP_PLUGIN_DIR . 'includes/blocks/class-vibebp-generated-styles.php';
			require_once VIBEBP_PLUGIN_DIR . 'includes/blocks/class-vibebp-body-classes.php';
			require_once VIBEBP_PLUGIN_DIR . 'includes/blocks/class-vibebp-form.php';
			require_once VIBEBP_PLUGIN_DIR . 'includes/blocks/class-vibebp-font-loader.php';
			require_once VIBEBP_PLUGIN_DIR . 'includes/blocks/class-vibebp-post-meta.php';
			require_once VIBEBP_PLUGIN_DIR . 'includes/blocks/class-vibebp-google-map-block.php';
			require_once VIBEBP_PLUGIN_DIR . 'includes/blocks/class-vibebp-accordion-ie-support.php';
			require_once VIBEBP_PLUGIN_DIR . 'includes/blocks/class-vibebp-block-settings.php';
			require_once VIBEBP_PLUGIN_DIR . 'includes/blocks/get-dynamic-blocks.php';

			
		}

		/**
		 * Load actions
		 *
		 * @return void
		 */
		private function init() {
			add_action( 'enqueue_block_editor_assets', array( $this, 'block_localization' ) );
		}

		public function asset_suffix() {

			$suffix = SCRIPT_DEBUG ? null : '.min';

			define( 'VIBEBP_ASSET_SUFFIX', $suffix );
		}


		public function asset_source( $type = 'js', $directory = null ) {

			if ( 'js' === $type ) {
				return SCRIPT_DEBUG ? VIBEBP_PLUGIN_URL . 'src/' . $type . '/' . $directory : VIBEBP_PLUGIN_URL . 'dist/' . $type . '/' . $directory;
			} else {
				return VIBEBP_PLUGIN_URL . 'dist/css/' . $directory;
			}
		}


		/**
		 * Enqueue localization data for our blocks.
		 */
		public function block_localization() {
			if ( function_exists( 'wp_set_script_translations' ) ) {
				wp_set_script_translations( 'vibebp-editor', 'vibebp' );
			}
		}
	}
endif;

function VibeBP_Blocks() {
	return VibeBP_Blocks::instance();
}

// Get the plugin running. Load on plugins_loaded action to avoid issue on multisite.
if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	add_action( 'plugins_loaded', 'VibeBP_Blocks', 90 );
} else {
	VibeBP_Blocks();
}
