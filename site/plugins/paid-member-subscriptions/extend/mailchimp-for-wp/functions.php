<?php

function pms_load_mailchimp_for_wp_integration(){
    if( !function_exists( 'mc4wp_register_integration' ) )
        return;

    require_once PMS_PLUGIN_DIR_PATH . 'extend/mailchimp-for-wp/class-mcwp-pms.php';

    mc4wp_register_integration( 'paid-member-subscriptions', 'PMS_MC4WP_Integration' );
}
add_action( 'plugins_loaded', 'pms_load_mailchimp_for_wp_integration' );
