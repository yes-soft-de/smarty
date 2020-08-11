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
Class PMS_Members_Add_New_Bulk_List_Table extends WP_List_Table {

    /**
     * Members per page
     *
     * @access public
     * @var int
     */
    public $items_per_page;

    /**
     * Members table data
     *
     * @access public
     * @var array
     */
    public $data;

    /**
     * Members table views count
     *
     * @access public
     * @var array
     */
    public $views_count = array();

    /*
     * Constructor function
     *
     */
    public function __construct() {

        parent::__construct( array(
            'singular'  => 'member-add-new-bulk',
            'plural'    => 'members-add-new-bulk',
            'ajax'      => false
        ));

        // Set items per page
        $items_per_page = get_user_meta( get_current_user_id(), 'pms_users_per_page', true );

        if( empty( $items_per_page ) ) {
            $screen         = get_current_screen();
            $per_page       = $screen->get_option( 'per_page' );
            $items_per_page = $per_page['default'];
        }

        $this->items_per_page = $items_per_page;

        // Set data
        $this->set_table_data();

    }

    /*
     * Overwrites the parent class.
     * Define the columns for the members
     *
     * @return array
     *
     */
    public function get_columns() {

        $columns = array(
            'cb'       => '<input type="checkbox" />',
            'user_id'  => __( 'User ID', 'paid-member-subscriptions' ),
            'username' => __( 'Username', 'paid-member-subscriptions' ),
            'email'    => __( 'E-mail', 'paid-member-subscriptions' ),
            'role'     => __( 'Role', 'paid-member-subscriptions' )
        );

        return $columns;

    }


    /*
     * Overwrites the parent class.
     * Define which columns to hide
     *
     * @return array
     *
     */
    public function get_hidden_columns() {

        return array();

    }


    /*
     * Overwrites the parent class.
     * Define which columns are sortable
     *
     * @return array
     *
     */
    public function get_sortable_columns() {

        return array(
            'user_id'           => array( 'user_id', false ),
            'username'          => array( 'username', false )
        );

    }


    /*
     * Returns the possible views for the members list table
     *
     */
    protected function get_views() {

        return array();

    }


    /*
     * Method to add extra actions before and after the table
     * Replaces parent method
     *
     * @param string @which     - which side of the table ( top or bottom )
     *
     */
    public function extra_tablenav( $which ) {

        if( $which == 'top' ) {

            $subscription_plans = pms_get_subscription_plans();

            echo '<select id="pms_add_member_bulk_subscription_plan" name="subscription_plan_id">';

            echo '<option value="-1">' . __( 'Select Subscription Plan...', 'paid-member-subscriptions' ) . '</option>';

            if( !empty( $subscription_plans ) ) {
                foreach( $subscription_plans as $subscription_plan )
                    echo '<option value="' . $subscription_plan->id . '">' . $subscription_plan->name . '</option>';
            }

            echo '</select>';

            submit_button( __( 'Assign', 'paid-member-subscriptions' ), 'secondary', 'pms_add_member_bulk_assign', false );

        }

    }


    /*
     * Sets the table data
     *
     * @return array
     *
     */
    public function set_table_data() {

        $data = array();

        $args = array();

        // If it's a search query send search parameter through $args
        if ( !empty($_REQUEST['s']) ) {
            $args = array(
                'order'                => 'ASC',
                'orderby'              => 'ID',
                'offset'               => '',
                'limit'               => '',
                'search'               => $_REQUEST['s']
            );
        }

        // Get users
        $users = pms_get_users_non_members( $args );

        // Set views count array to 0, we use this to display the count
        // next to the views links
        $views = $this->get_views();
        if( !empty( $views ) ) {
            foreach( $views as $view_slug => $view_link) {
                $this->views_count[$view_slug] = 0;
            }

            // Get the current view to filter results
            $selected_view = ( isset( $_GET['pms-view'] ) ? trim( $_GET['pms-view'] ) : '' );
        }


        foreach( $users as $user ) {

            $usr = get_userdata( $user['id'] );
            
            $checkbox = '<label class="screen-reader-text" for="user_' . $usr->data->ID . '">' . sprintf( __( 'Select %s' ), $usr->data->user_login ) . '</label>'
                . "<input type='checkbox' name='users[]' id='user_{$usr->data->ID}' value='{$usr->data->ID}' />";

            $data[] = array(
                'cb'                => $checkbox,
                'user_id'           => $usr->data->ID,
                'username'          => $usr->data->user_login,
                'email'             => $usr->data->user_email,
                'role'              => ( ! empty( $usr->roles[0] ) ? $usr->roles[0] : '' )
            );
        }

        $this->data = $data;

    }



    /*
     * Populates the items for the table
     *
     * @param array $item           - data for the current row
     *
     * @return string
     *
     */
    public function prepare_items() {

        $columns        = $this->get_columns();
        $hidden_columns = $this->get_hidden_columns();
        $sortable       = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden_columns, $sortable );

        $data = $this->data;
        usort( $data, array( $this, 'sort_data' ) );

        $paged = ( isset( $_GET['paged'] ) ? (int)$_GET['paged'] : 1 );

        $this->set_pagination_args( array(
            'total_items' => count( $data ),
            'per_page'    => $this->items_per_page
        ) );

        $data = array_slice( $data, $this->items_per_page * ( $paged-1 ), $this->items_per_page );

        $this->items = $data;

    }


    /*
     * Sorts the data by the variables in GET
     *
     */
    public function sort_data( $a, $b ) {

        // Set defaults
        $orderby = 'username';
        $order   = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = sanitize_text_field( $_GET['orderby'] );
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = sanitize_text_field( $_GET['order'] );
        }

        $result = strnatcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;

    }


    /*
     * Return data that will be displayed in each column
     *
     * @param array $item           - data for the current row
     * @param string $column_name   - name of the current column
     *
     * @return string
     *
     */
    public function column_default( $item, $column_name ) {

        return $item[ $column_name ];

    }


    /*
     * Return data that will be displayed in the cb ( checkbox ) column
     *
     * @param array $item   - row data
     *
     * @return string
     *
     */
    public function column_cb( $item ) {

        return $item['cb'];

    }


    /*
     * Return data that will be displayed in the username column
     *
     * @param array $item   - row data
     *
     * @return string
     *
     */
    public function column_username( $item ) {

        $actions = array();

        // Add an edit user action for each member
        $actions['view_user'] = '<a href="' . add_query_arg( array( 'user_id' => $item['user_id']), admin_url('user-edit.php') ) . '">' . __( 'Edit User', 'paid-member-subscriptions' ) . '</a>';

        // Return value saved for username and also the row actions
        return $item['username'] . $this->row_actions( $actions );

    }


    /*
     * Return data that will be displayed in the subscriptions column
     *
     * @param array $item   - row data
     *
     * @return string
     *
     */
    public function column_subscriptions( $item ) {

        $output = '';

        foreach( $item['subscriptions'] as $member_subscription ) {

            $subscription_plan = pms_get_subscription_plan( $member_subscription['subscription_plan_id'] );

            $output .= '<span class="pms-member-list-subscription pms-has-bubble">';

                $output .= apply_filters( 'pms_list_table_' . $this->_args['plural'] . '_show_status_dot', '<span class="pms-status-dot ' . $member_subscription['status'] . '"></span>' );

                $output .= ( !empty( $subscription_plan->id ) ? $subscription_plan->name : sprintf( __( 'Subscription Plan Not Found - ID: %s', 'paid-member-subscriptions' ), $member_subscription['subscription_plan_id'] ) );

                $output .= '<div class="pms-bubble">';

                    $statuses = pms_get_member_subscription_statuses();

                    $output .= '<div><span class="alignleft">' . 'Start date' . '</span><span class="alignright">' . pms_sanitize_date( $member_subscription['start_date'] ) . '</span></div>';
                    $output .= '<div><span class="alignleft">' . 'Expiration date' . '</span><span class="alignright">' . pms_sanitize_date( $member_subscription['expiration_date'] ) . '</span></div>';
                    $output .= '<div><span class="alignleft">' . 'Status' . '</span><span class="alignright">' .( isset( $statuses[ $member_subscription['status'] ] ) ? $statuses[ $member_subscription['status'] ] : '' ) . '</span></div>';

                $output .= '</div>';

            $output .= '</span>';

        }

        return $output;

    }


    /*
     * Display if no items are found
     *
     */
    public function no_items() {

        echo __( 'No users found', 'paid-member-subscriptions' );

    }

}
