<?php

/*
Widget Name: PMS Login
Description: Widget to include the [pms-login] shortcode.
Author: Cozmoslabs, Georgian Cocora
Author URI: https://www.cozmoslabs.com/
*/

class PMS_SO_Login_Widget extends SiteOrigin_Widget {
	function __construct() {

		parent::__construct(
			'pms-login',
			__('PMS Login', 'paid-member-subscriptions'),
			array(
				'description' => __('Widget for the [pms-login] shortcode.', 'paid-member-subscriptions'),
			),
            array(),
			array(
				'pms_login_redirect_url' => array(
					'type' => 'text',
					'label' => __('Login redirect URL', 'paid-member-subscriptions'),
				),
				'pms_logout_redirect_url' => array(
					'type' => 'text',
					'label' => __('Logout redirect URL', 'paid-member-subscriptions'),
				),
			),
			plugin_dir_path(__FILE__)
		);
	}

	function get_template_name($instance) {
		return 'pms-login-template';
	}

}

siteorigin_widget_register( 'pms-login', __FILE__, 'PMS_SO_Login_Widget' );
