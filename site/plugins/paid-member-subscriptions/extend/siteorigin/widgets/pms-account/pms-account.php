<?php

/*
Widget Name: PMS Account
Description: Widget to include the [pms-account] shortcode.
Author: Cozmoslabs, Georgian Cocora
Author URI: https://www.cozmoslabs.com/
*/

class PMS_SO_Account_Widget extends SiteOrigin_Widget {
	function __construct() {

		parent::__construct(
			'pms-account',
			__('PMS Account', 'paid-member-subscriptions'),
			array(
				'description' => __('Widget for the [pms-account] shortcode.', 'paid-member-subscriptions'),
			),
            array(),
			array(
                'show_tabs' => array(
    				'type'    => 'select',
    				'label'   => __( 'Show tabs', 'paid-member-subscriptions' ),
    				'default' => 'yes',
    				'options' => array(
    					'yes' => __( 'Yes', 'paid-member-subscriptions' ),
    					'no'  => __( 'No', 'paid-member-subscriptions' ),
    				)
    			),
			),
			plugin_dir_path(__FILE__)
		);
	}

	function get_template_name($instance) {
		return 'pms-account-template';
	}

}

siteorigin_widget_register( 'pms-account', __FILE__, 'PMS_SO_Account_Widget' );
