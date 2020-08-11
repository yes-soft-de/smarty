<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_Custom_Integration
 * @ignore
 */
class PMS_MC4WP_Integration extends MC4WP_Integration
{

    /**
     * @var string
     */
    public $name = "Paid Member Subscriptions";

    /**
     * @var string
     */
    public $description = "Subscribe users from the Paid Member Subscriptions registration form.";

    /**
    * Add hooks
    */
    public function add_hooks()
    {
        add_action('pms_register_form_after_fields', array( $this, 'output_checkbox' ));
        add_action('pms_edit_profile_form_after_fields', array( $this, 'output_checkbox' ));

        add_action( 'pms_register_form_after_create_user', array( $this, 'process' ) );
    }

    public function process( $user_data )
    {
        if (! $this->triggered() )
            return false;

        $data = array(
            'EMAIL' => $user_data['user_email'],
            'FNAME' => $user_data['first_name'],
            'LNAME' => $user_data['last_name'],
        );

        return $this->subscribe( $data, $user_data['user_id'] );
    }

    /**
     * @return bool
     */
    public function is_installed()
    {
        return class_exists( 'Paid_Member_Subscriptions' );
    }
}
