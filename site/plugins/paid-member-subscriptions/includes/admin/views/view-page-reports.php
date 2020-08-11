<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML output for the reports admin page
 */
?>

<div class="wrap">

    <h1><?php echo $this->page_title; ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="<?php echo admin_url( 'admin.php?page=pms-reports-page' ); ?>" class="nav-tab <?php echo $active_tab == 'pms-reports-page' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Reports', 'paid-member-subscriptions' ); ?></a>
        <a href="<?php echo admin_url( 'admin.php?page=pms-export-page' ); ?>"  class="nav-tab <?php echo $active_tab == 'pms-export-page' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Export', 'paid-member-subscriptions' ); ?></a>
        <?php do_action( 'pms_reports_tab' ); ?>
    </h2>

    <form id="pms-form-reports" class="pms-form" action="<?php echo admin_url( 'admin.php' ); ?>" method="get">
        <input type="hidden" name="page" value="pms-reports-page" />

        <!-- Filter box -->
        <div class="postbox">
            <div class="inside">
                <h4><?php echo __( 'Filters', 'paid-member-subscriptions' ); ?></h4>

                <?php do_action( 'pms_reports_filters' ); ?>

                <button name="pms-action" type="submit" class="button-secondary" value="filter_results"><?php echo __( 'Filter', 'paid-member-subscriptions' ); ?></button>
            </div>
        </div>

        <!-- Chart and details -->
        <div class="postbox">
            <div class="inside" style="padding: 20px 45px;">
                <canvas id="payment-report-chart" width="1000" height="250"></canvas>
            </div>
        </div>

        <?php do_action( 'pms_reports_form_bottom' ); ?>

        <?php wp_nonce_field( 'pms_reports_nonce', '_wpnonce', false ); ?>

    </form>

    <?php do_action( 'pms_reports_page_bottom' ); ?>

</div>
