<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class PMS_FLBuilder_Register_Module extends FLBuilderModule {

    /**
     * Constructor function for the module. You must pass the
     * name, description, dir and url in an array to the parent class.
     *
     * @method __construct
     */
    public function __construct() {
        parent::__construct(array(
            'name'          => __( 'Register', 'paid-member-subscriptions' ),
            'description'   => __( 'Module for adding the [pms-register] shortcode in your page.', 'paid-member-subscriptions' ),
            'category'		=> __( 'Paid Member Subscriptions', 'paid-member-subscriptions' ),
            'dir'           => PMS_FLBUILDER_MODULES_DIR . 'modules/pms-register/',
            'url'           => PMS_FLBUILDER_MODULES_URL . 'modules/pms-register/',
        ));

    }

}

FLBuilder::register_module( 'PMS_FLBuilder_Register_Module', array(
    'pms-general'      => array(
        'title'         => __( 'General', 'paid-member-subscriptions' ),
        'sections'      => array(
            'pms-section'  => array(
                'fields'        => array(
                    'subscription_plans'   => array(
                        'type'          => 'select',
                        'label'         => __( 'Subscription Plans', 'paid-member-subscriptions' ),
                        'options'       => pms_get_subscription_plans_list(),
                        'multi-select'  => true,
                    ),
                    'selected_subscription'   => array(
                        'type'          => 'select',
                        'label'         => __( 'Selected plan', 'paid-member-subscriptions' ),
                        'default'       => 'yes',
                        'options'       => pms_get_subscription_plans_list(),
                    ),
                    'plans_position'   => array(
                        'type'          => 'select',
                        'label'         => __( 'Subscription Plans', 'paid-member-subscriptions' ),
                        'default'       => 'bottom',
                        'options'       => array(
                            'top'    => __( 'Top', 'paid-member-subscriptions' ),
                            'bottom' => __( 'Bottom', 'paid-member-subscriptions' )
                        ),
                    ),
                )
            )
        )
    )
) );
