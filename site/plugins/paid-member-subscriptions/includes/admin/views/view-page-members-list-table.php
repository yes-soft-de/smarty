<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML output for the members admin page
 */
?>

<div class="wrap">

    <h1>
        <?php echo $this->page_title; ?>

        <a href="<?php echo esc_url( add_query_arg( array( 'page' => $this->menu_slug, 'subpage' => 'add_subscription' ), admin_url( 'admin.php' ) ) ); ?>" class="add-new-h2"><?php echo __( 'Add New', 'paid-member-subscriptions' ); ?></a>
        <a href="<?php echo esc_url( add_query_arg( array( 'page' => $this->menu_slug, 'subpage' => 'add_new_members_bulk' ), admin_url( 'admin.php' ) ) ); ?>" class="add-new-h2"><?php echo __( 'Bulk Add New', 'paid-member-subscriptions' ); ?></a>
    </h1>
    <form method="get">
        <input type="hidden" name="page" value="pms-members-page" />
    <?php
        $this->list_table->prepare_items();
        $this->list_table->views();
        $this->list_table->search_box( __( 'Search Members', 'paid-member-subscriptions' ), 'pms_search_members' );
        $this->list_table->display();
    ?>
    </form>

</div>
