<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class PMS_FLBuilder_Modules {

	/**
	 * Initializes the class once all plugins have loaded.
	 */
	static public function init() {
		add_action( 'plugins_loaded', __CLASS__ . '::setup_hooks' );
	}

	/**
	 * Setup hooks if the builder is installed and activated.
	 */
	static public function setup_hooks() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return;
		}

		// Load custom modules.
		add_action( 'init', __CLASS__ . '::load_modules' );

        define( 'PMS_FLBUILDER_MODULES_DIR', plugin_dir_path( __FILE__ ) );
        define( 'PMS_FLBUILDER_MODULES_URL', plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Loads our custom modules.
	 */
	static public function load_modules() {
		require_once __DIR__ . '/modules/pms-account/pms-account.php';
		require_once __DIR__ . '/modules/pms-login/pms-login.php';
		require_once __DIR__ . '/modules/pms-register/pms-register.php';
		require_once __DIR__ . '/modules/pms-recover-password/pms-recover-password.php';
	}

}

PMS_FLBuilder_Modules::init();
