<?php

if( !is_plugin_active( 'pms-add-on-recaptcha/index.php' ) ){

    Class PMS_ReCaptcha {

        private $settings = array();

        /**
         * Constructor
         *
         */
        public function __construct() {

            define( 'PMS_RECAPTCHA_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
            define( 'PMS_RECAPTCHA_PLUGIN_DIR_URL',  plugin_dir_url( __FILE__ ) );

            $this->load_dependencies();

            if( $this->is_enabled() ){

                add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
                add_action( 'login_head',         array( $this, 'register_scripts' ) );

                add_action( 'wp_footer',    array( $this, 'print_scripts' ) );
                add_action( 'login_footer', array( $this, 'print_scripts' ) );

            }

        }


        /*
         * Files needed for the add-on to work accordingly
         *
         */
        private function load_dependencies() {

            // Admin files
            if( file_exists( PMS_RECAPTCHA_PLUGIN_DIR_PATH . '/includes/admin/functions-admin-pages.php' ) )
                include PMS_RECAPTCHA_PLUGIN_DIR_PATH . '/includes/admin/functions-admin-pages.php';

            if( $this->is_enabled() ){

                // Output files
                if( file_exists( PMS_RECAPTCHA_PLUGIN_DIR_PATH . '/includes/functions-field-output.php' ) )
                    include PMS_RECAPTCHA_PLUGIN_DIR_PATH . '/includes/functions-field-output.php';

                // Validation files
                if( file_exists( PMS_RECAPTCHA_PLUGIN_DIR_PATH . '/includes/functions-field-validate.php' ) )
                    include PMS_RECAPTCHA_PLUGIN_DIR_PATH . '/includes/functions-field-validate.php';

            }
        }


        /**
         * Load needed scripts in the front-end
         *
         */
        public function register_scripts() {

            global $pms_print_scripts_recaptcha;

            $pms_print_scripts_recaptcha = false;

            wp_register_script( 'pms-recaptcha', PMS_RECAPTCHA_PLUGIN_DIR_URL . 'assets/js/recaptcha.js', array( 'jquery' ), time(), true );
            wp_register_script( 'google-recaptcha-api', 'https://www.google.com/recaptcha/api.js?onload=pms_recaptcha_callback&render=explicit', array(), time(), true );

        }


        /**
         * Prints the registered scripts in the footer of the page
         *
         */
        public function print_scripts() {

            global $pms_print_scripts_recaptcha;

            if( $pms_print_scripts_recaptcha )
                wp_print_scripts( array( 'pms-recaptcha', 'google-recaptcha-api' ) );

        }

        /**
         * Verifies if reCaptcha is setup and added to a form
         *
         * @return boolean
         */
        public function is_enabled() {

            // Look in the other option and populate current settings
            if( empty( $this->settings ) )
                $this->settings = $this->migrate_old_options( get_option( 'pms_misc_settings', array() ) );


            if( !empty( $this->settings['recaptcha']['site_key'] ) && !empty( $this->settings['recaptcha']['secret_key'] ) && isset( $this->settings['recaptcha']['display_form'] ) )
                return true;

            return false;

        }

        public function migrate_old_options( $options ) {

            if( !empty( $options['recaptcha']['site_key'] ) || !empty( $options['recaptcha']['secret_key'] ) )
                return $options;

            $old_settings = get_option( 'pms_recaptcha_settings', array() );

            if( !empty( $old_settings ) ){

                foreach( $old_settings as $key => $value ){
                    if( !empty( $value ) )
                        $options['recaptcha'][$key] = $value;
                }

                update_option( 'pms_misc_settings', $options );
            }

            return $options;

        }

    }

    new PMS_ReCaptcha;

} else {

    $message = sprintf( '<strong>reCaptcha</strong> is now part of <strong>Paid Member Subscriptions</strong> core. Please deactivate and delete the <strong><a href="%s">Paid Member Subscriptions - reCaptcha</a></strong> add-on.', admin_url( 'plugins.php?s=Paid Member Subscriptions - reCaptcha&plugin_status=all' ) );

    new PMS_Add_General_Notices( 'pms_recaptcha_add-on_deprecated',
        $message,
        'error' );

}
