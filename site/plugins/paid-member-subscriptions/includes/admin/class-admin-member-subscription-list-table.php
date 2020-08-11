<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// WP_List_Table is not loaded automatically in the plugins section
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/*
 * Extent WP default list table for our custom members section
 *
 */
Class PMS_Member_Subscription_List_Table extends WP_List_Table {

    /**
     * Member
     *
     * @access public
     * @var int
     */
    public $member;

    /**
     * Subscription plan ids
     *
     * @access public
     * @var array
     */
    public $existing_subscription_plan_ids;


    /*
     * Constructor function
     *
     */
    public function __construct( $user_id ) {

        global $pagenow, $wp_importers, $hook_suffix, $plugin_page, $typenow, $taxnow;
        $page_hook = get_plugin_page_hook($plugin_page, $plugin_page);

        parent::__construct( array(
            'singular'  => 'member-subscription',
            'plural'    => 'member-subscriptions',
            'ajax'      => false,

            // Screen is a must!
            'screen'    => $page_hook
        ));

        $this->member = pms_get_member($user_id);

    }


    /**
     * Overwrites the parent class.
     * Define the columns for the members
     *
     * @return array
     *
     */
    public function get_columns() {

        $columns = array(
            'subscription_plan' => __( 'Subscription Plan', 'paid-member-subscriptions' ),
            'start_date'        => __( 'Start Date', 'paid-member-subscriptions' ),
            'expiration_date'   => __( 'Expiration date', 'paid-member-subscriptions' ),
            'status'   			=> __( 'Status', 'paid-member-subscriptions' ),
            'auto_renewal'      => __( 'Auto-renewing', 'paid-member-subscriptions' ),
            'active_trial'      => __( 'Active Trial', 'paid-member-subscriptions' ),
            'actions'           => ''
        );

        if( ! pms_payment_gateways_support( pms_get_active_payment_gateways(), 'recurring_payments' ) )
            unset( $columns['auto_renewal'] );

        if( ! pms_payment_gateways_support( pms_get_active_payment_gateways(), 'subscription_free_trial' ) )
            unset( $columns['active_trial'] );

        return $columns;

    }


    /**
     * Overwrites the parent class.
     * Define which columns to hide
     *
     * @return array
     *
     */
    public function get_hidden_columns() {

        return array();

    }


    /**
     * Overwrites the parent class.
     * Define which columns are sortable
     *
     * @return array
     *
     */
    public function get_sortable_columns() {

        return array();

    }


    /**
     * Method to add an entire row to the table
     * Replaces parent method
     *
     * @param array $item - The current row information
     *
     */
    public function single_row( $item ) {

        $row_classes = '';

        if( isset( $item['errors'] ) )
            $row_classes .= ' pms-field-error';

        if( !$this->member->is_member() )
            $row_classes .= ' pms-add-new edit-active';

        echo '<tr class="' . $row_classes . '">';
        $this->single_row_columns( $item );
        echo '</tr>';
    }


    /**
     * Method to add extra actions before and after the table
     * Replaces parent method
     *
     * @param string @which     - which side of the table ( top or bottom )
     *
     */
    public function extra_tablenav( $which ) {

        do_action( 'pms_member_subscription_list_table_extra_tablenav', $which, $this->member, $this->existing_subscription_plan_ids );

    }


    /**
     * Returns the table data
     *
     * @return array
     *
     */
    public function get_table_data() {

        $data = array();

        $item_count = 0;

        $member_subscriptions = pms_get_member_subscriptions( array( 'user_id' => $this->member->user_id, 'include_abandoned' => true ) );

        foreach( $member_subscriptions as $member_subscription ) {

            $user_subscription_plan = pms_get_subscription_plan( $member_subscription->subscription_plan_id );

            $data[] = array(
                'subscription_id'      => $member_subscription->id,
                'subscription_plan_id' => $member_subscription->subscription_plan_id,
                'subscription_plan'    => $user_subscription_plan->name,
                'start_date'           => pms_sanitize_date( $member_subscription->start_date ),
                'expiration_date'      => pms_sanitize_date( $member_subscription->expiration_date ),
                'next_payment_date'    => pms_sanitize_date( $member_subscription->billing_next_payment ),
				'status'               => $member_subscription->status,
                'auto_renewal'         => $member_subscription->is_auto_renewing(),
                'active_trial'         => !empty( $member_subscription->trial_end ) && strtotime( $member_subscription->trial_end ) > time() ? true : false,
                'item_count'           => $item_count
            );

            $item_count++;
        }

        return $data;

    }


    /*
     * Populates the items for the table
     *
     */
    public function prepare_items() {

        $columns = $this->get_columns();
        $hidden_columns = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->get_table_data();

        $this->_column_headers = array( $columns, $hidden_columns, $sortable );
        $this->items = $data;

    }


    /**
     * Return data that will be displayed in each column
     *
     * @param array $item           - data for the current row
     * @param string $column_name   - name of the current column
     *
     * @return string
     *
     */
    public function column_default( $item, $column_name ) {

        if( !isset( $item[ $column_name ] ) )
            return false;

        return $item[ $column_name ];

    }


    /**
     * Return data that will be displayed in the subscription plan column
     *
     * @param array $item           - data for the current row
     *
     * @return string
     *
     */
    public function column_subscription_plan( $item ) {

        $output = '<span>' . ( !empty( $item['subscription_plan'] ) ? $item['subscription_plan'] : sprintf( __( 'Not Found - ID: %s', 'paid-member-subscriptions' ), $item['subscription_plan_id'] ) ) . '</span>';

        return $output;

    }


    /**
     * Return data that will be displayed in the start date column
     *
     * @param array $item           - data for the current row
     *
     * @return string
     *
     */
    public function column_start_date( $item ) {

        $output = '<span>' . $item['start_date'] . '</span>';

        return $output;

    }


    /**
     * Return data that will be displayed in the expiration date column
     *
     * @param array $item           - data for the current row
     *
     * @return string
     *
     */
    public function column_expiration_date( $item ) {

        $output = '<span>' . ( ! empty( $item['expiration_date'] ) ? $item['expiration_date'] : ( !empty( $item['next_payment_date'] ) ? $item['next_payment_date'] : __( 'Unlimited', 'paid-member-subscriptions' ) ) ) . '</span>';

        return $output;

    }


    /**
     * Return data that will be displayed in the status column
     *
     * @param array $item           - data for the current row
     *
     * @return string
     *
     */
    public function column_status( $item ) {

        $statuses = pms_get_member_subscription_statuses();

        $output = '<span>' . ( isset($statuses[ $item['status'] ]) ? $statuses[ $item['status'] ] : '' ) . '</span>';

        return $output;

    }


    /**
     * Return data that will be displayed in the auto-renewal column
     *
     * @param array $item           - data for the current row
     *
     * @return string
     *
     */
    public function column_auto_renewal( $item ) {

        $output = ( $item['auto_renewal'] ? __( 'Yes', 'paid-member-subscriptions' ) : __( 'No', 'paid-member-subscriptions' ) );

        return $output;

    }

    public function column_active_trial( $item ) {

        $output = ( $item['active_trial'] ? __( 'Yes', 'paid-member-subscriptions' ) : __( 'No', 'paid-member-subscriptions' ) );

        return $output;

    }


    /**
     * Return data that will be displayed in the actions column
     *
     * @param array $item           - data for the current row
     *
     * @return string
     *
     */
    public function column_actions( $item ) {

        $output = '<div class="row-actions">';

            $output .= '<a href="' . add_query_arg( array( 'page' => 'pms-members-page', 'subpage' => 'edit_subscription', 'subscription_id' => $item['subscription_id'] ), 'admin.php' ) . '" class="button button-secondary">' . __( 'Edit', 'paid-member-subscriptions' ) . '</a>';

        $output .= '</div>';

        return $output;

    }

}
