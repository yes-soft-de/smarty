<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

    /*
     * Add extra field properties for the Subscription Plans PB field
     *
     * @param array $manage_fields
     *
     * @return array
     *
     */
    function pms_pb_manage_fields( $manage_fields ) {

        // Get all subscription plans
        $subscription_plans = array();
        foreach( pms_get_subscription_plans() as $subscription_plan )
            $subscription_plans[] = '%' . $subscription_plan->name . '%' . $subscription_plan->id;


        // Prepare subscription plans for default select
        $subscription_plans_select = array( '%' . __( 'Choose...', 'paid-member-subscriptions' ) . '%-1' );
        $subscription_plans_select = array_merge( $subscription_plans_select, $subscription_plans );

        // Append field properties
        if ( empty($subscription_plans) )
            $manage_fields[] = array( 'type' => 'checkbox', 'slug' => 'subscription-plans', 'title' => __( 'Subscription Plans on Register Form', 'paid-member-subscriptions' ), 'options' => $subscription_plans, 'description' => sprintf( __( 'It looks like there are no active subscriptions. <a href="%s">Create one here</a>.', 'paid-member-subscriptions' ), 'edit.php?post_type=pms-subscription' ) );
        else
            $manage_fields[] = array( 'type' => 'checkbox', 'slug' => 'subscription-plans', 'title' => __( 'Subscription Plans on Register Form', 'paid-member-subscriptions' ), 'options' => $subscription_plans, 'description' => __( "Select which Subscription Plans to show to the user on the register forms ( drag and drop to re-order )", 'paid-member-subscriptions' ) );
        $manage_fields[] = array( 'type' => 'text', 'slug' => 'subscription-plans-sort-order', 'title' => __( 'Subscription Plans Order', 'paid-member-subscriptions' ), 'description' => __( "Save the subscription plan order from the subscription plans checkboxes", 'paid-member-subscriptions' ) );

        if( count( $subscription_plans_select ) > 1 )
            $manage_fields[] = array( 'type' => 'select', 'slug' => 'subscription-plan-selected', 'title' => __( 'Selected Subscription Plan', 'paid-member-subscriptions' ), 'options' => $subscription_plans_select, 'description' => __( "Select which plan will be by default selected when the front-end form loads.", 'paid-member-subscriptions' ) );

        return $manage_fields;

    }
    add_filter( 'wppb_manage_fields', 'pms_pb_manage_fields' );


    /*
     * Include necessary scripts for Profile Builder compatibility
     *
     */
    function pms_pb_enqueue_scripts() {

        if( is_admin() )
            wp_enqueue_script( 'pms-pb-main-js', PMS_PLUGIN_DIR_URL . 'extend/profile-builder/assets/js/main.js', array( 'jquery' ) );

    }
    add_action( 'admin_enqueue_scripts', 'pms_pb_enqueue_scripts' );


    /*
     * Function that ads the Subscription Plans field to the fields list
     * and also the list of fields that skip the meta-name check
     *
     * @param array $fields     - The names of all the fields
     *
     * @return array
     *
     */
    function pms_pb_manage_field_types( $fields ) {
        $fields[] = 'Subscription Plans';

        return $fields;
    }
    add_filter( 'wppb_manage_fields_types', 'pms_pb_manage_field_types' );
    add_filter( 'wppb_skip_check_for_fields', 'pms_pb_manage_field_types' );


    /**
     * Function that calls the pms_pb_handle_subscription_plans_field
     *
     * @since v.2.0
     *
     * @param void
     *
     * @return string
     */
    function pms_pb_subscription_plans_sortable( $meta_name, $id, $element_id ){
        if ( $meta_name == 'wppb_manage_fields' ) {
            echo "<script type=\"text/javascript\">pms_pb_handle_sorting_subscription_plans_field( '#container_wppb_manage_fields' );</script>";
        }

    }
    add_action("wck_after_adding_form", "pms_pb_subscription_plans_sortable", 10, 3);