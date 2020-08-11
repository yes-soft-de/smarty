<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class PMS_Meta_Box_Subscription_Details extends PMS_Meta_Box {


    /*
     * Method to hook the output and save data methods
     *
     */
    public function init() {

        // Hook the output method to the parent's class action for output instead of overwriting the
        // output_content method
        add_action( 'pms_output_content_meta_box_' . $this->post_type . '_' . $this->id, array( $this, 'output' ) );

        // Hook the save_data method to the parent's class action for saving data instead of overwriting the
        // save_meta_box method
        add_action( 'pms_save_meta_box_' . $this->post_type, array( $this, 'save_data' ) );

    }


    /*
     * Method to output the HTML for this meta-box
     *
     */
    public function output( $post ) {

        $subscription_plan = pms_get_subscription_plan( $post );

        include_once 'views/view-meta-box-subscription-details.php';

    }


    /*
     * Method to validate the data and save it for this meta-box
     *
     */
    public function save_data( $post_id ) {

        if( empty( $_POST['post_ID'] ) )
            return;

        if( $post_id != $_POST['post_ID'] )
            return;

        // Update subscription plan description post meta
        if( isset( $_POST['pms_subscription_plan_description'] ) )
            update_post_meta( $post_id, 'pms_subscription_plan_description', wp_kses_post( $_POST['pms_subscription_plan_description'] ) );


        // Update subscription plan duration meta data
        if( isset( $_POST['pms_subscription_plan_duration'] ) ) {

            $subscription_plan_duration = trim( $_POST['pms_subscription_plan_duration'] );

            // Check to see if entered value is a whole number, if not set the value to 0 (zero)
            if( ( !ctype_digit( $subscription_plan_duration ) ) || ( (int)$subscription_plan_duration === 0 && strlen( $subscription_plan_duration ) > 1 ) )
                $subscription_plan_duration = 0;

            update_post_meta( $post_id, 'pms_subscription_plan_duration', $subscription_plan_duration );
        }

        if( isset( $_POST['pms_subscription_plan_duration_unit'] ) )
            update_post_meta( $post_id, 'pms_subscription_plan_duration_unit', sanitize_text_field( $_POST['pms_subscription_plan_duration_unit'] ) );


        // Update price post meta
        if( isset( $_POST['pms_subscription_plan_price'] ) ) {

            $subscription_plan_price = trim( $_POST['pms_subscription_plan_price'] );

            if( !is_numeric( $subscription_plan_price ) )
                $subscription_plan_price = 0;

            update_post_meta( $post_id, 'pms_subscription_plan_price', $subscription_plan_price );

        }


        // Update sign-up fee post meta
        if( isset( $_POST['pms_subscription_plan_sign_up_fee'] ) ) {

            $subscription_plan_sign_up_fee = trim( $_POST['pms_subscription_plan_sign_up_fee'] );

            if( !is_numeric( $subscription_plan_sign_up_fee ) )
                $subscription_plan_sign_up_fee = 0;

            update_post_meta( $post_id, 'pms_subscription_plan_sign_up_fee', $subscription_plan_sign_up_fee );

        }


        // Update subscription plan free trial duration meta data
        if( isset( $_POST['pms_subscription_plan_trial_duration'] ) ) {

            $subscription_plan_trial_duration = trim( $_POST['pms_subscription_plan_trial_duration'] );

            // Check to see if entered value is a whole number, if not set the value to 0 (zero)
            if( ( !ctype_digit( $subscription_plan_trial_duration ) ) || ( (int)$subscription_plan_trial_duration === 0 && strlen( $subscription_plan_trial_duration ) > 1 ) )
                $subscription_plan_trial_duration = 0;

            update_post_meta( $post_id, 'pms_subscription_plan_trial_duration', $subscription_plan_trial_duration );
        }

        if( isset( $_POST['pms_subscription_plan_trial_duration_unit'] ) )
            update_post_meta( $post_id, 'pms_subscription_plan_trial_duration_unit', $_POST['pms_subscription_plan_trial_duration_unit'] );


        // Update subscription plan recurring
        if( isset( $_POST['pms_subscription_plan_recurring'] ) ) {

            update_post_meta( $post_id, 'pms_subscription_plan_recurring', (int)$_POST['pms_subscription_plan_recurring'] );

        }


        // Update status post meta
        if( isset( $_POST['pms_subscription_plan_status'] ) ) {

            update_post_meta($post_id, 'pms_subscription_plan_status', sanitize_text_field( $_POST['pms_subscription_plan_status'] ) );

            $status = sanitize_text_field( $_POST['pms_subscription_plan_status'] );

            if ( ! wp_is_post_revision( $post_id ) ){

                // unhook this function so it doesn't loop infinitely
                remove_action('pms_save_meta_box_pms-subscription', array( $this, 'save_data' ));

                // Change the post status as the discount status
                $post = array(
                    'ID'            => $post_id,
                    'post_status'   => $status,
                );
                wp_update_post( $post );

                // re-hook this function
                add_action('pms_save_meta_box_pms-subscription', array( $this, 'save_data' ) );

            }
        }


        // Update the user role
        if( isset( $_POST['pms_subscription_plan_user_role'] ) ) {

            $current_role = get_post_meta( $post_id, 'pms_subscription_plan_user_role', true );

            $new_role   = sanitize_text_field( $_POST['pms_subscription_plan_user_role'] );
            $post_title = sanitize_text_field( $_POST['post_title'] );

            // Create a new user role based on subscription plan
            if( $new_role == 'create-new' ) {

                $new_role = 'pms_subscription_plan_' . $post_id;
                add_role( $new_role, $post_title, array( 'read' => true ) );

                $role = get_role( $new_role );
                $role->add_cap( $new_role, true );
            }

            // Update all users user role if the value changes
            if( !empty($current_role) && $current_role != $new_role ) {

                // Get all members that are subscribed to the current subscription plan
                $members = pms_get_members( array( 'subscription_plan_id' => $post_id ) );

                foreach( $members as $member ) {

                    // Add new user role
                    pms_add_user_role( $member->user_id, $new_role );

                    // Remove old user role
                    if( count(array_keys( pms_get_user_roles_by_plan_ids($member->get_subscriptions_ids()), $current_role )) == 1 )
                        pms_remove_user_role( $member->user_id, $current_role );

                }

            }

            // Update the subscription plan default user role
            update_post_meta( $post_id, 'pms_subscription_plan_user_role', $new_role );

        }

    }

}


$pms_meta_box_subscription_details = new PMS_Meta_Box_Subscription_Details( 'pms_subscription_details', __( 'Subscription Plan Details', 'paid-member-subscriptions' ), 'pms-subscription', 'normal' );
$pms_meta_box_subscription_details->init();
