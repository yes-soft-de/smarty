<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class PMS_FLBuilder_Account_Module extends FLBuilderModule {

    /**
     * Constructor function for the module. You must pass the
     * name, description, dir and url in an array to the parent class.
     *
     * @method __construct
     */
    public function __construct() {
        parent::__construct(array(
            'name'          => __( 'Account', 'paid-member-subscriptions' ),
            'description'   => __( 'Module for adding the [pms-account] shortcode in your page.', 'paid-member-subscriptions' ),
            'category'		=> __( 'Paid Member Subscriptions', 'paid-member-subscriptions' ),
            'dir'           => PMS_FLBUILDER_MODULES_DIR . 'modules/pms-account/',
            'url'           => PMS_FLBUILDER_MODULES_URL . 'modules/pms-account/',
        ));

    }

}

FLBuilder::register_module( 'PMS_FLBuilder_Account_Module', array(
    'pms-general'      => array(
        'title'         => __( 'General', 'paid-member-subscriptions' ),
        'sections'      => array(
            'pms-section'  => array(
                'fields'        => array(
                    'show_tabs'   => array(
                        'type'          => 'select',
                        'label'         => __( 'Show Tabs', 'paid-member-subscriptions' ),
                        'default'       => 'yes',
                        'options'       => array(
                            'yes' => __( 'Yes', 'paid-member-subscriptions' ),
                            'no'  => __( 'No', 'paid-member-subscriptions' )
                        )
                    ),
                )
            )
        )
    )
) );
