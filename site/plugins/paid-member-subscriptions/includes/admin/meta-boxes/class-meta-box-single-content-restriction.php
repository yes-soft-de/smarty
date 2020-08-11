<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class PMS_Meta_Box_Content_Restriction extends PMS_Meta_Box {


    /*
     * Function to hook the output and save data methods
     *
     */
    public function init() {
        add_action( 'pms_output_content_meta_box_' . $this->post_type . '_' . $this->id, array( $this, 'output' ) );
        add_action( 'pms_save_meta_box_' . $this->post_type, array( $this, 'save_data' ), 10, 2 );
    }


    /*
     * Function to output the HTML for this meta-box
     *
     */
    public function output( $post ) {

        include_once 'views/view-meta-box-single-content-restriction.php';

    }


    /*
     * Function to validate the data and save it for this meta-box
     *
     */
    public function save_data( $post_id, $post ) {

        if( empty( $_POST['pmstkn'] ) || ! wp_verify_nonce( $_POST['pmstkn'], 'pms_meta_box_single_content_restriction_nonce' ) )
            return;

        /**
         * Handle restriction rules
         *
         */
        delete_post_meta( $post_id, 'pms-content-restrict-type' );
        if( ! empty( $_POST['pms-content-restrict-type'] ) )
            update_post_meta( $post_id, 'pms-content-restrict-type', sanitize_text_field( $_POST['pms-content-restrict-type'] ) );

        delete_post_meta( $post_id, 'pms-content-restrict-subscription-plan' );
        if( isset( $_POST['pms-content-restrict-subscription-plan'] ) ) {

            foreach( $_POST['pms-content-restrict-subscription-plan'] as $subscription_plan_id ) {

                $subscription_plan_id = (int)$subscription_plan_id;

                if( ! empty( $subscription_plan_id ) )
                    add_post_meta( $post_id, 'pms-content-restrict-subscription-plan', $subscription_plan_id );

            }

        }

        if( isset( $_POST['pms-content-restrict-user-status'] ) && $_POST['pms-content-restrict-user-status'] === 'loggedin' )
            update_post_meta( $post_id, 'pms-content-restrict-user-status', 'loggedin' );
        else
            delete_post_meta( $post_id, 'pms-content-restrict-user-status' );


        /**
         * Handle custom redirect URL
         *
         */
        delete_post_meta( $post_id, 'pms-content-restrict-custom-redirect-url-enabled' );
        if( isset( $_POST['pms-content-restrict-custom-redirect-url-enabled'] ) )
            update_post_meta( $post_id, 'pms-content-restrict-custom-redirect-url-enabled', 'yes' );

        update_post_meta( $post_id, 'pms-content-restrict-custom-redirect-url', ( ! empty( $_POST['pms-content-restrict-custom-redirect-url'] ) ? sanitize_text_field( $_POST['pms-content-restrict-custom-redirect-url'] ) : '' ) );


        /**
         * Handle custom messages
         *
         */
        delete_post_meta( $post_id, 'pms-content-restrict-messages-enabled' );
        if( isset( $_POST['pms-content-restrict-messages-enabled'] ) )
            update_post_meta( $post_id, 'pms-content-restrict-messages-enabled', 'yes' );

        update_post_meta( $post_id, 'pms-content-restrict-message-logged_out',  ( ! empty( $_POST['pms-content-restrict-message-logged_out'] )  ? $_POST['pms-content-restrict-message-logged_out'] : '' ) );
        update_post_meta( $post_id, 'pms-content-restrict-message-non_members', ( ! empty( $_POST['pms-content-restrict-message-non_members'] ) ? $_POST['pms-content-restrict-message-non_members'] : '' ) );

    }

}

// initialize the restrict content metaboxes on init.
function pms_initialize_content_restrict_metabox() {

	$post_types = get_post_types( array( 'public' => true ) );

	if( ! empty( $post_types ) ) {
		foreach( $post_types as $post_type ){

            // Exclude bbPress cpts as the default functionality is not working with bbPress
            if( in_array( $post_type, array( 'forum', 'topic', 'reply' ) ) )
                continue;

			$pms_meta_box_content_restriction = new PMS_Meta_Box_Content_Restriction( 'pms_post_content_restriction', __( 'Content Restriction', 'paid-member-subscriptions' ), $post_type, 'normal' );
			$pms_meta_box_content_restriction->init();

		}
	}
}
add_action( 'init', 'pms_initialize_content_restrict_metabox', 998 );
