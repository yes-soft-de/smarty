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
Class PMS_Payments_Log_List_Table extends WP_List_Table {

    private $payment_id;

    /*
     * Constructor function
     *
     */
    public function __construct() {

        global $pagenow, $wp_importers, $hook_suffix, $plugin_page, $typenow, $taxnow;
        $page_hook = get_plugin_page_hook($plugin_page, $plugin_page);

        parent::__construct( array(
            'singular'  => 'payment-log',
            'plural'    => 'payment-logs',
            'ajax'      => false,

            // Screen is a must!
            'screen'    => $page_hook
        ));

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
            'date'       => __( 'Date', 'paid-member-subscriptions' ),
            'message'    => __( 'Message', 'paid-member-subscriptions' ),
            'modal_data' => __( 'Modal Data', 'paid-member-subscriptions' ),
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

        return array();

    }


    /*
     * Returns the table data
     *
     * @return array
     *
     */
    public function get_table_data() {

        $this->payment_id = (int)sanitize_text_field( $_REQUEST['payment_id'] );

        $payment = pms_get_payment( $this->payment_id );

        $data = array();

        if ( !empty( $payment->logs ) ) {

            foreach ( array_reverse( $payment->logs ) as $log ) {

                //Remove logs added using the old structure by the Stripe add-on
                if ( isset( $log['type'] ) && $log['type'] == 'stripe' )
                    continue;

                $data[] = array(
                    'date'       => ucfirst( date_i18n( 'F d, Y H:i:s', strtotime( $log['date'] ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ),
                    'message'    => $this->get_message( $log ),
                    'modal_data' => $this->show_modal_link( $log['data'] ) ? $this->get_modal_content( $log ) : '',
                );

            }
        }

        return $data;

    }


    /*
     * Populates the items for the table
     *
     */
    public function prepare_items() {

        $columns        = $this->get_columns();
        $hidden_columns = $this->get_hidden_columns();
        $sortable       = $this->get_sortable_columns();

        $data = $this->get_table_data();

        $this->_column_headers = array( $columns, $hidden_columns, $sortable );
        $this->items = $data;

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

        if( !isset( $item[ $column_name ] ) )
            return false;

        return $item[ $column_name ];

    }

    public function column_message( $item ) {

        if ( empty( $item['modal_data'] ) )
            return $item['message'];

        $actions = array();

        // View Details action
        $actions['show_details'] = '<a id="payment-log-details" href="#">' . __( 'View Details', 'paid-member-subscriptions' ) . '</a>';

        $output  = $item['message'];
        $output .= $this->row_actions( $actions );

        return $output;

    }

    /*
     * Display if no items are found
     *
     */
    public function no_items() {
        _e( 'No logs found for this payment.', 'paid-member-subscriptions' );
    }

    /**
     * Returns a formatted message to show to the admin based on log data
     *
     * @return string
     */
    public function get_message( $log ) {

        $kses_args = array(
            'strong' => array()
        );

        switch ( $log['type'] ) {
            case 'payment_failed':
                $message = sprintf( __( 'Payment has failed. Reason: <strong>%s</strong>', 'paid-member-subscriptions' ), ( !empty( $log['data']['message'] ) ? $log['data']['message'] : 'no information provided by gateway, see details.' ) );
                break;
            case 'payment_added':
                $message = sprintf( __( 'Payment created manually by <strong>%s<strong>.', 'paid-member-subscriptions' ), $this->get_display_name( $log['data']['user'] ) );
                break;
            case 'status_changed':
                $message = sprintf( __( 'Payment status changed from <strong>%1$s</strong> to <strong>%2$s</strong>', 'paid-member-subscriptions' ), ucwords( $log['data']['old_data']['status'] ), ucwords( $log['data']['new_data']['status'] ) );

                if ( $log['data']['user'] == -1 || $log['data']['user'] == 0 )
                    $message .= ' by <strong>' . $this->get_display_name( $log['data']['user'] ) . '</strong>.';
                else {
                    $user = get_userdata( $log['data']['user'] );

                    if ( $user->has_cap( 'manage_options' ) )
                        $message .= ' by <strong>' . $this->get_display_name( $log['data']['user'] ) . '</strong>.';
                    else
                        $message .= '.';
                }

                break;
            case 'type_changed':
                $message = sprintf( __( 'Payment %1$s changed from <strong>%2$s</strong> to <strong>%3$s</strong>', 'paid-member-subscriptions' ), $log['data']['field'], $this->get_payment_type_human_format( $log['data']['old_data']['type'] ), $this->get_payment_type_human_format( $log['data']['new_data']['type'] ) );

                if ( $log['data']['user'] == -1 || $log['data']['user'] == 0 )
                    $message .= ' by <strong>' . $this->get_display_name( $log['data']['user'] ) . '</strong>.';
                else {
                    $user = get_userdata( $log['data']['user'] );

                    if ( $user->has_cap( 'manage_options' ) )
                        $message .= ' by <strong>' . $this->get_display_name( $log['data']['user'] ) . '</strong>.';
                    else
                        $message .= '.';
                }

                break;
            case 'new_payment':
                $message = sprintf( __( 'New payment added by <strong>%s</strong>.', 'paid-member-subscriptions' ), $this->get_display_name( $log['data']['user'] ) );
                break;
            default:
                $message = __( 'Something went wrong.', 'paid-member-subscriptions' );
                break;
        }

        return apply_filters( 'pms_payment_logs_system_error_messages', wp_kses( $message, $kses_args ), $log );
    }

    /**
     * Determines the name we need to show for messages which have the user id saved alongside the extra data
     *
     * @param  int      $user_id    An user id whose name we need to retrieve
     * @return string               Correct name based on the provided user id
     */
    private function get_display_name( $user_id ) {
        if ( $user_id == 0 )
            return 'system';
        else if ( $user_id == -1 )
            return 'gateway';

        $user = get_userdata( $user_id );

        if ( !$user )
            return 'system';
        else
            return $user->display_name;
    }

    /**
     * Based on the given log data, figures out which display method to call
     *
     * @param  array  $data   Extra data saved alongside the error message
     * @return HTML           [description]
     */
    private function get_modal_content( $log ) {

        if ( ( isset( $log['data']['request'] ) && isset( $log['data']['response'] ) ) || ( isset( $log['data']['new_data'] ) && isset( $log['data']['old_data'] ) ) )
            return $this->get_modal_half_content( $log );
        else
            return $this->get_modal_full_content( $log );

    }

    /**
     * Used when there are 2 points of data (request or response) to display them in columns, side by side
     *
     * @param  array   $data    Extra data saved alongside the error message
     * @return HTML
     */
    private function get_modal_half_content( $log ) {

        $data = $log['data'];

        $output = '';

        $data_left     = !empty( $data['request'] ) ? $data['request'] : $data['new_data'];
        $heading_left  = !empty( $data['request'] ) ? __( 'Request', 'paid-member-subscriptions' ) : __( 'Changed data', 'paid-member-subscriptions' );
        $data_right    = !empty( $data['response'] ) ? $data['response'] : $data['old_data'];
        $heading_right = !empty( $data['request'] ) ? __( 'Response', 'paid-member-subscriptions' ) : __( 'Old data', 'paid-member-subscriptions' );

        $header = apply_filters( 'pms_payment_logs_modal_header_content', '', $log, $this->payment_id );

        ob_start(); ?>

            <?php if ( !empty( $header ) ) : ?>
                <div class="pms-modal__fullrow">
                    <?php echo $header; ?>
                </div>
            <?php elseif ( !empty( $data['message'] ) ) : ?>
                <div class="pms-modal__fullrow">
                    <h2>Payment Gateway Message</h2>
                    <?php echo ( !empty( $data['message'] ) ? $data['message'] : '' ) ?>
                </div>
            <?php endif; ?>

            <div class="pms-modal__halfrow">
                <div class="pms-modal__half">
                    <h2><?php echo $heading_left; ?></h2>

                    <ul>
                        <?php foreach( $data_left as $key => $value ) : ?>
                            <li><strong><?php echo $key; ?></strong> => <?php echo $value ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="pms-modal__half">
                    <h2><?php echo $heading_right; ?></h2>

                    <ul>
                        <?php foreach( $data_right as $key => $value ) : ?>
                            <li><strong><?php echo $key; ?></strong> => <?php echo $value ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

        <?php

        $output .= ob_get_clean();

        return $output;

    }

    /**
     * Used when we have a single point of data saved alongside the log message.
     * Will output a 2 column wrapped list with all keys from the array
     *
     * @param  array    $data   Extra data saved alongside the error message
     * @return HTML
     */
    private function get_modal_full_content( $log ) {

        $data = $log['data'];

        $output = '';

        $header = apply_filters( 'pms_payment_logs_modal_header_content', '', $log, $this->payment_id );

        ob_start(); ?>

            <?php if ( !empty( $header ) ) : ?>
                <div class="pms-modal__fullrow">
                    <?php echo $header; ?>
                </div>
            <?php elseif ( !empty( $data['message'] ) ) : ?>
                <div class="pms-modal__fullrow">
                    <h2>Payment Gateway Message</h2>

                    <?php echo ( !empty( $data['message'] ) ? $data['message'] : '' ) ?>
                </div>
            <?php endif; ?>

            <div class="pms-modal__fullrow">
                <?php if ( !empty( $data['desc'] ) ) : ?>
                    <h2><?php echo ucwords( $data['desc'] ); ?></h2>
                <?php endif; ?>

                <ul class="pms-modal__wrapped-list">
                    <?php foreach( $data['data'] as $key => $value ) : ?>
                        <li><span>
                            <strong><?php echo $key; ?></strong> => <?php echo $value ?>
                        </span></li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php

        $output .= ob_get_clean();

        return $output;

    }

    /**
     * Determines if the modal link should be displayed or not.
     *
     * @param  array   $data   Extra data saved alongside the error message
     * @return bool
     */
    private function show_modal_link( $data ) {

        //PayPal API Request with an error response
        if ( !empty( $data['request'] ) && !empty( $data['response'] ) )
            return true;
        //Payment Updated
        else if ( !empty( $data['new_data'] ) && !empty( $data['old_data'] ) )
            return true;
        //Default data structure
        else if ( !empty( $data['data'] ) )
            return true;

        return false;

    }

    private function get_payment_type_human_format( $type ) {

        $payment_types = pms_get_payment_types();

        return isset( $payment_types[$type] ) ? $payment_types[$type] : $type;

    }

}
