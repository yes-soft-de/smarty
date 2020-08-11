<?php

/*
Widget Name: PMS Recover Password
Description: Widget to include the [pms-recover-password] shortcode.
Author: Cozmoslabs, Georgian Cocora
Author URI: https://www.cozmoslabs.com/
*/

class PMS_SO_Recover_Password_Widget extends SiteOrigin_Widget {
	function __construct() {

		parent::__construct(
			'pms-recover-password',
			__('PMS Recover Password', 'paid-member-subscriptions'),
			array(
				'description' => __('Widget for the [pms-recover-password] shortcode.', 'paid-member-subscriptions'),
			),
            array(),
			array(
				'pms_reset_redirect_url' => array(
					'type' => 'text',
					'label' => __('Successfull reset redirect URL', 'paid-member-subscriptions'),
				),
			),
			plugin_dir_path(__FILE__)
		);

	}

	function get_template_name($instance) {
		return 'pms-recover-password-template';
	}

}

siteorigin_widget_register( 'pms-recover-password', __FILE__, 'PMS_SO_Recover_Password_Widget' );
