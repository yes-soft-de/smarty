<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * User's payment history table
 */
?>

<?php

    $number_per_page = $args['number_per_page'];

    if (!is_numeric($number_per_page) || empty($number_per_page)){
        $number_per_page = 10;
    }

    $page            = ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 ) );

    $payments = pms_get_payments( array(
        'order'   => 'DESC',
        'user_id' => $user_id,
        'number'  => $number_per_page,
        'offset'  => ( $page !== 1 ? ( $page - 1 ) * $number_per_page : '' )
    ));

    $payment_statuses = pms_get_payment_statuses();

?>

<?php if( !empty( $payments ) ): // Handle no payments situation ?>
<table id="pms-payment-history" class="pms-table">

    <thead>
        <tr>
            <th class="pms-payment-id"><?php _e( 'ID', 'paid-member-subscriptions' ); ?></th>
            <th class="pms-payment-amount"><?php _e( 'Amount', 'paid-member-subscriptions' ); ?></th>
            <th class="pms-payment-date"><?php _e( 'Date / Time', 'paid-member-subscriptions' ); ?></th>
            <th class="pms-payment-status"><?php _e( 'Status', 'paid-member-subscriptions' ); ?></th>

            <?php do_action( 'pms_payment_history_table_header', $user_id, $payments ); ?>
        </tr>
    </thead>

    <tbody>
        <?php foreach( $payments as $payment ): ?>
            <tr>
                <td class="pms-payment-id"><?php echo '#' . esc_html( $payment->id ); ?></td>
                <td class="pms-payment-amount" title='<?php apply_filters( 'pms_payment_history_amount_row_title', '', $payment ); ?>'><?php echo esc_html( pms_format_price( $payment->amount, pms_get_active_currency() ) ); ?></td>
                <td class="pms-payment-date"><?php echo ucfirst( date_i18n( apply_filters( 'pms_payment_history_date_format', 'j F, Y H:i' ), strtotime( $payment->date ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ); ?></td>
                <td class="pms-payment-status"><?php echo ( ! empty( $payment_statuses[$payment->status] ) ? esc_html( $payment_statuses[$payment->status] ) : '' ); ?></td>

                <?php do_action( 'pms_payment_history_table_row', $user_id, $payment ); ?>
            </tr>
        <?php endforeach; ?>

        <?php do_action( 'pms_payment_history_table_body', $user_id, $payments ); ?>
    </tbody>

</table>

<?php echo pms_paginate_links( apply_filters( 'pms_payment_history_table_paginate_links', array( 'id' => 'pms-payment-history', 'current' => max( 1, $page ), 'total'   => ceil( pms_get_member_payments_count( $user_id ) / $number_per_page )  ) ) ); ?>

<?php else: // Add payments ?>
    <p class="pms-no-payments"><?php _e( 'No payments found', 'paid-member-subscriptions' ); ?></p>
<?php endif; // End of payments ?>
