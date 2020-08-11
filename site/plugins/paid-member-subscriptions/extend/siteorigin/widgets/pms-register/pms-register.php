<?php

/*
Widget Name: PMS Register
Description: Widget to include the [pms-register] shortcode.
Author: Cozmoslabs, Georgian Cocora
Author URI: https://www.cozmoslabs.com/
*/

class PMS_SO_Register_Widget extends SiteOrigin_Widget {
	function __construct() {

		parent::__construct(
			'pms-register',
			__('PMS Register', 'paid-member-subscriptions'),
			array(
				'description' => __('Widget for the [pms-register] shortcode.', 'paid-member-subscriptions'),
			),
            array(),
			array(
				'subscription_plans' => array(
					'type'     => 'select',
					'label'    => __( 'Subscription Plans', 'paid-member-subscriptions' ),
					'multiple' => true,
					'options'  => pms_get_subscription_plans_list()
				),
				'selected_plan' => array(
					'type'    => 'select',
					'label'   => __( 'Selected Plan', 'paid-member-subscriptions' ),
					'options' => pms_get_subscription_plans_list()
				),
				'plans_position' => array(
    				'type'    => 'select',
    				'label'   => __( 'Plans position', 'paid-member-subscriptions' ),
    				'default' => 'bottom',
    				'options' => array(
    					'top'    => __( 'Top', 'paid-member-subscriptions' ),
    					'bottom' => __( 'Bottom', 'paid-member-subscriptions' ),
    				)
    			),
			),
			plugin_dir_path(__FILE__)
		);
	}

	function get_template_name($instance) {
		return 'pms-register-template';
	}

}

siteorigin_widget_register( 'pms-register', __FILE__, 'PMS_SO_Register_Widget' );
