<?php
/*
 Plugin Name: TalentLMS
 Plugin URI: http://wordpress.org/extend/plugins/talentlms/
 Description: This plugin integrates Talentlms with Wordpress. Promote your TalentLMS content through your WordPress site.
 Version: 6.6.9.2
 Author: Epignosis LLC
 Author URI: www.epignosishq.com
 License: GPL2
 */

define("_TLMS_VERSION_", "6.6.9.2");
define("_TLMS_BASEPATH_", dirname(__FILE__));
define("_TLMS_BASEURL_", plugin_dir_url(__FILE__));

require_once (_TLMS_BASEPATH_ . '/TalentLMSLib/lib/TalentLMS.php');

require_once (_TLMS_BASEPATH_ . '/utils/utils.php');
require_once (_TLMS_BASEPATH_ . '/utils/db.php');
require_once (_TLMS_BASEPATH_ . '/utils/install.php');
require_once (_TLMS_BASEPATH_ . '/admin/admin.php');
require_once (_TLMS_BASEPATH_ . '/shortcodes/reg_shortcodes.php');
require_once (_TLMS_BASEPATH_ . '/integrations/woocommerce.php');
require_once (_TLMS_BASEPATH_ . '/widgets/reg_widgets.php');


register_activation_hook(__FILE__, 'tlms_install');
register_uninstall_hook(__FILE__, 'tlms_uninstall');

function tlms_isWoocommerceActive() {
	if ( is_plugin_active('woocommerce/woocommerce.php') ) {
		update_option('tlms-woocommerce-active', 1);
	} else {
		update_option('tlms-woocommerce-active', 0);
	}
    if( empty(get_option('tlms-enroll-user-to-courses')) ){
        update_option('tlms-enroll-user-to-courses', 'submission');    
    }
}
add_action( 'admin_init', 'tlms_isWoocommerceActive' );
