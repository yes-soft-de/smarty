<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class PMS_FLBuilder_Login_Module extends FLBuilderModule {

    /**
     * Constructor function for the module. You must pass the
     * name, description, dir and url in an array to the parent class.
     *
     * @method __construct
     */
    public function __construct() {
        parent::__construct(array(
            'name'          => __( 'Login', 'paid-member-subscriptions' ),
            'description'   => __( 'Module for adding the [pms-login] shortcode in your page.', 'paid-member-subscriptions' ),
            'category'		=> __( 'Paid Member Subscriptions', 'paid-member-subscriptions' ),
            'dir'           => PMS_FLBUILDER_MODULES_DIR . 'modules/pms-login/',
            'url'           => PMS_FLBUILDER_MODULES_URL . 'modules/pms-login/',
        ));

    }

}

FLBuilder::register_module( 'PMS_FLBuilder_Login_Module', array(
    'pms-general'      => array(
        'title'         => __( 'General', 'paid-member-subscriptions' ),
        'sections'      => array(
            'pms-section'  => array(
                'fields'        => array(
                    'after_login_redirect_url'     => array(
                        'type'          => 'text',
                        'label'         => __( 'After login redirect URL', 'paid-member-subscriptions' ),
                    ),
                    'after_logout_redirect_url'     => array(
                        'type'          => 'text',
                        'label'         => __( 'After logout redirect URL', 'paid-member-subscriptions' ),
                    ),
                )
            )
        )
    )
) );
