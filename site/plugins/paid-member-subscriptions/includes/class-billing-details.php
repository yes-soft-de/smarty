<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Eventually this should hold all the functionality relating to the Billing Details that we display in a form
 * Right now it's only used for displaying the billing details for admins on the back-end edit member page
 */
Class PMS_Billing_Details {

    public function init(){

        add_action( 'pms_member_edit_form_field', array( $this, 'admin_display_billing_details' ) );

        add_action( 'pms_submenu_page_enqueue_admin_scripts_pms-members-page', array( $this, 'localize_billing_details' ) );

        add_action( 'wp_ajax_pms_edit_member_billing_details', array( $this, 'edit_member_billing_details' ) );

    }

    public function admin_display_billing_details(){

        if( !isset( $_GET['member_id'] ) )
            return;

        $member_id = (int)$_GET['member_id'];

        $fields_data = $this->get_billing_fields_data( $member_id );

        if( empty( $fields_data ) || !( count( $fields_data ) > 1 ) )
            return;

        $billing_fields = $this->get_billing_fields( $member_id );

        ?>

        <h3><?php _e( 'Billing Details', 'paid-member-subscriptions' ); ?></h3>

        <div id="pms-member-billing-details" class="postbox">
            <div class="inside">

                <div class="billing-details">
                    <?php $this->format_billing_details( $fields_data ); ?>

                    <div class="billing-details__action">
                        <a href="" id="edit" class="button button-secondary"><?php _e( 'Edit', 'paid-member-subscriptions' ); ?></a>
                        <span><?php _e( 'Member details saved successfully !', 'paid-member-subscriptions' ); ?><span>
                    </div>
                </div>

                <div class="form">
                    <?php foreach( $billing_fields as $slug => $field ) : ?>
                        <?php pms_output_form_field( $field ); ?>
                    <?php endforeach; ?>

                    <input type="hidden" name="pms_member_id" value="<?php echo isset( $_GET['member_id'] ) ? $_GET['member_id'] : ''; ?>" />

                    <a href="" id="save" class="button button-secondary"><?php _e( 'Save', 'paid-member-subscriptions' ); ?></a>

                </div>
            </div>
        </div>

        <?php
    }

    /**
     * Billing Details fields
     * @return [type] [description]
     */
    public function get_billing_fields( $user_id = null ){

        if( $user_id == null && is_user_logged_in() )
            $user_id = pms_get_current_user_id();

        if( ! empty( $user_id ) ) {
            $user      = get_userdata( $user_id );
            $user_meta = get_user_meta( $user_id );
        }

        $fields = array(
            'pms_billing_details_heading' => array(
                'section'         => 'billing_details',
                'type'            => 'heading',
                'default'         => '<h3>' . __( 'Billing Details', 'paid-member-subscriptions' ) . '</h3>',
                'element_wrapper' => 'li',
            ),
            'pms_billing_first_name' => array(
                'section'         => 'billing_details',
                'type'            => 'text',
                'name'            => 'pms_billing_first_name',
                'default'         => '',
                'value'           => ( isset( $_POST['pms_billing_first_name'] ) ? $_POST['pms_billing_first_name'] : ( !(empty($user_meta['pms_billing_first_name'])) ? $user_meta['pms_billing_first_name'][0] : ( ! empty( $user->first_name ) ? $user->first_name : '' ) ) ),
                'label'           => __( 'Billing First Name', 'paid-member-subscriptions' ),
                'description'     => '',
                'element_wrapper' => 'li',
                'required'        => 1,
                'wrapper_class'   => 'pms-billing-first-name',
            ),
            'pms_billing_last_name' => array(
                'section'         => 'billing_details',
                'type'            => 'text',
                'name'            => 'pms_billing_last_name',
                'default'         => '',
                'value'           => ( isset( $_POST['pms_billing_last_name'] ) ? $_POST['pms_billing_last_name'] : ( !(empty($user_meta['pms_billing_last_name'])) ? $user_meta['pms_billing_last_name'][0] : ( ! empty( $user->last_name ) ? $user->last_name : '' ) ) ),
                'label'           => __( 'Billing Last Name', 'paid-member-subscriptions' ),
                'description'     => '',
                'element_wrapper' => 'li',
                'required'        => 1,
                'wrapper_class'   => 'pms-billing-last-name',
            ),
            'pms_billing_email' => array(
                'section'         => 'billing_details',
                'type'            => 'text',
                'name'            => 'pms_billing_email',
                'default'         => '',
                'value'           => ( isset( $_POST['pms_billing_email'] ) ? $_POST['pms_billing_email'] : ( !(empty($user_meta['pms_billing_email'])) ? $user_meta['pms_billing_email'][0] : ( ! empty( $user->user_email ) ? $user->user_email : '' ) ) ),
                'label'           => __( 'Billing Email', 'paid-member-subscriptions' ),
                'description'     => '',
                'element_wrapper' => 'li',
                'required'        => 1,
                'wrapper_class'   => 'pms-billing-email',
            ),
            'pms_billing_company' => array(
                'section'         => 'billing_details',
                'type'            => 'text',
                'name'            => 'pms_billing_company',
                'default'         => '',
                'value'           => ( isset( $_POST['pms_billing_company'] ) ? $_POST['pms_billing_company'] : ( !(empty($user_meta['pms_billing_company'])) ? $user_meta['pms_billing_company'][0] : '') ),
                'label'           => __( 'Billing Company', 'paid-member-subscriptions' ),
                'description'     => __( 'If entered, this will appear on the invoice, replacing the First and Last Name.', 'paid-member-subscriptions' ),
                'element_wrapper' => 'li',
                'wrapper_class'   => 'pms-billing-company',
            ),
            'pms_billing_address' => array(
                'section'         => 'billing_details',
                'type'            => 'text',
                'name'            => 'pms_billing_address',
                'default'         => '',
                'value'           => ( isset( $_POST['pms_billing_address'] ) ? $_POST['pms_billing_address'] : ( !(empty($user_meta['pms_billing_address'])) ? $user_meta['pms_billing_address'][0] : '') ),
                'label'           => __( 'Billing Address', 'paid-member-subscriptions' ),
                'description'     => '',
                'element_wrapper' => 'li',
                'required'        => 1,
                'wrapper_class'   => 'pms-billing-address',
            ),
            'pms_billing_city' => array(
                'section'         => 'billing_details',
                'type'            => 'text',
                'name'            => 'pms_billing_city',
                'default'         => '',
                'value'           => ( isset( $_POST['pms_billing_city'] ) ? $_POST['pms_billing_city'] : ( !(empty($user_meta['pms_billing_city'])) ? $user_meta['pms_billing_city'][0] : '') ),
                'label'           => __( 'Billing City', 'paid-member-subscriptions' ),
                'description'     => '',
                'element_wrapper' => 'li',
                'required'        => 1,
                'wrapper_class'   => 'pms-billing-city',
            ),
            'pms_billing_zip' => array(
                'section'         => 'billing_details',
                'type'            => 'text',
                'name'            => 'pms_billing_zip',
                'default'         => '',
                'value'           => ( isset( $_POST['pms_billing_zip']) ? $_POST['pms_billing_zip'] : ( !(empty($user_meta['pms_billing_zip'])) ? $user_meta['pms_billing_zip'][0] : '') ),
                'label'           => __( 'Billing Zip / Postal Code', 'paid-member-subscriptions' ),
                'description'     => '',
                'element_wrapper' => 'li',
                'wrapper_class'   => 'pms-billing-zip',
            ),
            'pms_billing_country' => array(
                'section'         => 'billing_details',
                'type'            => 'select',
                'name'            => 'pms_billing_country',
                'default'         => '',
                'value'           => ( isset( $_POST['pms_billing_country'] ) ? $_POST['pms_billing_country'] : ( !(empty($user_meta['pms_billing_country'])) ? $user_meta['pms_billing_country'][0] : '') ),
                'label'           => __( 'Billing Country', 'paid-member-subscriptions' ),
                'options'         => function_exists( 'pms_get_countries' ) ? pms_get_countries() : array(),
                'description'     => '',
                'element_wrapper' => 'li',
                'required'        => 1 ,
                'wrapper_class'   => 'pms-billing-country',
            ),
            'pms_billing_state' => array(
                'section'         => 'billing_details',
                'type'            => 'select_state',
                'name'            => 'pms_billing_state',
                'default'         => '',
                'value'           => ( isset( $_POST['pms_billing_state'] ) ? $_POST['pms_billing_state'] : ( !(empty($user_meta['pms_billing_state'])) ? $user_meta['pms_billing_state'][0] : '') ),
                'label'           => __( 'Billing State / Province', 'paid-member-subscriptions' ),
                'description'     => '',
                'element_wrapper' => 'li',
                'wrapper_class'   => 'pms-billing-state',
            )
        );

        $fields = apply_filters( 'pms_billing_details_fields', $fields, $user_id );

        if( is_admin() )
            $fields = array_map( array( $this, 'prepare_fields_for_admin' ), $fields );

        return $fields;

    }

    public function get_billing_fields_data( $user_id ){

        if( empty( $user_id ) )
            return false;

        $fields = $this->get_billing_fields( $user_id );

        $data = array();

        foreach( $fields as $key => $value ){
            if( !empty( $value['value'] ) )
                $data[$key] = $value['value'];
        }

        return $data;

    }

    public function prepare_fields_for_admin( $field ){

        if( isset( $field['type'] ) && $field['type'] == 'heading' )
            return array();

        unset( $field['description'] );
        unset( $field['required'] );

        $field['element_wrapper'] = 'div';
        $field['wrapper_class'] .= ' pms-meta-box-field-wrapper';

        return $field;

    }

    public function format_billing_details( $data, $return = false ){

        $billing_details = '';

        $billing_details .= ( ! empty( $data['pms_billing_first_name'] ) ? '<span id="pms_billing_first_name">' . $data['pms_billing_first_name'] . '</span>' : '' ) . ' ';
        $billing_details .= ( ! empty( $data['pms_billing_last_name'] ) ? '<span id="pms_billing_last_name">' . $data['pms_billing_last_name'] . '</span>' : '' );

        // Company
        if( ! empty( $data['pms_billing_company'] ) )
            $billing_details .= PHP_EOL . '<span id="pms_billing_company">' . $data['pms_billing_company'] . '</span>';

        // Address
        if( ! empty( $data['pms_billing_address'] ) )

            $billing_details .= PHP_EOL . '<span id="pms_billing_address">' . $data['pms_billing_address'] . '</span>';

        // City Line
        $billing_city = '';

        // Zip code
        if( ! empty( $data['pms_billing_zip'] ) )
            $billing_city .= '<span id="pms_billing_zip">' . $data['pms_billing_zip'] . '</span>';

        // City
        if( ! empty( $data['pms_billing_city'] ) )
            $billing_city .= ', ' . '<span id="pms_billing_city">' . $data['pms_billing_city'] . '</span>';

        if( !empty( $billing_city ) )
            $billing_details .= PHP_EOL . $billing_city;

        // Billing country
        if( ! empty( $data['pms_billing_country'] ) ) {

            $countries = pms_get_countries();

            if( ! empty( $countries[$data['pms_billing_country']] ) )
                $billing_details .= PHP_EOL . '<span id="pms_billing_country">' . $countries[$data['pms_billing_country']]  . '</span>';

        }

        // Email
        $billing_details .= PHP_EOL . ( ! empty( $data['pms_billing_email'] ) ? '<span id="pms_billing_email">' . $data['pms_billing_email'] . '</span>' : '' );

        $billing_details = wpautop( $billing_details );

        if( $return === true )
            return $billing_details;
        else
            echo $billing_details;

    }

    public function localize_billing_details( $menu_slug ){

        wp_localize_script( $menu_slug . '-js', 'pms_billing_details', array(
            'fields'                    => array_keys( $this->get_billing_fields() ),
            'edit_member_details_nonce' => wp_create_nonce( 'pms_edit_member_details_nonce' )
        ));

        wp_localize_script( $menu_slug . '-js', 'PMS_States', pms_get_billing_states() );

    }

    public function edit_member_billing_details(){

        check_ajax_referer( 'pms_edit_member_details_nonce', 'security' );

        if( !current_user_can( 'manage_options' ) || empty( $_POST['member_id'] ) )
            die( json_encode( array( 'status' => 'error' ) ) );

        $user_id         = (int)$_POST['member_id'];
        $billing_details = $this->get_billing_fields();

        foreach( $billing_details as $slug => $label ){

            if( isset( $_POST[$slug] ) )
                update_user_meta( $user_id, $slug, sanitize_text_field( $_POST[$slug] ) );

        }

        die( json_encode( array( 'status' => 'success', 'address_output' => $this->format_billing_details( $_POST, true ) ) ) );

    }

}

$pms_billing_details = new PMS_Billing_Details();
$pms_billing_details->init();
