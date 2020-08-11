<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * View for the Payments Summary meta-box in the WP Dashboard page
 */
?>

<?php

    // Get payments
    $today_payments = pms_get_payments( array( 'status' => 'completed', 'date' => date( 'Y-m-d' ), 'number' => '-1' ) );
    $month_payments = pms_get_payments( array( 'status' => 'completed', 'date' => array( date( 'Y-m-01' ) , date( 'Y-m-d' ) ), 'number' => '-1' ) );
    $recent_payments = pms_get_payments( array( 'status' => 'completed', 'order' => 'DESC', 'number' => 5 ) );

    // Calculate monthly income
    $month_income = 0;
    foreach( $month_payments as $payment )
        $month_income +=(int)$payment->amount;

    // Calculate today's income
    $today_income = 0;
    foreach( $today_payments as $payment )
        $today_income +=(int)$payment->amount;

    // Get currency symbol
    $currency_symbol = pms_get_currency_symbol( pms_get_active_currency() );

?>

<div id="pms-payments-summary">

    <!-- This Month's Payments -->
    <div class="pms-month-income">

        <h4><?php echo __( 'Current Month', 'paid-member-subscriptions' ); ?></h4>

        <p>
            <span><?php echo __( 'Income:', 'paid-member-subscriptions' ); ?></span>
            <span><?php echo $currency_symbol . $month_income; ?></span>
        </p>

        <p>
            <span><?php echo __( 'Payments:', 'paid-member-subscriptions' ); ?></span>
            <span><?php echo count( $month_payments ); ?></span>
        </p>

    </div>

    <!-- Today's Payments -->
    <div class="pms-today-income">

        <h4><?php echo __( 'Today', 'paid-member-subscriptions' ); ?></h4>

        <p>
            <span><?php echo __( 'Income:', 'paid-member-subscriptions' ); ?></span>
            <span><?php echo $currency_symbol . $today_income; ?></span>
        </p>

        <p>
            <span><?php echo __( 'Payments:', 'paid-member-subscriptions' ); ?></span>
            <span><?php echo count( $today_payments ); ?></span>
        </p>

    </div>

    <!-- Recent Payments -->
    <div class="pms-recent-payments">

        <h4><?php echo __( 'Recent Payments', 'paid-member-subscriptions' ); ?></h4>

        <?php if( !empty( $recent_payments ) ): ?>
        <?php foreach( $recent_payments as $payment ): ?>
            <?php $payment_user = get_userdata( $payment->user_id ); ?>
            <div class="pms-recent-payment">
                <div>
                    <?php echo $payment_user->user_login . ' (' . $payment_user->user_email . ')' ?>
                    <span class="pms-recent-payments-amount"><?php echo $currency_symbol . $payment->amount; ?></span>
                </div>
                <a href="<?php echo add_query_arg( array( 'page' => 'pms-payments-page', 'pms-action' => 'edit_payment', 'payment_id' => $payment->id ), admin_url( 'admin.php' ) ); ?>"><?php echo __( 'View Details', 'paid-member-subscriptions' ); ?></a>
            </div>
        <?php endforeach; ?>

            <a href="<?php echo add_query_arg( array( 'page' => 'pms-payments-page' ), admin_url( 'admin.php' ) ); ?>"><?php echo __( 'View All Payments', 'paid-member-subscriptions' ); ?></a>

        <?php else: ?>
            <div><?php echo __( 'No payments found.', 'paid-member-subscriptions' ); ?></div>
        <?php endif; ?>
    </div>

</div>
