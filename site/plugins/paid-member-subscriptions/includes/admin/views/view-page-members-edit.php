<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML output for the members admin edit member page
 */
?>

<div class="wrap">

    <h1>
        <?php echo __( 'Edit Member', 'paid-member-subscriptions' ); ?>
    </h1>

    <form id="pms-form-edit-member" class="pms-form" method="POST">

        <?php $member = pms_get_member( (int)trim( $_REQUEST['member_id'] ) ); ?>

        <div class="pms-form-field-wrapper pms-form-field-user-name">

            <label class="pms-form-field-label"><?php echo __( 'Username', 'paid-member-subscriptions' ); ?></label>
            <input type="hidden" id="pms-member-user-id" name="pms-member-user-id" class="widefat" value="<?php echo esc_attr( $member->user_id ); ?>" />

            <span class="readonly medium"><strong><?php echo $member->username; ?></strong></span>

        </div>

        <h3><?php _e( 'Subscriptions', 'paid-member-subscriptions' ); ?></h3>
        <?php
            $member_subscriptions_table = new PMS_Member_Subscription_List_Table( $member->user_id );
            $member_subscriptions_table->prepare_items();
            $member_subscriptions_table->display();
        ?>

        <br />

        <h3><?php _e( 'Recent Payments', 'paid-member-subscriptions' ); ?></h3>
        <?php
            $member_payments_table = new PMS_Member_Payments_List_Table();
            $member_payments_table->prepare_items();
            $member_payments_table->display();
        ?>

        <?php do_action( 'pms_member_edit_form_field' ); ?>

        <?php wp_nonce_field( 'pms_member_nonce' ); ?>

    </form>

</div>
