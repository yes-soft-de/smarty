<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML output for the payments admin page
 */
?>

<div class="wrap">

    <h1>
        <?php echo $this->page_title; ?>
        <a href="<?php echo esc_url( add_query_arg( array( 'page' => $this->menu_slug, 'pms-action' => 'add_payment' ), admin_url( 'admin.php' ) ) ); ?>" class="add-new-h2"><?php echo __( 'Add New', 'paid-member-subscriptions' ); ?></a>
    </h1>

    <form method="get">
        <input type="hidden" name="page" value="pms-payments-page" />
    <?php

        $this->list_table->prepare_items();
        $this->list_table->views();
        $this->list_table->search_box(__('Search Payments'),'pms_search_payments');
        $this->list_table->display();

    ?>
    </form>

</div>
